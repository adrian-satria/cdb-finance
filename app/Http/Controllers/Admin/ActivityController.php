<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::orderBy('created_at', 'desc');

        if ($request->filled('username')) {
            $query->where('username', 'like', '%' . $request->username . '%');
        }

        if ($request->filled('aktivitas')) {
            $query->where('aktivitas', $request->aktivitas);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->paginate(50);

        $activeUsers = User::whereHas('akses', function ($q) {
            $q->whereNotNull('role');
        })->withCount(['akses'])->get();

        $todayStats = ActivityLog::whereDate('created_at', today())
            ->select(
                DB::raw('COUNT(*) as total'),
                DB::raw('COUNT(DISTINCT username) as unique_users')
            )
            ->first();

        return view('admin.activity.index', compact('activities', 'activeUsers', 'todayStats'));
    }

    public function onlineUsers()
    {
        $recentActivity = ActivityLog::where('created_at', '>=', now()->subMinutes(15))
            ->select('username', 'role', 'aktivitas', 'created_at')
            ->distinct('username')
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('username')
            ->values();

        return response()->json($recentActivity);
    }
}
