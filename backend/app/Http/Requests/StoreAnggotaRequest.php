<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnggotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'angkatan' => ['required', 'in:X,XI'],
            'kelas_jurusan' => ['required', 'string', 'max:100'],
            'divisi' => ['required', 'in:PRIMA,LITBANG,PDD,KEBERSIHAN'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'angkatan.required' => 'Angkatan harus dipilih.',
            'angkatan.in' => 'Angkatan harus dipilih antara Kelas 10 (X) atau Kelas 11 (XI).',
            'kelas_jurusan.required' => 'Kelas/Jurusan wajib dipilih.',
            'divisi.required' => 'Divisi wajib dipilih.',
            'divisi.in' => 'Divisi yang dipilih tidak valid.',
        ];
    }
}
