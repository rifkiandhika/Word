<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\WordDocument;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WordDocumentController extends Controller
{
    private const EDITABLE = ['docx'];

    private const KEEP_VERSIONS = 5;

    private const UPLOAD_DIR = 'word-documents';

    public function index()
    {
        $documents = WordDocument::latest()->get();
        return view('word-documents.index', compact('documents'));
    }

    public function create()
    {
        return view('word-documents.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul'     => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'document'  => 'required|file|mimes:doc,docx|max:20480', // max 20MB
        ]);

        $file = $request->file('document');

        $docSize  = $file->getSize();
        $origName = $file->getClientOriginalName();
        $ext      = strtolower($file->getClientOriginalExtension());
        $fileName = time() . '_' . Str::random(10) . '.' . $ext;

        $file->move(public_path(self::UPLOAD_DIR), $fileName);

        WordDocument::create([
            'judul'         => $request->judul,
            'deskripsi'     => $request->deskripsi,
            'document_path' => self::UPLOAD_DIR . '/' . $fileName,
            'document_name' => $origName,
            'document_type' => $ext,
            'document_size' => $docSize,
        ]);

        return redirect()
            ->route('word-documents.index')
            ->with('success', 'Dokumen Word berhasil ditambahkan!');
    }

    public function edit(WordDocument $wordDocument)
    {
        return view('word-documents.edit', compact('wordDocument'));
    }

    public function update(Request $request, WordDocument $wordDocument)
    {
        $request->validate([
            'judul'     => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'document'  => 'nullable|file|mimes:doc,docx|max:20480',
        ]);

        $data = [
            'judul'     => $request->judul,
            'deskripsi' => $request->deskripsi,
        ];

        if ($request->hasFile('document')) {
            $file = $request->file('document');

            $docSize  = $file->getSize();
            $origName = $file->getClientOriginalName();
            $ext      = strtolower($file->getClientOriginalExtension());
            $fileName = time() . '_' . Str::random(10) . '.' . $ext;

            $file->move(public_path(self::UPLOAD_DIR), $fileName);

            $oldPath = public_path($wordDocument->document_path);
            if (File::exists($oldPath)) {
                File::delete($oldPath);
            }

            $data['document_path'] = self::UPLOAD_DIR . '/' . $fileName;
            $data['document_name'] = $origName;
            $data['document_type'] = $ext;
            $data['document_size'] = $docSize;
        }

        $wordDocument->update($data);

        return redirect()
            ->route('word-documents.index')
            ->with('success', 'Dokumen Word berhasil diupdate!');
    }

    public function destroy(WordDocument $wordDocument)
    {
        $fullPath = public_path($wordDocument->document_path);
        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }

        $wordDocument->delete();

        return redirect()
            ->route('word-documents.index')
            ->with('success', 'Dokumen Word berhasil dihapus!');
    }

    public function download(WordDocument $wordDocument)
    {
        $path = public_path($wordDocument->document_path);
        abort_unless(File::exists($path), 404, 'File dokumen tidak ditemukan.');

        return response()->download($path, $wordDocument->document_name, [
            'Cache-Control' => 'no-store, must-revalidate',
        ]);
    }

    public function editor(WordDocument $wordDocument)
    {
        $ext  = strtolower($wordDocument->document_type);
        $path = public_path($wordDocument->document_path);

        abort_unless(in_array($ext, self::EDITABLE, true), 403, 'Hanya file .docx yang bisa diedit di browser.');
        abort_unless(File::exists($path), 404, 'File dokumen tidak ditemukan di server.');
        abort_if(blank(config('onlyoffice.secret')), 500, 'ONLYOFFICE_JWT_SECRET belum diatur di .env.');

        $user = auth()->user();

        $config = [
            'document' => [
                'fileType'    => $ext,
                'key'         => $this->documentKey($wordDocument),
                'title'       => $wordDocument->document_name,
                'url'         => $this->appUrl('/' . ltrim($wordDocument->document_path, '/')),
                'permissions' => ['edit' => true, 'download' => true, 'print' => true],
            ],
            'documentType' => 'word',
            'editorConfig' => [
                'mode'        => 'edit',
                'lang'        => 'id',
                'callbackUrl' => $this->appUrl(route('word-documents.callback', $wordDocument, false)),
                'user'        => [
                    'id'   => (string) (auth()->id() ?? 'guest'),
                    'name' => $user->name ?? 'User',
                ],
                'customization' => [
                    'forcesave' => true, 
                    'autosave'  => true,
                ],
            ],
        ];

        $config['token'] = JWT::encode($config, config('onlyoffice.secret'), 'HS256');

        Cache::put($this->activeFlag($wordDocument), true, now()->addHours(12));

        return response()
            ->view('word-documents.editor', [
                'document'  => $wordDocument,
                'config'    => $config,
                'serverUrl' => rtrim(config('onlyoffice.server_url'), '/'),
                'statusUrl' => route('word-documents.editor-status', $wordDocument),
            ])
            ->header('Cache-Control', 'no-store');
    }

    public function editorStatus(WordDocument $wordDocument)
    {
        return response()
            ->json(['active' => Cache::has($this->activeFlag($wordDocument))])
            ->header('Cache-Control', 'no-store');
    }

    public function callback(Request $request, WordDocument $wordDocument)
    {
        $secret = config('onlyoffice.secret');

        if (blank($secret)) {
            Log::error('WordDocument OnlyOffice callback: ONLYOFFICE_JWT_SECRET kosong.');
            return response()->json(['error' => 1], 500);
        }

        try {
            $token = $request->bearerToken() ?: $request->input('token');
            if (!$token) {
                return response()->json(['error' => 1, 'message' => 'Token tidak ada'], 403);
            }

            $decoded = json_decode(json_encode(JWT::decode($token, new Key($secret, 'HS256'))), true);
            $data    = $decoded['payload'] ?? $decoded; 
        } catch (\Throwable $e) {
            Log::warning('WordDocument OnlyOffice callback: JWT tidak valid — ' . $e->getMessage());
            return response()->json(['error' => 1, 'message' => 'Token tidak valid'], 403);
        }

        $status = (int) ($data['status'] ?? 0);

        if (in_array($status, [2, 6], true)) {
            $url = $data['url'] ?? null;
            if (!$url) {
                return response()->json(['error' => 1, 'message' => 'URL file tidak ada']);
            }

            try {
                $response = Http::timeout(120)->get($this->toInternalServerUrl($url));
            } catch (\Throwable $e) {
                Log::error('WordDocument OnlyOffice callback: gagal mengunduh hasil edit — ' . $e->getMessage());
                return response()->json(['error' => 1]); // Document Server akan mencoba lagi
            }

            if (!$response->successful() || $response->body() === '') {
                Log::error('WordDocument OnlyOffice callback: respons unduhan tidak valid (HTTP ' . $response->status() . ').');
                return response()->json(['error' => 1]);
            }

            try {
                $this->overwriteFile($wordDocument, $response->body());
            } catch (\Throwable $e) {
                Log::error('WordDocument OnlyOffice callback: gagal menulis file — ' . $e->getMessage());
                return response()->json(['error' => 1]);
            }

            if ($status === 2) {
                Cache::forget($this->keyCache($wordDocument));
                Cache::forget($this->backupFlag($wordDocument));
                Cache::forget($this->activeFlag($wordDocument));
            }
        } elseif ($status === 4) {
            Cache::forget($this->activeFlag($wordDocument));
        } elseif (in_array($status, [3, 7], true)) {
            Log::error('WordDocument OnlyOffice: Document Server gagal menyimpan dokumen #' . $wordDocument->id . ' (status ' . $status . ').');
        }

        return response()->json(['error' => 0]);
    }

    private function documentKey(WordDocument $document): string
    {
        return Cache::rememberForever(
            $this->keyCache($document),
            fn () => 'word-' . $document->id . '-' . Str::random(20)
        );
    }

    private function keyCache(WordDocument $d): string   { return 'onlyoffice.word.key.'    . $d->id; }
    private function activeFlag(WordDocument $d): string { return 'onlyoffice.word.active.' . $d->id; }
    private function backupFlag(WordDocument $d): string { return 'onlyoffice.word.backup.' . $d->id; }

    private function appUrl(string $path): string
    {
        $base = rtrim(config('onlyoffice.app_url') ?: url('/'), '/');

        return $base . '/' . ltrim($path, '/');
    }

    private function toInternalServerUrl(string $url): string
    {
        $internal = config('onlyoffice.server_url_internal');
        if (blank($internal)) {
            return $url;
        }

        $p = parse_url($url);

        return rtrim($internal, '/')
            . ($p['path'] ?? '')
            . (isset($p['query']) ? '?' . $p['query'] : '');
    }

    private function overwriteFile(WordDocument $document, string $content): void
    {
        $path = public_path($document->document_path);

        if (!Cache::has($this->backupFlag($document))) {
            $this->backupCurrentFile($document);
            Cache::forever($this->backupFlag($document), true);
        }

        $tmp = $path . '.tmp';
        File::put($tmp, $content, true);
        File::move($tmp, $path);

        clearstatcache(true, $path);
        $document->update(['document_size' => filesize($path)]);
    }

    private function backupCurrentFile(WordDocument $document): void
    {
        $path = public_path($document->document_path);
        if (!File::exists($path)) {
            return;
        }

        $dir = storage_path('app/word-document-versions/' . $document->id);
        File::ensureDirectoryExists($dir);
        File::copy($path, $dir . '/' . now()->format('Ymd_His') . '.' . $document->document_type);

        collect(File::files($dir))
            ->sortByDesc(fn ($f) => $f->getFilename())
            ->slice(self::KEEP_VERSIONS)
            ->each(fn ($f) => File::delete($f->getPathname()));
    }
}