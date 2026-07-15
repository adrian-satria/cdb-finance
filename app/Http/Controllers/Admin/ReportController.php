<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function budgetVsActual(Request $request)
    {
        $kodeProject = $request->query('kode_project');
        $tahun = $request->query('tahun', date('Y'));

        $query = DB::table('master_budget')
            ->leftJoin('project', 'master_budget.kode_project', '=', 'project.kode_project')
            ->select(
                'master_budget.*',
                'project.nama_project',
                DB::raw('(master_budget.alokasi_dana - master_budget.terserap) as sisa_saldo'),
                DB::raw('CASE WHEN master_budget.alokasi_dana > 0 THEN ROUND((master_budget.terserap / master_budget.alokasi_dana) * 100, 2) ELSE 0 END as persentase_serap')
            );

        if ($kodeProject) {
            $query->where('master_budget.kode_project', $kodeProject);
        }

        $budgets = $query->orderBy('master_budget.kode_project')->paginate(50);

        $projects = DB::table('project')->orderBy('kode_project')->get();

        $summary = DB::table('master_budget')
            ->select(
                DB::raw('SUM(alokasi_dana) as total_alokasi'),
                DB::raw('SUM(terserap) as total_terserap'),
                DB::raw('SUM(alokasi_dana - terserap) as total_sisa'),
                DB::raw('COUNT(*) as total_budget')
            )
            ->first();

        return view('admin.reports.budget_vs_actual', compact('budgets', 'projects', 'summary', 'kodeProject', 'tahun'));
    }

    public function financialSummary(Request $request)
    {
        $tahun = $request->query('tahun', date('Y'));
        $bulan = $request->query('bulan', date('m'));

        $monthlyData = DB::table('surat_permintaan')
            ->select(
                DB::raw('YEAR(created_at) as tahun'),
                DB::raw('MONTH(created_at) as bulan'),
                DB::raw('COUNT(*) as total_spp'),
                DB::raw('SUM(total_nominal) as total_nominal'),
                DB::raw("SUM(CASE WHEN status_surat = 'Pending' THEN 1 ELSE 0 END) as pending"),
                DB::raw("SUM(CASE WHEN status_surat = 'Approved' THEN 1 ELSE 0 END) as approved"),
                DB::raw("SUM(CASE WHEN status_surat = 'Disbursed' THEN 1 ELSE 0 END) as disbursed"),
                DB::raw("SUM(CASE WHEN status_surat = 'Rejected' THEN 1 ELSE 0 END) as rejected")
            )
            ->whereYear('created_at', $tahun)
            ->groupBy('tahun', 'bulan')
            ->orderBy('bulan')
            ->get();

        $projectSummary = DB::table('surat_permintaan')
            ->select(
                'kode_project',
                DB::raw('COUNT(*) as total_spp'),
                DB::raw('SUM(total_nominal) as total_nominal'),
                DB::raw("SUM(CASE WHEN status_surat = 'Disbursed' THEN total_nominal ELSE 0 END) as total_disbursed")
            )
            ->whereYear('created_at', $tahun)
            ->groupBy('kode_project')
            ->get();

        return view('admin.reports.financial_summary', compact('monthlyData', 'projectSummary', 'tahun', 'bulan'));
    }

    public function areaPerformance(Request $request)
    {
        $tahun = $request->query('tahun', date('Y'));

        $areaData = DB::table('surat_permintaan')
            ->select(
                'kode_area',
                DB::raw('COUNT(*) as total_spp'),
                DB::raw('SUM(total_nominal) as total_nominal'),
                DB::raw('AVG(total_nominal) as rata_rata'),
                DB::raw("SUM(CASE WHEN status_surat = 'Disbursed' THEN total_nominal ELSE 0 END) as realized")
            )
            ->whereYear('created_at', $tahun)
            ->groupBy('kode_area')
            ->orderByDesc('total_nominal')
            ->get();

        return view('admin.reports.area_performance', compact('areaData', 'tahun'));
    }
}
