<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

class UserModelTest extends TestCase
{
    use DatabaseTransactions;

    private function createTestUser(): User
    {
        $userId = DB::table('users')->insertGetId([
            'name' => 'User Model Test',
            'username' => 'usermodeltest',
            'email' => 'usermodel@test.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('users')->where('id', $userId)->update(['id_user' => $userId]);
        return User::find($userId);
    }

    private function addAccess(User $user, string $role, ?string $area = null, ?string $project = null): void
    {
        DB::table('user_access')->insert([
            'id_user' => $user->id_user,
            'role' => $role,
            'jabatan' => $role,
            'kode_area' => $area,
            'kode_project' => $project,
        ]);
    }

    /** @test */
    public function has_role_returns_true_when_user_has_role()
    {
        $user = $this->createTestUser();
        $this->addAccess($user, 'MAKER', 'pusat');

        $this->assertTrue($user->hasRole('MAKER'));
        $this->assertFalse($user->hasRole('ADMIN'));
    }

    /** @test */
    public function has_any_role_returns_true_for_multiple_roles()
    {
        $user = $this->createTestUser();
        $this->addAccess($user, 'MAKER', 'pusat');
        $this->addAccess($user, 'CHECKER', 'pusat');

        $this->assertTrue($user->hasAnyRole(['MAKER', 'CHECKER']));
        $this->assertFalse($user->hasAnyRole(['ADMIN', 'DIREKTUR']));
    }

    /** @test */
    public function active_roles_returns_all_unique_roles()
    {
        $user = $this->createTestUser();
        $this->addAccess($user, 'MAKER', 'pusat');
        $this->addAccess($user, 'CHECKER', 'pusat');

        $roles = $user->active_roles;
        $this->assertCount(2, $roles);
        $this->assertContains('MAKER', $roles);
        $this->assertContains('CHECKER', $roles);
    }

    /** @test */
    public function has_access_to_project_checks_correctly()
    {
        $user = $this->createTestUser();
        $this->addAccess($user, 'FINANCE_PROJECT', 'pusat', '38');

        $this->assertTrue($user->hasAccessToProject('38'));
        $this->assertFalse($user->hasAccessToProject('40'));
    }

    /** @test */
    public function has_access_to_all_projects_when_project_is_all()
    {
        $user = $this->createTestUser();
        $this->addAccess($user, 'ADMIN', null, 'all');

        $this->assertTrue($user->hasAccessToProject('38'));
        $this->assertTrue($user->hasAccessToProject('40'));
    }

    /** @test */
    public function primary_role_returns_first_role()
    {
        $user = $this->createTestUser();
        $this->addAccess($user, 'MAKER', 'pusat');

        $this->assertEquals('MAKER', $user->primary_role);
    }

    /** @test */
    public function has_access_to_area_checks_correctly()
    {
        $user = $this->createTestUser();
        $this->addAccess($user, 'MAKER', 'alor');

        $this->assertTrue($user->hasAccessToArea('alor'));
        $this->assertFalse($user->hasAccessToArea('belu'));
    }
}
