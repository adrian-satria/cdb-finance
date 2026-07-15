<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditTrailController extends Controller
{
    public function index()
    {
        // Tarik data log, urutkan dari yang paling baru terjadi
        $logs = DB::table('audit_trails')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('admin.audit_trail.index', compact('logs'));
    }
}