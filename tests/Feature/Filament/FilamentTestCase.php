<?php

namespace Tests\Feature\Filament;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

abstract class FilamentTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        ini_set('memory_limit', '512M');
        Cache::flush();
        config([
            'seims.active_reporting_year' => (int) env('SEIMS_ACTIVE_REPORTING_YEAR', 2026),
        ]);

        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // creates a national_admin
        $this->user = User::factory()->create();
        $this->assignRoleToUser();
        $this->actingAs($this->user);

        $this->setFilamentPanel();
    }

    abstract protected function setFilamentPanel(): void;

    protected function getUserRoleName(): string
    {
        return 'national_admin';
    }

    protected function usePanel(string $panelId): void
    {
        Filament::setCurrentPanel($panelId);
        Filament::bootCurrentPanel();
    }

    private function assignRoleToUser(): void
    {
        $role = Role::query()
            ->where('name', $this->getUserRoleName())
            ->where('guard_name', 'web')
            ->firstOrFail();

        $this->user->assignRole($role);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
