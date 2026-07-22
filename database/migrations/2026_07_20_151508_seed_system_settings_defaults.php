<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            ['key' => 'app_name', 'value' => 'B-SMART', 'group' => 'general', 'label' => 'Nama Aplikasi', 'type' => 'text'],
            ['key' => 'max_file_size', 'value' => '5120', 'group' => 'general', 'label' => 'Max Ukuran File (KB)', 'type' => 'number'],
            ['key' => 'session_timeout', 'value' => '120', 'group' => 'security', 'label' => 'Session Timeout (menit)', 'type' => 'number'],
            ['key' => 'password_min_length', 'value' => '8', 'group' => 'security', 'label' => 'Min Panjang Password', 'type' => 'number'],
            ['key' => 'login_attempt_limit', 'value' => '5', 'group' => 'security', 'label' => 'Maks Percobaan Login', 'type' => 'number'],
            ['key' => 'maintenance_mode', 'value' => 'false', 'group' => 'general', 'label' => 'Mode Maintenance', 'type' => 'boolean'],
            ['key' => 'enable_notification', 'value' => 'true', 'group' => 'notification', 'label' => 'Aktifkan Notifikasi', 'type' => 'boolean'],
        ];

        foreach ($defaults as $setting) {
            DB::table('system_settings')->insert($setting);
        }
    }

    public function down(): void
    {
        DB::table('system_settings')->whereIn('key', [
            'app_name', 'max_file_size', 'session_timeout', 'password_min_length',
            'login_attempt_limit', 'maintenance_mode', 'enable_notification',
        ])->delete();
    }
};
