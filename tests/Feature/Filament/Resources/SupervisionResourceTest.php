<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\SupervisionResource;
use App\Filament\Resources\SupervisionResource\Pages\CreateSupervision;
use App\Filament\Resources\SupervisionResource\Pages\EditBasicInfo;
use App\Filament\Resources\SupervisionResource\Pages\EditDomainScores;
use App\Filament\Resources\SupervisionResource\Pages\EditObservations;
use App\Filament\Resources\SupervisionResource\Pages\ListSupervisions;
use App\Filament\Resources\SupervisionResource\Pages\ViewSupervision;
use App\Models\District;
use App\Models\Role;
use App\Models\School;
use App\Models\SupervisionReport;
use App\Models\User;
use Database\Seeders\RegionDistrictSeeder;
use Filament\Actions\DeleteBulkAction;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\Feature\Filament\SeimsResourceTestCase;

class SupervisionResourceTest extends SeimsResourceTestCase
{
    public function test_can_list_supervisions(): void
    {
        $records = SupervisionReport::factory()->count(2)->create();

        Livewire::test(ListSupervisions::class)
            ->assertOk()
            ->assertCanSeeTableRecords($records);
    }

    public function test_can_create_supervision(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::query()->where('name', 'district_officer')->where('guard_name', 'web')->firstOrFail());
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($user);

        $this->seed(RegionDistrictSeeder::class);

        $school = School::factory()->create();
        $district = District::query()->findOrFail($school->district_id);
        $user->forceFill([
            'region_id' => $district->region_id,
            'district_id' => $district->id,
        ])->save();
        $supervisor = User::factory()->create();
        $recipient = User::factory()->create();

        Livewire::test(CreateSupervision::class)
            ->fillForm([
                'school_id' => $school->id,
                'visit_date' => '2024-05-01',
                'supervisor_id' => $supervisor->id,
                // This field is populated from seeded roles (Role::display_name), not officer titles.
                'supervisor_role' => 'District Officer',
                'recipient_id' => $recipient->id,
                'observations' => [
                    [
                        'issues_found' => 'Issue noted',
                        'intervention_provided' => 'Intervention provided',
                        'deadline_date' => '2024-06-01',
                        'resolved' => false,
                    ],
                ],
                'domainScores' => [
                    [
                        'domain_name' => 'Inclusion Practices',
                        'score' => 3,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(SupervisionReport::class, [
            'school_id' => $school->id,
            'supervisor_id' => $supervisor->id,
            'recipient_id' => $recipient->id,
        ]);
    }

    public function test_supervision_requires_minimum_fields(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::query()->where('name', 'district_officer')->where('guard_name', 'web')->firstOrFail());
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($user);

        Livewire::test(CreateSupervision::class)
            ->fillForm([
                'school_id' => null,
                'visit_date' => null,
                'supervisor_id' => null,
                'supervisor_role' => null,
                'recipient_id' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'school_id' => 'required',
                'visit_date' => 'required',
                'supervisor_id' => 'required',
                'supervisor_role' => 'required',
                'recipient_id' => 'required',
            ]);
    }

    public function test_can_view_supervision(): void
    {
        $record = SupervisionReport::factory()->create();

        Livewire::test(ViewSupervision::class, ['record' => $record->id])
            ->assertOk();
    }

    public function test_can_edit_basic_info(): void
    {
        $record = SupervisionReport::factory()->create([
            // Keep in sync with SupervisionResource::getSupervisorRoleOptions() (seeded Role display names).
            'supervisor_role' => 'District Officer',
        ]);

        Livewire::test(EditBasicInfo::class, ['record' => $record->id])
            ->fillForm([
                'visit_date' => '2024-06-15',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(SupervisionReport::class, [
            'id' => $record->id,
            'visit_date' => '2024-06-15 00:00:00',
        ]);
    }

    public function test_can_load_custom_edit_pages(): void
    {
        $record = SupervisionReport::factory()->create();

        Livewire::test(EditObservations::class, ['record' => $record->id])->assertOk();
        Livewire::test(EditDomainScores::class, ['record' => $record->id])->assertOk();
    }

    public function test_cannot_delete_supervisions(): void
    {
        $records = SupervisionReport::factory()->count(2)->create();

        Livewire::test(ListSupervisions::class)
            ->assertTableBulkActionDoesNotExist(DeleteBulkAction::class);

        $records->each(fn (SupervisionReport $record) => $this->assertDatabaseHas('supervision_reports', ['id' => $record->id]));
    }

    public function test_supervisor_role_options_use_seeded_roles_except_national_admin(): void
    {
        $options = SupervisionResource::getSupervisorRoleOptions();

        $this->assertArrayHasKey('District Officer', $options);
        $this->assertArrayHasKey('School Coordinator', $options);
        $this->assertArrayHasKey('Regional Education Director', $options);
        $this->assertArrayHasKey('National SPED Officer', $options);

        $this->assertArrayNotHasKey('National Admin', $options);
    }
}
