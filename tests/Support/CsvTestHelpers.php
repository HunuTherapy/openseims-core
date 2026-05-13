<?php

namespace Tests\Support;

use App\Models\User;
use Filament\Actions\Imports\Events\ImportCompleted;
use Filament\Actions\Imports\Jobs\ImportCsv;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;

/**
 * @property User $user
 */
trait CsvTestHelpers
{
    protected function makeImport(string $importerClass, int $totalRows): Import
    {
        $import = new Import;
        $import->user()->associate($this->user);
        $import->file_name = 'import.csv';
        $import->file_path = 'import.csv';
        $import->importer = $importerClass;
        $import->total_rows = $totalRows;
        $import->processed_rows = 0;
        $import->successful_rows = 0;
        $import->save();

        return $import;
    }

    /**
     * @return array<string, string>
     */
    protected function makeColumnMap(string $importerClass): array
    {
        $map = [];

        foreach ($importerClass::getColumns() as $column) {
            $map[$column->getName()] = $column->getName();
        }

        return $map;
    }

    /**
     * @param  array<int, string>  $headers
     * @return array<string, string>
     */
    protected function makeColumnMapFromHeaders(string $importerClass, array $headers): array
    {
        $map = [];
        $headerLookup = array_flip($headers);

        foreach ($importerClass::getColumns() as $column) {
            $header = $column->getExampleHeader();

            if (! array_key_exists($header, $headerLookup)) {
                $header = $column->getName();
            }

            $map[$column->getName()] = $header;
        }

        return $map;
    }

    /**
     * @return array{headers: array<int, string>, rows: array<int, array<string, string>>}
     */
    protected function readCsvRows(string $path): array
    {
        HeadingRowFormatter::default('none');

        $import = new class implements ToCollection, WithHeadingRow
        {
            /**
             * @var array<int, array<string, mixed>>
             */
            public array $rows = [];

            public function collection(Collection $collection): void
            {
                $this->rows = $collection
                    ->map(fn ($row) => $row->toArray())
                    ->all();
            }
        };

        Excel::import($import, $path);

        $rows = collect($import->rows)
            ->filter(fn (array $row): bool => count(array_filter($row, fn ($value) => $value !== null && trim((string) $value) !== '')) > 0)
            ->values()
            ->all();

        $headers = $rows !== [] ? array_keys($rows[0]) : [];

        return [
            'headers' => $headers,
            'rows' => $rows,
        ];
    }

    /**
     * @return array{rows: array<int, array<string, string>>, columnMap: array<string, string>}
     */
    protected function loadCsvFixture(string $filename, string $importerClass): array
    {
        $path = base_path('tests/Fixtures/Imports/'.$filename);
        $csv = $this->readCsvRows($path);

        return [
            'rows' => $csv['rows'],
            'columnMap' => $this->makeColumnMapFromHeaders($importerClass, $csv['headers']),
        ];
    }

    /**
     * @param  array<string, string>  $overrides
     * @param  array<string, string>  $columnMap
     * @return array<string, string>
     */
    protected function makeRow(array $overrides, array $columnMap): array
    {
        $row = array_fill_keys(array_values($columnMap), '');

        foreach ($overrides as $key => $value) {
            $row[$key] = $value;
        }

        return $row;
    }

    /**
     * @param  array<int, array<string, string>>  $rows
     * @param  array<string, string>  $columnMap
     */
    protected function runImport(Import $import, array $rows, array $columnMap): void
    {
        (new ImportCsv($import, $rows, $columnMap))->handle();

        $import->refresh();
        event(new ImportCompleted($import, $columnMap, []));
        auth()->forgetGuards();
    }
}
