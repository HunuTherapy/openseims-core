<?php

namespace App\Filament\Imports;

use App\Enums\AccommodationStatus;
use App\Enums\DiagnosisStatus;
use App\Enums\DisabilityOnset;
use App\Enums\LearnerClass;
use App\Enums\SeverityLevel;
use App\Filament\Imports\Concerns\NormalizesImportStrings;
use App\Models\AccommodationType;
use App\Models\Condition;
use App\Models\Learner;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LearnerImporter extends Importer
{
    use NormalizesImportStrings;

    protected static ?string $model = Learner::class;

    protected static ?array $conditionCodeToIdCache = null;

    protected static ?array $accommodationCodeToIdCache = null;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('school_id')
                ->label('School ID')
                ->requiredMapping()
                ->rules(['required', 'integer', 'exists:schools,id'])
                ->exampleHeader('School ID')
                ->examples(['1', '1', '1', '1', '1']),

            ImportColumn::make('first_name')
                ->label('First Name')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->exampleHeader('First Name')
                ->examples(['Ama', 'Kwame', 'Kofi', 'Akosua', 'Yaw']),

            ImportColumn::make('middle_name')
                ->label('Middle Name')
                ->rules(['nullable', 'max:255'])
                ->exampleHeader('Middle Name')
                ->examples(['K', '', '', '', 'Evan']),

            ImportColumn::make('last_name')
                ->label('Last Name')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->exampleHeader('Last Name')
                ->examples(['Mensah', 'Boateng', 'Asare', 'Owusu', 'Lamptey']),

            ImportColumn::make('date_of_birth')
                ->label('Date of Birth')
                ->requiredMapping()
                ->rules(['required', 'date'])
                ->exampleHeader('Date of Birth')
                ->examples(['2014-01-10', '2013-06-20', '2012-11-02', '2014-09-14', '2015-03-01']),

            ImportColumn::make('sex')
                ->label('Sex')
                ->requiredMapping()
                ->rules(['required', 'in:M,F'])
                ->exampleHeader('Sex')
                ->examples(['F', 'M', 'M', 'F', 'M']),

            ImportColumn::make('enrol_date')
                ->label('Enrollment Date')
                ->requiredMapping()
                ->rules(['required', 'date'])
                ->exampleHeader('Enrollment Date')
                ->examples(['2023-09-01', '2022-09-15', '2023-09-01', '2024-01-10', '2023-09-01']),

            ImportColumn::make('status')
                ->label('Status')
                ->requiredMapping()
                ->rules(['required', 'in:enrolled,transferred,exited,deceased'])
                ->exampleHeader('Status')
                ->examples(['enrolled', 'transferred', 'enrolled', 'enrolled', 'enrolled']),

            ImportColumn::make('class')
                ->label('Class')
                ->requiredMapping()
                ->rules([
                    'required',
                    Rule::in(array_map(fn (LearnerClass $class) => $class->value, LearnerClass::cases())),
                ])
                ->exampleHeader('Class')
                ->examples(['P3', 'P4', 'P1', 'P2', 'JHS1']),

            ImportColumn::make('primary_contact_name')
                ->label('Parent Contact Name')
                ->rules(['nullable', 'max:255'])
                ->exampleHeader('Parent Contact Name')
                ->examples(['Parent Name', 'Guardian Name', 'Parent Name', 'Guardian Name', 'Parent Name']),

            ImportColumn::make('primary_contact_phone')
                ->label('Parent Contact Phone')
                ->rules(['nullable', 'max:100'])
                ->exampleHeader('Parent Contact Phone')
                ->examples(['+233200000000', '+233244000000', '+233201234567', '+233209876543', '+233208765432']),

            ImportColumn::make('specific_needs')
                ->label('Specific Needs')
                ->rules(['nullable', 'max:1000'])
                ->exampleHeader('Specific Needs')
                ->examples([
                    'Requires additional learning support.',
                    'Requires assistive support.',
                    'Requires additional learning support.',
                    'Requires additional learning support.',
                    'Requires additional learning support.',
                ]),

            ImportColumn::make('primary_special_need_code')
                ->label('Primary Special Need Code')
                ->requiredMapping()
                ->rules(['required', 'max:255', 'exists:conditions,code'])
                ->castStateUsing(fn (mixed $state): ?string => blank($state) ? null : Str::upper(trim((string) $state)))
                ->exampleHeader('Primary Special Need Code')
                ->examples(['AUTISM', 'DEAF', 'VISUAL_DISABILITY', 'DOWN_SYNDROME', 'HARD_OF_HEARING'])
                ->fillRecordUsing(function (): void {
                    // Saved as a learner condition in afterCreate().
                }),

            ImportColumn::make('primary_severity_level')
                ->label('Primary Severity Level')
                ->rules(['nullable', 'in:'.self::severityValues()->implode(',')])
                ->castStateUsing(fn (?string $state): ?string => self::normalizeSeverity($state))
                ->exampleHeader('Primary Severity Level')
                ->examples(['severe', 'moderate', 'mild', 'moderate', 'severe'])
                ->fillRecordUsing(function (): void {
                    // Saved as a learner condition in afterCreate().
                }),

            ImportColumn::make('primary_status')
                ->label('Primary Status')
                ->rules(['nullable', 'in:'.self::diagnosisStatusValues()->implode(',')])
                ->castStateUsing(fn (?string $state): ?string => self::normalizeDiagnosisStatus($state))
                ->exampleHeader('Primary Status')
                ->examples(['confirmed', 'provisional', 'confirmed', 'provisional', 'confirmed'])
                ->fillRecordUsing(function (): void {
                    // Saved as a learner condition in afterCreate().
                }),

            ImportColumn::make('primary_disability_onset')
                ->label('Primary Disability Onset')
                ->rules(['nullable', 'in:'.self::disabilityOnsetValues()->implode(',')])
                ->castStateUsing(fn (?string $state): ?string => self::normalizeDisabilityOnset($state))
                ->exampleHeader('Primary Disability Onset')
                ->examples(['congenital', 'adventitious', 'congenital', 'adventitious', 'congenital'])
                ->fillRecordUsing(function (): void {
                    // Saved as a learner condition in afterCreate().
                }),

            ImportColumn::make('primary_diagnosis_date')
                ->label('Primary Diagnosis Date')
                ->rules(['nullable', 'date'])
                ->exampleHeader('Primary Diagnosis Date')
                ->examples(['2024-01-11', '2024-01-12', '2024-01-13', '2024-01-14', '2024-01-15'])
                ->fillRecordUsing(function (): void {
                    // Saved as a learner condition in afterCreate().
                }),

            ImportColumn::make('accommodation_one_type_code')
                ->label('Accommodation 1 Type Code')
                ->rules(['nullable', 'max:255', 'exists:accommodation_types,code'])
                ->castStateUsing(fn (mixed $state): ?string => blank($state) ? null : Str::upper(trim((string) $state)))
                ->exampleHeader('Accommodation 1 Type Code')
                ->examples(['EXTRA_TIME', 'LARGE_PRINT', '', '', 'READER'])
                ->fillRecordUsing(function (): void {
                    // Saved as a learner accommodation in afterCreate().
                }),

            ImportColumn::make('accommodation_one_status')
                ->label('Accommodation 1 Status')
                ->rules(['nullable', 'in:'.self::accommodationStatusValues()->implode(',')])
                ->castStateUsing(fn (?string $state): ?string => self::normalizeAccommodationStatus($state))
                ->exampleHeader('Accommodation 1 Status')
                ->examples(['approved', 'requested', '', '', 'approved'])
                ->fillRecordUsing(function (): void {
                    // Saved as a learner accommodation in afterCreate().
                }),

            ImportColumn::make('accommodation_one_start_date')
                ->label('Accommodation 1 Start Date')
                ->rules(['nullable', 'date'])
                ->exampleHeader('Accommodation 1 Start Date')
                ->examples(['2024-02-01', '2024-02-15', '', '', '2024-03-01'])
                ->fillRecordUsing(function (): void {
                    // Saved as a learner accommodation in afterCreate().
                }),

            ImportColumn::make('accommodation_one_end_date')
                ->label('Accommodation 1 End Date')
                ->rules(['nullable', 'date'])
                ->exampleHeader('Accommodation 1 End Date')
                ->examples(['2024-06-30', '2024-06-30', '', '', '2024-07-01'])
                ->fillRecordUsing(function (): void {
                    // Saved as a learner accommodation in afterCreate().
                }),

            ImportColumn::make('accommodation_one_notes')
                ->label('Accommodation 1 Notes')
                ->rules(['nullable', 'max:1000'])
                ->exampleHeader('Accommodation 1 Notes')
                ->examples(['Term 2 accommodation', 'Needs support', '', '', 'End-term support'])
                ->fillRecordUsing(function (): void {
                    // Saved as a learner accommodation in afterCreate().
                }),
        ];
    }

    public function resolveRecord(): Learner
    {
        return new Learner;
    }

    protected function beforeValidate(): void
    {
        $this->ensurePrimaryAccommodationValid();
    }

    protected function beforeCreate(): void {}

    protected function afterCreate(): void
    {
        $this->createPrimaryLearnerCondition();
        $this->createPrimaryLearnerAccommodation();
    }

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

    protected function ensurePrimaryAccommodationValid(): void
    {
        $typeCode = $this->data['accommodation_one_type_code'] ?? null;

        if (blank($typeCode)) {
            return;
        }

        if (blank($this->data['accommodation_one_status'] ?? null)) {
            throw new RowImportFailedException('Accommodation 1 Status is required when Accommodation 1 Type Code is provided.');
        }

        if (blank($this->data['accommodation_one_start_date'] ?? null)) {
            throw new RowImportFailedException('Accommodation 1 Start Date is required when Accommodation 1 Type Code is provided.');
        }

        $this->resolveAccommodationTypeIdByCode((string) $typeCode);
    }

    protected function createPrimaryLearnerCondition(): void
    {
        if (! $this->record instanceof Learner) {
            return;
        }

        $conditionCode = $this->data['primary_special_need_code'] ?? null;

        if (blank($conditionCode)) {
            return;
        }

        $this->record->learnerConditions()->create([
            'condition_id' => $this->resolveConditionIdByCode((string) $conditionCode),
            'is_primary' => true,
            'severity_level' => $this->data['primary_severity_level'] ?? null,
            'status' => $this->data['primary_status'] ?? null,
            'disability_onset' => $this->data['primary_disability_onset'] ?? null,
            'assigned_at' => $this->data['primary_diagnosis_date'] ?? null,
            'notes' => null,
        ]);
    }

    protected function createPrimaryLearnerAccommodation(): void
    {
        if (! $this->record instanceof Learner) {
            return;
        }

        $typeCode = $this->data['accommodation_one_type_code'] ?? null;

        if (blank($typeCode)) {
            return;
        }

        $this->record->learnerAccommodations()->create([
            'accommodation_type_id' => $this->resolveAccommodationTypeIdByCode((string) $typeCode),
            'status' => $this->data['accommodation_one_status'] ?? null,
            'start_date' => $this->data['accommodation_one_start_date'] ?? null,
            'end_date' => $this->data['accommodation_one_end_date'] ?? null,
            'notes' => $this->data['accommodation_one_notes'] ?? null,
        ]);
    }

    protected function resolveConditionIdByCode(string $code): int
    {
        $normalized = Str::upper(trim($code));

        if (self::$conditionCodeToIdCache === null) {
            self::$conditionCodeToIdCache = Condition::query()
                ->pluck('id', 'code')
                ->mapWithKeys(fn (int $id, string $key): array => [Str::upper(trim($key)) => $id])
                ->all();
        }

        $conditionId = self::$conditionCodeToIdCache[$normalized] ?? null;

        if (! $conditionId) {
            throw new RowImportFailedException("Special Need code not found: {$code}.");
        }

        return $conditionId;
    }

    protected function resolveAccommodationTypeIdByCode(string $code): int
    {
        $normalized = Str::upper(trim($code));

        if (self::$accommodationCodeToIdCache === null) {
            self::$accommodationCodeToIdCache = AccommodationType::query()
                ->pluck('id', 'code')
                ->mapWithKeys(fn (int $id, string $key): array => [Str::upper(trim($key)) => $id])
                ->all();
        }

        $typeId = self::$accommodationCodeToIdCache[$normalized] ?? null;

        if (! $typeId) {
            throw new RowImportFailedException("Accommodation Type code not found: {$code}.");
        }

        return $typeId;
    }

    protected static function severityValues(): Collection
    {
        return collect(SeverityLevel::cases())->map(fn (SeverityLevel $item): string => $item->value);
    }

    protected static function diagnosisStatusValues(): Collection
    {
        return collect(DiagnosisStatus::cases())->map(fn (DiagnosisStatus $item): string => $item->value);
    }

    protected static function disabilityOnsetValues(): Collection
    {
        return collect(DisabilityOnset::cases())->map(fn (DisabilityOnset $item): string => $item->value);
    }

    protected static function accommodationStatusValues(): Collection
    {
        return collect(AccommodationStatus::cases())->map(fn (AccommodationStatus $item): string => $item->value);
    }

    protected static function normalizeSeverity(?string $state): ?string
    {
        if (blank($state)) {
            return null;
        }

        $value = self::normalizeString($state);

        foreach (SeverityLevel::cases() as $case) {
            if ($value === $case->value || $value === self::normalizeString($case->getLabel() ?? $case->value)) {
                return $case->value;
            }
        }

        return $value;
    }

    protected static function normalizeDiagnosisStatus(?string $state): ?string
    {
        if (blank($state)) {
            return null;
        }

        $value = self::normalizeString($state);

        foreach (DiagnosisStatus::cases() as $case) {
            if ($value === $case->value || $value === self::normalizeString($case->getLabel() ?? $case->value)) {
                return $case->value;
            }
        }

        return $value;
    }

    protected static function normalizeDisabilityOnset(?string $state): ?string
    {
        if (blank($state)) {
            return null;
        }

        $value = self::normalizeString($state);

        foreach (DisabilityOnset::cases() as $case) {
            if ($value === $case->value || $value === self::normalizeString($case->getLabel() ?? $case->value)) {
                return $case->value;
            }
        }

        return $value;
    }

    protected static function normalizeAccommodationStatus(?string $state): ?string
    {
        if (blank($state)) {
            return null;
        }

        $value = self::normalizeString($state);

        foreach (AccommodationStatus::cases() as $case) {
            if ($value === $case->value || $value === self::normalizeString($case->getLabel() ?? $case->value)) {
                return $case->value;
            }
        }

        return Str::of($value)->replace(' ', '_')->replace('-', '_')->replaceMatches('/_+/', '_')->toString();
    }
}
