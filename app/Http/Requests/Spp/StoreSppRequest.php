<?php

namespace App\Http\Requests\Spp;

use App\Support\RoleHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class StoreSppRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxKb = (int) \App\Models\SystemSetting::getValue('max_file_size', 5120);

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
            'file_lampiran.*' => 'file|mimes:pdf,jpg,jpeg,png|max:'.$maxKb,
        ];
    }

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function ($validator) {
            $this->validateScope($validator);
        });
    }

    /**
     * F2 — server-side scope check: role tidak bisa mengajukan SPP
     * ke project/area di luar kewenangan session-nya.
     */
    private function validateScope(\Illuminate\Validation\Validator $validator): void
    {
        $role = session('role');
        $userArea = session('kode_area');
        $userProject = session('kode_project');
        $project = (string) $this->input('kode_project', '');
        $area = (string) $this->input('kode_area', '');

        if (RoleHelper::isGlobal($role)) {
            return;
        }

        // MANAGER_KEUANGAN dengan project 'all' = unrestricted
        if ($role === 'MANAGER_KEUANGAN' && ($userProject === 'all' || ! $userProject)) {
            return;
        }

        if (RoleHelper::isStaffArea($role)) {
            if ($area !== '' && $area !== $userArea) {
                $validator->errors()->add('kode_area', 'Area pengajuan di luar kewenangan unit kerja Anda.');
            }

            // Restriksi project hanya jika area user terdaftar di project_area
            $areaProjects = DB::table('project_area')->where('kode_area', $userArea)->pluck('kode_project')->all();
            if ($areaProjects && ! in_array($project, $areaProjects, true)) {
                $validator->errors()->add('kode_project', 'Project pengajuan bukan kewenangan area Anda.');
            }

            return;
        }

        // Project-scoped roles
        if ($userProject && $userProject !== 'all' && $project !== '' && $project !== $userProject) {
            $validator->errors()->add('kode_project', 'Project pengajuan di luar kewenangan Anda.');
        }

        $projectAreas = DB::table('project_area')->where('kode_project', $project)->pluck('kode_area')->all();
        if ($area !== '' && $projectAreas && ! in_array($area, $projectAreas, true)) {
            $validator->errors()->add('kode_area', 'Area pengajuan bukan bagian dari project Anda.');
        }
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
