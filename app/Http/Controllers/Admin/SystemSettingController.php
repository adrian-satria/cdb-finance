<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SystemSettingController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::orderBy('group')->orderBy('label')->paginate(20);
        $groups = SystemSetting::select('group')->distinct()->pluck('group');

        return view('admin.settings.index', compact('settings', 'groups'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'value' => 'nullable|string',
        ]);

        SystemSetting::setValue($request->key, $request->value);

        return redirect()->back()->with('success', 'Pengaturan berhasil diperbarui!');
    }

    public function createDefault()
    {
        $defaults = [
            ['key' => 'app_name', 'value' => 'Finance Management Demo', 'group' => 'general', 'label' => 'Nama Aplikasi', 'type' => 'text'],
            ['key' => 'max_file_size', 'value' => '5120', 'group' => 'general', 'label' => 'Max Ukuran File (KB)', 'type' => 'number'],
            ['key' => 'session_timeout', 'value' => '120', 'group' => 'security', 'label' => 'Session Timeout (menit)', 'type' => 'number'],
            ['key' => 'password_min_length', 'value' => '8', 'group' => 'security', 'label' => 'Min Panjang Password', 'type' => 'number'],
            ['key' => 'login_attempt_limit', 'value' => '5', 'group' => 'security', 'label' => 'Maks Percobaan Login', 'type' => 'number'],
            ['key' => 'maintenance_mode', 'value' => 'false', 'group' => 'general', 'label' => 'Mode Maintenance', 'type' => 'boolean'],
            ['key' => 'enable_notification', 'value' => 'true', 'group' => 'notification', 'label' => 'Aktifkan Notifikasi', 'type' => 'boolean'],
        ];

        foreach ($defaults as $setting) {
            SystemSetting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        return redirect()->back()->with('success', 'Pengaturan default berhasil dibuat!');
    }

    public function clearCache()
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');

        return redirect()->back()->with('success', 'Cache berhasil dibersihkan!');
    }
}
