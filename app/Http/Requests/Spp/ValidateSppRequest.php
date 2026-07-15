<?php

namespace App\Http\Requests\Spp;

use Illuminate\Foundation\Http\FormRequest;

class ValidateSppRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'aksi' => 'required|in:approve,revise,reject',
            'alasan' => 'nullable|string|max:255',
            'file_checker' => 'nullable|array',
            'file_checker.*' => 'file|mimes:pdf,png,jpg,jpeg|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'aksi.required' => 'Aksi (approve/revise/reject) wajib dipilih.',
            'aksi.in' => 'Aksi harus salah satu: approve, revise, atau reject.',
            'alasan.max' => 'Alasan maksimal 255 karakter.',
            'file_checker.*.mimes' => 'File harus PDF, PNG, JPG, atau JPEG.',
            'file_checker.*.max' => 'Ukuran file maksimal 5 MB.',
        ];
    }
}
