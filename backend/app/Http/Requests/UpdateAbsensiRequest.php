<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAbsensiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_snapshot' => ['sometimes', 'required', 'string'],
            'angkatan_snapshot' => ['sometimes', 'required', 'string'],
            'kelas_jurusan_snapshot' => ['sometimes', 'required', 'string'],
            'divisi_snapshot' => ['sometimes', 'required', 'string'],
            'tanggal' => ['sometimes', 'required', 'date_format:Y-m-d'],
            'waktu' => ['sometimes', 'required', 'string'],
        ];
    }
}
