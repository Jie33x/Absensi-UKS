<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\AdminProfileResource;
use App\Models\AdminProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminProfileController extends Controller
{
    /**
     * Dapatkan profil admin yang sedang login
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        $profile = AdminProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['username' => $user->username]
        );

        return response()->json([
            'id' => $profile->id,
            'user_id' => $user->id,
            'username' => $user->username,
            'created_at' => $user->created_at?->toISOString(),
        ]);
    }

    /**
     * Perbarui username atau password admin
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        // Verifikasi password saat ini
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'error' => 'Password saat ini salah.',
            ], 400);
        }

        if ($request->filled('new_username')) {
            $newUsername = trim($request->new_username);
            $user->username = $newUsername;

            AdminProfile::updateOrCreate(
                ['user_id' => $user->id],
                ['username' => $newUsername]
            );
        }

        if ($request->filled('new_password')) {
            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        // Buat token baru setelah perubahan kredensial
        $user->tokens()->delete();
        $newToken = $user->createToken('admin_token', ['admin'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Profil dan keamanan admin berhasil diperbarui.',
            'token' => $newToken,
            'admin' => [
                'id' => $user->id,
                'username' => $user->username,
            ],
        ]);
    }

    /**
     * Ganti password khusus
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:5'],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'new_password.min' => 'Password baru minimal 5 karakter.',
        ]);

        /** @var User $user */
        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'error' => 'Password saat ini tidak sesuai.',
            ], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Kata sandi admin berhasil diperbarui.',
        ]);
    }
}
