<?php

namespace App\Filament\Resources\IepGoalResource\Pages;

use App\Enums\GoalType;
use App\Filament\Resources\IepGoalResource;
use App\Models\Learner;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class EditIepBasicInfo extends EditRecord
{
    protected static string $resource = IepGoalResource::class;

    public static function getNavigationLabel(): string
    {
        return 'Edit IEP Basic Info';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)->schema([
                Select::make('learner_id')
                    ->relationship('learner', 'id')
                    ->getOptionLabelFromRecordUsing(fn (Learner $record) => $record->getVisibleName())
                    ->label('Select Learner')
                    ->preload()
                    ->required()
                    ->live()
                    ->searchable(['first_name', 'middle_name', 'last_name'])
                    ->native(false)
                    ->columnSpanFull(),

                Placeholder::make('school_name')
                    ->label('School Name')
                    ->content(function (Get $get) {
                        $learner = Learner::with('school')->find($get('learner_id'));

                        return $learner?->school?->name ?? '-';
                    }),

                Placeholder::make('school_type')
                    ->label('School Type')
                    ->content(function (Get $get) {
                        $learner = Learner::with('school')->find($get('learner_id'));

                        return $learner?->school?->school_type?->getLabel() ?? '-';
                    }),

                DatePicker::make('start_date')
                    ->label('IEP Start Date')
                    ->placeholder('Click to select a date')
                    ->native(false)
                    ->required(),

                DatePicker::make('end_date')
                    ->label('IEP End Date')
                    ->native(false)
                    ->placeholder('Click to select a date')
                    ->afterOrEqual('start_date')
                    ->validationMessages([
                        'after_or_equal' => 'IEP end date must be on or after the start date.',
                    ])
                    ->required(),

                Select::make('goal_type')
                    ->label('Type of Goal')
                    ->options(GoalType::class)
                    ->native(false)
                    ->required(),

                Fieldset::make('IEP/Planning Team Members')
                    ->schema([
                        Repeater::make('iep_team')
                            ->label('IEP Team')
                            ->hiddenLabel()
                            ->relationship('iepTeamMembers')
                            ->schema([
                                Checkbox::make('is_guest')
                                    ->label('This member is not in the dropdown')
                                    ->live()
                                    ->columnSpanFull(),

                                Select::make('user_id')
                                    ->relationship('user', 'id', fn (Builder $query) => $query->whereDoesntHave('roles', fn (Builder $query) => $query->where('name', 'national_admin')))
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                                    ->label('Select Team Member')
                                    ->preload()
                                    ->visible(fn (Get $get) => ! $get('is_guest'))
                                    ->required(fn (Get $get) => ! $get('is_guest'))
                                    ->native(false),

                                TextInput::make('guest_name')
                                    ->label('Enter name manually')
                                    ->placeholder('Type full name…')
                                    ->rule('string')
                                    ->maxLength(255)
                                    ->visible(fn (Get $get) => $get('is_guest'))
                                    ->required(fn (Get $get) => $get('is_guest')),

                                Select::make('role')
                                    ->label('Role')
                                    ->options([
                                        'teacher' => 'Teacher',
                                        'school_coordinator' => 'School Coordinator',
                                        'therapist' => 'Therapist',
                                        'counselor' => 'Counselor',
                                        'parent' => 'Parent/Guardian',
                                        'administrator' => 'Administrator',
                                        'sped_coordinator' => 'SPED Co-ordinator',
                                        'other' => 'Other',
                                    ])
                                    ->native(false)
                                    ->required()
                                    ->live(),

                                TextInput::make('custom_role')
                                    ->label('Specify Role')
                                    ->placeholder('Enter role')
                                    ->rule('string')
                                    ->maxLength(255)
                                    ->visible(fn (Get $get) => $get('role') === 'other')
                                    ->required(fn (Get $get) => $get('role') === 'other'),
                            ])
                            ->addAction(fn ($action) => $action->color('primary'))
                            ->addActionLabel('Add another Team member')
                            ->minItems(1)
                            ->collapsible()
                            ->columns(2)
                            ->columnSpanFull(),
                    ]),
            ]),
        ]);
    }

    public function getRelationManagers(): array
    {
        return [];
    }
}
