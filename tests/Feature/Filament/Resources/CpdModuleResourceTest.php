<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\CpdModuleResource\Pages\CreateCpdModule;
use App\Filament\Resources\CpdModuleResource\Pages\EditCpdModule;
use App\Filament\Resources\CpdModuleResource\Pages\ListCpdModules;
use App\Models\CpdModule;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use Tests\Feature\Filament\SeimsResourceTestCase;

class CpdModuleResourceTest extends SeimsResourceTestCase
{
    public function test_can_list_cpd_modules(): void
    {
        $modules = CpdModule::factory()->count(3)->create();

        Livewire::test(ListCpdModules::class)
            ->assertOk()
            ->assertCanSeeTableRecords($modules);
    }

    public function test_can_create_cpd_module(): void
    {
        Livewire::test(CreateCpdModule::class)
            ->fillForm([
                'name' => 'Inclusive Education Basics',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(CpdModule::class, [
            'name' => 'Inclusive Education Basics',
        ]);
    }

    public function test_cpd_module_requires_name(): void
    {
        Livewire::test(CreateCpdModule::class)
            ->fillForm([
                'name' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
            ]);
    }

    public function test_can_edit_cpd_module(): void
    {
        $module = CpdModule::factory()->create();

        Livewire::test(EditCpdModule::class, ['record' => $module->id])
            ->fillForm([
                'name' => 'Updated Module',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(CpdModule::class, [
            'id' => $module->id,
            'name' => 'Updated Module',
        ]);
    }

    public function test_can_delete_cpd_modules(): void
    {
        $modules = CpdModule::factory()->count(2)->create();

        Livewire::test(ListCpdModules::class)
            ->selectTableRecords($modules)
            ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk())
            ->assertNotified();

        $modules->each(fn (CpdModule $module) => $this->assertDatabaseMissing('cpd_modules', ['id' => $module->id]));
    }
}
