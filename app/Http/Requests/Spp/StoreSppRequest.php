<?php

namespace App\Http\Requests\Spp;

use Illuminate\Foundation\Http\FormRequest;

class StoreSppRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'no_surat' => 'nullable|string',
            'tanggal' => 'required|date',
            'kode_project' => 'required|string|exists:project,kode_project',
            'sumber_dana' => 'nullable|string',
            'bank_tujuan' => 'nullable|string',
            'no_rekening_tujuan' => 'nullable|string',
            'nama_rekening_tujuan' => 'nullable|string',
            'jenis_permintaan' => 'nullable|string|in:SPP,UM',
            'kode_area' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.kode_budget' => 'required|string',
            'items.*.keterangan' => 'nullable|string|max:255',
            'items.*.jumlah' => 'required|numeric|min:0.01|max:999999999999.99',
            'file_lampiran' => 'nullable|array|max:5',
            'file_lampiran.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal.required' => 'Tanggal SPP wajib diisi.',
            'kode_project.required' => 'Project wajib dipilih.',
            'kode_project.exists' => 'Project yang dipilih tidak valid.',
            'items.required' => 'Minimal 1 item rincian anggaran wajib diisi.',
            'items.min' => 'Minimal 1 item rincian anggaran wajib diisi.',
            'items.*.kode_budget.required' => 'Kode budget wajib dipilih untuk setiap item.',
            'items.*.jumlah.required' => 'Jumlah nominal wajib diisi untuk setiap item.',
            'items.*.jumlah.min' => 'Nominal minimal Rp 0,01.',
            'file_lampiran.max' => 'Maksimal 5 file lampiran.',
            'file_lampiran.*.mimes' => 'File lampiran harus PDF, JPG, JPEG, atau PNG.',
            'file_lampiran.*.max' => 'Ukuran file lampiran maksimal 5 MB.',
        ];
    }
}
