<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'current_password' => ['required', 'string'],
            'new_username' => ['nullable', 'string', 'min:3', 'max:50', 'unique:users,username,' . $userId],
            'new_password' => ['nullable', 'string', 'min:5'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Password saat ini wajib diisi untuk verifikasi keamanan.',
            'new_username.unique' => 'Username sudah digunakan oleh akun lain.',
            'new_password.min' => 'Password baru minimal 5 karakter.',
        ];
    }
}
