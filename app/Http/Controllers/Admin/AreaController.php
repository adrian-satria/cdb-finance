<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AreaController extends Controller
{
    public function index()
    {
        $areas = Area::with('projects')->orderBy('kode_area')->paginate(20);
        return view('admin.area.index', compact('areas'));
    }

    public function create()
    {
        return view('admin.area.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_area' => 'required|string|max:20|unique:area,kode_area',
            'nama_area' => 'required|string|max:100',
        ]);

        Area::create($request->only(['kode_area', 'nama_area']));

        return redirect()->route('admin.area.index')->with('success', 'Area berhasil ditambahkan!');
    }

    public function edit($kode_area)
    {
        $area = Area::findOrFail($kode_area);
        return view('admin.area.form', compact('area'));
    }

    public function update(Request $request, $kode_area)
    {
        $area = Area::findOrFail($kode_area);

        $request->validate([
            'kode_area' => 'required|string|max:20|unique:area,kode_area,' . $kode_area . ',kode_area',
            'nama_area' => 'required|string|max:100',
        ]);

        $area->update($request->only(['kode_area', 'nama_area']));

        return redirect()->route('admin.area.index')->with('success', 'Area berhasil diperbarui!');
    }

    public function destroy($kode_area)
    {
        $area = Area::findOrFail($kode_area);

        $sppCount = DB::table('surat_permintaan')->where('kode_area', $kode_area)->count();
        if ($sppCount > 0) {
            return redirect()->route('admin.area.index')->with('error', "Tidak bisa hapus: masih ada {$sppCount} SPP terkait area ini.");
        }

        $area->delete();
        return redirect()->route('admin.area.index')->with('success', 'Area berhasil dihapus!');
    }
}
