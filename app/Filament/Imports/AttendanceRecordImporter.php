<?php

namespace App\Filament\Imports;

use App\Enums\LearnerClass;
use App\Models\AttendanceRecord;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Validation\Rule;

class AttendanceRecordImporter extends Importer
{
    protected static ?string $model = AttendanceRecord::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('learner_id')
                ->label('Learner ID')
                ->requiredMapping()
                ->rules(['required', 'integer', 'exists:learners,id'])
                ->exampleHeader('Learner ID')
                ->example('1'),

            ImportColumn::make('teacher_id')
                ->label('Teacher ID')
                ->requiredMapping()
                ->rules(['required', 'integer', 'exists:teachers,id'])
                ->exampleHeader('Teacher ID')
                ->example('1'),

            ImportColumn::make('class')
                ->label('Class')
                ->requiredMapping()
                ->rules([
                    'required',
                    Rule::in(array_map(fn (LearnerClass $class) => $class->value, LearnerClass::cases())),
                ])
                ->exampleHeader('Class')
                ->example('P1'),

            ImportColumn::make('date')
                ->label('Date')
                ->requiredMapping()
                ->rules(['required', 'date'])
                ->exampleHeader('Date')
                ->example('2025-06-10'),

            ImportColumn::make('present')
                ->label('Present')
                ->boolean()
                ->requiredMapping()
                ->rules(['required', 'boolean'])
                ->exampleHeader('Present')
                ->example('Yes'),

            ImportColumn::make('reason')
                ->label('Reason for Absence')
                ->rules(['nullable', 'max:255'])
                ->exampleHeader('Reason for Absence')
                ->example('Sick'),
        ];
    }

    public function resolveRecord(): AttendanceRecord
    {
        return new AttendanceRecord;
    }

    protected function beforeCreate(): void {}

    public static function getCompletedNotificationTitle(Import $import): string
    {
        return $import->getFailedRowsCount() > 0 ? 'Import failed' : 'Import completed';
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        return $import->getFailedRowsCount() > 0
            ? 'Your import completed with errors.'
            : 'Your import completed successfully.';
    }
}
