@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">

    {{-- Judul --}}
    <div class="col-md-12 mb-3">
        <label class="form-label fw-semibold">
            Judul <span class="text-danger">*</span>
        </label>
        <input type="text"
               name="judul"
               class="form-control"
               value="{{ old('judul', $wordDocument->judul ?? '') }}"
               placeholder="Contoh: Word Konversi"
               required>
    </div>

    {{-- Deskripsi --}}
    <div class="col-md-12 mb-3">
        <label class="form-label fw-semibold">
            Deskripsi (Opsional)
        </label>
        <textarea name="deskripsi"
                  class="form-control"
                  rows="3"
                  placeholder="Tambahkan deskripsi dokumen...">{{ old('deskripsi', $wordDocument->deskripsi ?? '') }}</textarea>
    </div>

    {{-- ============================== --}}
    {{-- UPLOAD DOKUMEN WORD --}}
    {{-- ============================== --}}
    <div class="col-md-12">
        <label class="form-label fw-semibold">
            File Dokumen Word
            <span class="badge bg-secondary ms-1" style="font-size:11px;">DOC / DOCX</span>
            @unless(isset($wordDocument))
                <span class="text-danger">*</span>
            @endunless
            <span class="text-muted fw-normal">(Maks. 20MB)</span>
        </label>
    </div>

    <div id="dropAreaDoc" class="upload-box upload-box--doc position-relative">
        <i class="ri-upload-cloud-2-line upload-icon upload-icon--doc"></i>
        <p class="fw-semibold mb-1">Klik atau Drag & Drop Dokumen</p>
        <small class="text-muted">DOC, DOCX (Maks. 20MB)</small>
    </div>

    <input type="file"
           name="document"
           id="documentInput"
           accept=".doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
           hidden
           @unless(isset($wordDocument)) required @endunless>

    <div id="documentPreview" class="col-md-12 mt-3"></div>

    {{-- ============================== --}}
    {{-- FILE SAAT INI (Edit Mode) --}}
    {{-- ============================== --}}
    @isset($wordDocument)
        <div class="col-md-12 mt-4">
            <label class="fw-bold mb-2">File Saat Ini</label>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center gap-3 py-3">
                            <i class="ri-file-word-2-line text-primary" style="font-size:32px;"></i>
                            <div class="flex-grow-1 overflow-hidden">
                                <p class="mb-0 fw-semibold text-truncate" title="{{ $wordDocument->document_name }}">
                                    {{ $wordDocument->document_name }}
                                </p>
                                <small class="text-muted text-uppercase">
                                    {{ $wordDocument->document_type }} · {{ $wordDocument->formatted_size }}
                                </small>
                            </div>
                            <div class="d-flex gap-2 flex-shrink-0">
                                @if($wordDocument->is_editable)
                                    <a href="{{ route('word-documents.editor', $wordDocument->id) }}"
                                       target="_blank"
                                       rel="noopener"
                                       class="btn btn-outline-success btn-sm"
                                       title="Edit di tab baru">
                                        <i class="ri-edit-line me-1"></i>Edit
                                    </a>
                                @endif
                                <a href="{{ route('word-documents.download', $wordDocument->id) }}"
                                   class="btn btn-outline-primary btn-sm"
                                   title="Download">
                                    <i class="ri-download-2-line"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <small class="text-muted">Upload file baru di atas untuk mengganti file ini.</small>
                </div>
            </div>
        </div>
    @endisset

</div>

<style>
.upload-box {
    border: 2px dashed #d0d5dd;
    border-radius: 12px;
    padding: 40px;
    text-align: center;
    cursor: pointer;
    transition: 0.3s;
    background: #f9fafb;
}
.upload-box:hover,
.upload-box--doc:hover {
    border-color: #f59e0b;
    background: #fffbeb;
}
.upload-icon--doc { color: #f59e0b; }
.upload-icon {
    font-size: 40px;
    margin-bottom: 10px;
    display: block;
}
.doc-preview-item {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 10px 14px;
    margin-bottom: 10px;
    position: relative;
}
.doc-preview-item .doc-icon { font-size: 28px; flex-shrink: 0; color: #2563eb; }
.doc-preview-item .doc-info { flex: 1; overflow: hidden; }
.doc-preview-item .doc-name {
    font-weight: 600; font-size: 13px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.doc-preview-item .doc-meta { font-size: 11px; color: #9ca3af; text-transform: uppercase; }
.doc-preview-item .remove-doc {
    background: rgba(220, 53, 69, 0.9);
    border: none; color: white; border-radius: 50%;
    width: 26px; height: 26px; font-size: 16px; line-height: 1;
    cursor: pointer; flex-shrink: 0;
}
</style>

@push('scripts')
<script>
$(document).ready(function () {

    const dropAreaDoc = $('#dropAreaDoc');
    const docInput    = $('#documentInput');

    dropAreaDoc.on('click', () => docInput.click());

    dropAreaDoc.on('dragover', function (e) {
        e.preventDefault();
        $(this).addClass('border-warning');
    });
    dropAreaDoc.on('dragleave', function () {
        $(this).removeClass('border-warning');
    });
    dropAreaDoc.on('drop', function (e) {
        e.preventDefault();
        $(this).removeClass('border-warning');
        const files = e.originalEvent.dataTransfer.files;
        if (files.length) {
            docInput[0].files = files;
            renderDocPreview(files[0]);
        }
    });

    docInput.on('change', function () {
        if (this.files.length) {
            renderDocPreview(this.files[0]);
        }
    });

    function renderDocPreview(file) {
        $('#documentPreview').empty();

        const ext = file.name.split('.').pop().toLowerCase();
        const sizeLabel = file.size > 1024 * 1024
            ? (file.size / 1024 / 1024).toFixed(1) + ' MB'
            : (file.size / 1024).toFixed(1) + ' KB';

        $('#documentPreview').append(`
            <div class="doc-preview-item">
                <i class="ri-file-word-2-line doc-icon"></i>
                <div class="doc-info">
                    <div class="doc-name" title="${file.name}">${file.name}</div>
                    <div class="doc-meta">${ext.toUpperCase()} · ${sizeLabel}</div>
                </div>
                <button type="button" class="remove-doc">×</button>
            </div>
        `);
    }

    $(document).on('click', '.remove-doc', function () {
        docInput.val('');
        $('#documentPreview').empty();
    });

});
</script>
@endpush