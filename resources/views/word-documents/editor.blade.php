
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit — {{ $document->document_name }}</title>
    <style>
        html, body { height: 100%; margin: 0; font-family: Arial, sans-serif; }
        body { display: flex; flex-direction: column; background: #f3f4f6; }
        .bar {
            display: flex; align-items: center; gap: 16px;
            background: #1f2937; color: #fff; padding: 8px 16px; flex-shrink: 0;
        }
        .bar .title { font-weight: 600; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 40%; }
        .bar .hint  { flex: 1; font-size: 12px; color: #9ca3af; }
        .bar button {
            background: #16a34a; color: #fff; border: 0; border-radius: 6px;
            padding: 7px 18px; font-size: 13px; font-weight: 600; cursor: pointer;
        }
        .bar button:disabled { background: #6b7280; cursor: default; }
        #editorHost { flex: 1; min-height: 0; position: relative; }
        .msg {
            position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
            text-align: center; padding: 24px; font-size: 16px; color: #374151; line-height: 1.6;
        }
        .msg.error { color: #b91c1c; }
    </style>
</head>
<body>

    <div class="bar">
        <div class="title" title="{{ $document->document_name }}">{{ $document->document_name }}</div>
        <div class="hint" id="hint">
            Ctrl+S menyimpan langsung ke file. Klik <strong>Selesai</strong> setelah mengedit supaya tahu kapan file sudah aman di-download.
        </div>
        <button type="button" id="btnDone">Selesai</button>
    </div>

    <div id="editorHost">
        <div id="officeEditor"></div>
    </div>

    <script src="{{ $serverUrl }}/web-apps/apps/api/documents/api.js"></script>
    <script>
    (function () {
        var config    = {{ Illuminate\Support\Js::from($config) }};
        var statusUrl = {{ Illuminate\Support\Js::from($statusUrl) }};

        var host = document.getElementById('editorHost');
        var hint = document.getElementById('hint');
        var btn  = document.getElementById('btnDone');

        var editor    = null;
        var finishing = false;

        function showMessage(text, isError) {
            host.innerHTML = '';
            var div = document.createElement('div');
            div.className = 'msg' + (isError ? ' error' : '');
            div.textContent = text;
            host.appendChild(div);
        }

        // Skrip editor gagal dimuat → server ONLYOFFICE tidak terjangkau dari browser
        if (typeof DocsAPI === 'undefined') {
            btn.disabled = true;
            showMessage('Tidak bisa terhubung ke server ONLYOFFICE. Cek ONLYOFFICE_SERVER_URL dan pastikan server dapat diakses dari browser.', true);
            return;
        }

        config.width  = '100%';
        config.height = '100%';
        config.events = {
            onDocumentStateChange: function (e) {
                hint.textContent = e.data
                    ? 'Ada perubahan yang sedang disimpan… Klik Selesai kalau sudah selesai mengedit.'
                    : 'Perubahan tersimpan di editor. Klik Selesai kalau sudah selesai mengedit.';
            },
            onError: function (e) {
                console.error('ONLYOFFICE error:', e && e.data);
            }
        };

        editor = new DocsAPI.DocEditor('officeEditor', config);

        // Server ONLYOFFICE mengirim file hasil edit beberapa detik setelah editor ditutup.
        // Tunggu sampai server Laravel menandai sesi selesai, baru file aman di-download.
        function finish() {
            if (finishing) return;
            finishing = true;
            btn.disabled = true;

            if (editor) { editor.destroyEditor(); editor = null; }
            hint.textContent = 'Menyimpan perubahan…';
            showMessage('Menyimpan perubahan ke file… mohon jangan tutup halaman ini.', false);

            var tries = 0;
            var timer = setInterval(function () {
                tries++;

                fetch(statusUrl, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                    .then(function (r) { return r.json(); })
                    .then(function (s) {
                        if (!s.active) {
                            clearInterval(timer);
                            hint.textContent = 'Tersimpan.';
                            showMessage('Tersimpan. File sudah aman di-download. Halaman ini akan menutup sendiri…', false);
                            setTimeout(function () {
                                window.close();
                                // Kalau browser menolak menutup otomatis
                                setTimeout(function () {
                                    showMessage('Tersimpan. Halaman ini boleh ditutup.', false);
                                }, 500);
                            }, 1200);
                        }
                    })
                    .catch(function () { /* coba lagi di putaran berikutnya */ });

                if (tries >= 20) {
                    clearInterval(timer);
                    hint.textContent = 'Belum terkonfirmasi.';
                    showMessage('Server belum mengonfirmasi penyimpanan. Tunggu sebentar lalu cek isi file sebelum menghapus versi lama.', true);
                }
            }, 3000);
        }

        btn.addEventListener('click', finish);
    })();
    </script>
</body>
</html>