<?php

namespace Tests\Feature\Filament\Admin;

use App\Filament\Admin\Resources\OfficerResource;
use App\Filament\Admin\Resources\OfficerResource\Pages\CreateOfficer;
use App\Filament\Admin\Resources\OfficerResource\Pages\EditOfficer;
use App\Filament\Admin\Resources\OfficerResource\Pages\ListOfficers;
use App\Filament\Admin\Resources\OfficerResource\Pages\ViewOfficer;
use App\Models\District;
use App\Models\Officer;
use App\Models\Region;
use App\Models\Role;
use App\Models\School;
use App\Models\User;
use Database\Seeders\RegionDistrictSeeder;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\Feature\Filament\AdminResourceTestCase;

class OfficerResourceTest extends AdminResourceTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RegionDistrictSeeder::class);
    }

    public function test_can_list_officers(): void
    {
        $officers = Officer::factory()->count(2)->create();

        Livewire::test(ListOfficers::class)
            ->assertOk();
    }

    public function test_can_view_officer(): void
    {
        $officer = Officer::factory()->create();

        Livewire::test(ViewOfficer::class, ['record' => $officer->id])
            ->assertOk();
    }

    public function test_edit_officer_form_hydrates_role_and_geography_from_linked_user(): void
    {
        $district = District::query()->firstOrFail();
        $user = User::factory()->create([
            'region_id' => $district->region_id,
            'district_id' => $district->id,
        ]);
        $user->assignRole('district_officer');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $officer = Officer::factory()->create([
            'role' => 'District SpED coordinator',
            'user_id' => $user->id,
        ]);

        Livewire::test(EditOfficer::class, ['record' => $officer->id])
            ->assertFormSet([
                'app_role' => 'district_officer',
                'region_id' => $district->region_id,
                'district_id' => $district->id,
            ]);
    }

    public function test_can_access_create_officer_page(): void
    {
        Livewire::test(CreateOfficer::class)
            ->assertOk();
    }

    public function test_manual_officer_creation_provisions_user_account(): void
    {
        $district = District::query()->firstOrFail();
        $role = Role::query()->where('name', 'district_officer')->where('guard_name', 'web')->firstOrFail();

        Livewire::test(CreateOfficer::class)
            ->fillForm([
                'name' => 'Manual Officer',
                'app_role' => 'district_officer',
                'formal_training' => true,
                'phone' => '0247771234',
                'email' => 'manual.officer@example.com',
                'region_id' => $district->region_id,
                'district_id' => $district->id,
                'is_deployed' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $officer = Officer::query()
            ->whereHas('user', fn ($query) => $query->where('email', 'manual.officer@example.com'))
            ->firstOrFail();
        $user = $officer->user()->first();

        $this->assertNotNull($user);
        $this->assertSame($role->display_name, $officer->role);
        $this->assertTrue($officer->formal_training);
        $this->assertTrue($officer->is_deployed);
        $this->assertSame($district->region_id, $user->region_id);
        $this->assertSame($district->id, $user->district_id);
        $this->assertTrue($user->hasRole('district_officer'));
        $this->assertTrue(Hash::check(config('seims.imports.default_password', 'Pass1234'), $user->password));
    }

    public function test_manual_officer_creation_rejects_email_used_by_existing_user(): void
    {
        $district = District::query()->firstOrFail();
        User::factory()->create([
            'email' => 'existing.user@example.com',
        ]);

        Livewire::test(CreateOfficer::class)
            ->fillForm([
                'name' => 'Manual Officer',
                'app_role' => 'district_officer',
                'formal_training' => false,
                'phone' => '0247772234',
                'email' => 'existing.user@example.com',
                'region_id' => $district->region_id,
                'district_id' => $district->id,
                'is_deployed' => true,
            ])
            ->call('create')
            ->assertHasFormErrors(['email']);
    }

    public function test_manual_officer_creation_rejects_duplicate_officer_phone(): void
    {
        $district = District::query()->firstOrFail();
        Officer::factory()->create([
            'phone' => '0247773234',
        ]);

        Livewire::test(CreateOfficer::class)
            ->fillForm([
                'name' => 'Manual Officer',
                'app_role' => 'district_officer',
                'formal_training' => false,
                'phone' => '0247773234',
                'email' => 'new.manual.officer@example.com',
                'region_id' => $district->region_id,
                'district_id' => $district->id,
                'is_deployed' => true,
            ])
            ->call('create')
            ->assertHasFormErrors(['phone']);
    }

    public function test_manual_officer_creation_rejects_invalid_phone_format(): void
    {
        $district = District::query()->firstOrFail();

        Livewire::test(CreateOfficer::class)
            ->fillForm([
                'name' => 'Manual Officer',
                'app_role' => 'district_officer',
                'formal_training' => false,
                'phone' => 'adsfaf',
                'email' => 'invalid.phone@example.com',
                'region_id' => $district->region_id,
                'district_id' => $district->id,
                'is_deployed' => true,
            ])
            ->call('create')
            ->assertHasFormErrors(['phone']);
    }

    public function test_manual_officer_creation_rejects_district_outside_selected_region(): void
    {
        $district = District::query()->firstOrFail();
        $otherRegionDistrict = District::query()
            ->where('region_id', '!=', $district->region_id)
            ->firstOrFail();

        Livewire::test(CreateOfficer::class)
            ->fillForm([
                'name' => 'Manual Officer',
                'app_role' => 'district_officer',
                'formal_training' => false,
                'phone' => '0247774234',
                'email' => 'region.mismatch@example.com',
                'region_id' => $district->region_id,
                'district_id' => $otherRegionDistrict->id,
                'is_deployed' => true,
            ])
            ->call('create')
            ->assertHasFormErrors(['district_id']);
    }

    public function test_seims_panel_cannot_see_add_or_import_officer_actions(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::query()->where('name', 'district_officer')->where('guard_name', 'web')->firstOrFail());
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($user);
        $this->usePanel('seims');

        Livewire::test(ListOfficers::class)
            ->assertOk()
            ->assertActionHidden('create')
            ->assertActionHidden('importOfficers');
    }

    public function test_admin_panel_shows_create_officer_action(): void
    {
        Livewire::test(ListOfficers::class)
            ->assertOk()
            ->assertActionExists('create');

        $this->get(OfficerResource::getUrl('create', panel: 'admin'))
            ->assertOk();
    }

    public function test_assign_school_action_rehydrates_existing_school_selection(): void
    {
        $district = District::query()->firstOrFail();
        $school = School::factory()->create([
            'district_id' => $district->id,
        ]);

        $officer = $this->createOfficerWithRole('district_officer', [
            'region_id' => $district->region_id,
            'district_id' => $district->id,
        ]);
        $officer->schools()->attach($school);

        Livewire::test(ListOfficers::class)
            ->mountTableAction('assign_school', $officer)
            ->assertTableActionDataSet([
                'schools' => [$school->id],
            ]);
    }

    public function test_national_admin_can_see_all_officer_contacts(): void
    {
        $officer = $this->createOfficerWithRole('district_officer', [
            'phone' => '0241111111',
            'email' => 'district.officer.one@example.com',
        ]);

        $this->assertTrue($this->user->canViewOfficerContactOf($officer->user));
    }

    public function test_region_scoped_user_sees_contacts_only_for_subordinate_users_in_region(): void
    {
        $region = Region::query()->firstOrFail();
        $viewer = User::factory()->create([
            'region_id' => $region->id,
        ]);
        $viewer->assignRole('regional_education_director');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $inScopeDistrict = District::query()->where('region_id', $region->id)->firstOrFail();
        $inScopeOfficer = $this->createOfficerWithRole('district_officer', [
            'region_id' => $region->id,
            'district_id' => $inScopeDistrict->id,
            'phone' => '0242222222',
            'email' => 'district.officer.two@example.com',
        ]);

        $otherDistrict = District::query()->where('region_id', '!=', $region->id)->firstOrFail();
        $outOfScopeOfficer = $this->createOfficerWithRole('district_officer', [
            'region_id' => $otherDistrict->region_id,
            'district_id' => $otherDistrict->id,
            'phone' => '0243333333',
            'email' => 'district.officer.three@example.com',
        ]);

        $this->assertTrue($viewer->canViewOfficerContactOf($inScopeOfficer->user));
        $this->assertFalse($viewer->canViewOfficerContactOf($outOfScopeOfficer->user));
    }

    public function test_district_scoped_user_cannot_see_peer_district_contacts(): void
    {
        $district = District::query()->firstOrFail();
        $viewer = User::factory()->create([
            'region_id' => $district->region_id,
            'district_id' => $district->id,
        ]);
        $viewer->assignRole('district_officer');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $peerOfficer = $this->createOfficerWithRole('district_officer', [
            'region_id' => $district->region_id,
            'district_id' => $district->id,
            'phone' => '0244444444',
            'email' => 'district.director.peer@example.com',
        ]);

        $this->assertFalse($viewer->canViewOfficerContactOf($peerOfficer->user));
    }

    private function createOfficerWithRole(string $roleName, array $attributes = []): Officer
    {
        $districtId = $attributes['district_id'] ?? District::query()->firstOrFail()->id;
        $district = District::query()->findOrFail($districtId);

        $user = User::factory()->create([
            'email' => $attributes['email'] ?? fake()->unique()->safeEmail(),
            'region_id' => $attributes['region_id'] ?? $district->region_id,
            'district_id' => $attributes['district_id'] ?? $district->id,
        ]);
        $user->assignRole($roleName);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return Officer::factory()->create([
            'user_id' => $user->id,
            'phone' => $attributes['phone'] ?? '0240000000',
        ]);
    }
}
