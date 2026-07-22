<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    protected function applyDateFilter($query, Request $request)
    {
        $from = $request->query('date_from');
        $to = $request->query('date_to');
        if ($from) $query->where('created_at', '>=', $from);
        if ($to) $query->where('created_at', '<=', $to . ' 23:59:59');
    }

    protected function csvResponse($filename, $headers, $rows)
    {
        $callback = function () use ($headers, $rows) {
            $f = fopen('php://output', 'w');
            fputs($f, "\xEF\xBB\xBF"); // BOM UTF-8 supaya Excel buka langsung
            fputcsv($f, $headers);
            foreach ($rows as $row) fputcsv($f, $row);
            fclose($f);
        };
        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ]);
    }

    // =========================================================================
    // 1. BUDGET VS ACTUAL
    // =========================================================================
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

        if ($request->query('export') === 'csv') {
            $data = $query->orderBy('master_budget.kode_project')->get();
            $rows = $data->map(fn($b) => [
                $b->kode_budget,
                $b->nama_budget,
                $b->kode_project,
                number_format($b->alokasi_dana, 0, ',', '.'),
                number_format($b->terserap, 0, ',', '.'),
                number_format($b->sisa_saldo, 0, ',', '.'),
                $b->persentase_serap . '%',
            ]);
            return $this->csvResponse('budget_vs_actual_' . date('Ymd'), [
                'Kode Budget', 'Nama Budget', 'Project', 'Alokasi (Rp)', 'Terserap (Rp)', 'Sisa (Rp)', 'Persentase'
            ], $rows);
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

    // =========================================================================
    // 2. RINGKASAN KEUANGAN
    // =========================================================================
    public function financialSummary(Request $request)
    {
        $tahun = $request->query('tahun', date('Y'));
        $kodeProject = $request->query('kode_project');

        $baseQuery = DB::table('surat_permintaan')->whereYear('created_at', $tahun);
        $this->applyDateFilter($baseQuery, $request);
        if ($kodeProject) $baseQuery->where('kode_project', $kodeProject);

        $monthlyData = (clone $baseQuery)
            ->select(
                DB::raw('YEAR(created_at) as tahun'),
                DB::raw('MONTH(created_at) as bulan'),
                DB::raw('COUNT(*) as total_spp'),
                DB::raw('SUM(total_nominal) as total_nominal'),
                DB::raw("SUM(CASE WHEN status_surat IN ('Pending','Pending Director Otorisasi') THEN 1 ELSE 0 END) as pending"),
                DB::raw("SUM(CASE WHEN status_surat = 'Approved' THEN 1 ELSE 0 END) as approved"),
                DB::raw("SUM(CASE WHEN status_surat = 'Disbursed' THEN 1 ELSE 0 END) as disbursed"),
                DB::raw("SUM(CASE WHEN status_surat = 'Rejected' THEN 1 ELSE 0 END) as rejected")
            )
            ->groupBy('tahun', 'bulan')
            ->orderBy('bulan')
            ->get();

        $projectSummary = (clone $baseQuery)
            ->select(
                'kode_project',
                DB::raw('COUNT(*) as total_spp'),
                DB::raw('SUM(total_nominal) as total_nominal'),
                DB::raw("SUM(CASE WHEN status_surat = 'Disbursed' THEN total_nominal ELSE 0 END) as total_disbursed")
            )
            ->groupBy('kode_project')
            ->get();

        if ($request->query('export') === 'csv') {
            $rows = collect();
            $rows->push(['--- DATA BULANAN ---']);
            $rows->push(['Bulan', 'Total SPP', 'Total Nominal', 'Pending', 'Approved', 'Disbursed', 'Rejected']);
            foreach ($monthlyData as $m) {
                $rows->push([$m->bulan, $m->total_spp, number_format($m->total_nominal, 0, ',', '.'),
                    $m->pending, $m->approved, $m->disbursed, $m->rejected]);
            }
            $rows->push([]);
            $rows->push(['--- RINGKASAN PER PROJECT ---']);
            $rows->push(['Project', 'Total SPP', 'Total Nominal', 'Total Disbursed']);
            foreach ($projectSummary as $p) {
                $rows->push([$p->kode_project, $p->total_spp, number_format($p->total_nominal, 0, ',', '.'),
                    number_format($p->total_disbursed, 0, ',', '.')]);
            }
            return $this->csvResponse('ringkasan_keuangan_' . date('Ymd'), [], $rows->toArray());
        }

        $projects = DB::table('project')->orderBy('kode_project')->get();
        return view('admin.reports.financial_summary', compact('monthlyData', 'projectSummary', 'tahun', 'kodeProject', 'projects'));
    }

    // =========================================================================
    // 3. PERFORMA AREA
    // =========================================================================
    public function areaPerformance(Request $request)
    {
        $tahun = $request->query('tahun', date('Y'));
        $kodeProject = $request->query('kode_project');

        $query = DB::table('surat_permintaan')->whereYear('created_at', $tahun);
        $this->applyDateFilter($query, $request);
        if ($kodeProject) $query->where('kode_project', $kodeProject);

        $areaData = (clone $query)
            ->select(
                'kode_area',
                DB::raw('COUNT(*) as total_spp'),
                DB::raw('SUM(total_nominal) as total_nominal'),
                DB::raw('AVG(total_nominal) as rata_rata'),
                DB::raw("SUM(CASE WHEN status_surat = 'Disbursed' THEN total_nominal ELSE 0 END) as realized")
            )
            ->groupBy('kode_area')
            ->orderByDesc('total_nominal')
            ->get();

        if ($request->query('export') === 'csv') {
            $rows = $areaData->map(fn($a) => [
                $a->kode_area,
                $a->total_spp,
                number_format($a->total_nominal, 0, ',', '.'),
                number_format($a->rata_rata, 0, ',', '.'),
                number_format($a->realized, 0, ',', '.'),
            ]);
            return $this->csvResponse('performa_area_' . date('Ymd'), [
                'Area', 'Total SPP', 'Total Nominal', 'Rata-rata', 'Realisasi'
            ], $rows);
        }

        $projects = DB::table('project')->orderBy('kode_project')->get();
        return view('admin.reports.area_performance', compact('areaData', 'tahun', 'kodeProject', 'projects'));
    }
}
