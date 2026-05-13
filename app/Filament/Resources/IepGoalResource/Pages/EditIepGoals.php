<?php

namespace App\Filament\Resources\IepGoalResource\Pages;

use App\Enums\EvaluationDecision;
use App\Enums\FrequencyUnit;
use App\Enums\GoalCompletionStatus;
use App\Enums\InstructionArea;
use App\Filament\Resources\IepGoalResource;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class EditIepGoals extends EditRecord
{
    protected static string $resource = IepGoalResource::class;

    public static function getNavigationLabel(): string
    {
        return 'Edit IEP Goals';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Fieldset::make()
                ->label('Current Program Placement')
                ->schema([
                    Radio::make('program_placement')
                        ->hiddenLabel()
                        ->options([
                            'self_contained_full_time' => 'Self-contained full-time',
                            'self_contained_part_time' => 'Self-contained part-time',
                            'is_inclusive' => 'Inclusive',
                            'regular_class_with_support' => 'Regular class with supportive services',
                            'other' => 'Others (Specify)',
                        ])
                        ->inline()
                        ->live(),

                    TextInput::make('program_placement_other')
                        ->label('Briefly specify:')
                        ->rule('string')
                        ->maxLength(255)
                        ->visible(fn (callable $get) => $get('program_placement') === 'other')
                        ->required(fn (callable $get) => $get('program_placement') === 'other'),
                ])
                ->columns(1),

            Grid::make(2)
                ->schema([
                    TextInput::make('frequency_value')
                        ->label('Frequency')
                        ->helperText('How many')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->maxValue(99)
                        ->suffix('times')
                        ->columnSpan(1),

                    Select::make('frequency_unit')
                        ->label('Unit')
                        ->prefix('per')
                        ->options(FrequencyUnit::class)
                        ->required()
                        ->columnSpan(1),
                ])
                ->columnSpanFull(),

            Repeater::make('goal_entries')
                ->label('Goal Details')
                ->relationship('goalEntries')
                ->minItems(1)
                ->columnSpanFull()
                ->columns([
                    'default' => 1,
                    'md' => 3,
                ])
                ->addActionLabel('Add another goal')
                ->collapsible()
                ->schema([
                    Select::make('instruction_area')
                        ->label('Area of Instruction')
                        ->options(InstructionArea::class)
                        ->columnSpan(1)
                        ->required()
                        ->native(false),

                    Checkbox::make('has_been_reviewed')
                        ->label('Has goal been reviewed?')
                        ->dehydrated(false)
                        ->columnSpanFull()
                        ->live(),

                    DatePicker::make('last_review_at')
                        ->label('Last Reviewed')
                        ->columnSpan(['default' => 1, 'md' => 3])
                        ->maxDate(now())
                        ->visible(fn (callable $get) => $get('has_been_reviewed')),

                    Select::make('baseline')
                        ->label('Baseline')
                        ->native(false)
                        ->required()
                        ->columnSpan(['default' => 1])
                        ->options(collect(range(0, 100, 10))->mapWithKeys(fn ($v) => [$v => "{$v}%"])),

                    Textarea::make('baseline_description')
                        ->label('Additional Baseline Information')
                        ->columnSpan(['default' => 1, 'md' => 2])
                        ->rule('string')
                        ->maxLength(1000)
                        ->rows(1),

                    Select::make('target')
                        ->label('Target')
                        ->native(false)
                        ->required()
                        ->columnSpan(['default' => 1])
                        ->options(collect(range(0, 100, 10))->mapWithKeys(fn ($v) => [$v => "{$v}%"])),

                    Textarea::make('target_description')
                        ->label('Additional Target Information')
                        ->columnSpan(['default' => 1, 'md' => 2])
                        ->rule('string')
                        ->maxLength(1000)
                        ->rows(1),

                    Select::make('completion_status')
                        ->label('Completion Status')
                        ->options(GoalCompletionStatus::class)
                        ->native(false)
                        ->required()
                        ->columnSpan(['default' => 1]),

                    Repeater::make('actualScores')
                        ->label('Actual Scores')
                        ->relationship('actualScores')
                        ->schema([
                            DatePicker::make('recorded_at')
                                ->label('Date')
                                ->native(false)
                                ->required(),

                            TextInput::make('score')
                                ->label('Score')
                                ->numeric()
                                ->required(),

                            Textarea::make('notes')
                                ->label('Additional Info')
                                ->rows(1)
                                ->rule('string')
                                ->maxLength(1000),
                        ])
                        ->addActionLabel('Add score')
                        ->columns(3)
                        ->columnSpanFull(),

                    Toggle::make('recommend_goal_change')
                        ->label('Recommend Goal Change?')
                        ->default(false)
                        ->columnSpanFull(),
                ]),

            Radio::make('evaluation_decision')
                ->label('Evaluation Decision')
                ->options(EvaluationDecision::class)
                ->required(),

            Textarea::make('recommendation_details')
                ->label('Recommendation Details')
                ->rule('string')
                ->maxLength(2000),
        ]);
    }

    public function getRelationManagers(): array
    {
        return [];
    }
}
