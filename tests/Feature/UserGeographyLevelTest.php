<?php

namespace Tests\Feature;

use App\Enums\GeographyLevel;
use App\Models\Role;
use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UserGeographyLevelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_national_admin_role_is_national(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::query()->where('name', 'national_admin')->firstOrFail());

        $this->assertSame(GeographyLevel::NATIONAL, $user->geographyLevel());
        $this->assertTrue($user->hasNationalAccess());
    }

    public function test_region_scoped_user_is_detected(): void
    {
        $school = School::factory()->create();

        $user = User::factory()->create([
            'region_id' => $school->district->region_id,
        ]);

        $this->assertSame(GeographyLevel::REGION, $user->geographyLevel());
        $this->assertTrue($user->isRegionScoped());
    }

    public function test_district_scoped_user_is_detected(): void
    {
        $school = School::factory()->create();

        $user = User::factory()->create([
            'region_id' => $school->district->region_id,
            'district_id' => $school->district_id,
        ]);

        $this->assertSame(GeographyLevel::DISTRICT, $user->geographyLevel());
        $this->assertTrue($user->isDistrictScoped());
    }

    public function test_school_scoped_user_is_detected(): void
    {
        $school = School::factory()->create();

        $user = User::factory()->create([
            'region_id' => $school->district->region_id,
            'district_id' => $school->district_id,
            'school_id' => $school->id,
        ]);

        $this->assertSame(GeographyLevel::SCHOOL, $user->geographyLevel());
        $this->assertTrue($user->isSchoolScoped());
    }

    public function test_school_scope_can_be_derived_from_linked_teacher(): void
    {
        $school = School::factory()->create();
        $user = User::factory()->create();

        Teacher::factory()->create([
            'user_id' => $user->id,
            'school_id' => $school->id,
        ]);

        $this->assertSame(GeographyLevel::SCHOOL, $user->geographyLevel());
        $this->assertTrue($user->isSchoolScoped());
    }

    public function test_district_officer_role_is_district_scoped(): void
    {
        $school = School::factory()->create();

        $user = User::factory()->create([
            'region_id' => $school->district->region_id,
            'district_id' => $school->district_id,
        ]);
        $user->assignRole(Role::query()->where('name', 'district_officer')->firstOrFail());

        $this->assertSame(GeographyLevel::DISTRICT, $user->geographyLevel());
    }

    public function test_school_coordinator_role_is_school_scoped(): void
    {
        $school = School::factory()->create();

        $user = User::factory()->create([
            'region_id' => $school->district->region_id,
            'district_id' => $school->district_id,
            'school_id' => $school->id,
        ]);
        $user->assignRole(Role::query()->where('name', 'school_coordinator')->firstOrFail());

        $this->assertSame(GeographyLevel::SCHOOL, $user->geographyLevel());
    }

    public function test_user_without_scope_is_none(): void
    {
        $user = User::factory()->create();

        $this->assertSame(GeographyLevel::NONE, $user->geographyLevel());
        $this->assertFalse($user->hasNationalAccess());
        $this->assertFalse($user->isRegionScoped());
        $this->assertFalse($user->isDistrictScoped());
        $this->assertFalse($user->isSchoolScoped());
    }
}
