<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AbsensiResource;
use App\Models\Absensi;
use App\Models\Anggota;
use App\Services\TimezoneService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RekapController extends Controller
{
    /**
     * Dapatkan daftar rekap presensi
     */
    public function index(Request $request)
    {
        return app(AbsensiController::class)->index($request);
    }

    /**
     * Dapatkan statistik ringkas kehadiran
     */
    public function statistik(): JsonResponse
    {
        $today = TimezoneService::getTanggal();
        $currentMonth = substr($today, 0, 7); // YYYY-MM

        $totalAnggota = Anggota::count();
        $absensiHariIni = Absensi::where('tanggal', $today)->count();
        $absensiBulanIni = Absensi::where('tanggal', 'LIKE', "{$currentMonth}%")->count();

        // Kehadiran per divisi
        $divisiStats = Absensi::selectRaw('divisi_snapshot, COUNT(*) as total')
            ->groupBy('divisi_snapshot')
            ->pluck('total', 'divisi_snapshot');

        // Kehadiran per angkatan
        $angkatanStats = Absensi::selectRaw('angkatan_snapshot, COUNT(*) as total')
            ->groupBy('angkatan_snapshot')
            ->pluck('total', 'angkatan_snapshot');

        return response()->json([
            'total_anggota' => $totalAnggota,
            'total_absensi_hari_ini' => $absensiHariIni,
            'total_absensi_bulan_ini' => $absensiBulanIni,
            'kehadiran_per_divisi' => $divisiStats,
            'kehadiran_per_angkatan' => $angkatanStats,
        ]);
    }

    /**
     * Ekspor data absensi ke file CSV/Excel
     */
    public function export(Request $request): StreamedResponse
    {
        $query = Absensi::query();

        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal', (int) $request->bulan);
        }
        if ($request->filled('tahun')) {
            $query->whereYear('tanggal', (int) $request->tahun);
        }
        if ($request->filled('angkatan')) {
            $query->where('angkatan_snapshot', $request->angkatan);
        }
        if ($request->filled('kelas_jurusan')) {
            $query->where('kelas_jurusan_snapshot', $request->kelas_jurusan);
        }
        if ($request->filled('divisi')) {
            $query->where('divisi_snapshot', $request->divisi);
        }
        if ($request->filled('search')) {
            $query->where('nama_snapshot', 'ILIKE', '%' . trim($request->search) . '%');
        }

        $records = $query->orderBy('tanggal', 'desc')->orderBy('waktu', 'desc')->get();
        $dateSuffix = TimezoneService::getTanggal();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"Rekap_Absensi_PMR_{$dateSuffix}.csv\"",
        ];

        $callback = function () use ($records) {
            $file = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel compatibility
            fputs($file, "\xEF\xBB\xBF");

            // Header row
            fputcsv($file, [
                'No',
                'Nama Lengkap',
                'Angkatan',
                'Kelas / Jurusan',
                'Divisi',
                'Tanggal',
                'Waktu (WITA)',
                'Status Tanda Tangan',
            ]);

            foreach ($records as $index => $row) {
                fputcsv($file, [
                    $index + 1,
                    $row->nama_snapshot,
                    $row->angkatan_snapshot === 'XI' ? 'Kelas 11 (XI)' : 'Kelas 10 (X)',
                    "{$row->angkatan_snapshot} {$row->kelas_jurusan_snapshot}",
                    $row->divisi_snapshot,
                    $row->tanggal instanceof \Carbon\Carbon ? $row->tanggal->format('Y-m-d') : $row->tanggal,
                    "{$row->waktu} WITA",
                    !empty($row->tanda_tangan) ? 'Ada (Digital)' : 'Tidak Ada',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
