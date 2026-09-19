<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Dokumen Word (.doc / .docx) yang berdiri sendiri — tidak ada relasi
 * ke SOP, Gallery, atau modul lain.
 */
class WordDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'judul',
        'deskripsi',
        'document_path',
        'document_name',
        'document_type',
        'document_size',
    ];

    /**
     * Ukuran dokumen dalam format yang mudah dibaca (KB/MB).
     */
    public function getFormattedSizeAttribute(): string
    {
        if (!$this->document_size) {
            return '-';
        }

        $kb = $this->document_size / 1024;
        if ($kb < 1024) {
            return number_format($kb, 1) . ' KB';
        }

        return number_format($kb / 1024, 1) . ' MB';
    }

    /**
     * Hanya .docx yang bisa diedit langsung di browser lewat ONLYOFFICE.
     * .doc lama hanya bisa di-download.
     */
    public function getIsEditableAttribute(): bool
    {
        return strtolower($this->document_type) === 'docx';
    }
}