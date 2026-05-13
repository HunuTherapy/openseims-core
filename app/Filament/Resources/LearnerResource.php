<?php

namespace App\Filament\Resources;

use App\Enums\AccommodationStatus;
use App\Enums\DiagnosisStatus;
use App\Enums\DisabilityOnset;
use App\Enums\LearnerClass;
use App\Enums\LearnerStatus;
use App\Enums\SeverityLevel;
use App\Filament\Resources\LearnerResource\Pages\CreateLearner;
use App\Filament\Resources\LearnerResource\Pages\EditLearner;
use App\Filament\Resources\LearnerResource\Pages\ListLearners;
use App\Filament\Resources\LearnerResource\RelationManagers\LearnerAccommodationsRelationManager;
use App\Filament\Resources\LearnerResource\RelationManagers\LearnerAssessmentHistoryRelationManager;
use App\Filament\Resources\LearnerResource\RelationManagers\LearnerConditionsRelationManager;
use App\Models\Learner;
use App\Models\Talent;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class LearnerResource extends Resource
{
    protected static ?string $model = Learner::class;

    public static function getRelations(): array
    {
        return [
            LearnerConditionsRelationManager::class,
            LearnerAccommodationsRelationManager::class,
            LearnerAssessmentHistoryRelationManager::class,
        ];
    }

    protected static string|\UnitEnum|null $navigationGroup = '🎓 Learner Support';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            ...static::basicInfoSection(),
            ...static::specialNeedsSection(),
            ...static::academicSocialSection(),
            ...static::contactNeedsSection(),
        ]);
    }

    public static function basicInfoSection(): array
    {
        return [
            Section::make('Basic Info')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('first_name')
                            ->label('First Name')
                            ->required()
                            ->maxLength(50),

                        TextInput::make('middle_name')
                            ->label('Middle Name')
                            ->maxLength(50),

                        TextInput::make('last_name')
                            ->label('Last Name')
                            ->required()
                            ->maxLength(50),

                        DatePicker::make('date_of_birth')
                            ->label('Date of Birth')
                            ->native(false)
                            ->required(),

                        Select::make('sex')
                            ->label('Sex')
                            ->options([
                                'M' => 'Male',
                                'F' => 'Female',
                            ])
                            ->preload()
                            ->native(false)
                            ->required(),

                        Select::make('school_id')
                            ->label('School')
                            ->relationship('school', 'name')
                            ->preload()
                            ->searchable()
                            ->native(false)
                            ->required(),

                        DatePicker::make('enrol_date')
                            ->label('Enrollment Date')
                            ->native(false)
                            ->required(),

                        Select::make('status')
                            ->label('Status')
                            ->options(LearnerStatus::class)
                            ->preload()
                            ->native(false)
                            ->required(),

                        Select::make('class')
                            ->label('Class')
                            ->options(LearnerClass::class)
                            ->preload()
                            ->native(false)
                            ->required(),

                        TagsInput::make('talents')
                            ->label('Gifts and Talents')
                            ->placeholder('Type a talent and press Enter')
                            ->suggestions(fn () => Talent::query()->orderBy('name')->pluck('name')->all()),

                        Select::make('referred')
                            ->label('Referral Made to Specialist?')
                            ->options([
                                1 => 'Yes',
                                0 => 'No',
                            ])
                            ->default(0)
                            ->required()
                            ->native(false)
                            ->live()
                            ->dehydrated(false)
                            ->afterStateHydrated(function (Set $set, ?Model $record) {
                                $set('referred', filled($record?->referred_at) ? 1 : 0);
                            })
                            ->afterStateUpdated(function (Set $set, Get $get, ?int $state) {
                                if ($state === 1 && blank($get('referred_at'))) {
                                    $set('referred_at', now());
                                }

                                if ($state !== 1) {
                                    $set('referred_at', null);
                                    $set('specialist_visit_completed', null);
                                }
                            }),

                        DatePicker::make('referred_at')
                            ->label('Referral Date')
                            ->native(false)
                            ->hidden(fn (Get $get) => ! $get('referred'))
                            ->required(fn (Get $get) => (bool) $get('referred'))
                            ->dehydrated(true)
                            ->dehydratedWhenHidden()
                            ->mutateDehydratedStateUsing(fn ($state, Get $get) => $get('referred') ? $state : null),

                        Select::make('specialist_visit_completed')
                            ->label('Specialist Visit Completed?')
                            ->options([
                                1 => 'Yes',
                                0 => 'No',
                            ])
                            ->required(fn (Get $get) => (bool) $get('referred'))
                            ->native(false)
                            ->visible(fn (Get $get) => (bool) $get('referred'))
                            ->dehydratedWhenHidden()
                            ->mutateDehydratedStateUsing(fn ($state, Get $get) => $get('referred') ? (bool) $state : null),

                        Placeholder::make('specialist_visit_completed_na')
                            ->label('Specialist Visit Completed?')
                            ->content('N/A')
                            ->visible(fn (Get $get) => ! $get('referred')),
                    ]),
                ]),
        ];
    }

    public static function specialNeedsSection(): array
    {
        return [
            Section::make('Special Needs')
                ->schema([
                    Repeater::make('learnerConditions')
                        ->relationship()
                        ->defaultItems(0)
                        ->schema([
                            Select::make('condition_id')
                                ->label('Special Needs Name')
                                ->relationship('condition', 'name')
                                ->required()
                                ->rules(function (?Model $record): array {
                                    $rules = ['distinct'];

                                    if ($record) {
                                        $rules[] = Rule::unique('learner_condition', 'condition_id')
                                            ->where('learner_id', $record->id);
                                    }

                                    return $rules;
                                })
                                ->preload()
                                ->searchable()
                                ->native(false)
                                ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                ->helperText('Each special need can only be added once.')
                                ->validationMessages([
                                    'distinct' => 'Each special need can only be added once.',
                                    'unique' => 'This special need has already been added for the learner.',
                                ]),

                            Select::make('severity_level')
                                ->label('Severity Level')
                                ->options(SeverityLevel::class)
                                ->preload()
                                ->native(false),

                            Select::make('status')
                                ->label('Status')
                                ->options(DiagnosisStatus::class)
                                ->preload()
                                ->native(false),

                            Toggle::make('is_primary')
                                ->label('Primary')
                                ->fixIndistinctState()
                                ->inline(false),

                            Select::make('disability_onset')
                                ->label('Disability Onset')
                                ->options(DisabilityOnset::class)
                                ->preload()
                                ->native(false),

                            DatePicker::make('assigned_at')
                                ->label('Date of Assessment/Diagnosis')
                                ->native(false),

                            SpatieMediaLibraryFileUpload::make('diagnosis_documents')
                                ->label('Upload Official Assessment Form/Diagnosis')
                                ->collection('diagnosis_documents')
                                ->acceptedFileTypes(['application/pdf'])
                                ->preserveFilenames()
                                ->helperText('PDFs only.')
                                ->columnSpanFull(),

                            Textarea::make('notes')
                                ->label('Additional Notes')
                                ->rows(2)
                                ->columnSpanFull(),
                        ])
                        ->columns(2),
                ]),
        ];
    }

    public static function academicSocialSection(): array
    {
        return [
            Section::make('Academic & Social Notes')
                ->schema([
                    Textarea::make('academic_strengths')
                        ->label('Academic Strengths')
                        ->maxLength(1000)
                        ->rows(2),
                    Textarea::make('academic_weaknesses')
                        ->label('Academic Weaknesses')
                        ->maxLength(1000)
                        ->rows(2),
                    Textarea::make('social_life_observations')
                        ->label('Social Life Observations')
                        ->maxLength(1000)
                        ->rows(2),
                    Textarea::make('extracurricular_activity_notes')
                        ->label('Extracurricular Activities')
                        ->maxLength(1000)
                        ->rows(2),
                ])
                ->columns(2),
        ];
    }

    public static function contactNeedsSection(): array
    {
        return [
            Section::make('Contact & Needs')
                ->schema([
                    TextInput::make('primary_contact_name')
                        ->label('Parent/Guardian Name')
                        ->required()
                        ->maxLength(100),

                    TextInput::make('primary_contact_phone')
                        ->label('Parent/Guardian Phone')
                        ->tel()
                        ->rule('regex:/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                        ->validationMessages([
                            'regex' => 'Enter a valid phone number (e.g., +233 24 123 4567).',
                        ])
                        ->maxLength(100)
                        ->required(),

                    // TextInput::make('secondary_contact_phone')
                    //     ->label('Parent/Guardian Secondary Phone')
                    //     ->tel()
                    //     ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                    //     ->validationMessages([
                    //         'regex' => 'Enter a valid phone number (e.g., +233 20 987 6543).',
                    //     ])
                    //     ->maxLength(100),

                    // TextInput::make('primary_contact_email')
                    //     ->label('Parent/Guardian email')
                    //     ->email()
                    //     ->maxLength(100),

                    TextInput::make('specific_needs')
                        ->label('Specific Needs')
                        ->required()
                        ->maxLength(255),

                    Repeater::make('learnerAccommodations')
                        ->label('Learner Accommodations')
                        ->relationship()
                        ->defaultItems(0)
                        ->schema([
                            Select::make('accommodation_type_id')
                                ->label('Accommodation Type')
                                ->relationship('accommodationType', 'name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->native(false),

                            Select::make('status')
                                ->label('Status')
                                ->options(AccommodationStatus::class)
                                ->required()
                                ->native(false)
                                ->preload(),

                            DatePicker::make('start_date')
                                ->label('Start Date')
                                ->native(false)
                                ->required(),

                            DatePicker::make('end_date')
                                ->label('End Date')
                                ->native(false)
                                ->afterOrEqual('start_date')
                                ->validationMessages([
                                    'after_or_equal' => 'End date must be the same as or after the start date.',
                                ]),

                            Textarea::make('notes')
                                ->label('Notes')
                                ->rows(2)
                                ->maxLength(1000)
                                ->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ];
    }

    public static function table(Table $table): Table
    {
        $canViewLearnerNames = auth()->user()?->hasPermissionTo('view learner names') ?? false;

        return $table
            ->poll('10s')
            ->columns([
                TextColumn::make('id')
                    ->label('Learner Code')
                    ->sortable()
                    ->searchable()
                    ->hidden($canViewLearnerNames),

                TextColumn::make('full_name')
                    ->label('Name')
                    ->sortable()
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->orderBy('first_name', $direction)
                            ->orderBy('middle_name', $direction)
                            ->orderBy('last_name', $direction);
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $nameQuery) use ($search): void {
                            $nameQuery
                                ->where('first_name', 'like', "%{$search}%")
                                ->orWhere('middle_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    })
                    ->visible($canViewLearnerNames),

                TextColumn::make('school.name')->label('School')->sortable()->searchable(),
                TextColumn::make('sex'),
                TextColumn::make('age')
                    ->sortable()
                    ->searchable(false),
                TextColumn::make('primary_condition.name')->label('Primary Condition'),

                TextColumn::make('status')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('school')
                    ->relationship('school', 'name')
                    ->native(false),
                SelectFilter::make('status')
                    ->options(LearnerStatus::class)
                    ->native(false),
            ])
            ->defaultSort('last_name')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLearners::route('/'),
            'create' => CreateLearner::route('/create'),
            'edit' => EditLearner::route('/{record}/edit'),
        ];
    }
}
