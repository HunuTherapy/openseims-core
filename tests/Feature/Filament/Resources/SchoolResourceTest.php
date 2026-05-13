<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\SchoolResource\Pages\CreateSchool;
use App\Filament\Resources\SchoolResource\Pages\EditSchool;
use App\Filament\Resources\SchoolResource\Pages\ListSchools;
use App\Models\District;
use App\Models\Region;
use App\Models\School;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use Tests\Feature\Filament\SeimsResourceTestCase;

class SchoolResourceTest extends SeimsResourceTestCase
{
    public function test_can_list_schools(): void
    {
        $schools = School::factory()->count(3)->create();

        Livewire::test(ListSchools::class)
            ->assertOk()
            ->assertCanSeeTableRecords($schools);
    }

    public function test_can_create_school(): void
    {
        $region = Region::query()->where('name', 'Greater Accra')->firstOrFail();
        $district = District::query()->where('region_id', $region->id)->where('name', 'Accra')->firstOrFail();

        Livewire::test(CreateSchool::class)
            ->fillForm([
                'emis_code' => '11001001',
                'name' => 'Sunrise Primary',
                'region_id' => $region->id,
                'district_id' => $district->id,
                'school_type' => 'public',
                'school_level' => 'tvet',
                'is_inclusive' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(School::class, [
            'emis_code' => '11001001',
            'name' => 'Sunrise Primary',
            'school_level' => 'tvet',
        ]);
    }

    public function test_school_requires_minimum_fields(): void
    {
        Livewire::test(CreateSchool::class)
            ->fillForm([
                'emis_code' => null,
                'name' => null,
                'district_id' => null,
                'region_id' => null,
                'school_type' => null,
                'school_level' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'emis_code' => 'required',
                'name' => 'required',
                'district_id' => 'required',
                'region_id' => 'required',
                'school_type' => 'required',
                'school_level' => 'required',
            ]);
    }

    public function test_school_emis_code_must_be_numeric_and_between_8_and_13_digits(): void
    {
        $region = Region::query()->where('name', 'Greater Accra')->firstOrFail();
        $district = District::query()->where('region_id', $region->id)->where('name', 'Accra')->firstOrFail();

        Livewire::test(CreateSchool::class)
            ->fillForm([
                'emis_code' => 'GH110001',
                'name' => 'Invalid EMIS School',
                'region_id' => $region->id,
                'district_id' => $district->id,
                'school_type' => 'public',
                'school_level' => 'primary',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'emis_code' => 'regex',
            ]);

        Livewire::test(CreateSchool::class)
            ->fillForm([
                'emis_code' => '1234567',
                'name' => 'Short EMIS School',
                'region_id' => $region->id,
                'district_id' => $district->id,
                'school_type' => 'public',
                'school_level' => 'primary',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'emis_code' => 'regex',
            ]);
    }

    public function test_can_edit_school(): void
    {
        $school = School::factory()->create();

        Livewire::test(EditSchool::class, ['record' => $school->id])
            ->fillForm([
                'name' => 'Updated School',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(School::class, [
            'id' => $school->id,
            'name' => 'Updated School',
        ]);
    }

    public function test_can_delete_schools(): void
    {
        $schools = School::factory()->count(2)->create();

        Livewire::test(ListSchools::class)
            ->selectTableRecords($schools)
            ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk())
            ->assertNotified();

        $schools->each(fn (School $school) => $this->assertDatabaseMissing('schools', ['id' => $school->id]));
    }
}
