<?php

namespace Tests\Unit\Services;

use App\Services\NotificationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use DatabaseTransactions;

    private NotificationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(NotificationService::class);

        foreach (['38', '40'] as $code) {
            DB::table('project')->upsert(
                ['kode_project' => $code, 'nama_project' => 'Project '.$code],
                'kode_project'
            );
        }

        $this->seedUser('pm.40', 'PROJECT_MANAGER', '40', 'belu');
        $this->seedUser('pm.38', 'PROJECT_MANAGER', '38', 'belu');
        $this->seedUser('maker.belu', 'MAKER', 'all', 'belu');
        $this->seedUser('maker.atu', 'MAKER', 'all', 'atu');
        $this->seedUser('dir', 'DIREKTUR', 'all', 'all');
    }

    private function seedUser(string $username, string $role, string $project, string $area): int
    {
        $id = DB::table('users')->insertGetId([
            'name' => $username,
            'username' => $username,
            'password' => bcrypt('password'),
            'email' => $username.'@test.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('users')->where('id', $id)->update(['id_user' => $id]);
        DB::table('user_access')->insert([
            'id_user' => $id,
            'role' => $role,
            'jabatan' => $role,
            'kode_area' => $area,
            'kode_project' => $project,
        ]);

        return $id;
    }

    private function notifyCount(int $userId): int
    {
        return DB::table('notifications')->where('user_id', $userId)->count();
    }

    /** @test */
    public function project_scoped_role_only_notifies_matching_project(): void
    {
        $pm40 = DB::table('users')->where('username', 'pm.40')->first()->id;
        $pm38 = DB::table('users')->where('username', 'pm.38')->first()->id;

        $this->service->sendToRoleScoped(
            'PROJECT_MANAGER', 'belu', '40',
            NotificationService::TYPE_PENDING_APPROVAL, 'T', 'M', 'SPP', 'X'
        );

        $this->assertEquals(1, $this->notifyCount($pm40));
        $this->assertEquals(0, $this->notifyCount($pm38));
    }

    /** @test */
    public function staff_area_role_only_notifies_matching_area(): void
    {
        $makerBelu = DB::table('users')->where('username', 'maker.belu')->first()->id;
        $makerAtu = DB::table('users')->where('username', 'maker.atu')->first()->id;

        $this->service->sendToRoleScoped(
            'MAKER', 'belu', '40',
            NotificationService::TYPE_PENDING_APPROVAL, 'T', 'M', 'SPP', 'X'
        );

        $this->assertEquals(1, $this->notifyCount($makerBelu));
        $this->assertEquals(0, $this->notifyCount($makerAtu));
    }

    /** @test */
    public function global_role_notifies_all_with_role(): void
    {
        $dir = DB::table('users')->where('username', 'dir')->first()->id;

        $this->service->sendToRoleScoped(
            'DIREKTUR', 'belu', '40',
            NotificationService::TYPE_PENDING_APPROVAL, 'T', 'M', 'SPP', 'X'
        );

        $this->assertEquals(1, $this->notifyCount($dir));
    }
}
