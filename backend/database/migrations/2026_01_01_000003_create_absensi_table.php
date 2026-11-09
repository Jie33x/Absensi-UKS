<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anggota_id')->nullable()->constrained('anggota')->nullOnDelete();
            $table->string('nama_snapshot');
            $table->string('angkatan_snapshot', 10);
            $table->string('kelas_jurusan_snapshot');
            $table->string('divisi_snapshot');
            $table->date('tanggal'); // YYYY-MM-DD
            $table->time('waktu');   // HH:mm:ss
            $table->string('tanda_tangan', 500); // Supabase storage path (e.g. signatures/2026-11/1_1731110400.png)
            $table->timestamps();

            // Unique constraint: anggota tidak dapat melakukan absensi lebih dari satu kali pada tanggal yang sama
            $table->unique(['anggota_id', 'tanggal'], 'unique_anggota_harian');

            // Indexes for filtering
            $table->index('tanggal');
            $table->index('angkatan_snapshot');
            $table->index('divisi_snapshot');
            $table->index('nama_snapshot');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
