<?php

namespace App\Http\Requests\Spp;

use Illuminate\Foundation\Http\FormRequest;

class DisburseSppRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array(session('role'), ['KASIR_PUSAT', 'ADMIN'], true);
    }

    public function rules(): array
    {
        return [
            'no_surat' => 'required|string|exists:surat_permintaan,no_surat',
        ];
    }

    public function messages(): array
    {
        return [
            'no_surat.required' => 'Nomor SPP wajib diisi.',
            'no_surat.exists' => 'SPP dengan nomor tersebut tidak ditemukan.',
        ];
    }
}
