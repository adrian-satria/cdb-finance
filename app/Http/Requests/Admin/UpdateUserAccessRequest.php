<?php

namespace App\Http\Requests\Admin;

use App\Http\Controllers\Admin\UserAccessController;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserAccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'akses' => 'required|array|min:1',
            'akses.*.role' => ['required', 'string', Rule::in(UserAccessController::VALID_ROLES)],
            'akses.*.jabatan' => 'required|string|max:255',
            'akses.*.kode_area' => 'nullable|string|max:20',
            'akses.*.kode_project' => 'nullable|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'akses.required' => 'Minimal 1 akses harus diisi.',
            'akses.*.role.required' => 'Role wajib dipilih.',
            'akses.*.role.in' => 'Role tidak valid.',
            'akses.*.jabatan.required' => 'Jabatan wajib diisi.',
        ];
    }
}
