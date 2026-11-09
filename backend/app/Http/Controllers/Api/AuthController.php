<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Autentikasi Admin dan penerbitan token Laravel Sanctum
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'error' => 'Username atau password salah.',
            ], 401);
        }

        // Hapus token lama jika ada
        $user->tokens()->delete();

        // Buat personal access token Sanctum (berlaku 12 jam)
        $token = $user->createToken('admin_token', ['admin'])->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
            ],
            'admin' => [
                'id' => $user->id,
                'username' => $user->username,
            ],
        ]);
    }

    /**
     * Cabut token Sanctum dan akhiri sesi admin
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil keluar (logout). Sesi telah diakhiri.',
        ]);
    }

    /**
     * Dapatkan data user admin yang sedang terotentikasi
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'username' => $user->username,
            'created_at' => $user->created_at?->toISOString(),
        ]);
    }
}
