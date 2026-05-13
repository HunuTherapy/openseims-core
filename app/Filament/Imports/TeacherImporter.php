<?php

namespace App\Filament\Imports;

use App\Enums\LearnerClass;
use App\Enums\TeacherType;
use App\Models\Teacher;
use App\Models\User;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Validation\Rule;

class TeacherImporter extends Importer
{
    protected static ?string $model = Teacher::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('teacher_no')
                ->label('Teacher Number')
                ->requiredMapping()
                ->rules([
                    'required',
                    'max:255',
                    Rule::unique('teachers', 'teacher_no'),
                ])
                ->exampleHeader('Teacher Number')
                ->example('TCH-1001')
                ->guess(['teacher_no', 'teacher number']),

            ImportColumn::make('teacher_type')
                ->label('Teacher Type')
                ->requiredMapping()
                ->rules([
                    'required',
                    Rule::in(array_map(fn (TeacherType $type) => $type->value, TeacherType::cases())),
                ])
                ->castStateUsing(fn (?string $state): ?string => self::normalizeTeacherType($state))
                ->exampleHeader('Teacher Type')
                ->example('class_teacher')
                ->guess(['teacher_type', 'teacher type', 'type', 'role']),

            ImportColumn::make('school_id')
                ->label('School ID')
                ->requiredMapping()
                ->rules(['required', 'integer', 'exists:schools,id'])
                ->exampleHeader('School ID')
                ->example('1'),

            ImportColumn::make('user_id')
                ->label('User ID')
                ->rules(['nullable', 'integer', 'exists:users,id', Rule::unique('teachers', 'user_id')])
                ->castStateUsing(fn (mixed $state): ?int => blank($state) ? null : (int) $state)
                ->exampleHeader('User ID')
                ->example('1'),

            ImportColumn::make('first_name')
                ->label('First Name')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->exampleHeader('First Name')
                ->example('John'),

            ImportColumn::make('last_name')
                ->label('Last Name')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->exampleHeader('Last Name')
                ->example('Doe'),

            ImportColumn::make('class')
                ->label('Class')
                ->rules([
                    'nullable',
                    Rule::in(array_map(fn (LearnerClass $class) => $class->value, LearnerClass::cases())),
                ])
                ->exampleHeader('Class')
                ->example('P1'),

            ImportColumn::make('qualification')
                ->label('Qualification')
                ->rules(['nullable', 'max:255'])
                ->exampleHeader('Qualification')
                ->example('B.Ed'),

            ImportColumn::make('special_education_background')
                ->label('Special Education background')
                ->rules(['nullable', 'max:1000'])
                ->exampleHeader('Special Education background')
                ->example('Foundations of inclusive education.'),

            ImportColumn::make('sen_certified')
                ->label('SEN Certified')
                ->boolean()
                ->rules(['nullable', 'boolean'])
                ->exampleHeader('SEN Certified')
                ->example('Yes'),

            ImportColumn::make('training_on_inclusion')
                ->label('Training on Inclusion')
                ->boolean()
                ->rules(['nullable', 'boolean'])
                ->exampleHeader('Training on Inclusion')
                ->example('No'),

            ImportColumn::make('skills')
                ->label('Skills in Inclusive Pedagogies')
                ->rules(['nullable', 'max:1000'])
                ->exampleHeader('Skills in Inclusive Pedagogies')
                ->example('Differentiated instruction, UDL'),

            ImportColumn::make('in_service_trainings_attended')
                ->label('Number of in-service trainings attended')
                ->integer()
                ->rules(['nullable', 'integer', 'min:0'])
                ->exampleHeader('Number of in-service trainings attended')
                ->example('3'),

            ImportColumn::make('other_qualifications')
                ->label('Other Qualifications')
                ->rules(['nullable', 'max:1000'])
                ->exampleHeader('Other Qualifications')
                ->example('M.Ed'),
        ];
    }

    public function resolveRecord(): Teacher
    {
        return new Teacher;
    }

    protected function beforeValidate(): void
    {
        $userId = $this->data['user_id'] ?? null;

        if (blank($userId)) {
            return;
        }

        $schoolId = $this->data['school_id'] ?? null;

        if (blank($schoolId)) {
            return;
        }

        $user = User::query()->withoutGlobalScopes()->find((int) $userId);

        if (! $user) {
            return;
        }

        if ((int) $user->school_id !== (int) $schoolId) {
            throw new RowImportFailedException('User must belong to the selected school.');
        }
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

    protected static function normalizeTeacherType(?string $state): ?string
    {
        return TeacherType::normalize($state)?->value;
    }
}
