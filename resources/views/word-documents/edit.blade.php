@extends('layouts.app')
@section('title', 'Edit Dokumen Word')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('word-documents.index') }}">Dokumen Word</a>
    </li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ route('word-documents.update', $wordDocument->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        @include('word-documents.form')

                        <div class="text-end mt-3 mb-1">
                            <a href="{{ route('word-documents.index') }}" class="btn btn-light">
                                <i class="ri-arrow-left-line me-1"></i>Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-3-line me-1"></i>Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection