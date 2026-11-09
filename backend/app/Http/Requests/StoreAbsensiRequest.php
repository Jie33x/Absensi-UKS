<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAbsensiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'anggota_id' => ['required', 'integer', 'exists:anggota,id'],
            'tanda_tangan' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'anggota_id.required' => 'Anggota belum dipilih.',
            'anggota_id.exists' => 'Data anggota tidak ditemukan dalam database.',
            'tanda_tangan.required' => 'Tanda tangan digital wajib diisi sebelum absensi dikirim.',
        ];
    }
}
