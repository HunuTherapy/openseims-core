<?php

namespace Tests\Feature\Filament\Admin;

use App\Filament\Admin\Resources\RoleResource\Pages\ListRoles;
use App\Filament\Admin\Resources\RoleResource\Pages\ViewRole;
use App\Models\Role;
use Livewire\Livewire;
use Tests\Feature\Filament\AdminResourceTestCase;

class RoleResourceTest extends AdminResourceTestCase
{
    public function test_can_list_roles(): void
    {
        Role::query()->create([
            'name' => 'teacher',
            'display_name' => 'Teacher',
            'guard_name' => 'web',
        ]);

        Livewire::test(ListRoles::class)
            ->assertOk()
            ->loadTable()
            ->assertSee('Teacher');
    }

    public function test_can_view_role_details(): void
    {
        $role = Role::query()->create([
            'name' => 'teacher',
            'display_name' => 'Teacher',
            'guard_name' => 'web',
        ]);

        Livewire::test(ViewRole::class, ['record' => $role->getKey()])
            ->assertOk()
            ->assertSee('Teacher');
    }
}
