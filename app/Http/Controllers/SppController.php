<?php

namespace App\Http\Controllers;

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

class SppController extends Controller
{
    // =========================================================================
    // Halaman Form Pembuatan SPP Baru (Maker)
    // =========================================================================
    public function create()
    {
        $tahun = date('Y');
        $bulan_romawi = ["", "I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X", "XI", "XII"];
        $bulan = $bulan_romawi[date('n')];

        // Ambil nomor terakhir berdasarkan waktu dibuat (created_at)
        $terakhir = SuratPermintaan::whereYear('created_at', $tahun)
                    ->orderBy('created_at', 'desc')
                    ->first();

        if ($terakhir) {
            $no_urut = (int) substr($terakhir->no_surat, -3) + 1;
        } else {
            $no_urut = 1;
        }

        $nomor_baru = $tahun . "/" . $bulan . "/SPP/PROJECT-X/" . str_pad($no_urut, 3, '0', STR_PAD_LEFT);

        $projects = Project::all();
        $master_budgets = DB::table('master_budget')->get()->groupBy('kode_project');
        $areas = Area::all();
        $sumber_danas = SumberDana::all();

        return view('spp.tambah', compact('nomor_baru', 'projects', 'areas', 'sumber_danas', 'master_budgets'));
    }

    // =========================================================================
    // 1. PROSES SIMPAN DATA SPP BARU (MAKER - BUDGET CEILING LOCK)
    // =========================================================================
    public function store(Request $request)
    {
        // catatan: nomor surat dihitung ulang server-side untuk menghindari race condition
        $request->validate([
            'no_surat' => 'nullable|string', // tidak dijadikan source of truth
            'tanggal' => 'required|date',
            'kode_project' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.kode_budget' => 'required|string',
            'items.*.jumlah' => 'required|numeric|min:0.01|max:999999999999.99',
            'file_lampiran' => 'nullable|array|max:5',
            'file_lampiran.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Gunakan Database Transaction dengan Pessimistic Locking tingkat tinggi
        DB::beginTransaction();

        try {
            $totalNominal = '0'; // Gunakan string untuk presisi tinggi

            // Ambil id_maker dari user login untuk FK surat_permintaan.id_maker
            $idMaker = Auth::user()->id_user;

            // -----------------------------------------------------------------
            // Generator nomor surat (anti race) berdasarkan periode tahun/bulan
            // -----------------------------------------------------------------
            $tahun = (int) date('Y', strtotime($request->tanggal));
            $bulan_romawi = ["", "I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X", "XI", "XII"];
            $bulan = $bulan_romawi[(int) date('n', strtotime($request->tanggal))];

            $terakhir = DB::table('surat_permintaan')
                ->whereYear('created_at', $tahun)
                ->whereRaw('MONTH(created_at) = ?', [date('n', strtotime($request->tanggal))])
                ->orderBy('created_at', 'desc')
                ->lockForUpdate()
                ->first();

            if ($terakhir && !empty($terakhir->no_surat)) {
                $no_urut = (int) substr($terakhir->no_surat, -3) + 1;
            } else {
                $no_urut = 1;
            }

            $nomor_baru = $tahun . "/" . $bulan . "/SPP/PROJECT-X/" . str_pad($no_urut, 3, '0', STR_PAD_LEFT);

            $collision = DB::table('surat_permintaan')->where('no_surat', $nomor_baru)->exists();
            if ($collision) {
                DB::rollBack();
                return redirect()->back()->withInput()->with('error', 'Terjadi konflik nomor surat. Silakan coba kembali.');
            }

            // DETEKSI DINI: Validasi Anggaran Sebelum Insert Data Apapun
            foreach ($request->items as $item) {
                $kodeBudget = $item['kode_budget'];
                $jumlahDiminta = (string) $item['jumlah'];

                // Tarik data budget master dan lakukan lock baris agar tidak terjadi race condition
                $budget = DB::table('master_budget')
                            ->where('kode_budget', $kodeBudget)
                            ->lockForUpdate()
                            ->first();

                if (!$budget) {
                    DB::rollBack();
                    return redirect()->back()->withInput()->with('error', "Kode Budget [{$kodeBudget}] tidak ditemukan di sistem master!");
                }

                // Kalkulasi sisa saldo riil (Deterministic Calculation)
                $sisaSaldo = bcsub((string)$budget->alokasi_dana, (string)$budget->terserap, 2);

                // Jika dana yang diminta bocor melampaui sisa plafon, blokir seketika!
                if (bccomp($jumlahDiminta, $sisaSaldo, 2) === 1) {
                    DB::rollBack();

                    $namaBudget = $budget->nama_budget ?? $kodeBudget;
                    $sisaSaldoFormat = number_format($sisaSaldo, 0, ',', '.');
                    $dimintaFormat = number_format($jumlahDiminta, 0, ',', '.');

                    return redirect()->back()->withInput()->with('error',
                        "⚠️ PENGAKSESAN DANA DITOLAK! Saldo untuk akun [{$namaBudget}] tidak mencukupi. Sisa saldo saat ini: Rp {$sisaSaldoFormat}, namun Anda mencoba mengajukan: Rp {$dimintaFormat}."
                    );
                }

                $totalNominal = bcadd($totalNominal, $jumlahDiminta, 2);
            }

            // Tentukan Alur Workflow awal (Next Step setelah Maker) berdasarkan Kode Project
            $kodeProject = $request->kode_project;
            $posisiAwal = 'MANAGER_KEUANGAN'; // Fallback default

            // Step awal approval setelah Maker
            // Sesuaikan dengan SOP per kode project:
            //  {38,40} -> Area Manager
            //  {01} -> Koordinator Keuangan
            //  {03} -> Koordinator PK
            //  {07} -> Koordinator TC
            //  {02} -> Koordinator Diklat
            //  {04} -> Koordinator Klinik
            //  {06} -> Koordinator Batra
            if (in_array($kodeProject, ['38', '40'], true)) {
                $posisiAwal = 'AREA_MANAGER';
            } elseif (in_array($kodeProject, ['01'], true)) {
                $posisiAwal = 'KOORDINATOR_KEUANGAN';
            } elseif (in_array($kodeProject, ['03'], true)) {
                $posisiAwal = 'KOORDINATOR_PK';
            } elseif (in_array($kodeProject, ['07'], true)) {
                $posisiAwal = 'KOORDINATOR_TC';
            } elseif (in_array($kodeProject, ['06'], true)) {
                $posisiAwal = 'KOORDINATOR_BATRA';
            } elseif (in_array($kodeProject, ['04'], true)) {
                $posisiAwal = 'KOORDINATOR_KLINIK';
            } elseif (in_array($kodeProject, ['02'], true)) {
                $posisiAwal = 'KOORDINATOR_DIKLAT';
            }

            // Simpan ke Tabel Utama Surat Permintaan
            DB::table('surat_permintaan')->insert([
                'no_surat' => $nomor_baru,
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
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Simpan ke Tabel Detail
            foreach ($request->items as $item) {
                DB::table('surat_permintaan_detail')->insert([
                    'no_surat' => $nomor_baru,
                    'keterangan' => $item['keterangan'] ?? '-',
                    'kode_budget' => $item['kode_budget'],
                    'nominal' => $item['jumlah'],
                ]);
            }

            // Simpan Berkas Lampiran
            if ($request->hasFile('file_lampiran')) {
                foreach ($request->file('file_lampiran') as $file) {
                    $namaFile = 'maker_' . $nomor_baru . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move(storage_path('app/private/lampiran_spp'), $namaFile);

                    DB::table('surat_permintaan_files')->insert([
                        'no_surat' => $nomor_baru,
                        'nama_file' => $namaFile,
                        'kategori' => 'MAKER',
                        'tipe_file' => $file->getClientOriginalExtension(),
                    ]);
                }
            }

            // Rekam Jejak Forensik Awal
            self::simpanLog('INSERT_SPP', "Maker berhasil mendistribusikan berkas SPP baru No {$nomor_baru} senilai Rp " . number_format($totalNominal, 0, ',', '.'));

            // Catat history SPP
            DB::table('spp_history')->insert([
                'no_surat' => $nomor_baru,
                'status_dari' => null,
                'status_ke' => 'Pending',
                'posisi_dari' => null,
                'posisi_ke' => $posisiAwal,
                'aktor_username' => Auth::user()->username,
                'aktor_role' => session('role'),
                'keterangan' => 'Pembuatan SPP baru oleh ' . Auth::user()->username,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Kirim notifikasi ke role yang berhak approve
            NotificationService::sendToRole($posisiAwal, NotificationService::TYPE_NEW_SPP,
                'SPP Baru - ' . $nomor_baru,
                "SPP baru senilai Rp " . number_format($totalNominal, 0, ',', '.') . " dari project {$kodeProject} membutuhkan persetujuan Anda.",
                'SPP', $nomor_baru);

            DB::commit();
            return redirect('/spp')->with('success', 'Pengajuan dana SPP baru berhasil dikirim dan lolos verifikasi pagu anggaran!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SPP store failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan sistem internal. Silakan coba kembali.');
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
    public function validasi(Request $request)
    {
        $request->validate([
            'aksi' => 'required|in:approve,revise,reject',
            'alasan' => 'nullable|string|max:255',
            'file_checker.*' => 'nullable|file|mimes:pdf,png,jpg,jpeg|max:5120'
        ]);

        $currentRole = session('role');
        $id = $request->query('no_surat') ?? $request->input('no_surat');

        if (!$id) {
            return redirect()->back()->with('error', 'Nomor SPP tidak ditemukan untuk validasi.');
        }

        DB::beginTransaction();
        try {
            $surat = DB::table('surat_permintaan')->where('no_surat', $id)->lockForUpdate()->first();

            if (!$surat) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Data pengajuan SPP tidak ditemukan!');
            }

            $effectiveRole = ($currentRole === 'ADMIN') ? $surat->posisi_saat_ini : $currentRole;

            if ($surat->posisi_saat_ini !== $effectiveRole) {
                DB::rollBack();
                return redirect()->back()->with('error', "WORKFLOW VIOLATION: Berkas ini sedang berada dalam otoritas [{$surat->posisi_saat_ini}], bukan di meja kerja Anda!");
            }

            $totalNominal = (string) $surat->total_nominal;
            $status = $surat->status_surat;
            $nextPosisi = $surat->posisi_saat_ini;

            if ($request->aksi == 'approve') {
                $kodeProject = $surat->kode_project ?? null;
                $flowType = match (true) {
                    in_array($kodeProject, ['38', '40'], true) => 'PROJECT_FLOW',
                    in_array($kodeProject, ['01', '03', '07'], true) => 'PO_PK_TC_FLOW',
                    in_array($kodeProject, ['02', '04', '06'], true) => 'BATRA_KLINIK_DIKLAT_FLOW',
                    default => 'DEFAULT_FLOW',
                };

                if ($request->aksi === 'approve') {
                    $nominalOver50M = bccomp($totalNominal, '50000000', 2) === 1;
                    $nextMap = [];

                    if ($flowType === 'PROJECT_FLOW') {
                        $nextMap = [
                            'AREA_MANAGER' => fn() => ['status' => 'Pending', 'next' => 'FINANCE_PROJECT'],
                            'FINANCE_PROJECT' => fn() => ['status' => 'Pending', 'next' => 'PROJECT_MANAGER'],
                            'PROJECT_MANAGER' => fn() => ['status' => 'Pending', 'next' => 'MANAGER_KEUANGAN'],
                            'MANAGER_KEUANGAN' => fn() => $nominalOver50M
                                ? ['status' => 'Pending Director Otorisasi', 'next' => 'DIREKTUR']
                                : ['status' => 'Approved', 'next' => 'KASIR_PUSAT'],
                            'DIREKTUR' => fn() => ['status' => 'Approved', 'next' => 'KASIR_PUSAT'],
                        ];
                    } elseif ($flowType === 'PO_PK_TC_FLOW') {
                        $nextMap = [
                            'KASIR_PUSAT' => fn() => ['status' => 'Pending', 'next' => 'KOORDINATOR_KEUANGAN'],
                            'KOORDINATOR_KEUANGAN' => fn() => ['status' => 'Pending', 'next' => 'MANAGER_KEUANGAN'],
                            'MANAGER_KEUANGAN' => fn() => $nominalOver50M
                                ? ['status' => 'Pending Director Otorisasi', 'next' => 'DIREKTUR']
                                : ['status' => 'Approved', 'next' => 'KASIR_PUSAT'],
                            'DIREKTUR' => fn() => ['status' => 'Approved', 'next' => 'KASIR_PUSAT'],
                            'KOORDINATOR_TC' => fn() => ['status' => 'Pending', 'next' => 'MANAGER_KEUANGAN'],
                            'KOORDINATOR_PK' => fn() => ['status' => 'Pending', 'next' => 'MANAGER_KEUANGAN'],
                        ];
                    } elseif ($flowType === 'BATRA_KLINIK_DIKLAT_FLOW') {
                        $nextMap = [
                            'KASIR_PUSAT' => fn() => ['status' => 'Pending', 'next' => 'KOORDINATOR_BIDANG'],
                            'KOORDINATOR_BIDANG' => fn() => ['status' => 'Pending', 'next' => 'MANAGER_PKP'],
                            'MANAGER_PKP' => fn() => ['status' => 'Pending', 'next' => 'MANAGER_KEUANGAN'],
                            'MANAGER_KEUANGAN' => fn() => $nominalOver50M
                                ? ['status' => 'Pending Director Otorisasi', 'next' => 'DIREKTUR']
                                : ['status' => 'Approved', 'next' => 'KASIR_PUSAT'],
                            'DIREKTUR' => fn() => ['status' => 'Approved', 'next' => 'KASIR_PUSAT'],
                            'KOORDINATOR_BATRA' => fn() => ['status' => 'Pending', 'next' => 'MANAGER_PKP'],
                            'KOORDINATOR_KLINIK' => fn() => ['status' => 'Pending', 'next' => 'MANAGER_PKP'],
                            'KOORDINATOR_DIKLAT' => fn() => ['status' => 'Pending', 'next' => 'MANAGER_PKP'],
                        ];
                    }

                    $resolver = $nextMap[$effectiveRole] ?? null;
                    if (!$resolver) {
                        throw new \Exception("WORKFLOW UNMAPPED: Role {$effectiveRole} tidak punya transisi approve pada flow {$flowType}.");
                    }

                    $result = $resolver();
                    $status = $result['status'];
                    $nextPosisi = $result['next'];
                } elseif ($request->aksi === 'revise') {
                    $status = 'Revisi';
                    $nextPosisi = 'MAKER';
                } else {
                    $status = 'Rejected';
                    $nextPosisi = 'REJECTED';
                }
            } else {
                $status = $surat->status_surat;
                $nextPosisi = $surat->posisi_saat_ini;
            }

            DB::table('surat_permintaan')
                ->where('no_surat', $id)
                ->where('posisi_saat_ini', $effectiveRole)
                ->update([
                    'status_surat' => $status,
                    'posisi_saat_ini' => $nextPosisi,
                    'keterangan_checker' => $request->alasan,
                    'updated_at' => now()
                ]);

            if ($request->hasFile('file_checker')) {
                foreach ($request->file('file_checker') as $file) {
                    $namaFileAccess = 'checker_' . $id . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move(storage_path('app/private/lampiran_spp'), $namaFileAccess);

                    DB::table('surat_permintaan_files')->insert([
                        'no_surat' => $id,
                        'nama_file' => $namaFileAccess,
                        'kategori' => 'CHECKER',
                    ]);
                }
            }

            $pesanLog = "User [{$currentRole}] mengeksekusi keputusan [{$request->aksi}] pada SPP No {$surat->no_surat}. Dokumen bermigrasi ke: [{$nextPosisi}] dengan status [{$status}].";
            self::simpanLog('APPROVAL_TRANSACTION', $pesanLog, $surat);

            // Catat history approval SPP
            DB::table('spp_history')->insert([
                'no_surat' => $id,
                'status_dari' => $surat->status_surat,
                'status_ke' => $status,
                'posisi_dari' => $surat->posisi_saat_ini,
                'posisi_ke' => $nextPosisi,
                'aktor_username' => Auth::user()->username,
                'aktor_role' => $currentRole,
                'keterangan' => $request->aksi . ($request->alasan ? ': ' . $request->alasan : ''),
                'payload_before' => json_encode($surat),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Kirim notifikasi ke posisi selanjutnya
            if (in_array($request->aksi, ['approve', 'revise'])) {
                $notifType = $request->aksi === 'approve' ? NotificationService::TYPE_PENDING_APPROVAL : NotificationService::TYPE_REVISED;
                $notifTitle = $request->aksi === 'approve' ? 'Perlu Persetujuan - ' . $id : 'Revisi - ' . $id;
                $notifMsg = $request->aksi === 'approve'
                    ? "SPP {$id} telah disetujui oleh {$currentRole}. Sekarang menunggu persetujuan {$nextPosisi}."
                    : "SPP {$id} direvisi oleh {$currentRole}. Silakan perbaiki dan kirim ulang.";

                if ($nextPosisi === 'MAKER' && $request->aksi === 'revise') {
                    NotificationService::sendToUser($surat->id_maker, $notifType, $notifTitle, $notifMsg, 'SPP', $id);
                } else {
                    NotificationService::sendToRole($nextPosisi, $notifType, $notifTitle, $notifMsg, 'SPP', $id);
                }
            } elseif ($request->aksi === 'reject') {
                NotificationService::sendToUser($surat->id_maker, NotificationService::TYPE_REJECTED,
                    'Ditolak - ' . $id,
                    "SPP {$id} ditolak oleh {$currentRole}. Alasan: " . ($request->alasan ?? '-'),
                    'SPP', $id);
            }

            DB::commit();

            $feedbackSuccess = ($request->aksi == 'approve')
                ? "Pengajuan berhasil diproses! Aliran dokumen diteruskan ke: {$nextPosisi}."
                : "Pengajuan SPP resmi ditolak.";

            return redirect('/spp')->with('success', $feedbackSuccess);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SPP validasi failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'no_surat' => $id ?? null,
            ]);

            return redirect()->back()->with('error', 'Gagal memproses validasi. Silakan coba kembali.');
        }
    }

    // =========================================================================
    // 3. PROSES PENCUIRAN DANA FINAL (IDEMPOTENCY ENFORCED)
    // =========================================================================
    public function cairkan(Request $request)
    {
        if (session('role') != 'KASIR_PUSAT' && session('role') != 'ADMIN') {
            abort(403, 'Akses Otoritas Ditolak.');
        }

        $id = $request->input('no_surat');
        if (!$id) {
            return redirect()->back()->with('error', 'Nomor SPP tidak ditemukan untuk proses pencairan.');
        }

        DB::beginTransaction();

        try {
            $surat = DB::table('surat_permintaan')
                ->where('no_surat', $id)
                ->where('status_surat', 'Approved')
                ->where('posisi_saat_ini', 'KASIR_PUSAT')
                ->lockForUpdate()
                ->first();

            if (!$surat) {
                DB::rollBack();
                return redirect()->back()->with('error', '⚠️ PERINGATAN IDEMPOTENCY: Berkas ini sudah dicairkan sebelumnya atau status transaksi tidak valid!');
            }

            $items = DB::table('surat_permintaan_detail')
                ->where('no_surat', $surat->no_surat)
                ->get();

            foreach ($items as $item) {
                DB::table('master_budget')
                    ->where('kode_budget', $item->kode_budget)
                    ->increment('terserap', $item->nominal);
            }

            DB::table('surat_permintaan')->where('no_surat', $id)->update([
                'status_surat' => 'Disbursed',
                'posisi_saat_ini' => 'FINISH',
                'updated_at' => now()
            ]);

            self::simpanLog('DISBURSED_FINAL', "Kasir Pusat resmi mencairkan dana transfer bank untuk SPP No {$surat->no_surat}", $surat);

            // Catat history pencairan
            DB::table('spp_history')->insert([
                'no_surat' => $surat->no_surat,
                'status_dari' => 'Approved',
                'status_ke' => 'Disbursed',
                'posisi_dari' => 'KASIR_PUSAT',
                'posisi_ke' => 'FINISH',
                'aktor_username' => Auth::user()->username,
                'aktor_role' => session('role'),
                'keterangan' => 'Pencairan dana oleh Kasir Pusat',
                'payload_before' => json_encode($surat),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Kirim notifikasi ke pembuat SPP
            NotificationService::sendToUser($surat->id_maker, NotificationService::TYPE_DISBURSED,
                'Pencairan - ' . $surat->no_surat,
                "SPP {$surat->no_surat} telah dicairkan sebesar Rp " . number_format($surat->total_nominal, 0, ',', '.'),
                'SPP', $surat->no_surat);

            DB::commit();
            return redirect('/spp')->with('success', "💵 Sukses! Dana SPP No {$surat->no_surat} telah resmi dicairkan dan saldo anggaran actual berhasil diperbarui!");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SPP cairkan failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'no_surat' => $id ?? null,
            ]);

            return redirect()->back()->with('error', 'Gagal mencairkan dana. Silakan coba kembali.');
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

