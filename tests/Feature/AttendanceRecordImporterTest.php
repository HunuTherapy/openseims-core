<?php

namespace Tests\Feature;

use App\Filament\Imports\AttendanceRecordImporter;
use App\Models\AttendanceRecord;
use App\Models\Learner;
use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\RegionDistrictSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Support\CsvTestHelpers;
use Tests\TestCase;

class AttendanceRecordImporterTest extends TestCase
{
    use CsvTestHelpers;
    use RefreshDatabase;

    private User $user;

    private School $school;

    private Teacher $teacher;

    private Learner $primaryLearner;

    private Learner $secondaryLearner;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        $this->seed(RegionDistrictSeeder::class);

        $this->user = User::factory()->create();
        $this->school = School::factory()->create([
            'name' => 'Abeka Basic School',
            'emis_code' => 'GH110001',
        ]);

        $this->teacher = Teacher::factory()->create([
            'teacher_no' => 'TCH-1001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'school_id' => $this->school->id,
        ]);

        $this->primaryLearner = Learner::factory()->create([
            'first_name' => 'Ama',
            'middle_name' => null,
            'last_name' => 'Mensah',
            'school_id' => $this->school->id,
            'class' => 'P1',
        ]);

        $this->secondaryLearner = Learner::factory()->create([
            'first_name' => 'Kofi',
            'middle_name' => null,
            'last_name' => 'Asare',
            'school_id' => $this->school->id,
            'class' => 'JHS1',
        ]);
    }

    public function test_imports_all_rows_when_all_valid(): void
    {
        $fixture = $this->loadCsvFixture('attendance_valid.csv', AttendanceRecordImporter::class);
        $rows = $fixture['rows'];
        $columnMap = $fixture['columnMap'];
        $import = $this->makeImport(AttendanceRecordImporter::class, count($rows));
        $beforeCount = AttendanceRecord::query()->count();

        $this->runImport($import, $rows, $columnMap);

        $errors = $import->failedRows()->pluck('validation_error')->all();
        $this->assertEmpty($errors, json_encode($errors));

        $this->assertSame($beforeCount + count($rows), AttendanceRecord::query()->count());
    }

    public function test_imports_none_when_any_row_invalid(): void
    {
        $fixture = $this->loadCsvFixture('attendance_invalid.csv', AttendanceRecordImporter::class);
        $rows = $fixture['rows'];
        $columnMap = $fixture['columnMap'];
        $import = $this->makeImport(AttendanceRecordImporter::class, count($rows));
        $beforeCount = AttendanceRecord::query()->count();

        $this->runImport($import, $rows, $columnMap);

        $this->assertSame($beforeCount + 1, AttendanceRecord::query()->count());
        $this->assertSame(1, $import->getFailedRowsCount());
    }

    public function test_import_allows_duplicate_attendance_rows_and_existing_database_matches(): void
    {
        AttendanceRecord::factory()->create([
            'learner_id' => $this->primaryLearner->id,
            'teacher_id' => $this->teacher->id,
            'class' => 'P1',
            'date' => '2025-06-10',
            'present' => true,
        ]);

        $fixture = $this->loadCsvFixture('attendance_valid.csv', AttendanceRecordImporter::class);
        $rows = [$fixture['rows'][0], $fixture['rows'][0]];
        $columnMap = $fixture['columnMap'];
        $import = $this->makeImport(AttendanceRecordImporter::class, count($rows));
        $beforeCount = AttendanceRecord::query()->count();

        $this->runImport($import, $rows, $columnMap);

        $errors = $import->failedRows()->pluck('validation_error')->all();
        $this->assertEmpty($errors, json_encode($errors));
        $this->assertSame($beforeCount + 2, AttendanceRecord::query()->count());
        $this->assertSame(3, AttendanceRecord::query()->where('learner_id', $this->primaryLearner->id)->whereDate('date', '2025-06-10')->count());
    }
}
