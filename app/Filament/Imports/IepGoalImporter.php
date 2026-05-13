<?php

namespace App\Filament\Imports;

use App\Enums\GoalCompletionStatus;
use App\Enums\GoalType;
use App\Enums\InstructionArea;
use App\Enums\ParentalConsent;
use App\Models\IepGoal;
use App\Models\IepGoalEntry;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Validation\Rule;

class IepGoalImporter extends Importer
{
    protected static ?string $model = IepGoal::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('learner_id')
                ->label('Learner ID')
                ->requiredMapping()
                ->rules(['required', 'integer', 'exists:learners,id'])
                ->exampleHeader('Learner ID')
                ->example('1'),

            ImportColumn::make('start_date')
                ->label('Start Date')
                ->requiredMapping()
                ->rules(['required', 'date'])
                ->exampleHeader('Start Date')
                ->example('2025-01-01'),

            ImportColumn::make('end_date')
                ->label('End Date')
                ->requiredMapping()
                ->rules(['required', 'date'])
                ->exampleHeader('End Date')
                ->example('2025-12-31'),

            ImportColumn::make('goal_type')
                ->label('Goal Type')
                ->requiredMapping()
                ->rules([
                    'required',
                    Rule::in(array_map(fn (GoalType $type) => $type->value, GoalType::cases())),
                ])
                ->exampleHeader('Goal Type')
                ->example('short_term'),

            ImportColumn::make('parental_consent')
                ->label('Parental Consent')
                ->requiredMapping()
                ->rules([
                    'required',
                    Rule::in(array_map(fn (ParentalConsent $consent) => $consent->value, ParentalConsent::cases())),
                ])
                ->exampleHeader('Parental Consent')
                ->example('participated_and_approve'),

            ImportColumn::make('instruction_area')
                ->label('Instruction Area')
                ->requiredMapping()
                ->rules([
                    'required',
                    Rule::in(array_map(fn (InstructionArea $area) => $area->value, InstructionArea::cases())),
                ])
                ->exampleHeader('Instruction Area')
                ->example('literacy')
                ->fillRecordUsing(function (): void {
                    // Saved on IEP goal entry in afterCreate().
                }),

            ImportColumn::make('baseline')
                ->label('Baseline')
                ->requiredMapping()
                ->integer()
                ->rules(['required', 'integer', 'min:0', 'max:100'])
                ->exampleHeader('Baseline')
                ->example('10')
                ->fillRecordUsing(function (): void {
                    // Saved on IEP goal entry in afterCreate().
                }),

            ImportColumn::make('target')
                ->label('Target')
                ->requiredMapping()
                ->integer()
                ->rules(['required', 'integer', 'min:0', 'max:100'])
                ->exampleHeader('Target')
                ->example('40')
                ->fillRecordUsing(function (): void {
                    // Saved on IEP goal entry in afterCreate().
                }),

            ImportColumn::make('completion_status')
                ->label('Completion Status')
                ->requiredMapping()
                ->rules([
                    'required',
                    Rule::in(array_map(fn (GoalCompletionStatus $status) => $status->value, GoalCompletionStatus::cases())),
                ])
                ->exampleHeader('Completion Status')
                ->example('not_started')
                ->fillRecordUsing(function (): void {
                    // Saved on IEP goal entry in afterCreate().
                }),
        ];
    }

    public function resolveRecord(): IepGoal
    {
        return new IepGoal;
    }

    protected function beforeFill(): void
    {
        if (! $this->record instanceof IepGoal) {
            return;
        }

        if (blank($this->record->program_placement)) {
            $this->record->program_placement = 'is_inclusive';
        }
    }

    protected function beforeCreate(): void {}

    protected function afterCreate(): void
    {
        if (! $this->record instanceof IepGoal) {
            return;
        }

        IepGoalEntry::query()->create([
            'iep_goal_id' => $this->record->id,
            'instruction_area' => $this->data['instruction_area'],
            'baseline' => $this->data['baseline'],
            'target' => $this->data['target'],
            'completion_status' => $this->data['completion_status'],
            'recommend_goal_change' => false,
        ]);
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
}
