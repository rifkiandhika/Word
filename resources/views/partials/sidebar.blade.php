<!-- ========== App Menu (Sidebar) ========== -->
<div class="app-menu navbar-menu">

    <div class="dropdown sidebar-user m-1 rounded">
        <button type="button" class="btn material-shadow-none" id="sidebar-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="d-flex align-items-center gap-2">
                <img class="rounded header-profile-user" src="{{ asset('assets/images/users/avatar-1.jpg') }}" alt="Header Avatar">
                <span class="text-start">
                    <span class="d-block fw-medium sidebar-user-name-text">{{ auth()->check() ? auth()->user()->name : 'Anna Adame' }}</span>
                    <span class="d-block fs-14 sidebar-user-name-sub-text"><i class="ri ri-circle-fill fs-10 text-success align-baseline"></i> <span class="align-middle">Online</span></span>
                </span>
            </span>
        </button>
        <div class="dropdown-menu dropdown-menu-end">
            <h6 class="dropdown-header">Welcome {{ auth()->check() ? auth()->user()->name : 'Anna' }}!</h6>
            <a class="dropdown-item" href="#"><i class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Profile</span></a>
            <a class="dropdown-item" href="#"><i class="mdi mdi-cog-outline text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Settings</span></a>
        </div>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">
            <div id="two-column-menu"></div>
            <ul class="navbar-nav" id="navbar-nav">
                {{-- <li class="menu-title"><span data-key="t-menu">Menu</span></li> --}}

                {{-- ===================== DOKUMEN ===================== --}}
                <li class="menu-title"><i class="ri-more-fill"></i> <span data-key="t-dokumen">Dokumen</span></li>

                <li class="nav-item">
                    <a href="{{ route('word-documents.index') }}" class="nav-link menu-link">
                        <i class="ri-file-word-2-line"></i> <span data-key="t-dokumen-word">Dokumen Word</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="sidebar-background"></div>
</div>
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>