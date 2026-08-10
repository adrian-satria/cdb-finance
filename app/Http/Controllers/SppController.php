<?php

namespace App\Http\Controllers;

use App\Http\Requests\Spp\DisburseSppRequest;
use App\Http\Requests\Spp\StoreSppRequest;
use App\Http\Requests\Spp\ValidateSppRequest;
use App\Models\Area;
use App\Models\Project;
use App\Models\SumberDana;
use App\Models\SuratPermintaan;
use App\Services\AuditLogService;
use App\Services\BudgetValidationService;
use App\Services\FileUploadService;
use App\Services\NotificationService;
use App\Services\SppNumberGeneratorService;
use App\Services\SppWorkflowService;
use App\Support\RoleHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SppController extends Controller
{
    public function __construct(
        protected SppWorkflowService $workflowService,
        protected BudgetValidationService $budgetService,
        protected SppNumberGeneratorService $numberService,
        protected FileUploadService $fileService,
        protected NotificationService $notificationService
    ) {}

    // =========================================================================
    // Halaman Form Pembuatan SPP Baru (Maker)
    // =========================================================================
    public function create()
    {
        $userProject = session('kode_project');
        $nomor_baru = $this->numberService->generatePreviewNumber(now()->format('Y-m-d'), $userProject ?? 'XX');

        $projects = Cache::remember('ref_projects', 3600, fn() => Project::orderBy('kode_project')->get());
        $role = session('role');
        $userProject = session('kode_project');
        
        $budgetQuery = DB::table('master_budget');
        if (RoleHelper::isProjectScoped($role) && $userProject !== null && $userProject !== '' && $userProject !== 'all') {
            $budgetQuery->where('kode_project', $userProject);
        }
        $master_budgets = $budgetQuery->get()->groupBy('kode_project');
        
        $sumber_danas = Cache::remember('ref_sumber_danas', 3600, fn() => SumberDana::all());
        $noBudgetProjects = config('spp_workflow.no_budget_projects', []);

        if (RoleHelper::isStaffArea($role)) {
            $areas = Area::where('kode_area', session('kode_area'))->get();
            $isAreaLocked = true;
        } elseif (RoleHelper::isGlobal($role) && (!$userProject || $userProject === 'all')) {
            $areas = Cache::remember('ref_areas', 3600, fn() => Area::all());
            $isAreaLocked = false;
        } else {
            $areas = Area::whereIn('kode_area', function ($q) use ($userProject) {
                $q->select('kode_area')->from('project_area')->where('kode_project', $userProject);
            })->get();
            $isAreaLocked = false;
        }

        return view('spp.tambah', compact('nomor_baru', 'projects', 'areas', 'sumber_danas', 'master_budgets', 'noBudgetProjects', 'isAreaLocked'));
    }

    // =========================================================================
    // 1. PROSES SIMPAN DATA SPP BARU (MAKER - BUDGET CEILING LOCK)
    // =========================================================================
    public function store(StoreSppRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $kodeArea = $request->kode_area ?? (session('kode_area') ?? 'PUSAT');

                // 1. Validate Budget Ceiling & Get Total Nominal (per-area)
                $validation = $this->budgetService->lockAndValidateMultiple(
                    $request->items,
                    $kodeArea,
                    $request->kode_project
                );

                if (! $validation->isValid) {
                    throw new \Exception($validation->errorMessage);
                }

                $totalNominal = $validation->details['total_nominal'];

                // 2. Generate SPP Number
                $nomorBaru = $this->numberService->generateNextNumber($request->tanggal, $request->kode_project);

                // 3. Determine Initial Workflow Position
                $posisiAwal = $this->workflowService->determineInitialPosition($request->kode_project);

                // 4. Create SPP Record
                SuratPermintaan::create([
                    'no_surat' => $nomorBaru,
                    'tanggal' => $request->tanggal,
                    'jenis_permintaan' => $request->jenis_permintaan ?? 'PROJECT',
                    'kode_project' => $request->kode_project,
                    'kode_area' => $kodeArea,
                    'sumber_dana' => $request->sumber_dana,
                    'bank_tujuan' => $request->bank_tujuan,
                    'no_rekening_tujuan' => $request->no_rekening_tujuan,
                    'nama_rekening_tujuan' => $request->nama_rekening_tujuan,
                    'total_nominal' => $totalNominal,
                    'status_surat' => 'Pending',
                    'posisi_saat_ini' => $posisiAwal,
                    'id_maker' => Auth::user()->id_user,
                ]);

                // 5. Create Detail Items (batch insert)
                $detailRecords = array_map(fn($item) => [
                    'no_surat' => $nomorBaru,
                    'keterangan' => $item['keterangan'] ?? '-',
                    'kode_budget' => $item['kode_budget'],
                    'nominal' => $item['jumlah'],
                ], $request->items);

                DB::table('surat_permintaan_detail')->insert($detailRecords);

                // 5b. Reserve budget to prevent double-booking of pending SPP
                $this->budgetService->reserveBudget($request->items, $kodeArea, $request->kode_project);

                // 6. Handle File Uploads
                if ($request->hasFile('file_lampiran')) {
                    $this->fileService->uploadSppAttachments(
                        $request->file('file_lampiran'),
                        $nomorBaru,
                        'MAKER'
                    );
                }

                // Note: Audit log and SppHistory are now handled automatically by SppObserver

                // 7. Send Notification
                $this->notificationService->sendToRole(
                    $posisiAwal,
                    NotificationService::TYPE_NEW_SPP,
                    "SPP Baru - {$nomorBaru}",
                    'SPP baru senilai Rp '.number_format($totalNominal, 0, ',', '.')." dari project {$request->kode_project} membutuhkan persetujuan Anda.",
                    'SPP',
                    $nomorBaru
                );

                // 8. Overspend warning
                if ($validation->isOverBudget) {
                    $defisitFormat = number_format($validation->defisit, 0, ',', '.');
                    return redirect('/spp')->with('success', 'Pengajuan dana SPP berhasil dikirim!')->with(
                        'warning',
                        "⚠️ PERHATIAN: Terdapat budget yang melebihi pagu! Defisit total: Rp {$defisitFormat}. Pastikan ada penutupan dari budget lain."
                    );
                }

                return redirect('/spp')->with('success', 'Pengajuan dana SPP baru berhasil dikirim dan lolos verifikasi pagu anggaran!');
            });
        } catch (\Exception $e) {
            Log::error('SPP store failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->withInput()->with('error', $e->getMessage() ?: 'Terjadi kesalahan sistem internal. Silakan coba kembali.');
        }
    }

    // =========================================================================
    // FORM PERBAIKAN SPP REVISI (Maker - F3 Fix)
    // =========================================================================
    public function edit(Request $request)
    {
        $noSurat = $request->query('no_surat');

        $surat = SuratPermintaan::with('details', 'files')->where('no_surat', $noSurat)->first();

        if (! $surat) {
            return redirect('/spp')->with('error', 'SPP tidak ditemukan.');
        }

        if ($surat->status_surat !== 'Revisi' || $surat->posisi_saat_ini !== 'MAKER') {
            return redirect('/spp')->with('error', 'SPP ini tidak dalam status Revisi.');
        }

        if ((int) $surat->id_maker !== (int) Auth::id() && session('role') !== 'ADMIN') {
            abort(403, 'Hanya pembuat SPP yang dapat memperbaiki.');
        }

        return view('spp.edit', compact('surat'));
    }

    // =========================================================================
    // PROSES KIRIM ULANG SPP REVISI (Maker - F3 Fix)
    // =========================================================================
    public function update(StoreSppRequest $request)
    {
        $noSurat = $request->input('no_surat');

        try {
            return DB::transaction(function () use ($request, $noSurat) {
                $surat = SuratPermintaan::where('no_surat', $noSurat)->lockForUpdate()->first();

                if (! $surat || $surat->status_surat !== 'Revisi' || $surat->posisi_saat_ini !== 'MAKER') {
                    throw new \Exception('SPP tidak ditemukan atau tidak dalam status Revisi.');
                }

                if ((int) $surat->id_maker !== (int) Auth::id()) {
                    throw new \Exception('Hanya pembuat SPP yang dapat memperbaiki.');
                }

                $kodeArea = $surat->kode_area;

                // Release reservasi lama, validasi ulang, reserve yang baru
                $oldItems = DB::table('surat_permintaan_detail')
                    ->where('no_surat', $noSurat)
                    ->get(['kode_budget', 'nominal'])
                    ->toArray();

                $this->budgetService->releaseReservation($oldItems, $kodeArea, $surat->kode_project);

                $validation = $this->budgetService->lockAndValidateMultiple(
                    $request->items,
                    $kodeArea,
                    $surat->kode_project
                );

                if (! $validation->isValid) {
                    throw new \Exception($validation->errorMessage);
                }

                $totalNominal = $validation->details['total_nominal'];

                $this->budgetService->reserveBudget($request->items, $kodeArea, $surat->kode_project);

                // Reset ke awal workflow
                $posisiAwal = $this->workflowService->determineInitialPosition($surat->kode_project);

                $surat->update([
                    'tanggal' => $request->tanggal,
                    'sumber_dana' => $request->sumber_dana ?? $surat->sumber_dana,
                    'bank_tujuan' => $request->bank_tujuan ?? $surat->bank_tujuan,
                    'no_rekening_tujuan' => $request->no_rekening_tujuan ?? $surat->no_rekening_tujuan,
                    'nama_rekening_tujuan' => $request->nama_rekening_tujuan ?? $surat->nama_rekening_tujuan,
                    'total_nominal' => $totalNominal,
                    'status_surat' => 'Pending',
                    'posisi_saat_ini' => $posisiAwal,
                    'keterangan_checker' => null,
                    'updated_at' => now(),
                ]);

                // Ganti seluruh item
                DB::table('surat_permintaan_detail')->where('no_surat', $noSurat)->delete();

                $detailRecords = array_map(fn($item) => [
                    'no_surat' => $noSurat,
                    'keterangan' => $item['keterangan'] ?? '-',
                    'kode_budget' => $item['kode_budget'],
                    'nominal' => $item['jumlah'],
                ], $request->items);

                DB::table('surat_permintaan_detail')->insert($detailRecords);

                // Upload lampiran tambahan
                if ($request->hasFile('file_lampiran')) {
                    $this->fileService->uploadSppAttachments(
                        $request->file('file_lampiran'),
                        $noSurat,
                        'MAKER'
                    );
                }

                // Notifikasi ke posisi pertama
                $this->notificationService->sendToRole(
                    $posisiAwal,
                    NotificationService::TYPE_PENDING_APPROVAL,
                    "SPP Diperbaiki - {$noSurat}",
                    "SPP {$noSurat} telah diperbaiki maker dan dikirim ulang untuk persetujuan {$posisiAwal}.",
                    'SPP',
                    $noSurat
                );

                return redirect('/spp')->with('success', "SPP {$noSurat} berhasil diperbaiki dan dikirim ulang. Menunggu persetujuan {$posisiAwal}.");
            });
        } catch (\Exception $e) {
            Log::error('SPP update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'no_surat' => $noSurat ?? null,
            ]);

            return redirect()->back()->withInput()->with('error', $e->getMessage() ?: 'Gagal memperbaiki SPP. Silakan coba kembali.');
        }
    }

    // =========================================================================
    // Halaman Riwayat Transaksi SPP (Index)
    // =========================================================================
    public function index(Request $request)
    {
        $role = session('role');
        $kodeArea = session('kode_area');
        $userProject = session('kode_project');
        $selectedProject = $request->query('kode_project');

        if (empty($selectedProject) && ! empty($userProject) && $userProject !== 'all' && RoleHelper::isProjectScoped($role)) {
            $selectedProject = $userProject;
        }

        $scopeQuery = DB::table('surat_permintaan');

        if (RoleHelper::isStaffArea($role)) {
            $scopeQuery->where('kode_area', $kodeArea);
        } elseif (RoleHelper::isProjectScoped($role)) {
            if ($userProject !== null && $userProject !== '' && $userProject !== 'all') {
                $scopeQuery->where('kode_project', $userProject);
            }
        }

        $query = clone $scopeQuery;

        if (! empty($selectedProject) && $selectedProject !== 'all') {
            if (RoleHelper::isProjectScoped($role) && $userProject !== 'all' && $selectedProject !== $userProject) {
                $selectedProject = $userProject;
            }

            $query->where('kode_project', $selectedProject);
        }

        $sortColumns = ['no_surat', 'kode_project', 'tanggal', 'kode_area', 'total_nominal', 'status_surat', 'created_at'];
        $sort = in_array($request->query('sort'), $sortColumns) ? $request->query('sort') : 'created_at';
        $direction = strtolower($request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        $data = $query->orderBy($sort, $direction)->paginate(20);

        $sppNumbers = $data->pluck('no_surat');
        $allFiles = DB::table('surat_permintaan_files')
            ->whereIn('no_surat', $sppNumbers)
            ->get()
            ->groupBy('no_surat');

        $data->transform(function ($spp) use ($allFiles) {
            $files = $allFiles->get($spp->no_surat, collect());
            $spp->files_maker = $files->where('kategori', 'MAKER')->values();
            $spp->files_checker = $files->where('kategori', 'CHECKER')->values();

            return $spp;
        });

        if (RoleHelper::isGlobal($role) || ($userProject === null || $userProject === '' || $userProject === 'all')) {
            $projects = Cache::remember('ref_projects', 3600, fn() => Project::orderBy('kode_project')->get());
        } elseif (RoleHelper::isProjectScoped($role) && $userProject !== null && $userProject !== '' && $userProject !== 'all') {
            $projects = Project::where('kode_project', $userProject)->get();
        } else {
            $allowedProjectCodes = $scopeQuery->distinct()->pluck('kode_project')->toArray();
            $projects = Project::whereIn('kode_project', $allowedProjectCodes)->orderBy('kode_project')->get();
        }

        return view('spp.index', compact('data', 'projects', 'selectedProject'));
    }

    // =========================================================================
    //  [SUPER REVIEW] Halaman Kelola Surat (/spp/kelola)
    // =========================================================================
    public function kelola(Request $request)
    {
        $role = session('role');

        if (! in_array($role, ['ADMIN', 'MANAGER_KEUANGAN'], true)) {
            abort(403, 'Akses Otoritas Ditolak.');
        }

        $status = $request->query('status');
        if (! in_array($status, [null, '', 'Pending', 'Approved', 'Rejected'], true)) {
            $status = null;
        }

        $query = DB::table('surat_permintaan');
        if (! empty($status)) {
            $query->where('status_surat', $status);
        }

        $sortColumns = ['no_surat', 'kode_project', 'tanggal', 'kode_area', 'total_nominal', 'status_surat', 'created_at'];
        $sort = in_array($request->query('sort'), $sortColumns) ? $request->query('sort') : 'created_at';
        $direction = strtolower($request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        $data = $query->orderBy($sort, $direction)->paginate(20);

        $sppNumbers = $data->pluck('no_surat');
        $allFiles = DB::table('surat_permintaan_files')
            ->whereIn('no_surat', $sppNumbers)
            ->get()
            ->groupBy('no_surat');

        $data->transform(function ($spp) use ($allFiles) {
            $files = $allFiles->get($spp->no_surat, collect());
            $spp->files_maker = $files->where('kategori', 'MAKER')->values();
            $spp->files_checker = $files->where('kategori', 'CHECKER')->values();

            return $spp;
        });

        return view('spp.kelola', compact('data'));
    }

    // =========================================================================
    // 2. PROSES OTORISASI VALIDASI APPROVAL (STATE MACHINE ENFORCED)
    // =========================================================================
    public function validasi(ValidateSppRequest $request)
    {
        $currentRole = session('role');
        $id = $request->query('no_surat') ?? $request->input('no_surat');

        if (! $id) {
            return redirect()->back()->with('error', 'Nomor SPP tidak ditemukan untuk validasi.');
        }

        try {
            return DB::transaction(function () use ($request, $currentRole, $id) {
                $surat = SuratPermintaan::where('no_surat', $id)->lockForUpdate()->first();

                if (! $surat) {
                    throw new \Exception('Data pengajuan SPP tidak ditemukan!');
                }

                if (! RoleHelper::canAccessSpp($currentRole, session('kode_area'), session('kode_project'), $surat)) {
                    AuditLogService::log('UNAUTHORIZED_VALIDASI', "User coba validasi SPP {$id} di luar area/project scope");
                    throw new \Exception('AKSES DITOLAK: SPP ini berada di luar area/project kewenangan Anda.');
                }

                if (! $this->workflowService->validateWorkflowTransition($surat, $currentRole)) {
                    throw new \Exception("WORKFLOW VIOLATION: Berkas ini sedang berada dalam otoritas [{$surat->posisi_saat_ini}], bukan di meja kerja Anda!");
                }

                $totalNominal = (string) $surat->total_nominal;
                $result = $this->workflowService->getNextPosition(
                    $currentRole,
                    $surat->kode_project,
                    $totalNominal,
                    $request->aksi
                );

                $surat->update([
                    'status_surat' => $result['status'],
                    'posisi_saat_ini' => $result['next'],
                    'keterangan_checker' => $request->alasan,
                    'updated_at' => now(),
                ]);

                // File upload for checker
                if ($request->hasFile('file_checker')) {
                    $this->fileService->uploadSppAttachments(
                        $request->file('file_checker'),
                        $id,
                        'CHECKER'
                    );
                }

                // Audit log & history handled automatically by SppObserver

                // Send notifications
                if (in_array($request->aksi, ['approve', 'revise'])) {
                    $notifType = $request->aksi === 'approve'
                        ? NotificationService::TYPE_PENDING_APPROVAL
                        : NotificationService::TYPE_REVISED;
                    $notifTitle = $request->aksi === 'approve' ? "Perlu Persetujuan - {$id}" : "Revisi - {$id}";

                    if ($result['next'] === 'MAKER') {
                        $this->notificationService->sendToUser(
                            $surat->id_maker, $notifType, $notifTitle,
                            "SPP {$id} direvisi oleh {$currentRole}. Silakan perbaiki dan kirim ulang.",
                            'SPP', $id
                        );
                    } else {
                        $this->notificationService->sendToRole(
                            $result['next'], $notifType, $notifTitle,
                            "SPP {$id} telah disetujui oleh {$currentRole}. Sekarang menunggu persetujuan {$result['next']}.",
                            'SPP', $id
                        );
                    }
                } elseif ($request->aksi === 'reject') {
                    $this->notificationService->sendToUser(
                        $surat->id_maker, NotificationService::TYPE_REJECTED,
                        "Ditolak - {$id}",
                        "SPP {$id} ditolak oleh {$currentRole}. Alasan: ".($request->alasan ?? '-'),
                        'SPP', $id
                    );

                    $rejectedItems = DB::table('surat_permintaan_detail')
                        ->where('no_surat', $surat->no_surat)
                        ->get(['kode_budget', 'nominal']);
                    $this->budgetService->releaseReservation($rejectedItems->toArray(), $surat->kode_area, $surat->kode_project);
                }

                $feedbackSuccess = ($request->aksi == 'approve')
                    ? "Pengajuan berhasil diproses! Aliran dokumen diteruskan ke: {$result['next']}."
                    : 'Pengajuan SPP resmi ditolak.';

                $redirect = redirect('/spp')->with('success', $feedbackSuccess);

                // Check overspend warning
                $noBudgetProjects = config('spp_workflow.no_budget_projects', []);
                if (! in_array($surat->kode_project, $noBudgetProjects, true)) {
                    $items = DB::table('surat_permintaan_detail')
                        ->where('no_surat', $surat->no_surat)
                        ->get();
                    foreach ($items as $item) {
                        $remaining = $this->budgetService->calculateRemainingBudget($item->kode_budget, $surat->kode_area);
                        if (bccomp('0', $remaining, 2) === 1) {
                            $defisit = bcsub('0', $remaining, 2);
                            $defisitFormat = number_format($defisit, 0, ',', '.');
                            $redirect->with(
                                'warning',
                                "⚠️ Budget {$item->kode_budget} minus Rp {$defisitFormat}. Pastikan ada penutupan dari budget lain."
                            );
                            break;
                        }
                    }
                }

                return $redirect;
            });
        } catch (\Exception $e) {
            Log::error('SPP validasi failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'no_surat' => $id ?? null,
            ]);

            return redirect()->back()->with('error', $e->getMessage() ?: 'Gagal memproses validasi. Silakan coba kembali.');
        }
    }

    // =========================================================================
    // 3. PROSES PENCUIRAN DANA FINAL (IDEMPOTENCY ENFORCED)
    // =========================================================================
    public function cairkan(DisburseSppRequest $request)
    {
        $id = $request->input('no_surat');

        try {
            return DB::transaction(function () use ($id) {
                $surat = SuratPermintaan::where('no_surat', $id)
                    ->where('status_surat', 'Approved')
                    ->where('posisi_saat_ini', 'KASIR_PUSAT')
                    ->lockForUpdate()
                    ->first();

                if (! $surat) {
                    throw new \Exception('⚠️ PERINGATAN IDEMPOTENCY: Berkas ini sudah dicairkan sebelumnya atau status transaksi tidak valid!');
                }

                $items = DB::table('surat_permintaan_detail')
                    ->where('no_surat', $surat->no_surat)
                    ->get();

                $biayaAdmin = (string) ($request->biaya_admin ?? 0);
                $totalDibayar = bcadd((string) $surat->total_nominal, $biayaAdmin, 2);

                $this->budgetService->updateTerserap(
                    $items->toArray(),
                    $surat->kode_area,
                    $surat->kode_project,
                    $biayaAdmin
                );

                $surat->update([
                    'status_surat' => 'Disbursed',
                    'posisi_saat_ini' => 'FINISH',
                    'biaya_admin' => $biayaAdmin,
                    'updated_at' => now(),
                ]);

                // Audit log & history handled by SppObserver

                $this->notificationService->sendToUser(
                    $surat->id_maker,
                    NotificationService::TYPE_DISBURSED,
                    "Pencairan - {$surat->no_surat}",
                    "SPP {$surat->no_surat} telah dicairkan sebesar Rp ".number_format($surat->total_nominal, 0, ',', '.'),
                    'SPP',
                    $surat->no_surat
                );

                return redirect('/spp')->with('success', "💵 Sukses! Dana SPP No {$surat->no_surat} telah resmi dicairkan dan saldo anggaran actual berhasil diperbarui!");
            });
        } catch (\Exception $e) {
            Log::error('SPP cairkan failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'no_surat' => $id ?? null,
            ]);

            return redirect()->back()->with('error', $e->getMessage() ?: 'Gagal mencairkan dana. Silakan coba kembali.');
        }
    }

    // =========================================================================
    // API INTERNAL: GET DETAIL RINCIAN DATA VIA AJAX
    // =========================================================================
    public function getDetailItems(Request $request)
    {
        $no_surat = urldecode($request->query('no_surat', ''));

        if (empty($no_surat)) {
            return response()->json(['error' => 'Nomor SPP tidak dikirimkan.'], 400);
        }

        $surat = DB::table('surat_permintaan')->where('no_surat', $no_surat)->first();
        if (! $surat) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        $role = session('role');
        $userArea = session('kode_area');
        $userProject = session('kode_project');

        if (! RoleHelper::canAccessSpp($role, $userArea, $userProject, $surat)) {
            AuditLogService::log('UNAUTHORIZED_API_ACCESS', "User mencoba akses detail SPP {$no_surat}");

            return response()->json(['error' => 'Akses Ditolak'], 403);
        }

        $items = DB::table('surat_permintaan_detail')
            ->leftJoin('master_budget', 'surat_permintaan_detail.kode_budget', '=', 'master_budget.kode_budget')
            ->select('surat_permintaan_detail.*', 'master_budget.nama_budget')
            ->where('surat_permintaan_detail.no_surat', $no_surat)
            ->get();

        return response()->json($items);
    }

    // =========================================================================
    // 4. DOWNLOAD LAMPIRAN SECURE CONTROL (SERVER-SIDE PROTECTION)
    // =========================================================================
    public function downloadFile($nama_file)
    {
        if (! Auth::check()) {
            abort(401);
        }

        $nama_file = basename($nama_file);

        $fileRecord = DB::table('surat_permintaan_files as f')
            ->join('surat_permintaan as s', 'f.no_surat', '=', 's.no_surat')
            ->where('f.nama_file', $nama_file)
            ->select('s.kode_area', 's.kode_project', 's.no_surat')
            ->first();

        if (! $fileRecord) {
            abort(404, 'Arsip tidak ditemukan.');
        }

        $role = session('role');
        $userArea = session('kode_area');
        $userProject = session('kode_project');

        if (! RoleHelper::canAccessSpp($role, $userArea, $userProject, $fileRecord)) {
            AuditLogService::log('ILLEGAL_FILE_ACCESS', "Percobaan akses file tanpa izin: {$nama_file}");
            abort(403, 'Anda tidak memiliki hak akses atas dokumen ini.');
        }

        $path = storage_path('app/private/lampiran_spp/' . basename($nama_file));
        if (! file_exists($path)) {
            abort(404, 'Berkas arsip fisik tidak ditemukan di secure area storage server.');
        }

        return response()->download($path, basename($nama_file));
    }

    // =========================================================================
    // 5. CETAK PDF RESMI DENGAN FILTER OTORISASI KEPEMILIKAN
    // =========================================================================
    public function cetakPdf(Request $request)
    {
        $id = $request->query('no_surat');
        $surat = DB::table('surat_permintaan')->where('no_surat', $id)->first();

        if (! $surat) {
            return redirect()->back()->with('error', 'Data SPP tidak ditemukan!');
        }

        $role = session('role');
        $userArea = session('kode_area');
        $userProject = session('kode_project');

        if (! RoleHelper::canAccessSpp($role, $userArea, $userProject, $surat)) {
            abort(403, 'AKSI ILEGAL: Anda dilarang mencetak dokumen dari unit area kerja lain!');
        }

        AuditLogService::log('CETAK_PDF_SECURE', "User mengunduh lembar arsip fisik PDF untuk SPP No {$surat->no_surat}", $surat);

        $ttd = $this->loadSignatures($surat->no_surat);

        $items = DB::table('surat_permintaan_detail')
            ->leftJoin('master_budget', 'surat_permintaan_detail.kode_budget', '=', 'master_budget.kode_budget')
            ->select('surat_permintaan_detail.*', 'master_budget.nama_budget')
            ->where('surat_permintaan_detail.no_surat', $surat->no_surat)
            ->get();

        $pdf = Pdf::loadView('spp.cetak_pdf', compact('surat', 'items', 'ttd'))->setPaper('a4', 'portrait');
        $namaFileDownload = 'SPP_'.str_replace('/', '-', $surat->no_surat).'.pdf';

        return $pdf->stream($namaFileDownload);
    }

    // =========================================================================
    // 6. PREVIEW CETAK SPP DENGAN HTML BROWSER (Sebelum Generate PDF)
    // =========================================================================
    public function previewPdf(Request $request)
    {
        $id = $request->query('no_surat');
        $surat = DB::table('surat_permintaan')->where('no_surat', $id)->first();

        if (! $surat) {
            return redirect()->back()->with('error', 'Data SPP tidak ditemukan!');
        }

        $role = session('role');
        $userArea = session('kode_area');
        $userProject = session('kode_project');

        if (! RoleHelper::canAccessSpp($role, $userArea, $userProject, $surat)) {
            abort(403, 'AKSI ILEGAL: Anda dilarang melihat preview dokumen dari unit area kerja lain!');
        }

        $items = DB::table('surat_permintaan_detail')
            ->leftJoin('master_budget', 'surat_permintaan_detail.kode_budget', '=', 'master_budget.kode_budget')
            ->select('surat_permintaan_detail.*', 'master_budget.nama_budget')
            ->where('surat_permintaan_detail.no_surat', $surat->no_surat)
            ->get();

        $ttd = [
            'maker' => null,
            'checker' => null,
            'cashier' => null,
        ];

        return view('spp.cetak_pdf', compact('surat', 'items', 'ttd'));
    }

    private function loadSignatures(string $noSurat): array
    {
        $surat = SuratPermintaan::where('no_surat', $noSurat)->first();
        $history = DB::table('spp_history')
            ->where('no_surat', $noSurat)
            ->orderBy('created_at', 'asc')
            ->get();

        $signatures = [];

        // 1. Collect all user IDs and usernames to batch query
        $userIdPool = [];
        $usernamePool = [];

        if ($surat) {
            $userIdPool[] = $surat->id_maker;
        }

        foreach ($history as $row) {
            if ($row->aktor_username) {
                $usernamePool[] = $row->aktor_username;
            }
        }

        // 2. Batch load users (2 queries max instead of N)
        $usersById = [];
        $usersByUsername = [];

        if (! empty($userIdPool)) {
            $usersById = DB::table('users')
                ->whereIn('id_user', array_unique($userIdPool))
                ->get()
                ->keyBy('id_user');
        }

        if (! empty($usernamePool)) {
            $usersByUsername = DB::table('users')
                ->whereIn('username', array_unique($usernamePool))
                ->get()
                ->keyBy('username');
        }

        // 3. Maker — from surat_permintaan.id_maker
        if ($surat) {
            $maker = $usersById[$surat->id_maker] ?? null;
            if ($maker) {
                $signatures[] = $this->buildSignature('Diajukan Oleh', 'Pemohon', $maker);
            }
        }

        // 4. Approvers + Cashier — from spp_history
        foreach ($history as $row) {
            $label = null;
            $detail = null;

            if ($row->posisi_ke === 'FINISH') {
                $label = 'Dijalankan Oleh';
                $detail = 'Kasir Pusat';
            } elseif ($row->posisi_dari && ! in_array($row->posisi_dari, ['REJECTED', 'MAKER'], true)) {
                $label = 'Disetujui';
                $detail = $this->getRoleSignatureLabel($row->posisi_dari);
            }

            if ($label && $detail) {
                $user = $usersByUsername[$row->aktor_username] ?? null;
                $signatures[] = $this->buildSignature($label, $detail, $user);
            }
        }

        // 3. Load signature images
        foreach ($signatures as &$sig) {
            $sig['img_base64'] = null;
            if (! empty($sig['signature_path'])) {
                $path = storage_path('app/private/signatures/'.$sig['signature_path']);
                if (file_exists($path)) {
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $data = file_get_contents($path);
                    $sig['img_base64'] = 'data:image/'.$type.';base64,'.base64_encode($data);
                }
            }
        }

        return $signatures;
    }

    private function buildSignature(string $label, string $detail, ?object $user): array
    {
        return [
            'label' => $label,
            'role_detail' => $detail,
            'nama_lengkap' => $user ? ($user->nama_lengkap ?? $user->name ?? $user->nama ?? '-') : '-',
            'signature_path' => $user ? ($user->signature_path ?? null) : null,
            'img_base64' => null,
        ];
    }

    private function getRoleSignatureLabel(?string $role): ?string
    {
        $labels = [
            'AREA_MANAGER' => 'Area Manager',
            'FINANCE_PROJECT' => 'Finance Project',
            'PROJECT_MANAGER' => 'Project Manager',
            'MANAGER_KEUANGAN' => 'Koordinator Keuangan',
            'MANAGER_PKP' => 'Manager PKP',
            'DIREKTUR' => 'Direktur',
            'KASIR_PUSAT' => 'Kasir Pusat',
            'KOORDINATOR_KEUANGAN' => 'Koordinator',
            'KOORDINATOR_PK' => 'Koordinator',
            'KOORDINATOR_TC' => 'Koordinator',
            'KOORDINATOR_DIKLAT' => 'Koordinator',
            'KOORDINATOR_KLINIK' => 'Koordinator',
            'KOORDINATOR_BATRA' => 'Koordinator',
            'KOORDINATOR_BIDANG' => 'Koordinator',
        ];

        return $labels[$role] ?? null;
    }
}
