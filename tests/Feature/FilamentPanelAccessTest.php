<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class FilamentPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_national_admin_can_access_admin_panel(): void
    {
        $user = User::factory()->create();
        $user->assignRole($this->getOrCreateRole('national_admin'));

        $panel = Filament::getPanel('admin');

        $this->assertTrue($user->canAccessPanel($panel));
    }

    public function test_national_admin_can_access_seims_panel(): void
    {
        $user = User::factory()->create();
        $user->assignRole($this->getOrCreateRole('national_admin'));

        $panel = Filament::getPanel('seims');

        $this->assertTrue($user->canAccessPanel($panel));
    }

    public function test_non_national_admin_user_can_access_seims_but_not_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole($this->getOrCreateRole('district_officer'));

        $adminPanel = Filament::getPanel('admin');
        $seimsPanel = Filament::getPanel('seims');

        $this->assertFalse($user->canAccessPanel($adminPanel));
        $this->assertTrue($user->canAccessPanel($seimsPanel));
    }

    public function test_national_sped_officer_can_access_seims_but_not_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole($this->getOrCreateRole('national_sped_officer'));

        $adminPanel = Filament::getPanel('admin');
        $seimsPanel = Filament::getPanel('seims');

        $this->assertFalse($user->canAccessPanel($adminPanel));
        $this->assertTrue($user->canAccessPanel($seimsPanel));
    }

    public function test_user_without_role_cannot_access_any_panel(): void
    {
        $user = User::factory()->create();

        $adminPanel = Filament::getPanel('admin');
        $seimsPanel = Filament::getPanel('seims');

        $this->assertFalse($user->canAccessPanel($adminPanel));
        $this->assertFalse($user->canAccessPanel($seimsPanel));
    }

    private function getOrCreateRole(string $name): Role
    {
        return Role::firstOrCreate([
            'name' => $name,
            'guard_name' => 'web',
        ]);
    }
}
