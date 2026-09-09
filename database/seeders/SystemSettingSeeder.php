<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['key' => 'app_name', 'value' => 'Finance Management Demo', 'group' => 'general', 'label' => 'Nama Aplikasi', 'type' => 'text'],
            ['key' => 'max_file_size', 'value' => '5120', 'group' => 'general', 'label' => 'Max Ukuran File (KB)', 'type' => 'number'],
            ['key' => 'session_timeout', 'value' => '7200', 'group' => 'security', 'label' => 'Session Timeout (detik)', 'type' => 'number'],
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

        $this->command->info('Default system settings created successfully!');
    }
}
