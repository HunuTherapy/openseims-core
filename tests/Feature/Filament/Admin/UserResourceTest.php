<?php

namespace Tests\Feature\Filament\Admin;

use App\Filament\Admin\Resources\UserResource\Pages\CreateUser;
use App\Filament\Admin\Resources\UserResource\Pages\EditUser;
use App\Filament\Admin\Resources\UserResource\Pages\ListUsers;
use App\Models\User;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\Feature\Filament\AdminResourceTestCase;

class UserResourceTest extends AdminResourceTestCase
{
    public function test_can_list_users(): void
    {
        $users = User::factory()->count(3)->create();

        Livewire::test(ListUsers::class)
            ->set('tableRecordsPerPage', 25)
            ->assertOk()
            ->assertCanSeeTableRecords($users);
    }

    public function test_can_create_user(): void
    {
        $role = Role::create(['name' => 'Admin', 'guard_name' => 'web']);

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'New User',
                'email' => 'new-user@example.com',
                'password' => 'password123',
                'role' => [$role->id],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(User::class, [
            'email' => 'new-user@example.com',
        ]);
    }

    public function test_user_requires_minimum_fields(): void
    {
        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => null,
                'email' => null,
                'password' => null,
                'role' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
                'email' => 'required',
                'password' => 'required',
                'role' => 'required',
            ]);
    }

    public function test_can_edit_user(): void
    {
        $role = Role::create(['name' => 'Editor', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($role);

        Livewire::test(EditUser::class, ['record' => $user->id])
            ->fillForm([
                'name' => 'Updated User',
                'role' => [$role->id],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(User::class, [
            'id' => $user->id,
            'name' => 'Updated User',
        ]);
    }

    public function test_can_delete_users(): void
    {
        $users = User::factory()->count(2)->create();

        Livewire::test(ListUsers::class)
            ->selectTableRecords($users)
            ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk())
            ->assertNotified();

        $users->each(fn (User $user) => $this->assertDatabaseMissing('users', ['id' => $user->id]));
    }
}
