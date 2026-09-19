<?php

use App\Http\Controllers\backend\WordDocumentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('word-documents.index');
});

Route::prefix('word-documents')->name('word-documents.')->group(function () {
 
    // Editor / download (harus di atas wildcard {wordDocument})
    Route::get('{wordDocument}/editor',        [WordDocumentController::class, 'editor'])->name('editor');
    Route::get('{wordDocument}/editor-status', [WordDocumentController::class, 'editorStatus'])->name('editor-status');
    Route::get('{wordDocument}/download',      [WordDocumentController::class, 'download'])->name('download');
 
    // CRUD
    Route::get('/word',                  [WordDocumentController::class, 'index'])->name('index');
    Route::get('/create',            [WordDocumentController::class, 'create'])->name('create');
    Route::post('/word/store',                 [WordDocumentController::class, 'store'])->name('store');
    Route::get('/{wordDocument}/edit', [WordDocumentController::class, 'edit'])->name('edit');
    Route::put('/{wordDocument}',    [WordDocumentController::class, 'update'])->name('update');
    Route::delete('/{wordDocument}', [WordDocumentController::class, 'destroy'])->name('destroy');
 
});
