<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anggota', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->enum('angkatan', ['X', 'XI']);
            $table->string('kelas_jurusan');
            $table->enum('divisi', ['PRIMA', 'LITBANG', 'PDD', 'KEBERSIHAN']);
            $table->timestamps();

            // Indexing for search performance
            $table->index('nama');
            $table->index('angkatan');
            $table->index('divisi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anggota');
    }
};
