<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterBudget;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::orderBy('kode_project')->get();
        $kodeProject = $request->query('kode_project');
        $budgets = collect();

        if ($kodeProject) {
            $query = MasterBudget::with('project')->where('kode_project', $kodeProject);

            if ($search = $request->query('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('kode_budget', 'like', "%{$search}%")
                      ->orWhere('nama_budget', 'like', "%{$search}%");
                });
            }

            $budgets = $query->orderBy('kode_budget')->paginate(20);
        }

        if ($request->wantsJson()) {
            $html = view('admin.budget._table', compact('budgets', 'kodeProject'))->render();
            return response()->json(['html' => $html]);
        }

        return view('admin.budget.index', compact('budgets', 'projects', 'kodeProject'));
    }

    public function create()
    {
        $projects = Project::all();

        return view('admin.budget.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_project' => 'required',
            'kode_budget' => 'required|string|max:50',
            'nama_budget' => 'required|string|max:255',
            'alokasi_dana' => 'required|numeric|min:0',
        ]);

        MasterBudget::create($request->only(['kode_project', 'kode_budget', 'nama_budget', 'alokasi_dana']));

        return redirect()->route('admin.budget.index')->with('success', 'Master Budget berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $budget = MasterBudget::findOrFail($id);
        $projects = Project::all();

        return view('admin.budget.edit', compact('budget', 'projects'));
    }

    public function update(Request $request, $id)
    {
        $budget = MasterBudget::findOrFail($id);

        $request->validate([
            'kode_project' => 'required',
            'kode_budget' => 'required|string|max:50',
            'nama_budget' => 'required|string|max:255',
            'alokasi_dana' => 'required|numeric|min:0',
        ]);

        if ($request->alokasi_dana < ($budget->terserap ?? 0)) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'alokasi_dana' => 'Alokasi dana tidak boleh kurang dari total actual yang sudah terserap (Rp '.number_format($budget->terserap ?? 0, 0, ',', '.').').',
                ]);
        }

        $budget->update($request->only(['kode_project', 'kode_budget', 'nama_budget', 'alokasi_dana']));

        return redirect()->route('admin.budget.index')->with('success', 'Master Budget berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $budget = MasterBudget::findOrFail($id);
        $budget->delete();

        return redirect()->route('admin.budget.index')->with('success', 'Master Budget berhasil dihapus!');
    }

    public function areaForm($id)
    {
        $master = MasterBudget::findOrFail($id);

        $projectAreas = DB::table('project_area')
            ->join('area', 'project_area.kode_area', '=', 'area.kode_area')
            ->where('kode_project', $master->kode_project)
            ->select('area.kode_area', 'area.nama_area')
            ->orderBy('area.kode_area')
            ->get();

        $budgetAreas = DB::table('budget_area')
            ->where('kode_project', $master->kode_project)
            ->where('kode_budget', $master->kode_budget)
            ->get()
            ->keyBy('kode_area');

        return view('admin.budget.area', compact('master', 'projectAreas', 'budgetAreas'));
    }

    public function areaStore(Request $request, $id)
    {
        $master = MasterBudget::findOrFail($id);

        $request->validate([
            'alokasi' => 'required|array',
            'alokasi.*' => 'numeric|min:0',
        ]);

        $totalAlokasi = array_sum($request->alokasi);

        if (bccomp((string) $totalAlokasi, (string) $master->alokasi_dana, 2) === 1) {
            $fmtTotal = number_format($totalAlokasi, 0, ',', '.');
            $fmtMaster = number_format($master->alokasi_dana, 0, ',', '.');
            return back()->withInput()->with('error', "Total alokasi per area (Rp {$fmtTotal}) melebihi alokasi master (Rp {$fmtMaster}).");
        }

        DB::transaction(function () use ($request, $master) {
            foreach ($request->alokasi as $kodeArea => $alokasi) {
                $alokasi = (string) $alokasi;
                $existing = DB::table('budget_area')
                    ->where('kode_project', $master->kode_project)
                    ->where('kode_area', $kodeArea)
                    ->where('kode_budget', $master->kode_budget)
                    ->first();

                if ($existing) {
                    DB::table('budget_area')
                        ->where('id', $existing->id)
                        ->update(['alokasi_dana' => $alokasi, 'updated_at' => now()]);
                } else {
                    DB::table('budget_area')->insert([
                        'kode_project' => $master->kode_project,
                        'kode_area' => $kodeArea,
                        'kode_budget' => $master->kode_budget,
                        'nama_budget' => $master->nama_budget,
                        'alokasi_dana' => $alokasi,
                        'terserap' => 0,
                        'tahun' => $master->tahun ?? date('Y'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });

        return redirect()->route('admin.budget.index')->with('success', 'Budget per area berhasil disimpan!');
    }
}
