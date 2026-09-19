<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('word_documents', function (Blueprint $table) {
            $table->id();
            $table->string('judul');                                   
            $table->text('deskripsi')->nullable();
            $table->string('document_path');                           
            $table->string('document_name');                           
            $table->string('document_type', 10);                       
            $table->unsignedBigInteger('document_size')->nullable();   
            $table->timestamps();
        });

        // Pastikan folder upload ada
        if (!is_dir(public_path('word-documents'))) {
            mkdir(public_path('word-documents'), 0755, true);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('word_documents');
    }
};