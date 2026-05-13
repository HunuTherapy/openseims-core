<?php

namespace App\Filament\Imports;

use App\Models\Officer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Validation\Rule;

class OfficerImporter extends Importer
{
    protected static ?string $model = Officer::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('user_id')
                ->label('User ID')
                ->requiredMapping()
                ->rules(['required', 'integer', 'exists:users,id', Rule::unique('officers', 'user_id')])
                ->exampleHeader('User ID')
                ->example('1'),

            ImportColumn::make('name')
                ->label('Name')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->exampleHeader('Name')
                ->example('Roseline Mary Davis'),

            ImportColumn::make('formal_training')
                ->label('Formal Training')
                ->boolean()
                ->requiredMapping()
                ->rules(['required', 'boolean'])
                ->exampleHeader('Formal Training')
                ->example('Yes'),

            ImportColumn::make('phone')
                ->label('Phone')
                ->requiredMapping()
                ->rules([
                    'required',
                    'max:30',
                    Rule::unique('officers', 'phone'),
                ])
                ->exampleHeader('Phone')
                ->example('0242259181'),

            ImportColumn::make('is_deployed')
                ->label('Active?')
                ->boolean()
                ->requiredMapping()
                ->rules(['required', 'boolean'])
                ->exampleHeader('Active?')
                ->example('Yes'),
        ];
    }

    public function resolveRecord(): Officer
    {
        return new Officer;
    }

    protected function beforeCreate(): void {}

    public static function getCompletedNotificationBody(Import $import): string
    {
        return $import->getFailedRowsCount() > 0
            ? 'Your import completed with errors.'
            : 'Your import completed successfully.';
    }

    public static function getCompletedNotificationTitle(Import $import): string
    {
        return $import->getFailedRowsCount() > 0 ? 'Import failed' : 'Import completed';
    }
}
