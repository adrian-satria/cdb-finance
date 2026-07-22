<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterBudget;
use App\Models\Project;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function index()
    {
        // Mengambil semua data budget beserta data project terkait
        $budgets = MasterBudget::with('project')->paginate(20);

        return view('admin.budget.index', compact('budgets'));
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
}
