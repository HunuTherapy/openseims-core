<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupervisionResource\Pages;
use App\Filament\Resources\SupervisionResource\Pages\CreateSupervision;
use App\Filament\Resources\SupervisionResource\Pages\EditBasicInfo;
use App\Filament\Resources\SupervisionResource\Pages\EditDomainScores;
use App\Filament\Resources\SupervisionResource\Pages\EditObservations;
use App\Filament\Resources\SupervisionResource\Pages\ListSupervisions;
use App\Filament\Resources\SupervisionResource\Pages\MySupervisionReports;
use App\Filament\Resources\SupervisionResource\Pages\ViewSupervision;
use App\Models\Role;
use App\Models\SupervisionReport;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class SupervisionResource extends Resource
{
    protected static ?string $model = SupervisionReport::class;

    protected static string|\UnitEnum|null $navigationGroup = '📋 Monitoring & Supervision';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Basic Information')->schema([
                        Section::make('School details')
                            ->schema([
                                Select::make('school_id')->relationship('school', 'name')->required(),
                                DatePicker::make('visit_date')
                                    ->required()
                                    ->maxDate(now())
                                    ->native(false)
                                    ->helperText('Date the supervision visit occurred.'),
                            ])
                            ->columns(3),

                        Section::make('Supervisor details')
                            ->schema([
                                Select::make('supervisor_id')
                                    ->relationship('supervisor', 'name', fn (Builder $query) => $query->whereDoesntHave('roles', fn (Builder $query) => $query->where('name', 'national_admin')))
                                    ->required()
                                    ->label('Supervisor Name'),
                                Select::make('supervisor_role')->options([
                                    ...static::getSupervisorRoleOptions(),
                                ])->required(),
                            ])
                            ->columns(2),

                        Section::make('Recipient details')
                            ->schema([
                                Select::make('recipient_id')
                                    ->relationship('recipient', 'name')
                                    ->required()
                                    ->helperText('Staff member assigned to act on the report.'),
                            ])
                            ->columns(2),
                    ]),

                    Step::make('Observations')->schema([
                        Repeater::make('observations')->relationship()->label('Observations')->schema([
                            Textarea::make('issues_found')
                                ->label('Issue Found')
                                ->helperText('Summarize any problems or barriers observed during visit.')
                                ->rule('string')
                                ->maxLength(1000)
                                ->required(),

                            Textarea::make('intervention_provided')
                                ->label('Intervention Provided')
                                ->rule('string')
                                ->maxLength(1000)
                                ->required(),

                            DatePicker::make('deadline_date')
                                ->label('Deadline')
                                ->native(false)
                                ->afterOrEqual(fn (Get $get) => $get('../../visit_date'))
                                ->validationMessages([
                                    'after_or_equal' => 'Deadline must be on or after the visit date.',
                                ]),

                            Toggle::make('resolved')
                                ->label('Resolved?')
                                ->default(false)
                                ->helperText('Mark this ON if all listed issues have been addressed.'),
                        ])->createItemButtonLabel('Add Observation')->columns(1),
                    ]),

                    Step::make('Domain Scores')
                        ->schema([
                            Repeater::make('domainScores')
                                ->relationship()
                                ->schema([
                                    TextInput::make('domain_name')
                                        ->required()
                                        ->rule('string')
                                        ->maxLength(255)
                                        ->helperText('Score the observed domain (e.g., Inclusion Practices, Teaching Methods)'),
                                    TextInput::make('score')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(100)
                                        ->helperText('Enter a score between 0 and 100.')
                                        ->validationMessages([
                                            'min' => 'Score must be at least 0.',
                                            'max' => 'Score must be 100 or less.',
                                        ])
                                        ->required(),
                                ])
                                ->createItemButtonLabel('Add Domain')
                                ->columns(2),
                        ]),

                    // Wizard\Step::make('Attachments')->schema([
                    //     SpatieMediaLibraryFileUpload::make('attachments')
                    //         ->label('Upload Photos')
                    //         ->collection('attachments')
                    //         ->multiple()
                    //         ->preserveFilenames()
                    //         ->enableOpen()
                    //         ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    //         ->helperText('Attach relevant photos (JPEG, PNG, or WebP). Max size 5MB each.')
                    //         ->columnSpanFull(),
                    // ]),
                ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('school.name')
                    ->label('School')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('supervisor.name')
                    ->label('Supervisor Name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('visit_date')
                    ->label('Visit Date')
                    ->date()
                    ->sortable()
                    ->tooltip('Date the supervision visit occurred.'),
            ])
            ->filters([
                SelectFilter::make('school_id')
                    ->label('School')
                    ->relationship('school', 'name')
                    ->searchable(),

                SelectFilter::make('supervisor_id')
                    ->label('Supervisor name')
                    ->preload()
                    ->relationship('supervisor', 'name', fn (Builder $query) => $query->whereDoesntHave('roles', fn (Builder $query) => $query->where('name', 'national_admin')))
                    ->searchable(),

                Filter::make('visit_date')
                    ->schema([
                        DatePicker::make('visit_date')->label('Visited on')->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['visit_date'], fn ($query, $date) => $query->whereDate('visit_date', $date));
                    }),

                TernaryFilter::make('observations.resolved')
                    ->label('Resolved')
                    ->nullable(),

                SelectFilter::make('recipient_id')
                    ->label('Recipient')
                    ->preload()
                    ->relationship('recipient', 'name', fn (Builder $query) => $query->whereDoesntHave('roles', fn (Builder $query) => $query->where('name', 'national_admin')))
                    ->searchable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->url(fn ($record) => route('filament.seims.resources.supervisions.edit-basic-info', ['record' => $record])),
            ])
            ->defaultSort('visit_date', 'desc');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Flex::make([
                    // LEFT SIDE
                    Section::make([
                        Section::make('Observations')
                            ->schema([
                                RepeatableEntry::make('observations')
                                    ->hiddenLabel()
                                    ->schema([
                                        TextEntry::make('issues_found')->label('Issue Found'),
                                        TextEntry::make('intervention_provided')->label('Intervention Provided'),
                                        TextEntry::make('deadline_date')->label('Deadline')->date(),
                                        IconEntry::make('resolved'),
                                    ])
                                    ->columns(2),
                            ]),

                        Section::make('Domain Scores')
                            ->schema([
                                RepeatableEntry::make('domainScores')
                                    ->hiddenLabel()
                                    ->schema([
                                        TextEntry::make('domain_name'),
                                        TextEntry::make('score'),
                                    ])
                                    ->columns(2),
                            ]),

                        // Infolists\Components\Section::make('Attachments')
                        //     ->schema([
                        //         SpatieMediaLibraryImageEntry::make('attachments')
                        //             ->collection('attachments')
                        //             ->label('Attached Photos')
                        //             ->columnSpanFull(),
                        //     ]),
                    ])->extraAttributes(['class' => 'invisible-section']),

                    // RIGHT SIDE
                    Section::make([
                        TextEntry::make('school.name')->label('School'),
                        TextEntry::make('school.school_district')->label('District')->getStateUsing(fn ($record) => $record->school->district_name),
                        TextEntry::make('school.school_level')->label('School Level'),
                        TextEntry::make('school.school_type')->label('School Type'),
                        TextEntry::make('visit_date')->label('Visit Date')->date(),
                        TextEntry::make('supervisor.name')->label('Supervisor Name'),
                        TextEntry::make('supervisor_role')->label('Supervisor Role'),
                        TextEntry::make('recipient.name')->label('Report Recipient'),
                    ])
                        ->columns(2)
                        ->grow(false)
                        ->extraAttributes(['class' => 'w-80']),
                ])->from('md'),
            ]);
    }

    public static function getSupervisorRoleOptions(): array
    {
        return Role::query()
            ->where('guard_name', 'web')
            ->where('name', '!=', 'national_admin')
            ->orderBy('display_name')
            ->pluck('display_name', 'display_name')
            ->all();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSupervisions::route('/'),
            'create' => CreateSupervision::route('/create'),
            'my-reports' => MySupervisionReports::route('/my-reports'),
            'view' => ViewSupervision::route('/{record}'),
            // 'edit' => Pages\EditSupervision::route('/{record}/edit'),
            'edit-basic-info' => EditBasicInfo::route('/{record}/basic-info'),
            'edit-observations' => EditObservations::route('/{record}/observations'),
            'edit-domain-scores' => EditDomainScores::route('/{record}/domain-scores'),
            // 'edit-attachments' => Pages\EditAttachments::route('/{record}/attachments'),
        ];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            EditBasicInfo::class,
            EditObservations::class,
            EditDomainScores::class,
            // Pages\EditAttachments::class,
        ]);
    }

    public static function getNavigationItems(): array
    {
        $user = Auth::user();

        return [
            NavigationItem::make('All Reports')
                ->label('All Supervision Reports')
                ->url(static::getUrl())
                ->icon('heroicon-o-clipboard-document')
                ->visible((bool) $user?->hasPermissionTo('view all reports'))
                ->group('📋 Monitoring & Supervision'),

            NavigationItem::make('My Reports')
                ->label('My Reports')
                ->url(static::getUrl('my-reports'))
                ->icon('heroicon-o-clipboard-document-check')
                ->visible((bool) $user?->hasPermissionTo('view assigned reports'))
                ->badge(fn () => SupervisionReport::query()
                    ->where('recipient_id', auth()->id())
                    ->count())
                ->group('📋 Monitoring & Supervision'),
        ];
    }
}
