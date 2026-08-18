<?php

namespace App\Http\Controllers;

use App\Models\LpjUangMuka;
use App\Models\PengajuanUangMuka;
use App\Models\ReimburseLpj;
use App\Services\AuditLogService;
use App\Services\BudgetValidationService;
use App\Services\FileUploadService;
use App\Services\NotificationService;
use App\Services\SequenceNumberService;
use App\Services\WorkflowService;
use App\Support\RoleHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LpjController extends Controller
{
    protected WorkflowService $workflowService;

    public function __construct(
        protected BudgetValidationService $budgetService,
        protected SequenceNumberService $numberService,
        protected FileUploadService $fileService,
        protected NotificationService $notificationService
    ) {
        $this->workflowService = new WorkflowService('lpj_workflow');
    }

    public function index(Request $request)
    {
        $role = session('role');
        $userArea = session('kode_area');
        $userProject = session('kode_project');

        $query = LpjUangMuka::query()->with('pengajuan');

        if (RoleHelper::isStaffArea($role)) {
            $query->whereHas('pengajuan', fn($q) => $q->where('kode_area', $userArea));
        } elseif (RoleHelper::isProjectScoped($role) && $userProject && $userProject !== 'all') {
            $query->whereHas('pengajuan', fn($q) => $q->where('kode_project', $userProject));
        }

        if ($request->filled('status')) {
            $query->where('status_lpj', $request->status);
        }

        $list = $query->orderByDesc('created_at')->paginate(15);

        return view('uangmuka.lpj_index', compact('list'));
    }

    public function create(Request $request)
    {
        $noAju = $request->query('no_aju');
        $um = PengajuanUangMuka::with('details')->where('no_aju', $noAju)->firstOrFail();

        if ($um->status_um !== 'Cair' || $um->sisa_lpj <= 0) {
            return redirect('/uang-muka/'.$noAju)->with('error', 'UM tidak dalam status Cair atau sudah lunas LPJ.');
        }

        return view('uangmuka.lpj_tambah', compact('um'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'no_aju' => 'required|string',
            'tanggal' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.kode_budget' => 'required|string',
            'items.*.jumlah' => 'required|numeric|min:0',
            'items.*.keterangan' => 'nullable|string|max:255',
        ]);

        $um = PengajuanUangMuka::where('no_aju', $validated['no_aju'])->firstOrFail();

        if (! RoleHelper::canAccessSpp(session('role'), session('kode_area'), session('kode_project'), $um)) {
            return redirect()->back()->with('error', 'AKSES DITOLAK: UM berada di luar kewenangan Anda.');
        }
        if ($um->status_um !== 'Cair' || $um->sisa_lpj <= 0) {
            return redirect()->back()->with('error', 'UM tidak dalam status Cair atau sudah lunas LPJ.');
        }

        $budgetItems = array_map(fn($i) => ['kode_budget' => $i['kode_budget'], 'jumlah' => $i['jumlah']], $validated['items']);
        $validation = $this->budgetService->lockAndValidateMultiple($budgetItems, $um->kode_area, $um->kode_project);
        if (! $validation->isValid) {
            return redirect()->back()->withInput()->with('error', $validation->errorMessage);
        }

        $realisasi = $validation->details['total_nominal'];
        $selisih = bcsub((string) $um->total_nominal, $realisasi, 2);
        $noLpj = $this->numberService->generate($validated['tanggal'], $um->kode_project, 'LPJ');

        try {
            DB::transaction(function () use ($validated, $um, $realisasi, $selisih, $noLpj) {
                LpjUangMuka::create([
                    'no_lpj' => $noLpj,
                    'no_aju' => $um->no_aju,
                    'id_pelaksana' => Auth::user()->id_user,
                    'tanggal' => $validated['tanggal'],
                    'total_realisasi' => $realisasi,
                    'selisih' => $selisih,
                    'status_lpj' => 'Pending',
                    'posisi_saat_ini' => 'MAKER',
                ]);

                foreach ($validated['items'] as $item) {
                    DB::table('lpj_uang_muka_detail')->insert([
                        'no_lpj' => $noLpj,
                        'keterangan' => $item['keterangan'] ?? '-',
                        'kode_budget' => $item['kode_budget'],
                        'nominal' => $item['jumlah'],
                    ]);
                }
            });

            if ($request->hasFile('file_lampiran')) {
                $this->fileService->uploadAttachments(
                    $request->file('file_lampiran'), 'lpj_uang_muka_files', 'no_lpj', $noLpj,
                    \App\Services\FileUploadService::UM_STORAGE_PATH, 'MAKER'
                );
            }

            $this->notificationService->sendToRoleScoped(
                'MANAGER_KEUANGAN', $um->kode_area, $um->kode_project,
                NotificationService::TYPE_PENDING_APPROVAL,
                "LPJ Baru - {$noLpj}",
                "LPJ untuk UM {$um->no_aju} menunggu persetujuan Anda.",
                'LPJ', $noLpj
            );

            return redirect('/uang-muka/lpj/'.$noLpj)->with('success', "LPJ {$noLpj} berhasil dikirim.");
        } catch (\Exception $e) {
            Log::error('LPJ store failed', ['error' => $e->getMessage()]);

            return redirect()->back()->withInput()->with('error', $e->getMessage() ?: 'Gagal menyimpan LPJ.');
        }
    }

    public function show(string $noLpj)
    {
        $lpj = LpjUangMuka::with(['details', 'files', 'pengajuan', 'pelaksana'])->where('no_lpj', $noLpj)->firstOrFail();
        $canAct = session('role') === $lpj->posisi_saat_ini
            && in_array($lpj->status_lpj, ['Pending', 'Approved', 'Revisi'], true);

        return view('uangmuka.lpj_show', compact('lpj', 'canAct'));
    }

    public function validasi(Request $request)
    {
        $request->validate(['no_lpj' => 'required|string', 'aksi' => 'required|in:approve,revise,reject', 'alasan' => 'nullable|string|max:255']);

        $currentRole = session('role');

        try {
            return DB::transaction(function () use ($request, $currentRole) {
                $lpj = LpjUangMuka::where('no_lpj', $request->no_lpj)->lockForUpdate()->first();
                if (! $lpj) {
                    throw new \Exception('Data LPJ tidak ditemukan!');
                }

                $um = $lpj->pengajuan;
                if (! RoleHelper::canAccessSpp($currentRole, session('kode_area'), session('kode_project'), $um)) {
                    AuditLogService::log('UNAUTHORIZED_VALIDASI_LPJ', "User coba validasi LPJ {$lpj->no_lpj} di luar scope");
                    throw new \Exception('AKSES DITOLAK: LPJ ini berada di luar kewenangan Anda.');
                }
                if (! $this->workflowService->validateWorkflowTransition($lpj, $currentRole)) {
                    throw new \Exception("WORKFLOW VIOLATION: LPJ berada di otoritas [{$lpj->posisi_saat_ini}].");
                }

                $result = $this->workflowService->getNextPosition($currentRole, $um->kode_project, (string) $lpj->total_realisasi, $request->aksi);

                $lpj->update([
                    'status_lpj' => $result['status'],
                    'posisi_saat_ini' => $result['next'],
                    'keterangan_checker' => $request->alasan,
                ]);

                // Finalisasi saat KASIR_PUSAT menyetujui (langkah terakhir LPJ).
                if ($currentRole === 'KASIR_PUSAT' && $request->aksi === 'approve') {
                    $this->finalizeLpj($lpj, $um);
                } elseif (in_array($request->aksi, ['approve', 'revise'])) {
                    if ($result['next'] === 'MAKER') {
                        $this->notificationService->sendToUser($lpj->id_pelaksana, NotificationService::TYPE_REVISED,
                            "Revisi LPJ - {$lpj->no_lpj}", "LPJ {$lpj->no_lpj} direvisi oleh {$currentRole}.", 'LPJ', $lpj->no_lpj);
                    } else {
                        $this->notificationService->sendToRoleScoped($result['next'], $um->kode_area, $um->kode_project,
                            NotificationService::TYPE_PENDING_APPROVAL, "Perlu Persetujuan - {$lpj->no_lpj}",
                            "LPJ {$lpj->no_lpj} disetujui {$currentRole}. Menunggu {$result['next']}.", 'LPJ', $lpj->no_lpj);
                    }
                } elseif ($request->aksi === 'reject') {
                    $this->notificationService->sendToUser($lpj->id_pelaksana, NotificationService::TYPE_REJECTED,
                        "Ditolak - {$lpj->no_lpj}", "LPJ {$lpj->no_lpj} ditolak. Alasan: ".($request->alasan ?? '-'), 'LPJ', $lpj->no_lpj);
                }

                $msg = $request->aksi === 'approve' ? 'LPJ berhasil disetujui.' : ($request->aksi === 'revise' ? 'LPJ dikembalikan untuk revisi.' : 'LPJ ditolak.');

                return redirect('/uang-muka/lpj/'.$lpj->no_lpj)->with('success', $msg);
            });
        } catch (\Exception $e) {
            Log::error('LPJ validasi failed', ['error' => $e->getMessage()]);

            return redirect()->back()->with('error', $e->getMessage() ?: 'Gagal memproses LPJ.');
        }
    }

    protected function finalizeLpj(LpjUangMuka $lpj, PengajuanUangMuka $um): void
    {
        // Budget dipotong sebesar realisasi LPJ.
        $items = $lpj->details->map(fn($d) => ['kode_budget' => $d->kode_budget, 'nominal' => $d->nominal])->toArray();
        $this->budgetService->updateTerserap($items, $um->kode_area, $um->kode_project, '0');

        // Update sisa piutang UM.
        $um->update(['sisa_lpj' => $lpj->selisih > 0 ? $lpj->selisih : 0]);

        // Selisih negatif -> auto-create Reimburse.
        if (bccomp((string) $lpj->selisih, '0', 2) === -1) {
            $noReimburse = $this->numberService->generate((string) $lpj->tanggal, $um->kode_project, 'REIM');
            $reimburse = ReimburseLpj::create([
                'no_reimburse' => $noReimburse,
                'no_lpj' => $lpj->no_lpj,
                'no_aju' => $um->no_aju,
                'total_nominal' => bcmul((string) $lpj->selisih, '-1', 2),
                'status_reimburse' => 'Pending',
                'posisi_saat_ini' => 'MANAGER_KEUANGAN',
            ]);

            $this->notificationService->sendToRoleScoped('MANAGER_KEUANGAN', $um->kode_area, $um->kode_project,
                NotificationService::TYPE_PENDING_APPROVAL, "Reimburse Baru - {$noReimburse}",
                "Reimburse otomatis dari LPJ {$lpj->no_lpj} menunggu persetujuan Anda.", 'REIMBURSE', $noReimburse);
        }
    }
}
