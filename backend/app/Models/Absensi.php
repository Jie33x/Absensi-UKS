<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\SupabaseStorageService;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensi';

    protected $fillable = [
        'anggota_id',
        'nama_snapshot',
        'angkatan_snapshot',
        'kelas_jurusan_snapshot',
        'divisi_snapshot',
        'tanggal',
        'waktu',
        'tanda_tangan',
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
    ];

    public function anggota()
    {
        return $this->belongsTo(Anggota::class, 'anggota_id');
    }

    /**
     * Accessor untuk URL tanda tangan yang ditandatangani dari Supabase Storage
     */
    public function getTandaTanganUrlAttribute(): ?string
    {
        if (empty($this->tanda_tangan)) {
            return null;
        }

        // Jika sudah berbentuk base64 atau absolute URL
        if (str_starts_with($this->tanda_tangan, 'data:image') || str_starts_with($this->tanda_tangan, 'http')) {
            return $this->tanda_tangan;
        }

        /** @var SupabaseStorageService $storage */
        $storage = app(SupabaseStorageService::class);
        return $storage->getSignedUrl($this->tanda_tangan);
    }
}
