<?php

namespace App\Filament\Resources;

use App\Enums\FrequencyUnit;
use App\Enums\GoalCompletionStatus;
use App\Enums\GoalType;
use App\Enums\InstructionArea;
use App\Filament\Resources\IepGoalResource\Pages\CreateIepGoal;
use App\Filament\Resources\IepGoalResource\Pages\EditIepBasicInfo;
use App\Filament\Resources\IepGoalResource\Pages\EditIepDocuments;
use App\Filament\Resources\IepGoalResource\Pages\EditIepGoals;
use App\Filament\Resources\IepGoalResource\Pages\EditIepParentalConsent;
use App\Filament\Resources\IepGoalResource\Pages\EditIepServices;
use App\Filament\Resources\IepGoalResource\Pages\ListIepGoals;
use App\Filament\Resources\IepGoalResource\Pages\ViewIepGoal;
use App\Filament\Resources\IepGoalResource\RelationManagers\TeamMembersRelationManager;
use App\Models\DeviceType;
use App\Models\IepGoal;
use App\Models\Learner;
use App\Models\ServiceType;
use Carbon\Carbon;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Size;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class IepGoalResource extends Resource
{
    protected static ?string $model = IepGoal::class;

    protected static string|\UnitEnum|null $navigationGroup = '🎓 Learner Support';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'IEP Goals';

    protected static ?string $modelLabel = 'IEP Goal';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Wizard::make()
                ->skippable(app()->isLocal())
                ->steps([
                    Step::make('Basic Info')
                        ->schema([
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
                        ]),

                    Step::make('Goals')
                        ->schema([
                            Fieldset::make()
                                ->label('Current Program Placement')
                                ->schema([
                                    Radio::make('program_placement')
                                        ->hiddenLabel()
                                        ->required()
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
                                        ->native(false)
                                        ->maxDate(now())
                                        ->required()
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
                                ]),
                        ]),

                    Step::make('Services')
                        ->schema([
                            Fieldset::make()
                                ->label('Related services')
                                ->schema([
                                    CheckboxList::make('related_services')
                                        ->label('Related Services')
                                        ->hiddenLabel()
                                        ->options(ServiceType::pluck('name', 'code')->toArray())
                                        ->columns(2)
                                        ->live(),

                                    TextInput::make('related_services_other')
                                        ->label('Specify Other Related Service')
                                        ->rule('string')
                                        ->maxLength(255)
                                        ->visible(fn (Get $get) => in_array('OTHER', $get('related_services') ?? []))
                                        ->required(fn (Get $get) => in_array('OTHER', $get('related_services') ?? [])),
                                ]),

                            Select::make('device_type_ids')
                                ->label('Assistive Devices')
                                ->multiple()
                                ->searchable()
                                ->options(function (Get $get) {
                                    $selectedServiceCodes = $get('related_services') ?? [];

                                    // Fetch device types related to selected service codes
                                    return DeviceType::whereHas('serviceTypes', function ($query) use ($selectedServiceCodes) {
                                        $query->whereIn('code', $selectedServiceCodes);
                                    })
                                        ->pluck('name', 'id')
                                        ->toArray();
                                })
                                // ->visible(fn (Get $get) => filled($get('related_services')))
                                ->columnSpanFull(),
                        ]),

                    Step::make('Documents')
                        ->schema([
                            SpatieMediaLibraryFileUpload::make('iep_documents')
                                ->label('Upload IEP Documents')
                                ->multiple()
                                ->collection('iep_documents')
                                ->preserveFilenames()
                                ->acceptedFileTypes(['application/pdf'])
                                ->helperText('PDFs only. Max size 5MB each.')
                                ->required()
                                ->columnSpanFull(),

                            Radio::make('parental_consent')
                                ->label('Parent/Guardian Consent')
                                ->options([
                                    'participated_and_approve' => 'I participated in the development of my child’s IEP and approve the plan.',
                                    'not_participated_but_approve' => 'I did not participate but I approve of the plan',
                                    'do_not_approve' => 'I do not approve of my child’s IEP and request that the plan be reviewed.',
                                ])
                                ->required(),

                            SpatieMediaLibraryFileUpload::make('parental_consent_evidence')
                                ->label('Upload Parental Consent Evidence')
                                ->collection('parental_consent_evidence')
                                ->preserveFilenames()
                                ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                                ->helperText('PDF, JPG, or PNG. Max size 5MB.')
                                ->columnSpanFull(),
                        ]),
                ])
                ->columnSpanFull()
                ->submitAction(new HtmlString(Blade::render(<<<'BLADE'
                        <x-filament::button
                            type="submit"
                            size="sm"
                        >
                            Submit
                        </x-filament::button>
                    BLADE
                ))),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                return $query->withCount('goalEntries');
            })
            ->columns([
                TextColumn::make('learner.full_name')
                    ->label('Learner')
                    ->getStateUsing(fn (IepGoal $record) => $record->learner?->getVisibleName() ?? sprintf('Learner #%d', $record->learner_id))
                    ->searchable(['first_name', 'middle_name', 'last_name'])
                    ->sortable(),

                TextColumn::make('start_date')
                    ->label('Start Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('End Date')
                    ->date()
                    ->sortable(),

                BadgeColumn::make('program_placement')
                    ->label('Placement')
                    ->colors([
                        'primary' => 'regular_class_with_support',
                        'info' => 'is_inclusive',
                        'warning' => 'self_contained_part_time',
                        'danger' => 'self_contained_full_time',
                        'gray' => 'other',
                    ])
                    ->formatStateUsing(fn ($state) => str($state)->replace('_', ' ')->headline()),

                TextColumn::make('goal_entries_count')
                    ->counts('goalEntries')
                    ->label('Number of Goals'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()->url(fn ($record) => route('filament.seims.resources.iep-goals.edit-basic-info', ['record' => $record])),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Grid::make(['md' => 6])
                    ->schema([
                        // LEFT SECTION
                        Section::make([
                            Section::make('Goals')
                                ->schema([

                                    RepeatableEntry::make('goalEntries')
                                        ->hiddenLabel()
                                        ->schema([
                                            TextEntry::make('instruction_area')
                                                ->label('Instruction Area')
                                                ->badge()
                                                ->formatStateUsing(fn ($state) => str($state->getLabel())->headline()),

                                            TextEntry::make('narrative_summary')
                                                ->label('Goal Summary')
                                                ->state(function ($record): HtmlString {
                                                    $area = str($record->instruction_area->getLabel())->headline();

                                                    $baseline = $record->baseline !== null ? "{$record->baseline}%" : 'not provided';
                                                    $target = $record->target !== null ? "{$record->target}%" : 'not specified';
                                                    $latestScore = $record->actualScores()
                                                        ->latest('recorded_at')
                                                        ->first();
                                                    $current = $latestScore
                                                        ? "is {$latestScore->score}%"
                                                        : 'has not been set';
                                                    $review = $record->last_review_at
                                                        ? 'was last reviewed at '.Carbon::parse($record->last_review_at)->format('F j, Y')
                                                        : 'has not been reviewed yet';

                                                    return new HtmlString("At the time of starting this goal, the baseline was {$baseline} and the target was {$target}. The current score {$current}. This goal {$review}.");
                                                }),
                                        ]),
                                ]),

                            Section::make('Assistive Devices')
                                ->afterHeader(fn (IepGoal $record): array => [
                                    \Filament\Actions\Action::make('addAssistiveDevices')
                                        ->label('Add devices')
                                        ->icon('heroicon-m-plus')
                                        ->modalHeading('Add assistive devices')
                                        ->schema([
                                            Select::make('device_type_ids')
                                                ->label('Assistive Devices')
                                                ->multiple()
                                                ->searchable()
                                                ->options(function (): array {
                                                    $groupedOptions = ServiceType::query()
                                                        ->with('deviceTypes')
                                                        ->orderBy('name')
                                                        ->get()
                                                        ->mapWithKeys(function (ServiceType $serviceType): array {
                                                            $options = $serviceType->deviceTypes
                                                                ->sortBy('name')
                                                                ->pluck('name', 'id')
                                                                ->toArray();

                                                            return $options === [] ? [] : [$serviceType->name => $options];
                                                        })
                                                        ->toArray();

                                                    $uncategorizedOptions = DeviceType::query()
                                                        ->whereDoesntHave('serviceTypes')
                                                        ->orderBy('name')
                                                        ->pluck('name', 'id')
                                                        ->toArray();

                                                    if ($uncategorizedOptions !== []) {
                                                        $groupedOptions['Other'] = $uncategorizedOptions;
                                                    }

                                                    return $groupedOptions;
                                                })
                                                ->columnSpanFull(),
                                        ])
                                        ->fillForm(fn (IepGoal $record): array => [
                                            'device_type_ids' => $record->learner?->deviceTypes->pluck('id')->toArray() ?? [],
                                        ])
                                        ->action(function (array $data, IepGoal $record, $livewire): void {
                                            $deviceIds = $data['device_type_ids'] ?? [];
                                            $learner = $record->learner;

                                            if ($learner) {
                                                $deviceIdsWithPivotData = collect($deviceIds)->mapWithKeys(function ($id): array {
                                                    return [$id => ['requested_at' => now()]];
                                                })->all();

                                                $learner->deviceTypes()->syncWithoutDetaching($deviceIdsWithPivotData);
                                            }

                                            Notification::make()
                                                ->title('Assistive devices updated')
                                                ->success()
                                                ->send();

                                            $livewire->dispatch('refresh');
                                        }),
                                ])
                                ->schema([
                                    RepeatableEntry::make('learner.deviceTypes')
                                        ->label('Devices assigned')
                                        ->table([
                                            TableColumn::make('Device'),
                                            TableColumn::make('Date fulfilled'),
                                            TableColumn::make('Date returned'),
                                            TableColumn::make('Actions')->hiddenHeaderLabel(),
                                        ])
                                        ->schema([
                                            TextEntry::make('name')->label('Device Name'),
                                            TextEntry::make('fulfilled_at')
                                                ->label('Date fulfilled')
                                                ->hiddenLabel()
                                                ->state(function ($record) {
                                                    return $record->pivot->fulfilled_at ? Carbon::parse($record->pivot->fulfilled_at)->diffForHumans() : 'Not fulfilled';
                                                })
                                                ->color(fn ($record) => ! $record->pivot->fulfilled_at ? Color::Red : Color::Green)
                                                ->badge(),
                                            TextEntry::make('returned_at')
                                                ->label('Date returned')
                                                ->hiddenLabel()
                                                ->hidden(fn ($record) => ! $record->pivot->fulfilled_at)
                                                ->state(function ($record) {
                                                    return $record->pivot->returned_at ? Carbon::parse($record->pivot->returned_at)->diffForHumans() : 'Not returned';
                                                })
                                                ->color(fn ($record) => ! $record->pivot->returned_at ? Color::Red : Color::Green)
                                                ->badge(),

                                            Actions::make([
                                                \Filament\Actions\Action::make('markFulfilled')
                                                    ->label('Mark as fulfilled')
                                                    // ->size(Size::ExtraLarge)
                                                    ->size(Size::ExtraSmall)
                                                    ->icon('heroicon-s-check-circle')
                                                    ->visible(fn ($record) => blank(optional($record->pivot)->fulfilled_at))
                                                    ->requiresConfirmation()
                                                    ->action(function ($record, $livewire) {
                                                        $record->pivot->update(['fulfilled_at' => now()]);

                                                        Notification::make()
                                                            ->title('Marked as fulfilled')
                                                            ->success()
                                                            ->send();

                                                        $livewire->dispatch('refresh');
                                                    }),

                                                \Filament\Actions\Action::make('markReturned')
                                                    ->label('Mark as returned')
                                                    ->size(Size::ExtraSmall)
                                                    ->icon('heroicon-s-check-circle')
                                                    ->visible(fn ($record) => filled(optional($record->pivot)->fulfilled_at) &&
                                                        blank(optional($record->pivot)->returned_at)
                                                    )
                                                    ->requiresConfirmation()
                                                    ->action(function ($record, $livewire) {
                                                        $record->pivot->update(['returned_at' => now()]);

                                                        Notification::make()
                                                            ->title('Marked as returned')
                                                            ->success()
                                                            ->send();

                                                        $livewire->dispatch('refresh');
                                                    }),

                                                \Filament\Actions\Action::make('removeDevice')
                                                    ->label('Remove')
                                                    ->size(Size::ExtraSmall)
                                                    ->icon('heroicon-m-trash')
                                                    ->iconButton()
                                                    ->color('danger')
                                                    ->requiresConfirmation()
                                                    ->modalHeading('Remove device')
                                                    ->modalDescription('This will remove the device from this learner.')
                                                    ->action(function ($record, $livewire): void {
                                                        $iepGoal = $livewire->record;
                                                        $learner = $iepGoal?->learner;

                                                        if ($learner) {
                                                            $learner->deviceTypes()->detach($record->id);
                                                        }

                                                        Notification::make()
                                                            ->title('Device removed')
                                                            ->success()
                                                            ->send();

                                                        $livewire->dispatch('refresh');
                                                    }),
                                            ]),
                                        ]),
                                ]),
                        ])
                            ->columnSpan(['md' => 4])
                            ->extraAttributes(['class' => 'invisible-section']),

                        // RIGHT SECTION
                        Grid::make(1)
                            ->schema([
                                Section::make()->schema([
                                    TextEntry::make('learner.full_name')
                                        ->label('Learner')
                                        ->state(fn (IepGoal $record) => $record->learner?->getVisibleName() ?? sprintf('Learner #%d', $record->learner_id)),
                                    TextEntry::make('start_date')->label('Start Date')->date(),
                                    TextEntry::make('end_date')->label('End Date')->date(),
                                    // Marks if the goal should be continued, modified, or discontinued
                                    TextEntry::make('evaluation_decision')->badge(),
                                ]),
                                // ->footerActions([
                                //     fn (string $operation): Action => Action::make('save')
                                //         ->action(function (Section $component, EditRecord $livewire) {
                                //             $livewire->saveFormComponentOnly($component);
                                //         }),
                                // ]),

                                Section::make()->schema([
                                    TextEntry::make('parental_consent')
                                        ->label('Parent/Guardian consent declaration')
                                        ->extraAttributes(['class' => 'text-wrap']),
                                ]),
                            ])
                            ->columnSpan(['md' => 2])
                            ->extraAttributes(['class' => 'invisible-section']),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIepGoals::route('/'),
            'create' => CreateIepGoal::route('/create'),
            'view' => ViewIepGoal::route('/{record}'),
            'edit-basic-info' => EditIepBasicInfo::route('/{record}/basic-info'),
            'edit-goals' => EditIepGoals::route('/{record}/goals'),
            'edit-services' => EditIepServices::route('/{record}/services'),
            'edit-documents' => EditIepDocuments::route('/{record}/documents'),
            'edit-parental-consent' => EditIepParentalConsent::route('/{record}/parental-consent'),
        ];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            EditIepBasicInfo::class,
            EditIepGoals::class,
            EditIepServices::class,
            EditIepDocuments::class,
            EditIepParentalConsent::class,
        ]);
    }

    public static function getRelations(): array
    {
        return [
            TeamMembersRelationManager::class,
        ];
    }
}
