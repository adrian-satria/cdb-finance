<?php

namespace App\Http\Controllers;

use App\Http\Requests\Spp\StoreSppRequest;
use App\Http\Requests\Spp\ValidateSppRequest;
use App\Http\Requests\Spp\DisburseSppRequest;
use Illuminate\Http\Request;
use App\Models\SuratPermintaan;
use App\Models\Project;
use App\Models\Area;
use App\Models\SumberDana;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\NotificationService;
use App\Services\SppWorkflowService;
use App\Services\BudgetValidationService;
use App\Services\SppNumberGeneratorService;
use App\Services\FileUploadService;

class SppController extends Controller
{
    public function __construct(
        protected SppWorkflowService $workflowService,
        protected BudgetValidationService $budgetService,
        protected SppNumberGeneratorService $numberService,
        protected FileUploadService $fileService
    ) {}

    // =========================================================================
    // Halaman Form Pembuatan SPP Baru (Maker)
    // =========================================================================
    public function create()
    {
        $nomor_baru = $this->numberService->generateNextNumber(now()->format('Y-m-d'));

        $projects = Project::all();
        $master_budgets = DB::table('master_budget')->get()->groupBy('kode_project');
        $areas = Area::all();
        $sumber_danas = SumberDana::all();

        return view('spp.tambah', compact('nomor_baru', 'projects', 'areas', 'sumber_danas', 'master_budgets'));
    }

    // =========================================================================
    // 1. PROSES SIMPAN DATA SPP BARU (MAKER - BUDGET CEILING LOCK)
    // =========================================================================
    public function store(StoreSppRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                // 1. Validate Budget Ceiling & Get Total Nominal
                $validation = $this->budgetService->lockAndValidateMultiple($request->items);
                
                if (!$validation->isValid) {
                    throw new \Exception($validation->errorMessage);
                }

                $totalNominal = $validation->details['total_nominal'];

                // 2. Generate SPP Number
                $nomorBaru = $this->numberService->generateNextNumber($request->tanggal);

                // 3. Determine Initial Workflow Position
                $posisiAwal = $this->workflowService->determineInitialPosition($request->kode_project);

                // 4. Create SPP Record
                SuratPermintaan::create([
                    'no_surat' => $nomorBaru,
                    'tanggal' => $request->tanggal,
                    'jenis_permintaan' => $request->jenis_permintaan ?? 'PROJECT',
                    'kode_project' => $request->kode_project,
                    'kode_area' => $request->kode_area ?? (session('kode_area') ?? 'PUSAT'),
                    'sumber_dana' => $request->sumber_dana,
                    'bank_tujuan' => $request->bank_tujuan,
                    'no_rekening_tujuan' => $request->no_rekening_tujuan,
                    'nama_rekening_tujuan' => $request->nama_rekening_tujuan,
                    'total_nominal' => $totalNominal,
                    'status_surat' => 'Pending',
                    'posisi_saat_ini' => $posisiAwal,
                    'id_maker' => Auth::user()->id_user,
                ]);

                // 5. Create Detail Items
                foreach ($request->items as $item) {
                    DB::table('surat_permintaan_detail')->insert([
                        'no_surat' => $nomorBaru,
                        'keterangan' => $item['keterangan'] ?? '-',
                        'kode_budget' => $item['kode_budget'],
                        'nominal' => $item['jumlah'],
                    ]);
                }

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
                NotificationService::sendToRole(
                    $posisiAwal, 
                    NotificationService::TYPE_NEW_SPP,
                    "SPP Baru - {$nomorBaru}",
                    "SPP baru senilai Rp " . number_format($totalNominal, 0, ',', '.') . " dari project {$request->kode_project} membutuhkan persetujuan Anda.",
                    'SPP', 
                    $nomorBaru
                );

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
    // Halaman Riwayat Transaksi SPP (Index)
    // =========================================================================
    public function index(Request $request)
    {
        $role = session('role');
        $kodeArea = session('kode_area');
        $userProject = session('kode_project');
        $selectedProject = $request->query('kode_project');

        $projectScopedRoles = ['FINANCE_PROJECT', 'PROJECT_MANAGER', 'MANAGER_KEUANGAN', 'KOORDINATOR_KEUANGAN', 'KOORDINATOR_PK', 'KOORDINATOR_TC', 'KOORDINATOR_DIKLAT', 'KOORDINATOR_KLINIK', 'KOORDINATOR_BATRA', 'KOORDINATOR_BIDANG'];

        if (empty($selectedProject) && !empty($userProject) && $userProject !== 'all' && in_array($role, $projectScopedRoles)) {
            $selectedProject = $userProject;
        }

        $scopeQuery = DB::table('surat_permintaan');

        if (in_array($role, ['MAKER', 'AREA_MANAGER'])) {
            $scopeQuery->where('kode_area', $kodeArea);
        } elseif (in_array($role, $projectScopedRoles)) {
            if ($userProject !== null && $userProject !== '' && $userProject !== 'all') {
                $scopeQuery->where('kode_project', $userProject);
            }
        }

        $query = clone $scopeQuery;

        if (!empty($selectedProject) && $selectedProject !== 'all') {
            if (in_array($role, $projectScopedRoles) && $userProject !== 'all' && $selectedProject !== $userProject) {
                $selectedProject = $userProject;
            }

            $query->where('kode_project', $selectedProject);
        }

        $data = $query->orderBy('created_at', 'desc')->paginate(20);

        $sppNumbers = $data->pluck('no_surat');
        $allFiles = DB::table('surat_permintaan_files')
            ->whereIn('no_surat', $sppNumbers)
            ->get()
            ->groupBy('no_surat');

        $data->transform(function ($spp) use ($allFiles) {
            $files = $allFiles->get($spp->no_surat, collect());
            $spp->files_maker   = $files->where('kategori', 'MAKER')->values();
            $spp->files_checker = $files->where('kategori', 'CHECKER')->values();
            return $spp;
        });

        if ($role === 'ADMIN' || ($userProject === null || $userProject === '' || $userProject === 'all')) {
            $projects = Project::orderBy('kode_project')->get();
        } elseif (in_array($role, $projectScopedRoles) && $userProject !== null && $userProject !== '' && $userProject !== 'all') {
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

        if (!in_array($role, ['ADMIN', 'MANAGER_KEUANGAN'], true)) {
            abort(403, 'Akses Otoritas Ditolak.');
        }

        $status = $request->query('status');
        if (!in_array($status, [null, '', 'Pending', 'Approved', 'Rejected'], true)) {
            $status = null;
        }

        $query = DB::table('surat_permintaan');
        if (!empty($status)) {
            $query->where('status_surat', $status);
        }

        $data = $query->orderBy('created_at', 'desc')->paginate(20);

        $sppNumbers = $data->pluck('no_surat');
        $allFiles = DB::table('surat_permintaan_files')
            ->whereIn('no_surat', $sppNumbers)
            ->get()
            ->groupBy('no_surat');

        $data->transform(function ($spp) use ($allFiles) {
            $files = $allFiles->get($spp->no_surat, collect());
            $spp->files_maker   = $files->where('kategori', 'MAKER')->values();
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

        if (!$id) {
            return redirect()->back()->with('error', 'Nomor SPP tidak ditemukan untuk validasi.');
        }

        try {
            return DB::transaction(function () use ($request, $currentRole, $id) {
                $surat = SuratPermintaan::where('no_surat', $id)->lockForUpdate()->first();

                if (!$surat) {
                    throw new \Exception('Data pengajuan SPP tidak ditemukan!');
                }

                $effectiveRole = ($currentRole === 'ADMIN') ? $surat->posisi_saat_ini : $currentRole;

                if (!$this->workflowService->validateWorkflowTransition($surat, $effectiveRole)) {
                    throw new \Exception("WORKFLOW VIOLATION: Berkas ini sedang berada dalam otoritas [{$surat->posisi_saat_ini}], bukan di meja kerja Anda!");
                }

                $totalNominal = (string) $surat->total_nominal;
                $result = $this->workflowService->getNextPosition(
                    $effectiveRole,
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
                        NotificationService::sendToUser(
                            $surat->id_maker, $notifType, $notifTitle,
                            "SPP {$id} direvisi oleh {$effectiveRole}. Silakan perbaiki dan kirim ulang.",
                            'SPP', $id
                        );
                    } else {
                        NotificationService::sendToRole(
                            $result['next'], $notifType, $notifTitle,
                            "SPP {$id} telah disetujui oleh {$effectiveRole}. Sekarang menunggu persetujuan {$result['next']}.",
                            'SPP', $id
                        );
                    }
                } elseif ($request->aksi === 'reject') {
                    NotificationService::sendToUser(
                        $surat->id_maker, NotificationService::TYPE_REJECTED,
                        "Ditolak - {$id}",
                        "SPP {$id} ditolak oleh {$effectiveRole}. Alasan: " . ($request->alasan ?? '-'),
                        'SPP', $id
                    );
                }

                $feedbackSuccess = ($request->aksi == 'approve')
                    ? "Pengajuan berhasil diproses! Aliran dokumen diteruskan ke: {$result['next']}."
                    : "Pengajuan SPP resmi ditolak.";

                return redirect('/spp')->with('success', $feedbackSuccess);
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

                if (!$surat) {
                    throw new \Exception('⚠️ PERINGATAN IDEMPOTENCY: Berkas ini sudah dicairkan sebelumnya atau status transaksi tidak valid!');
                }

                $items = DB::table('surat_permintaan_detail')
                    ->where('no_surat', $surat->no_surat)
                    ->get();

                foreach ($items as $item) {
                    DB::table('master_budget')
                        ->where('kode_budget', $item->kode_budget)
                        ->increment('terserap', $item->nominal);
                }

                $surat->update([
                    'status_surat' => 'Disbursed',
                    'posisi_saat_ini' => 'FINISH',
                    'updated_at' => now(),
                ]);

                // Audit log & history handled by SppObserver

                NotificationService::sendToUser(
                    $surat->id_maker,
                    NotificationService::TYPE_DISBURSED,
                    "Pencairan - {$surat->no_surat}",
                    "SPP {$surat->no_surat} telah dicairkan sebesar Rp " . number_format($surat->total_nominal, 0, ',', '.'),
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
        if (!$surat) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        $role = session('role');
        $userArea = session('kode_area');
        $userProject = session('kode_project');

        if (in_array($role, ['MAKER', 'AREA_MANAGER']) && $userArea !== $surat->kode_area) {
            self::simpanLog('UNAUTHORIZED_API_ACCESS', "User mencoba akses detail SPP area lain: {$no_surat}");
            return response()->json(['error' => 'Akses Ditolak'], 403);
        }

        if (in_array($role, ['FINANCE_PROJECT', 'PROJECT_MANAGER', 'MANAGER_KEUANGAN'])) {
            $hasProjectAccess = ($role === 'MANAGER_KEUANGAN' && $userProject === null) || ($userProject === $surat->kode_project);
            if (!$hasProjectAccess) {
                self::simpanLog('UNAUTHORIZED_API_ACCESS', "User mencoba akses detail SPP di luar project: {$no_surat}");
                return response()->json(['error' => 'Akses Ditolak'], 403);
            }
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
        if (!Auth::check()) {
            abort(401);
        }

        $fileRecord = DB::table('surat_permintaan_files as f')
            ->join('surat_permintaan as s', 'f.no_surat', '=', 's.no_surat')
            ->where('f.nama_file', $nama_file)
            ->select('s.kode_area', 's.kode_project', 's.no_surat')
            ->first();

        if (!$fileRecord) {
            abort(404, 'Arsip tidak ditemukan.');
        }

        $role = session('role');
        $userArea = session('kode_area');
        $userProject = session('kode_project');

        $isStaffArea = in_array($role, ['MAKER', 'AREA_MANAGER'], true) && ($userArea === $fileRecord->kode_area);
        $isProjectRole = false;
        if (in_array($role, ['FINANCE_PROJECT', 'PROJECT_MANAGER', 'MANAGER_KEUANGAN'], true)) {
            $isProjectRole = ($role === 'MANAGER_KEUANGAN' && $userProject === null) || ($userProject === $fileRecord->kode_project);
        }
        $isGlobalRole = in_array($role, ['ADMIN', 'KASIR_PUSAT', 'DIREKTUR'], true);

        if (!$isStaffArea && !$isProjectRole && !$isGlobalRole) {
            self::simpanLog('ILLEGAL_FILE_ACCESS', "Percobaan akses file tanpa izin: {$nama_file}");
            abort(403, 'Anda tidak memiliki hak akses atas dokumen ini.');
        }

        $path = storage_path('app/private/lampiran_spp/' . $nama_file);
        if (!file_exists($path)) {
            abort(404, 'Berkas arsip fisik tidak ditemukan di secure area storage server.');
        }

        return response()->download($path);
    }

    // =========================================================================
    // 5. CETAK PDF RESMI DENGAN FILTER OTORISASI KEPEMILIKAN
    // =========================================================================
    public function cetakPdf(Request $request)
    {
        $id = $request->query('no_surat');
        $surat = DB::table('surat_permintaan')->where('no_surat', $id)->first();

        if (!$surat) {
            return redirect()->back()->with('error', 'Data SPP tidak ditemukan!');
        }

        $role = session('role');
        $userArea = session('kode_area');
        $userProject = session('kode_project');
        $isGlobalUser = in_array($role, ['ADMIN', 'KASIR_PUSAT', 'DIREKTUR'], true);
        $isProjectRole = false;
        if (in_array($role, ['FINANCE_PROJECT', 'PROJECT_MANAGER', 'MANAGER_KEUANGAN'], true)) {
            $isProjectRole = ($role === 'MANAGER_KEUANGAN' && $userProject === null) || ($userProject === $surat->kode_project);
        }

        if (!$isGlobalUser && !$isProjectRole && ($userArea !== $surat->kode_area)) {
            abort(403, 'AKSI ILEGAL: Anda dilarang mencetak dokumen dari unit area kerja lain!');
        }

        self::simpanLog('CETAK_PDF_SECURE', "User mengunduh lembar arsip fisik PDF untuk SPP No {$surat->no_surat}", $surat);

        $getSignatures = function ($aksi, $no_surat) {
            return DB::table('audit_trails as a')
                ->join('users as u', DB::raw('a.username COLLATE utf8mb4_general_ci'), '=', DB::raw('u.username COLLATE utf8mb4_general_ci'))
                ->where('a.aksi', $aksi)
                ->where('a.deskripsi', 'like', "%{$no_surat}%")
                ->select('u.nama_lengkap', 'u.signature_path', 'a.created_at')
                ->orderBy('a.created_at', 'desc')
                ->first();
        };

        $ttd = [
            'maker' => $getSignatures('INSERT_SPP', $surat->no_surat),
            'area_manager' => $getSignatures('APPROVE_AREA', $surat->no_surat),
            'project_manager' => $getSignatures('APPROVE_PROJECT', $surat->no_surat),
            'finance_manager' => $getSignatures('APPROVE_FINANCE', $surat->no_surat),
            'cashier' => $getSignatures('DISBURSED_FINAL', $surat->no_surat),
        ];

        foreach ($ttd as $key => $person) {
            if ($person && $person->signature_path) {
                $path = storage_path('app/private/signatures/' . $person->signature_path);
                if (file_exists($path)) {
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $data = file_get_contents($path);
                    $ttd[$key]->img_base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                } else {
                    $ttd[$key]->img_base64 = null;
                }
            } else {
                $ttd[$key] = null;
            }
        }

        $items = DB::table('surat_permintaan_detail')
            ->leftJoin('master_budget', 'surat_permintaan_detail.kode_budget', '=', 'master_budget.kode_budget')
            ->select('surat_permintaan_detail.*', 'master_budget.nama_budget')
            ->where('surat_permintaan_detail.no_surat', $surat->no_surat)
            ->get();

        $pdf = Pdf::loadView('spp.cetak_pdf', compact('surat', 'items', 'ttd'))->setPaper('a4', 'portrait');
        $namaFileDownload = 'SPP_' . str_replace('/', '-', $surat->no_surat) . '.pdf';
        return $pdf->stream($namaFileDownload);
    }

    // =========================================================================
    // 6. PREVIEW CETAK SPP DENGAN HTML BROWSER (Sebelum Generate PDF)
    // =========================================================================
    public function previewPdf(Request $request)
    {
        $id = $request->query('no_surat');
        $surat = DB::table('surat_permintaan')->where('no_surat', $id)->first();

        if (!$surat) {
            return redirect()->back()->with('error', 'Data SPP tidak ditemukan!');
        }

        $role = session('role');
        $userArea = session('kode_area');
        $userProject = session('kode_project');
        $isGlobalUser = in_array($role, ['ADMIN', 'KASIR_PUSAT', 'DIREKTUR'], true);
        $isProjectRole = false;

        if (in_array($role, ['FINANCE_PROJECT', 'PROJECT_MANAGER', 'MANAGER_KEUANGAN'], true)) {
            $isProjectRole = ($role === 'MANAGER_KEUANGAN' && $userProject === null) || ($userProject === $surat->kode_project);
        }

        if (!$isGlobalUser && !$isProjectRole && ($userArea !== $surat->kode_area)) {
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
}

