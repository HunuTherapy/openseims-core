<?php

namespace Tests\Feature;

use App\Filament\Imports\IepGoalImporter;
use App\Models\IepGoal;
use App\Models\IepGoalEntry;
use App\Models\Learner;
use App\Models\School;
use App\Models\User;
use Database\Seeders\RegionDistrictSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Support\CsvTestHelpers;
use Tests\TestCase;

class IepGoalImporterTest extends TestCase
{
    use CsvTestHelpers;
    use RefreshDatabase;

    private User $user;

    private School $school;

    private Learner $learner;

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
        $this->learner = Learner::factory()->create([
            'first_name' => 'Kwame',
            'middle_name' => null,
            'last_name' => 'Boateng',
            'school_id' => $this->school->id,
        ]);

        Learner::factory()->create([
            'first_name' => 'Yaw',
            'middle_name' => 'Evan',
            'last_name' => 'Lamptey',
            'school_id' => $this->school->id,
        ]);
    }

    public function test_imports_all_rows_when_all_valid(): void
    {
        $fixture = $this->loadCsvFixture('iep_goals_valid.csv', IepGoalImporter::class);
        $rows = $fixture['rows'];
        $columnMap = $fixture['columnMap'];
        $import = $this->makeImport(IepGoalImporter::class, count($rows));
        $beforeGoals = IepGoal::query()->count();

        $this->runImport($import, $rows, $columnMap);

        $errors = $import->failedRows()->pluck('validation_error')->all();
        $this->assertEmpty($errors, json_encode($errors));

        $this->assertSame($beforeGoals + count($rows), IepGoal::query()->count());
        $this->assertSame(count($rows), IepGoalEntry::query()->count());
    }

    public function test_imports_none_when_any_row_invalid(): void
    {
        $fixture = $this->loadCsvFixture('iep_goals_invalid.csv', IepGoalImporter::class);
        $rows = $fixture['rows'];
        $columnMap = $fixture['columnMap'];
        $import = $this->makeImport(IepGoalImporter::class, count($rows));
        $beforeGoals = IepGoal::query()->count();

        $this->runImport($import, $rows, $columnMap);

        $this->assertSame($beforeGoals + 1, IepGoal::query()->count());
        $this->assertSame(1, $import->getFailedRowsCount());
    }
}
