<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditTrailController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('audit_trails');

        $sortColumns = ['created_at', 'username', 'role', 'aksi', 'ip_address'];
        $sort = in_array($request->query('sort'), $sortColumns) ? $request->query('sort') : 'created_at';
        $direction = strtolower($request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $logs = $query->orderBy($sort, $direction)->paginate(50);

        return view('admin.audit_trail.index', compact('logs'));
    }
}
