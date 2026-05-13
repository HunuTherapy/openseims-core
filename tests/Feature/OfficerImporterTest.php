<?php

namespace Tests\Feature;

use App\Filament\Imports\OfficerImporter;
use App\Models\Officer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Support\CsvTestHelpers;
use Tests\TestCase;

class OfficerImporterTest extends TestCase
{
    use CsvTestHelpers;
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        $this->user = User::factory()->create();
        User::factory()->create();
    }

    public function test_imports_all_rows_when_all_valid(): void
    {
        $fixture = $this->loadCsvFixture('officers_valid.csv', OfficerImporter::class);
        $rows = $fixture['rows'];
        $columnMap = $fixture['columnMap'];
        $import = $this->makeImport(OfficerImporter::class, count($rows));
        $beforeOfficers = Officer::query()->count();
        $beforeUsers = User::query()->count();

        $this->runImport($import, $rows, $columnMap);

        $errors = $import->failedRows()->pluck('validation_error')->all();
        $this->assertEmpty($errors, json_encode($errors));

        $this->assertSame($beforeOfficers + count($rows), Officer::query()->count());
        $this->assertSame($beforeUsers, User::query()->count());
        $this->assertNotNull(Officer::query()->firstOrFail()->user_id);
    }

    public function test_imports_none_when_any_row_invalid(): void
    {
        $fixture = $this->loadCsvFixture('officers_invalid.csv', OfficerImporter::class);
        $rows = $fixture['rows'];
        $columnMap = $fixture['columnMap'];
        $import = $this->makeImport(OfficerImporter::class, count($rows));
        $beforeOfficers = Officer::query()->count();

        $this->runImport($import, $rows, $columnMap);

        $this->assertSame($beforeOfficers + 1, Officer::query()->count());
        $this->assertSame(1, $import->getFailedRowsCount());
    }
}
