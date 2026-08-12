<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;

class CheckMaintenance
{
    public function handle(Request $request, Closure $next)
    {
        if (SystemSetting::getValue('maintenance_mode') === 'true' && session('role') !== 'ADMIN') {
            abort(503);
        }

        return $next($request);
    }
}