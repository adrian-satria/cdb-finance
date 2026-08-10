<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Support\RoleHelper;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $role = session('role');
        $kodeArea = session('kode_area');
        $userProject = session('kode_project');
        $selectedProject = $request->query('kode_project');

        // Admin filter takes precedence
        if ($role === 'ADMIN' && $selectedProject) {
            $userProject = $selectedProject;
        }

        // --- SPP Query with Role Scope ---
        $sppQuery = self::buildScopedQuery($role, $kodeArea, $userProject, $selectedProject);

        // --- Stat Counts ---
        $countPending = (clone $sppQuery)
            ->whereIn('status_surat', ['Pending', 'Pending Director Otorisasi'])
            ->count();
        $countApproved = (clone $sppQuery)
            ->where('status_surat', 'Approved')
            ->count();
        $countRejected = (clone $sppQuery)
            ->where('status_surat', 'Rejected')
            ->count();
        $countDisbursed = (clone $sppQuery)
            ->where('status_surat', 'Disbursed')
            ->count();

        // --- Budget Overview (scoped) ---
        $budgetQuery = DB::table('master_budget');
        if ($role !== 'ADMIN' || $selectedProject) {
            $projectFilter = $selectedProject ?: ($userProject ?? null);
            if ($projectFilter && $projectFilter !== 'all') {
                $budgetQuery->where('kode_project', $projectFilter);
            }
        }
        $totalBudget = (float) (clone $budgetQuery)->sum('alokasi_dana');
        $totalTerserap = (float) (clone $budgetQuery)->sum('terserap');
        $sisaBudget = max($totalBudget - $totalTerserap, 0);
        $persenUtilisasi = $totalBudget > 0 ? round(($totalTerserap / $totalBudget) * 100, 1) : 0;

        // --- Budget Per Project (Admin only) ---
        $budgetPerProject = null;
        $criticalBudgets = null;
        if ($role === 'ADMIN' && ! $selectedProject) {
            $budgetPerProject = DB::table('master_budget')
                ->select(
                    'kode_project',
                    DB::raw('COALESCE(SUM(alokasi_dana), 0) as total_alokasi'),
                    DB::raw('COALESCE(SUM(terserap), 0) as total_terserap'),
                    DB::raw('ROUND(COALESCE(SUM(terserap) / NULLIF(SUM(alokasi_dana), 0) * 100, 0), 1) as persen')
                )
                ->groupBy('kode_project')
                ->orderBy('kode_project')
                ->get();

            $criticalBudgets = $budgetPerProject->filter(fn ($b) => $b->persen >= 90);
        }

        // --- Monthly Chart ---
        $monthlyChartQuery = self::buildScopedQuery($role, $kodeArea, $userProject, $selectedProject);
        $monthlyChart = $monthlyChartQuery
            ->select(
                DB::raw('MONTH(created_at) as bulan_num'),
                DB::raw('COUNT(*) as total'),
                DB::raw('COALESCE(SUM(total_nominal), 0) as nominal')
            )
            ->whereYear('created_at', date('Y'))
            ->groupBy('bulan_num')
            ->orderBy('bulan_num')
            ->get()
            ->map(function ($item) {
                $months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

                return [
                    'bulan' => $months[$item->bulan_num] ?? $item->bulan_num,
                    'total' => (int) $item->total,
                    'nominal' => (float) $item->nominal,
                ];
            });

        // --- My Tasks ---
        $tasksQuery = DB::table('surat_permintaan');
        if ($role === 'ADMIN') {
            if ($selectedProject) {
                $tasksQuery->where('kode_project', $selectedProject);
            }
            $tasksQuery->whereIn('status_surat', ['Pending', 'Pending Director Otorisasi']);
        } elseif (in_array($role, ['MAKER', 'AREA_MANAGER'], true)) {
            $tasksQuery->where('kode_area', $kodeArea)
                ->whereIn('status_surat', ['Pending', 'Pending Director Otorisasi']);
        } else {
            // Project-scoped roles & others see tasks at their current position
            $tasksQuery->where('posisi_saat_ini', $role)
                ->whereIn('status_surat', ['Pending', 'Pending Director Otorisasi']);
            if ($userProject && $userProject !== 'all') {
                $tasksQuery->where('kode_project', $userProject);
            }
        }
        $tasks = $tasksQuery->orderBy('created_at', 'desc')->limit(10)->get();

        // --- Projects for Admin Filter ---
        $projects = RoleHelper::isGlobal($role)
            ? Cache::remember('ref_projects', 3600, fn() => Project::orderBy('kode_project')->get())
            : collect();

        return view('dashboard.index', compact(
            'countPending', 'countApproved', 'countRejected', 'countDisbursed',
            'totalBudget', 'totalTerserap', 'sisaBudget', 'persenUtilisasi',
            'budgetPerProject', 'criticalBudgets',
            'monthlyChart', 'tasks', 'projects', 'selectedProject'
        ));
    }

    protected static function buildScopedQuery(string $role, ?string $kodeArea, ?string $userProject, ?string $selectedProject): Builder
    {
        $query = DB::table('surat_permintaan');

        if ($role === 'ADMIN' && $selectedProject) {
            return $query->where('kode_project', $selectedProject);
        }

        if (RoleHelper::isStaffArea($role)) {
            $query->where('kode_area', $kodeArea);
        } elseif (RoleHelper::isProjectScoped($role)) {
            if ($userProject && $userProject !== '' && $userProject !== 'all') {
                $query->where('kode_project', $userProject);
            }
        }

        return $query;
    }
}
