<?php

namespace App\Http\Controllers;

use App\Models\PengajuanUangMuka;
use App\Models\Project;
use App\Models\Area;
use App\Models\SumberDana;
use App\Services\AuditLogService;
use App\Services\BudgetValidationService;
use App\Services\FileUploadService;
use App\Services\NotificationService;
use App\Services\SequenceNumberService;
use App\Services\WorkflowService;
use App\Support\RoleHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdvanceController extends Controller
{
    protected WorkflowService $workflowService;

    public function __construct(
        protected BudgetValidationService $budgetService,
        protected SequenceNumberService $numberService,
        protected FileUploadService $fileService,
        protected NotificationService $notificationService
    ) {
        $this->workflowService = new WorkflowService('um_workflow');
    }

    public function index(Request $request)
    {
        $role = session('role');
        $userArea = session('kode_area');
        $userProject = session('kode_project');

        $query = PengajuanUangMuka::query();

        if (RoleHelper::isStaffArea($role)) {
            $query->where('kode_area', $userArea);
        } elseif (RoleHelper::isProjectScoped($role) && $userProject && $userProject !== 'all') {
            $query->where('kode_project', $userProject);
        }

        if ($request->filled('status')) {
            $query->where('status_um', $request->status);
        }

        if ($request->query('register') === 'outstanding') {
            $query->where('status_um', 'Cair')->where('sisa_lpj', '>', 0);
        }

        $list = $query->orderByDesc('created_at')->paginate(15);

        return view('uangmuka.index', compact('list'));
    }

    public function create()
    {
        $userProject = session('kode_project');
        $role = session('role');

        $nomor_baru = $this->numberService->generate(date('Y-m-d'), $userProject ?? 'XX', 'UM');

        $projects = Cache::remember('ref_projects', 3600, fn() => Project::orderBy('kode_project')->get());
        $sumber_danas = Cache::remember('ref_sumber_danas', 3600, fn() => SumberDana::all());
        $noBudgetProjects = config('spp_workflow.no_budget_projects', []);

        if (RoleHelper::isStaffArea($role)) {
            $areas = Area::where('kode_area', session('kode_area'))->get();
            $isAreaLocked = true;
        } elseif (RoleHelper::isGlobal($role) && (! $userProject || $userProject === 'all')) {
            $areas = Cache::remember('ref_areas', 3600, fn() => Area::all());
            $isAreaLocked = false;
        } else {
            $areas = Area::whereIn('kode_area', function ($q) use ($userProject) {
                $q->select('kode_area')->from('project_area')->where('kode_project', $userProject);
            })->get();
            $isAreaLocked = false;
        }

        return view('uangmuka.tambah', compact('nomor_baru', 'projects', 'areas', 'sumber_danas', 'noBudgetProjects', 'isAreaLocked'));
    }

    public function store(Request $request)
    {
        $role = session('role');
        $userArea = session('kode_area');
        $userProject = session('kode_project');

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'kode_project' => 'required|string',
            'kode_area' => 'required|string',
            'keterangan' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.kode_budget' => 'required|string',
            'items.*.jumlah' => 'required|numeric|min:1',
            'items.*.keterangan' => 'nullable|string|max:255',
        ]);

        if (RoleHelper::isStaffArea($role) && $validated['kode_area'] !== $userArea) {
            return redirect()->back()->withInput()->with('error', 'Area tidak sesuai dengan kewenangan Anda.');
        }
        if (RoleHelper::isProjectScoped($role) && $userProject && $userProject !== 'all' && $validated['kode_project'] !== $userProject) {
            return redirect()->back()->withInput()->with('error', 'Project tidak sesuai dengan kewenangan Anda.');
        }

        $idPengaju = Auth::user()->id_user;

        $overdue = PengajuanUangMuka::where('id_pengaju', $idPengaju)
            ->where('status_um', 'Cair')
            ->where('sisa_lpj', '>', 0)
            ->whereNotNull('tanggal_jatuh_tempo')
            ->where('tanggal_jatuh_tempo', '<', now()->format('Y-m-d'))
            ->exists();

        if ($overdue) {
            return redirect()->back()->withInput()->with('error', 'Anda memiliki UM yang jatuh tempo dan belum LPJ. Tidak dapat mengajukan UM baru.');
        }

        $budgetItems = array_map(fn($i) => ['kode_budget' => $i['kode_budget'], 'jumlah' => $i['jumlah']], $validated['items']);
        $validation = $this->budgetService->lockAndValidateMultiple($budgetItems, $validated['kode_area'], $validated['kode_project']);

        if (! $validation->isValid) {
            return redirect()->back()->withInput()->with('error', $validation->errorMessage);
        }

        $totalNominal = $validation->details['total_nominal'];
        $noAju = $this->numberService->generate($validated['tanggal'], $validated['kode_project'], 'UM');
        $posisiAwal = $this->workflowService->determineInitialPosition($validated['kode_project']);

        try {
            DB::transaction(function () use ($validated, $noAju, $totalNominal, $posisiAwal, $idPengaju) {
                PengajuanUangMuka::create([
                    'no_aju' => $noAju,
                    'tanggal' => $validated['tanggal'],
                    'id_pengaju' => $idPengaju,
                    'kode_project' => $validated['kode_project'],
                    'kode_area' => $validated['kode_area'],
                    'keterangan' => $validated['keterangan'] ?? null,
                    'total_nominal' => $totalNominal,
                    'sisa_lpj' => 0,
                    'status_um' => 'Pending',
                    'posisi_saat_ini' => $posisiAwal,
                ]);

                foreach ($validated['items'] as $item) {
                    DB::table('pengajuan_uang_muka_detail')->insert([
                        'no_aju' => $noAju,
                        'keterangan' => $item['keterangan'] ?? '-',
                        'kode_budget' => $item['kode_budget'],
                        'nominal' => $item['jumlah'],
                    ]);
                }
            });

            if ($request->hasFile('file_lampiran')) {
                $this->fileService->uploadUangMukaAttachments($request->file('file_lampiran'), $noAju, 'MAKER');
            }

            $this->notificationService->sendToRoleScoped(
                $posisiAwal, $validated['kode_area'], $validated['kode_project'],
                NotificationService::TYPE_NEW_SPP,
                "UM Baru - {$noAju}",
                "Uang muka senilai Rp ".number_format($totalNominal, 0, ',', '.')." dari project {$validated['kode_project']} membutuhkan persetujuan Anda.",
                'UM', $noAju
            );

            return redirect('/uang-muka')->with('success', "Pengajuan uang muka {$noAju} berhasil dikirim!");
        } catch (\Exception $e) {
            Log::error('UM store failed', ['error' => $e->getMessage()]);

            return redirect()->back()->withInput()->with('error', $e->getMessage() ?: 'Gagal menyimpan pengajuan uang muka.');
        }
    }

    public function show(string $noAju)
    {
        $um = PengajuanUangMuka::with(['details', 'files', 'pengaju'])->where('no_aju', $noAju)->firstOrFail();

        $canAct = session('role') === $um->posisi_saat_ini
            && in_array($um->status_um, ['Pending', 'Approved', 'Revisi'], true);

        return view('uangmuka.show', compact('um', 'canAct'));
    }

    public function validasi(Request $request)
    {
        $request->validate([
            'no_aju' => 'required|string',
            'aksi' => 'required|in:approve,revise,reject',
            'alasan' => 'nullable|string|max:255',
        ]);

        $currentRole = session('role');
        $userArea = session('kode_area');
        $userProject = session('kode_project');

        try {
            return DB::transaction(function () use ($request, $currentRole, $userArea, $userProject) {
                $um = PengajuanUangMuka::where('no_aju', $request->no_aju)->lockForUpdate()->first();

                if (! $um) {
                    throw new \Exception('Data pengajuan uang muka tidak ditemukan!');
                }

                if (! RoleHelper::canAccessSpp($currentRole, $userArea, $userProject, $um)) {
                    AuditLogService::log('UNAUTHORIZED_VALIDASI_UM', "User coba validasi UM {$request->no_aju} di luar scope");
                    throw new \Exception('AKSES DITOLAK: UM ini berada di luar kewenangan Anda.');
                }

                if (! $this->workflowService->validateWorkflowTransition($um, $currentRole)) {
                    throw new \Exception("WORKFLOW VIOLATION: UM ini sedang berada dalam otoritas [{$um->posisi_saat_ini}], bukan di meja kerja Anda!");
                }

                $result = $this->workflowService->getNextPosition(
                    $currentRole, $um->kode_project, (string) $um->total_nominal, $request->aksi
                );

                $um->update([
                    'status_um' => $result['status'],
                    'posisi_saat_ini' => $result['next'],
                    'keterangan_checker' => $request->alasan,
                ]);

                if (in_array($request->aksi, ['approve', 'revise'])) {
                    if ($result['next'] === 'MAKER') {
                        $this->notificationService->sendToUser(
                            $um->id_pengaju, NotificationService::TYPE_REVISED,
                            "Revisi UM - {$um->no_aju}",
                            "UM {$um->no_aju} direvisi oleh {$currentRole}. Silakan perbaiki dan kirim ulang.",
                            'UM', $um->no_aju
                        );
                    } else {
                        $this->notificationService->sendToRoleScoped(
                            $result['next'], $um->kode_area, $um->kode_project,
                            NotificationService::TYPE_PENDING_APPROVAL,
                            "Perlu Persetujuan - {$um->no_aju}",
                            "UM {$um->no_aju} telah disetujui oleh {$currentRole}. Sekarang menunggu persetujuan {$result['next']}.",
                            'UM', $um->no_aju
                        );
                    }
                } elseif ($request->aksi === 'reject') {
                    $this->notificationService->sendToUser(
                        $um->id_pengaju, NotificationService::TYPE_REJECTED,
                        "Ditolak - {$um->no_aju}",
                        "UM {$um->no_aju} ditolak oleh {$currentRole}. Alasan: ".($request->alasan ?? '-'),
                        'UM', $um->no_aju
                    );
                }

                $msg = $request->aksi === 'approve' ? 'UM berhasil disetujui.' : ($request->aksi === 'revise' ? 'UM dikembalikan untuk revisi.' : 'UM ditolak.');

                return redirect('/uang-muka/'.$um->no_aju)->with('success', $msg);
            });
        } catch (\Exception $e) {
            Log::error('UM validasi failed', ['error' => $e->getMessage()]);

            return redirect()->back()->with('error', $e->getMessage() ?: 'Gagal memproses UM.');
        }
    }

    public function cairkan(Request $request)
    {
        $request->validate(['no_aju' => 'required|string']);

        $currentRole = session('role');
        $userArea = session('kode_area');
        $userProject = session('kode_project');

        try {
            return DB::transaction(function () use ($request, $currentRole, $userArea, $userProject) {
                $um = PengajuanUangMuka::where('no_aju', $request->no_aju)->lockForUpdate()->first();

                if (! $um) {
                    throw new \Exception('Data pengajuan uang muka tidak ditemukan!');
                }

                if (! RoleHelper::canAccessSpp($currentRole, $userArea, $userProject, $um)) {
                    throw new \Exception('AKSES DITOLAK: UM ini berada di luar kewenangan Anda.');
                }

                if ($currentRole !== 'KASIR_PUSAT' || $um->status_um !== 'Approved') {
                    throw new \Exception('Hanya KASIR_PUSAT yang dapat mencairkan UM yang sudah Approved.');
                }

                // Ceiling check only (budget NOT deducted until LPJ approved).
                $items = $um->details->map(fn($d) => ['kode_budget' => $d->kode_budget, 'jumlah' => $d->nominal])->toArray();
                $validation = $this->budgetService->lockAndValidateMultiple($items, $um->kode_area, $um->kode_project);
                if (! $validation->isValid) {
                    throw new \Exception($validation->errorMessage);
                }

                $deadline = now()->addDays((int) config('um_workflow.lpj_deadline_days', 14))->format('Y-m-d');

                $um->update([
                    'status_um' => 'Cair',
                    'posisi_saat_ini' => 'FINISH',
                    'tanggal_jatuh_tempo' => $deadline,
                    'sisa_lpj' => $um->total_nominal,
                ]);

                $this->notificationService->sendToUser(
                    $um->id_pengaju, NotificationService::TYPE_DISBURSED,
                    "Cair - {$um->no_aju}",
                    "UM {$um->no_aju} telah cair. Sisa LPJ Rp ".number_format($um->total_nominal, 0, ',', '.').". Batas LPJ: {$deadline}.",
                    'UM', $um->no_aju
                );

                return redirect('/uang-muka/'.$um->no_aju)->with('success', "UM {$um->no_aju} berhasil dicairkan. Piutang LPJ dibuat.");
            });
        } catch (\Exception $e) {
            Log::error('UM cairkan failed', ['error' => $e->getMessage()]);

            return redirect()->back()->with('error', $e->getMessage() ?: 'Gagal mencairkan UM.');
        }
    }

    public function downloadFile(string $namaFile)
    {
        $file = $this->fileService->downloadUangMukaFile(
            $namaFile, session('role'), session('kode_area'), session('kode_project')
        );

        if (! $file) {
            abort(404);
        }

        return $file;
    }
}
