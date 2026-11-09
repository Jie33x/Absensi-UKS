<?php

namespace Database\Seeders;

use App\Models\Anggota;
use Illuminate\Database\Seeder;

class AnggotaSeeder extends Seeder
{
    public function run(): void
    {
        $anggotaList = [
            ['nama' => 'Salma Rahmawati', 'angkatan' => 'XI', 'kelas_jurusan' => 'PPLG 1', 'divisi' => 'LITBANG'],
            ['nama' => 'Ahmad Fauzan', 'angkatan' => 'XI', 'kelas_jurusan' => 'DKV 1', 'divisi' => 'PDD'],
            ['nama' => 'Nabila Putri Ayu', 'angkatan' => 'XI', 'kelas_jurusan' => 'PPLG 2', 'divisi' => 'PRIMA'],
            ['nama' => 'Bintang Pratama', 'angkatan' => 'X', 'kelas_jurusan' => 'TJKT 1', 'divisi' => 'KEBERSIHAN'],
            ['nama' => 'Dewi Lestari', 'angkatan' => 'X', 'kelas_jurusan' => 'ANIMASI', 'divisi' => 'PDD'],
            ['nama' => 'Rian Hidayat', 'angkatan' => 'XI', 'kelas_jurusan' => 'TJKT 2', 'divisi' => 'PRIMA'],
            ['nama' => 'Siti Nurhaliza', 'angkatan' => 'X', 'kelas_jurusan' => 'PPLG 1', 'divisi' => 'LITBANG'],
            ['nama' => 'Fajar Maulana', 'angkatan' => 'XI', 'kelas_jurusan' => 'DKV 2', 'divisi' => 'PDD'],
        ];

        foreach ($anggotaList as $data) {
            Anggota::firstOrCreate(
                ['nama' => $data['nama'], 'angkatan' => $data['angkatan']],
                $data
            );
        }
    }
}
