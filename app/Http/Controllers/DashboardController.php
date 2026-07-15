<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $role = session('role');
        $kodeArea = session('kode_area');

        $base = DB::table('surat_permintaan');
        if ($role === 'MAKER') {
            $base->where('kode_area', $kodeArea);
        }

        $countPending = (clone $base)
            ->whereIn('status_surat', ['Pending', 'Pending Director Otorisasi'])
            ->count();

        $countApproved = (clone $base)
            ->where('status_surat', 'Approved')
            ->count();

        $countRejected = (clone $base)
            ->where('status_surat', 'Rejected')
            ->count();

        $countDisbursed = (clone $base)
            ->where('status_surat', 'Disbursed')
            ->count();

        $tasksQuery = (clone $base);
        if ($role === 'MAKER') {
            $tasksQuery->where('kode_area', $kodeArea)
                ->whereIn('status_surat', ['Pending', 'Pending Director Otorisasi']);
        } else {
            $tasksQuery->where('posisi_saat_ini', $role);
        }

        $tasks = $tasksQuery->orderBy('created_at', 'desc')->limit(10)->get();

        $totalBudget = DB::table('master_budget')->sum('alokasi_dana');
        $totalTerserap = DB::table('master_budget')->sum('terserap');

        $monthlyChart = DB::table('surat_permintaan')
            ->select(
                DB::raw('MONTH(created_at) as bulan_num'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(total_nominal) as nominal')
            )
            ->whereYear('created_at', date('Y'))
            ->groupBy('bulan_num')
            ->orderBy('bulan_num')
            ->get()
            ->map(function($item) {
                $months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                return [
                    'bulan' => $months[$item->bulan_num] ?? $item->bulan_num,
                    'total' => $item->total,
                    'nominal' => $item->nominal,
                ];
            });

        return view('dashboard.index', compact('countPending', 'countApproved', 'countRejected', 'countDisbursed', 'tasks', 'totalBudget', 'totalTerserap', 'monthlyChart'));
    }
}

