<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAbsensiRequest;
use App\Http\Requests\UpdateAbsensiRequest;
use App\Http\Resources\AbsensiResource;
use App\Models\Absensi;
use App\Models\Anggota;
use App\Services\SupabaseStorageService;
use App\Services\TimezoneService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AbsensiController extends Controller
{
    protected SupabaseStorageService $storageService;

    public function __construct(SupabaseStorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    /**
     * Submit absensi kehadiran baru dengan tanda tangan digital
     */
    public function store(StoreAbsensiRequest $request): JsonResponse
    {
        $anggota = Anggota::findOrFail($request->anggota_id);

        $tanggal = TimezoneService::getTanggal();
        $waktu = TimezoneService::getWaktu();

        // Validasi duplikasi absensi pada hari yang sama (Zona Asia/Makassar)
        $existing = Absensi::where('anggota_id', $anggota->id)
            ->where('tanggal', $tanggal)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'error' => "Pemberitahuan: {$anggota->nama} sudah melakukan absensi hari ini pada pukul {$existing->waktu} WITA.",
                'sudahAbsen' => true,
                'waktu' => (string) $existing->waktu,
                'tanggal' => (string) $existing->tanggal,
            ], 409);
        }

        // Simpan tanda tangan ke Supabase Storage (hanya simpan path storage di database)
        $storagePath = $this->storageService->uploadSignature($request->tanda_tangan, $anggota->id);

        // Catat absensi beserta snapshot keanggotaan
        $absensi = Absensi::create([
            'anggota_id' => $anggota->id,
            'nama_snapshot' => $anggota->nama,
            'angkatan_snapshot' => $anggota->angkatan,
            'kelas_jurusan_snapshot' => $anggota->kelas_jurusan,
            'divisi_snapshot' => $anggota->divisi,
            'tanggal' => $tanggal,
            'waktu' => $waktu,
            'tanda_tangan' => $storagePath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absensi berhasil dicatat!',
            'absensi' => [
                'id' => $absensi->id,
                'nama' => $absensi->nama_snapshot,
                'angkatan' => $absensi->angkatan_snapshot,
                'kelas_jurusan' => $absensi->kelas_jurusan_snapshot,
                'divisi' => $absensi->divisi_snapshot,
                'tanggal' => $tanggal,
                'waktu' => $waktu,
            ],
        ], 201);
    }

    /**
     * Periksa apakah anggota sudah absensi hari ini
     */
    public function checkToday(Request $request): JsonResponse
    {
        $anggotaId = $request->query('anggota_id');
        if (!$anggotaId || !is_numeric($anggotaId)) {
            return response()->json(['error' => 'Parameter anggota_id tidak valid.'], 400);
        }

        $tanggal = TimezoneService::getTanggal();

        $existing = Absensi::where('anggota_id', (int) $anggotaId)
            ->where('tanggal', $tanggal)
            ->first();

        if ($existing) {
            return response()->json([
                'alreadyAttended' => true,
                'waktu' => (string) $existing->waktu,
                'tanggal' => (string) $existing->tanggal,
                'message' => "Anggota ini sudah melakukan absensi hari ini pada pukul {$existing->waktu} WITA.",
            ]);
        }

        return response()->json(['alreadyAttended' => false]);
    }

    /**
     * Dapatkan daftar rekap absensi dengan filter (admin)
     */
    public function index(Request $request)
    {
        $query = Absensi::query();

        // Filter Bulan (1-12)
        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal', (int) $request->bulan);
        }

        // Filter Tahun
        if ($request->filled('tahun')) {
            $query->whereYear('tanggal', (int) $request->tahun);
        }

        // Filter Angkatan
        if ($request->filled('angkatan')) {
            $query->where('angkatan_snapshot', $request->angkatan);
        }

        // Filter Kelas / Jurusan
        if ($request->filled('kelas_jurusan')) {
            $query->where('kelas_jurusan_snapshot', $request->kelas_jurusan);
        }

        // Filter Divisi
        if ($request->filled('divisi')) {
            $query->where('divisi_snapshot', $request->divisi);
        }

        // Search by member name
        if ($request->filled('search')) {
            $query->where('nama_snapshot', 'ILIKE', '%' . trim($request->search) . '%');
        }

        // Sorting
        $sortBy = in_array($request->sort_by, ['tanggal', 'waktu', 'nama']) ? $request->sort_by : 'tanggal';
        $sortOrder = strtolower($request->sort_order) === 'asc' ? 'asc' : 'desc';

        if ($sortBy === 'nama') {
            $query->orderBy('nama_snapshot', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder)->orderBy('waktu', 'desc');
        }

        $records = $query->get();

        return AbsensiResource::collection($records);
    }

    /**
     * Tampilkan detail record absensi
     */
    public function show(int $id): JsonResponse
    {
        $absensi = Absensi::findOrFail($id);
        return response()->json(new AbsensiResource($absensi));
    }

    /**
     * Koreksi data absensi (admin)
     */
    public function update(UpdateAbsensiRequest $request, int $id): JsonResponse
    {
        $absensi = Absensi::findOrFail($id);

        $absensi->update($request->only([
            'nama_snapshot',
            'angkatan_snapshot',
            'kelas_jurusan_snapshot',
            'divisi_snapshot',
            'tanggal',
            'waktu',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Data absensi berhasil diperbarui.',
            'absensi' => new AbsensiResource($absensi),
        ]);
    }

    /**
     * Hapus data absensi (admin)
     */
    public function destroy(int $id): JsonResponse
    {
        $absensi = Absensi::findOrFail($id);
        $nama = $absensi->nama_snapshot;

        // Hapus tanda tangan dari storage jika ada
        if (!empty($absensi->tanda_tangan)) {
            $this->storageService->deleteSignature($absensi->tanda_tangan);
        }

        $absensi->delete();

        return response()->json([
            'success' => true,
            'message' => "Data absensi {$nama} berhasil dihapus.",
        ]);
    }
}
