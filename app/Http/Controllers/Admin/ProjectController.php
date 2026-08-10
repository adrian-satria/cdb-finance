<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with('areas')->orderBy('kode_project')->paginate(20);
        return view('admin.project.index', compact('projects'));
    }

    public function create()
    {
        $areas = Area::orderBy('kode_area')->get();
        return view('admin.project.form', compact('areas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_project' => 'required|string|max:10|unique:project,kode_project',
            'nama_project' => 'required|string|max:100',
            'areas' => 'nullable|array',
            'areas.*' => 'string|max:20|exists:area,kode_area',
        ]);

        DB::transaction(function () use ($request) {
            Project::create([
                'kode_project' => $request->kode_project,
                'nama_project' => $request->nama_project,
            ]);

            if ($request->areas) {
                $pivot = array_map(fn($a) => [
                    'kode_project' => $request->kode_project,
                    'kode_area' => $a,
                ], $request->areas);
                DB::table('project_area')->insert($pivot);
            }
        });

        return redirect()->route('admin.project.index')->with('success', 'Project berhasil ditambahkan!');
    }

    public function edit($kode_project)
    {
        $project = Project::with('areas')->findOrFail($kode_project);
        $areas = Area::orderBy('kode_area')->get();
        return view('admin.project.form', compact('project', 'areas'));
    }

    public function update(Request $request, $kode_project)
    {
        $project = Project::findOrFail($kode_project);

        $request->validate([
            'nama_project' => 'required|string|max:100',
            'areas' => 'nullable|array',
            'areas.*' => 'string|max:20|exists:area,kode_area',
        ]);

        DB::transaction(function () use ($request, $project) {
            $project->update(['nama_project' => $request->nama_project]);

            DB::table('project_area')->where('kode_project', $project->kode_project)->delete();
            if ($request->areas) {
                $pivot = array_map(fn($a) => [
                    'kode_project' => $project->kode_project,
                    'kode_area' => $a,
                ], $request->areas);
                DB::table('project_area')->insert($pivot);
            }
        });

        return redirect()->route('admin.project.index')->with('success', 'Project berhasil diperbarui!');
    }

    public function destroy($kode_project)
    {
        $project = Project::findOrFail($kode_project);

        $sppCount = DB::table('surat_permintaan')->where('kode_project', $kode_project)->count();
        if ($sppCount > 0) {
            return redirect()->route('admin.project.index')->with('error', "Tidak bisa hapus: masih ada {$sppCount} SPP terkait project ini.");
        }

        DB::transaction(function () use ($project) {
            DB::table('project_area')->where('kode_project', $project->kode_project)->delete();
            $project->delete();
        });

        return redirect()->route('admin.project.index')->with('success', 'Project berhasil dihapus!');
    }
}
