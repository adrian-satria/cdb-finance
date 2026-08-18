<?php

namespace App\Http\Controllers;

use App\Models\LpjUangMuka;
use App\Models\PengajuanUangMuka;
use App\Models\ReimburseLpj;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use App\Services\WorkflowService;
use App\Support\RoleHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReimburseController extends Controller
{
    protected WorkflowService $workflowService;

    public function __construct(protected NotificationService $notificationService)
    {
        $this->workflowService = new WorkflowService('reimburse_workflow');
    }

    public function index(Request $request)
    {
        $role = session('role');
        $userArea = session('kode_area');
        $userProject = session('kode_project');

        $query = ReimburseLpj::query()->with('pengajuan');

        if (RoleHelper::isStaffArea($role)) {
            $query->whereHas('pengajuan', fn($q) => $q->where('kode_area', $userArea));
        } elseif (RoleHelper::isProjectScoped($role) && $userProject && $userProject !== 'all') {
            $query->whereHas('pengajuan', fn($q) => $q->where('kode_project', $userProject));
        }

        if ($request->filled('status')) {
            $query->where('status_reimburse', $request->status);
        }

        $list = $query->orderByDesc('created_at')->paginate(15);

        return view('uangmuka.reimburse_index', compact('list'));
    }

    public function show(string $noReimburse)
    {
        $reimburse = ReimburseLpj::with(['pengajuan', 'lpj'])->where('no_reimburse', $noReimburse)->firstOrFail();
        $canAct = session('role') === $reimburse->posisi_saat_ini
            && in_array($reimburse->status_reimburse, ['Pending', 'Approved', 'Revisi'], true);

        return view('uangmuka.reimburse_show', compact('reimburse', 'canAct'));
    }

    public function validasi(Request $request)
    {
        $request->validate(['no_reimburse' => 'required|string', 'aksi' => 'required|in:approve,revise,reject', 'alasan' => 'nullable|string|max:255']);

        $currentRole = session('role');

        try {
            return DB::transaction(function () use ($request, $currentRole) {
                $reimburse = ReimburseLpj::where('no_reimburse', $request->no_reimburse)->lockForUpdate()->first();
                if (! $reimburse) {
                    throw new \Exception('Data Reimburse tidak ditemukan!');
                }

                $um = $reimburse->pengajuan;
                if (! RoleHelper::canAccessSpp($currentRole, session('kode_area'), session('kode_project'), $um)) {
                    AuditLogService::log('UNAUTHORIZED_VALIDASI_REIMBURSE', "User coba validasi Reimburse {$reimburse->no_reimburse} di luar scope");
                    throw new \Exception('AKSES DITOLAK: Reimburse berada di luar kewenangan Anda.');
                }
                if (! $this->workflowService->validateWorkflowTransition($reimburse, $currentRole)) {
                    throw new \Exception("WORKFLOW VIOLATION: Reimburse berada di otoritas [{$reimburse->posisi_saat_ini}].");
                }

                $result = $this->workflowService->getNextPosition($currentRole, $um->kode_project, (string) $reimburse->total_nominal, $request->aksi);

                $reimburse->update([
                    'status_reimburse' => $result['status'],
                    'posisi_saat_ini' => $result['next'],
                ]);

                if (in_array($request->aksi, ['approve', 'revise'])) {
                    if ($result['next'] === 'MANAGER_KEUANGAN') {
                        $this->notificationService->sendToUser($um->id_pengaju, NotificationService::TYPE_REVISED,
                            "Revisi Reimburse - {$reimburse->no_reimburse}", "Reimburse {$reimburse->no_reimburse} direvisi oleh {$currentRole}.", 'REIMBURSE', $reimburse->no_reimburse);
                    } else {
                        $this->notificationService->sendToRoleScoped($result['next'], $um->kode_area, $um->kode_project,
                            NotificationService::TYPE_PENDING_APPROVAL, "Perlu Persetujuan - {$reimburse->no_reimburse}",
                            "Reimburse {$reimburse->no_reimburse} disetujui {$currentRole}. Menunggu {$result['next']}.", 'REIMBURSE', $reimburse->no_reimburse);
                    }
                } elseif ($request->aksi === 'reject') {
                    $this->notificationService->sendToUser($um->id_pengaju, NotificationService::TYPE_REJECTED,
                        "Ditolak - {$reimburse->no_reimburse}", "Reimburse {$reimburse->no_reimburse} ditolak. Alasan: ".($request->alasan ?? '-'), 'REIMBURSE', $reimburse->no_reimburse);
                }

                $msg = $request->aksi === 'approve' ? 'Reimburse berhasil disetujui.' : ($request->aksi === 'revise' ? 'Reimburse dikembalikan untuk revisi.' : 'Reimburse ditolak.');

                return redirect('/uang-muka/reimburse/'.$reimburse->no_reimburse)->with('success', $msg);
            });
        } catch (\Exception $e) {
            Log::error('Reimburse validasi failed', ['error' => $e->getMessage()]);

            return redirect()->back()->with('error', $e->getMessage() ?: 'Gagal memproses Reimburse.');
        }
    }
}
