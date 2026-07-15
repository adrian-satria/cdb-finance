<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode_project' => 'required|string|exists:project,kode_project',
            'kode_budget' => 'required|string|max:50|unique:master_budget,kode_budget',
            'nama_budget' => 'required|string|max:255',
            'alokasi_dana' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'kode_project.required' => 'Project wajib dipilih.',
            'kode_budget.required' => 'Kode budget wajib diisi.',
            'kode_budget.unique' => 'Kode budget sudah terdaftar.',
            'nama_budget.required' => 'Nama budget wajib diisi.',
            'alokasi_dana.required' => 'Alokasi dana wajib diisi.',
            'alokasi_dana.min' => 'Alokasi dana minimal Rp 0.',
        ];
    }
}
