<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AbsensiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'anggota_id' => $this->anggota_id,
            'nama_snapshot' => $this->nama_snapshot,
            'angkatan_snapshot' => $this->angkatan_snapshot,
            'kelas_jurusan_snapshot' => $this->kelas_jurusan_snapshot,
            'divisi_snapshot' => $this->divisi_snapshot,
            'tanggal' => $this->tanggal instanceof \Carbon\Carbon ? $this->tanggal->format('Y-m-d') : (string) $this->tanggal,
            'waktu' => (string) $this->waktu,
            'tanda_tangan' => $this->tanda_tangan_url ?: $this->tanda_tangan,
            'storage_path' => $this->tanda_tangan,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
