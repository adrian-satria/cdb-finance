<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SystemSettingRuntimeTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function maintenance_mode_blocks_public_access()
    {
        DB::table('system_settings')->upsert(
            ['key' => 'maintenance_mode', 'value' => 'true', 'group' => 'general', 'label' => 'Mode Maintenance', 'type' => 'boolean'],
            'key'
        );

        $this->get('/login')->assertStatus(503);
    }

    /** @test */
    public function maintenance_mode_bypasses_admin_session()
    {
        DB::table('system_settings')->upsert(
            ['key' => 'maintenance_mode', 'value' => 'true', 'group' => 'general', 'label' => 'Mode Maintenance', 'type' => 'boolean'],
            'key'
        );

        $this->withSession(['role' => 'ADMIN'])->get('/login')->assertOk();
    }

    /** @test */
    public function maintenance_mode_off_allows_access_when_disabled()
    {
        $this->get('/login')->assertOk();
    }
}