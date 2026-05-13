<?php

namespace App\Filament\Imports;

use App\Enums\SchoolLevel;
use App\Models\School;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Validation\Rule;

class SchoolImporter extends Importer
{
    protected static ?string $model = School::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('emis_code')
                ->label('EMIS Code')
                ->requiredMapping()
                ->rules([
                    'required',
                    'regex:/^\d{8,13}$/',
                    Rule::unique('schools', 'emis_code'),
                ])
                ->exampleHeader('EMIS Code')
                ->example('11000123')
                ->guess(['emis_code', 'emis code']),

            ImportColumn::make('name')
                ->label('Name')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->exampleHeader('Name')
                ->example('Abeka Basic School'),

            ImportColumn::make('district_id')
                ->label('District ID')
                ->requiredMapping()
                ->rules(['required', 'integer', 'exists:districts,id'])
                ->exampleHeader('District ID')
                ->example('1'),

            ImportColumn::make('school_type')
                ->label('School Type')
                ->requiredMapping()
                ->rules(['required', 'in:public,private,special_unit'])
                ->exampleHeader('School Type')
                ->example('Public')
                ->guess(['school_type', 'school type']),

            ImportColumn::make('school_level')
                ->label('School Level')
                ->requiredMapping()
                ->rules([
                    'required',
                    Rule::in(array_map(fn (SchoolLevel $level): string => $level->value, SchoolLevel::cases())),
                ])
                ->exampleHeader('School Level')
                ->example('Primary')
                ->guess(['school_level', 'school level']),

            ImportColumn::make('is_inclusive')
                ->label('Is Inclusive')
                ->boolean()
                ->rules(['nullable', 'boolean'])
                ->exampleHeader('Is Inclusive')
                ->example('Yes'),

            ImportColumn::make('resource_teacher')
                ->label('Resource Teacher')
                ->boolean()
                ->rules(['nullable', 'boolean'])
                ->exampleHeader('Resource Teacher')
                ->example('No'),

            ImportColumn::make('number_of_teachers')
                ->label('Number of Teachers')
                ->integer()
                ->rules(['nullable', 'integer', 'min:0'])
                ->exampleHeader('Number of Teachers')
                ->example('42'),
        ];
    }

    public function resolveRecord(): School
    {
        return new School;
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
