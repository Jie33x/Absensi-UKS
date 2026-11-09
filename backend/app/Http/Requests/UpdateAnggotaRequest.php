<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAnggotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => ['sometimes', 'required', 'string', 'max:255'],
            'angkatan' => ['sometimes', 'required', 'in:X,XI'],
            'kelas_jurusan' => ['sometimes', 'required', 'string', 'max:100'],
            'divisi' => ['sometimes', 'required', 'in:PRIMA,LITBANG,PDD,KEBERSIHAN'],
        ];
    }
}
