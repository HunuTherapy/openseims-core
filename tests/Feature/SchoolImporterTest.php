<?php

namespace Tests\Feature;

use App\Filament\Imports\SchoolImporter;
use App\Models\District;
use App\Models\School;
use App\Models\User;
use Database\Seeders\RegionDistrictSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Support\CsvTestHelpers;
use Tests\TestCase;

class SchoolImporterTest extends TestCase
{
    use CsvTestHelpers;
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        $this->user = User::factory()->create();
        $this->seed(RegionDistrictSeeder::class);

        District::query()->firstOrFail();
    }

    public function test_imports_all_rows_when_all_valid(): void
    {
        $fixture = $this->loadCsvFixture('schools_valid.csv', SchoolImporter::class);
        $rows = $fixture['rows'];
        $columnMap = $fixture['columnMap'];
        $import = $this->makeImport(SchoolImporter::class, count($rows));
        $beforeCount = School::query()->count();

        $this->runImport($import, $rows, $columnMap);

        $this->assertSame($beforeCount + count($rows), School::query()->count());
        $this->assertDatabaseHas(School::class, [
            'emis_code' => '20000006',
            'school_level' => 'tvet',
        ]);
    }

    public function test_imports_none_when_any_row_invalid(): void
    {
        $fixture = $this->loadCsvFixture('schools_invalid.csv', SchoolImporter::class);
        $rows = $fixture['rows'];
        $columnMap = $fixture['columnMap'];
        $import = $this->makeImport(SchoolImporter::class, count($rows));
        $beforeCount = School::query()->count();

        $this->runImport($import, $rows, $columnMap);

        $this->assertSame($beforeCount + 1, School::query()->count());
        $this->assertSame(1, $import->getFailedRowsCount());
    }
}
