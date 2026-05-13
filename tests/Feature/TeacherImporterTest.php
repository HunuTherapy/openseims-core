<?php

namespace Tests\Feature;

use App\Filament\Imports\TeacherImporter;
use App\Models\District;
use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\RegionDistrictSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Support\CsvTestHelpers;
use Tests\TestCase;

class TeacherImporterTest extends TestCase
{
    use CsvTestHelpers;
    use RefreshDatabase;

    private User $user;

    private School $primarySchool;

    private School $secondarySchool;

    private User $linkedUser;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        $this->seed(RegionDistrictSeeder::class);

        $this->user = User::factory()->create();
        $greaterAccraDistrict = District::query()->whereHas('region', fn ($query) => $query->where('name', 'Greater Accra'))->firstOrFail();
        $bonoDistrict = District::query()->whereHas('region', fn ($query) => $query->where('name', 'Bono'))->firstOrFail();
        $this->primarySchool = School::factory()->create([
            'name' => 'Abeka Basic School',
            'emis_code' => 'GH110001',
            'district_id' => $greaterAccraDistrict->id,
        ]);

        $this->secondarySchool = School::factory()->create([
            'name' => 'Techiman Basic School',
            'emis_code' => 'GH208012',
            'district_id' => $bonoDistrict->id,
        ]);

        $this->linkedUser = User::factory()->create([
            'school_id' => $this->secondarySchool->id,
        ]);
    }

    public function test_imports_all_rows_when_all_valid(): void
    {
        $fixture = $this->loadCsvFixture('teachers_valid.csv', TeacherImporter::class);
        $rows = $fixture['rows'];
        $columnMap = $fixture['columnMap'];
        $import = $this->makeImport(TeacherImporter::class, count($rows));
        $beforeCount = Teacher::query()->count();

        $this->runImport($import, $rows, $columnMap);

        $errors = $import->failedRows()->pluck('validation_error')->all();
        $this->assertEmpty($errors, json_encode($errors));

        $this->assertSame($beforeCount + count($rows), Teacher::query()->count());

        $classTeacher = Teacher::query()->where('teacher_no', 'TCH-2001')->firstOrFail();
        $this->assertSame('class_teacher', $classTeacher->teacher_type->value);
        $this->assertNull($classTeacher->user_id);

        $schoolCoordinator = Teacher::query()->where('teacher_no', 'TCH-2002')->firstOrFail();
        $this->assertSame('school_coordinator', $schoolCoordinator->teacher_type->value);
        $this->assertSame($this->linkedUser->id, $schoolCoordinator->user_id);
    }

    public function test_imports_none_when_any_row_invalid(): void
    {
        $fixture = $this->loadCsvFixture('teachers_invalid.csv', TeacherImporter::class);
        $rows = $fixture['rows'];
        $columnMap = $fixture['columnMap'];
        $import = $this->makeImport(TeacherImporter::class, count($rows));
        $beforeCount = Teacher::query()->count();

        $this->runImport($import, $rows, $columnMap);

        $this->assertSame($beforeCount + 1, Teacher::query()->count());
        $this->assertSame(1, $import->getFailedRowsCount());
    }
}
