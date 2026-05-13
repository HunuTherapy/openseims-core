<?php

namespace Tests\Feature;

use App\Filament\Imports\LearnerImporter;
use App\Models\Learner;
use App\Models\LearnerAccommodation;
use App\Models\LearnerCondition;
use App\Models\School;
use App\Models\User;
use Database\Seeders\AccommodationTypeSeeder;
use Database\Seeders\ConditionCategorySeeder;
use Database\Seeders\ConditionSeeder;
use Database\Seeders\RegionDistrictSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Support\CsvTestHelpers;
use Tests\TestCase;

class LearnerImporterTest extends TestCase
{
    use CsvTestHelpers;
    use RefreshDatabase;

    private User $user;

    private School $school;

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

        $this->seed(ConditionCategorySeeder::class);
        $this->seed(ConditionSeeder::class);
        $this->seed(AccommodationTypeSeeder::class);
    }

    public function test_imports_all_rows_when_all_valid(): void
    {
        $fixture = $this->loadCsvFixture('learners_valid.csv', LearnerImporter::class);
        $rows = $fixture['rows'];
        $columnMap = $fixture['columnMap'];
        $import = $this->makeImport(LearnerImporter::class, count($rows));
        $beforeLearners = Learner::query()->count();

        $this->runImport($import, $rows, $columnMap);

        $this->assertSame($beforeLearners + count($rows), Learner::query()->count());
        $this->assertSame(count($rows), LearnerCondition::query()->count());
        $this->assertSame(1, LearnerAccommodation::query()->count());
    }

    public function test_imports_none_when_any_row_invalid(): void
    {
        $fixture = $this->loadCsvFixture('learners_invalid.csv', LearnerImporter::class);
        $rows = $fixture['rows'];
        $columnMap = $fixture['columnMap'];
        $import = $this->makeImport(LearnerImporter::class, count($rows));
        $beforeLearners = Learner::query()->count();

        $this->runImport($import, $rows, $columnMap);

        $this->assertSame($beforeLearners + 1, Learner::query()->count());
        $this->assertSame(1, $import->getFailedRowsCount());
    }

    public function test_import_allows_duplicate_learner_rows_and_existing_database_matches(): void
    {
        Learner::factory()->create([
            'first_name' => 'Ama',
            'middle_name' => 'Kojo',
            'last_name' => 'Mensah',
            'date_of_birth' => '2016-05-10',
            'school_id' => $this->school->id,
        ]);

        $fixture = $this->loadCsvFixture('learners_valid.csv', LearnerImporter::class);
        $rows = [$fixture['rows'][0], $fixture['rows'][0]];
        $columnMap = $fixture['columnMap'];
        $import = $this->makeImport(LearnerImporter::class, count($rows));
        $beforeLearners = Learner::query()->count();

        $this->runImport($import, $rows, $columnMap);

        $errors = $import->failedRows()->pluck('validation_error')->all();
        $this->assertEmpty($errors, json_encode($errors));
        $this->assertSame($beforeLearners + 2, Learner::query()->count());
        $this->assertSame(3, Learner::query()->where('school_id', $this->school->id)->count());
    }
}
