<?php

use App\Http\Controllers\Api\AbsensiController;
use App\Http\Controllers\Api\AdminProfileController;
use App\Http\Controllers\Api\AnggotaController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RekapController;
use App\Services\TimezoneService;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Absensi Digital PMR Wira (Laravel 12 REST API)
|--------------------------------------------------------------------------
*/

// ==========================================
// 1. PUBLIC ROUTES (Portal Absensi & Login)
// ==========================================
Route::post('/login', [AuthController::class, 'login']);

// Autocomplete pencarian nama anggota
Route::get('/anggota/search', [AnggotaController::class, 'search']);

// Cek apakah anggota sudah absen hari ini (WITA)
Route::get('/absensi/check-today', [AbsensiController::class, 'checkToday']);

// Submit absensi baru (bertanda tangan digital)
Route::post('/absensi', [AbsensiController::class, 'store']);

// Server time ticker resmi (Asia/Makassar, UTC+8)
Route::get('/server-time', function () {
    $now = TimezoneService::now();
    return response()->json([
        'tanggal' => $now->format('Y-m-d'),
        'waktu' => $now->format('H:i:s'),
        'timezone' => 'Asia/Makassar (WITA, UTC+8)',
        'formattedDateIndo' => TimezoneService::getFormattedIndoDate(),
        'year' => (int) $now->format('Y'),
        'month' => (int) $now->format('m'),
        'day' => (int) $now->format('d'),
    ]);
});

// ==========================================
// 2. PROTECTED ROUTES (Laravel Sanctum Admin)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {
    // Sesi & Profil Pengguna Admin
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // CRUD Pengelolaan Anggota
    Route::get('/anggota', [AnggotaController::class, 'index']);
    Route::post('/anggota', [AnggotaController::class, 'store']);
    Route::get('/anggota/{id}', [AnggotaController::class, 'show']);
    Route::put('/anggota/{id}', [AnggotaController::class, 'update']);
    Route::delete('/anggota/{id}', [AnggotaController::class, 'destroy']);

    // Arsip & Manajemen Absensi
    Route::get('/absensi', [AbsensiController::class, 'index']);
    Route::get('/absensi/{id}', [AbsensiController::class, 'show']);
    Route::put('/absensi/{id}', [AbsensiController::class, 'update']);
    Route::delete('/absensi/{id}', [AbsensiController::class, 'destroy']);

    // Rekap & Statistik Presensi
    Route::get('/rekap', [RekapController::class, 'index']);
    Route::get('/rekap/statistik', [RekapController::class, 'statistik']);
    Route::get('/rekap/export', [RekapController::class, 'export']);

    // Pengaturan Akun Admin & Keamanan
    Route::get('/admin/profile', [AdminProfileController::class, 'profile']);
    Route::put('/admin/profile', [AdminProfileController::class, 'update']);
    Route::post('/admin/change-password', [AdminProfileController::class, 'changePassword']);
});
