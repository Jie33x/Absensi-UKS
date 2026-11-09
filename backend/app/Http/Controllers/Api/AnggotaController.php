<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnggotaRequest;
use App\Http\Requests\UpdateAnggotaRequest;
use App\Http\Resources\AnggotaResource;
use App\Models\Anggota;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnggotaController extends Controller
{
    /**
     * Tampilkan seluruh anggota dengan filter (halaman admin)
     */
    public function index(Request $request)
    {
        $query = Anggota::query();

        if ($request->filled('search')) {
            $query->where('nama', 'ILIKE', '%' . trim($request->search) . '%');
        }

        if ($request->filled('angkatan')) {
            $query->where('angkatan', $request->angkatan);
        }

        if ($request->filled('divisi')) {
            $query->where('divisi', $request->divisi);
        }

        if ($request->filled('kelas')) {
            $query->where('kelas_jurusan', $request->kelas);
        }

        $anggota = $query->orderBy('nama', 'asc')->get();

        return AnggotaResource::collection($anggota);
    }

    /**
     * Autocomplete search untuk portal absensi publik
     */
    public function search(Request $request): JsonResponse
    {
        $term = trim($request->query('q', ''));

        if (empty($term)) {
            $rows = Anggota::select('id', 'nama', 'angkatan', 'kelas_jurusan', 'divisi')
                ->orderBy('nama', 'asc')
                ->limit(15)
                ->get();
            return response()->json($rows);
        }

        $prefix = "{$term}%";
        $anywhere = "%{$term}%";

        $rows = Anggota::select('id', 'nama', 'angkatan', 'kelas_jurusan', 'divisi')
            ->where('nama', 'ILIKE', $anywhere)
            ->orderByRaw("CASE WHEN nama ILIKE ? THEN 0 ELSE 1 END", [$prefix])
            ->orderBy('nama', 'asc')
            ->limit(20)
            ->get();

        return response()->json($rows);
    }

    /**
     * Simpan data anggota baru
     */
    public function store(StoreAnggotaRequest $request): JsonResponse
    {
        $anggota = Anggota::create([
            'nama' => trim($request->nama),
            'angkatan' => $request->angkatan,
            'kelas_jurusan' => trim($request->kelas_jurusan),
            'divisi' => trim($request->divisi),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Anggota baru berhasil ditambahkan.',
            'anggota' => new AnggotaResource($anggota),
        ], 201);
    }

    /**
     * Tampilkan detail anggota
     */
    public function show(int $id): JsonResponse
    {
        $anggota = Anggota::findOrFail($id);
        return response()->json(new AnggotaResource($anggota));
    }

    /**
     * Perbarui data anggota
     */
    public function update(UpdateAnggotaRequest $request, int $id): JsonResponse
    {
        $anggota = Anggota::findOrFail($id);

        $anggota->update([
            'nama' => trim($request->input('nama', $anggota->nama)),
            'angkatan' => $request->input('angkatan', $anggota->angkatan),
            'kelas_jurusan' => trim($request->input('kelas_jurusan', $anggota->kelas_jurusan)),
            'divisi' => trim($request->input('divisi', $anggota->divisi)),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data anggota berhasil diperbarui.',
            'anggota' => new AnggotaResource($anggota),
        ]);
    }

    /**
     * Hapus anggota (riwayat absensi lampau tetap utuh melalui snapshot)
     */
    public function destroy(int $id): JsonResponse
    {
        $anggota = Anggota::findOrFail($id);
        $nama = $anggota->nama;
        $anggota->delete();

        return response()->json([
            'success' => true,
            'message' => "Data anggota {$nama} berhasil dihapus. Riwayat absensi tetap tersimpan.",
        ]);
    }
}
