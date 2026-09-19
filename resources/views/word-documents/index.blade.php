@extends('layouts.app')

@section('title', 'Dokumen Word')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dokumen Word</li>
@endsection

@section('content')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ri-checkbox-circle-line me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ri-error-warning-line me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-xl-6 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="avatar-sm rounded-circle bg-primary bg-gradient d-flex align-items-center justify-content-center me-3">
                        <i class="ri-file-word-2-line fs-4 text-white"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Total Dokumen</p>
                        <h4 class="mb-0">{{ $documents->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="avatar-sm rounded-circle bg-success bg-gradient d-flex align-items-center justify-content-center me-3">
                        <i class="ri-hard-drive-2-line fs-4 text-white"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Total Ukuran</p>
                        <h4 class="mb-0">{{ number_format($documents->sum('document_size') / 1024 / 1024, 1) }} MB</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0"><i class="ri-file-word-2-line me-2"></i>Daftar Dokumen Word</h5>
            <a href="{{ route('word-documents.create') }}" class="btn btn-primary btn-sm">
                <i class="ri-add-circle-line me-1"></i>Tambah Dokumen
            </a>
        </div>

        <div class="card-body border-bottom bg-light">
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label small fw-bold">Cari</label>
                    <input type="text" id="searchBox" class="form-control form-control-sm" placeholder="Cari judul atau deskripsi...">
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                @if($documents->count() > 0)
                    <table class="table table-hover table-striped align-middle" id="wordDocumentTable">
                        <thead class="table-light">
                            <tr>
                                <th width="50">No</th>
                                <th>Judul</th>
                                <th>Deskripsi</th>
                                <th width="90" class="text-center">Tipe</th>
                                <th width="100" class="text-center">Ukuran</th>
                                <th width="170" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $x => $doc)
                                <tr>
                                    <td class="text-center">{{ $x + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="ri-file-word-2-line text-primary fs-4 me-2"></i>
                                            <strong>{{ $doc->judul }}</strong>
                                        </div>
                                    </td>
                                    <td>{{ Str::limit($doc->deskripsi, 50) ?? '-' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary text-uppercase">{{ $doc->document_type }}</span>
                                    </td>
                                    <td class="text-center">{{ $doc->formatted_size }}</td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            @if($doc->is_editable)
                                                <a href="{{ route('word-documents.editor', $doc->id) }}"
                                                   target="_blank" rel="noopener"
                                                   class="btn btn-sm btn-outline-success" title="Edit di browser">
                                                    <i class="ri-edit-line"></i>
                                                </a>
                                            @endif
                                            <a href="{{ route('word-documents.download', $doc->id) }}"
                                               class="btn btn-sm btn-outline-primary" title="Download">
                                                <i class="ri-download-2-line"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                <i class="ri-more-2-fill"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                                <li>
                                                    <a href="{{ route('word-documents.edit', $doc->id) }}" class="dropdown-item">
                                                        <i class="ri-pencil-line me-2"></i>Edit Data
                                                    </a>
                                                </li>
                                                <li>
                                                    <form action="{{ route('word-documents.destroy', $doc->id) }}" method="POST" class="delete-confirm">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="ri-delete-bin-6-line me-2"></i>Hapus
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="text-center py-5">
                        <i class="ri-file-word-2-line ri-3x text-muted mb-3 d-block"></i>
                        <p class="text-muted mb-2">Belum ada dokumen Word</p>
                        <a href="{{ route('word-documents.create') }}" class="btn btn-primary btn-sm">
                            <i class="ri-add-circle-line me-1"></i>Tambah Dokumen
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection

@push('styles')
{{-- app.blade.php belum me-load DataTables secara global, jadi di-load di sini saja --}}
<link href="{{ asset('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
<style>
    .badge { font-weight: 500; padding: 0.4em 0.8em; font-size: 0.85rem; }
    .avatar-sm { width: 3rem; height: 3rem; display: flex; align-items: center; justify-content: center; }
    .table-hover tbody tr:hover { background-color: #f8f9fa; }
    .card { border-radius: 0.5rem; }
    .dropdown-item i { width: 20px; }
    .table td { vertical-align: middle; }
    .table { border: 1px solid #ced4da !important; }
</style>
@endpush

@push('scripts')
{{-- app.blade.php belum me-load DataTables & SweetAlert2 secara global, jadi di-load di sini saja.
     Sesuaikan path di bawah kalau lokasi lib di project berbeda. --}}
<script src="{{ asset('assets/libs/datatables.net/js/dataTables.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
$(document).ready(function() {
    let table = $('#wordDocumentTable').DataTable({
        dom: 'rtip',
        pageLength: 10,
        responsive: true,
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' }
    });

    $('#searchBox').on('keyup', function() {
        table.search(this.value).draw();
    });

    $(document).on('submit', '.delete-confirm', function(e) {
        e.preventDefault();
        var form = this;
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Dokumen ini akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) form.submit();
        });
    });

    setTimeout(() => $('.alert').fadeOut('slow'), 4000);
});
</script>
@endpush