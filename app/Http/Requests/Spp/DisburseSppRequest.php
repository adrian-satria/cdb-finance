<?php

namespace App\Http\Requests\Spp;

use App\Support\RoleHelper;
use Illuminate\Foundation\Http\FormRequest;

class DisburseSppRequest extends FormRequest
{
    public function authorize(): bool
    {
        return RoleHelper::isGlobal(session('role'));
    }

    public function rules(): array
    {
        return [
            'no_surat' => 'required|string|exists:surat_permintaan,no_surat',
            'biaya_admin' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'no_surat.required' => 'Nomor SPP wajib diisi.',
            'no_surat.exists' => 'SPP dengan nomor tersebut tidak ditemukan.',
            'biaya_admin.numeric' => 'Biaya admin harus berupa angka.',
            'biaya_admin.min' => 'Biaya admin tidak boleh negatif.',
        ];
    }
}
