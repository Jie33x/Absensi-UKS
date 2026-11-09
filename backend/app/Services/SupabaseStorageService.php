<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Exception;

class SupabaseStorageService
{
    protected string $supabaseUrl;
    protected string $serviceKey;
    protected string $bucket;
    protected int $signedUrlExpiresIn;

    public function __construct()
    {
        $this->supabaseUrl = rtrim(config('filesystems.disks.supabase.url', env('SUPABASE_URL', '')), '/');
        $this->serviceKey = config('filesystems.disks.supabase.key', env('SUPABASE_SERVICE_ROLE_KEY', ''));
        $this->bucket = config('filesystems.disks.supabase.bucket', env('SUPABASE_STORAGE_BUCKET', 'pmr-signatures'));
        $this->signedUrlExpiresIn = (int) config('filesystems.disks.supabase.signed_url_expires_in', env('SUPABASE_SIGNED_URL_EXPIRES_IN', 3600));
    }

    /**
     * Simpan file tanda tangan (dari string data:image/png;base64,...) ke bucket privat Supabase
     *
     * @param string $base64Data
     * @param int|string $anggotaId
     * @return string Path relatif file yang disimpan (e.g. "signatures/2026-11/1_1731110400_abc123.png")
     * @throws Exception
     */
    public function uploadSignature(string $base64Data, int|string $anggotaId): string
    {
        // 1. Validasi format data URL
        if (!preg_match('/^data:image\/(png|jpeg|webp);base64,/', $base64Data, $matches)) {
            throw new Exception('Format berkas tanda tangan harus berupa gambar (PNG/JPEG/WEBP).');
        }

        $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
        $binaryData = base64_decode(substr($base64Data, strpos($base64Data, ',') + 1));

        // 2. Validasi ukuran (maksimum 2MB)
        $sizeInBytes = strlen($binaryData);
        if ($sizeInBytes === 0 || $sizeInBytes > 2 * 1024 * 1024) {
            throw new Exception('Ukuran file tanda tangan tidak boleh kosong dan tidak boleh melebihi 2MB.');
        }

        // 3. Nama file terorganisasi berdasarkan tahun-bulan/anggota_timestamp
        $yearMonth = TimezoneService::now()->format('Y-m');
        $fileName = "signatures/{$yearMonth}/{$anggotaId}_" . time() . '_' . Str::random(8) . ".{$extension}";

        // Jika Supabase belum terkonfigurasi (misal di local dev tanpa service role key), simpan ke storage local
        if (empty($this->supabaseUrl) || empty($this->serviceKey)) {
            $localPath = storage_path("app/private/{$fileName}");
            if (!file_exists(dirname($localPath))) {
                mkdir(dirname($localPath), 0755, true);
            }
            file_put_contents($localPath, $binaryData);
            return $fileName;
        }

        // 4. Kirim ke Supabase Storage REST API
        $endpoint = "{$this->supabaseUrl}/storage/v1/object/{$this->bucket}/{$fileName}";
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->serviceKey}",
            'apiKey' => $this->serviceKey,
            'Content-Type' => "image/{$extension}",
            'x-upsert' => 'true',
        ])->withBody($binaryData, "image/{$extension}")->post($endpoint);

        if (!$response->successful()) {
            throw new Exception('Gagal mengunggah tanda tangan ke Supabase Storage: ' . $response->body());
        }

        return $fileName;
    }

    /**
     * Dapatkan signed URL sementara untuk bucket private
     *
     * @param string $path
     * @return string
     */
    public function getSignedUrl(string $path): string
    {
        if (empty($path)) {
            return '';
        }

        if (empty($this->supabaseUrl) || empty($this->serviceKey)) {
            // Local fallback path
            return "/api/signatures/preview?path=" . urlencode($path);
        }

        $endpoint = "{$this->supabaseUrl}/storage/v1/object/sign/{$this->bucket}/{$path}";
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->serviceKey}",
            'apiKey' => $this->serviceKey,
            'Content-Type' => 'application/json',
        ])->post($endpoint, [
            'expiresIn' => $this->signedUrlExpiresIn,
        ]);

        if ($response->successful()) {
            $signedUrl = $response->json('signedURL');
            return "{$this->supabaseUrl}/storage/v1{$signedUrl}";
        }

        return "{$this->supabaseUrl}/storage/v1/object/{$this->bucket}/{$path}";
    }

    /**
     * Hapus berkas tanda tangan dari Supabase Storage
     */
    public function deleteSignature(string $path): bool
    {
        if (empty($path) || empty($this->supabaseUrl) || empty($this->serviceKey)) {
            return false;
        }

        $endpoint = "{$this->supabaseUrl}/storage/v1/object/{$this->bucket}";
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->serviceKey}",
            'apiKey' => $this->serviceKey,
            'Content-Type' => 'application/json',
        ])->delete($endpoint, [
            'prefixes' => [$path],
        ]);

        return $response->successful();
    }
}
