<?php

namespace App\Filament\Resources;

use App\Enums\LearnerClass;
use App\Enums\TeacherType;
use App\Filament\Resources\TeacherResource\Pages\CreateTeacher;
use App\Filament\Resources\TeacherResource\Pages\EditTeacherBasicInfo;
use App\Filament\Resources\TeacherResource\Pages\EditTeacherQualifications;
use App\Filament\Resources\TeacherResource\Pages\ListTeachers;
use App\Filament\Resources\TeacherResource\Pages\ViewTeacher;
use App\Filament\Resources\TeacherResource\RelationManagers\TeacherConditionsRelationManager;
use App\Models\Teacher;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class TeacherResource extends Resource
{
    protected static ?string $model = Teacher::class;

    public static function getRelations(): array
    {
        return [
            TeacherConditionsRelationManager::class,
        ];
    }

    protected static string|\UnitEnum|null $navigationGroup = '🏫 Schools & Staff';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Wizard::make()
                ->steps([
                    Step::make('Basic Information')
                        ->schema(self::basicInfoSchema()),
                    Step::make('Qualifications')
                        ->schema(self::qualificationsSchema()),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('10s')
            ->columns([
                TextColumn::make('teacher_no')->sortable()->searchable(),
                TextColumn::make('teacher_type')
                    ->label('Teacher Type')
                    ->badge()
                    ->formatStateUsing(fn (?TeacherType $state): string => $state?->getLabel() ?? '-'),
                TextColumn::make('first_name')->sortable()->searchable(),
                TextColumn::make('last_name')->sortable()->searchable(),
                TextColumn::make('school.name')->label('School')->sortable(),
                TextColumn::make('special_education_background'),
                BooleanColumn::make('sen_certified')->label('SEN?'),
                BooleanColumn::make('training_on_inclusion'),
                TextColumn::make('skills')
                    ->separator(',')
                    ->badge(),
                TextColumn::make('in_service_trainings_attended')->sortable(),
            ])
            ->filters([
                SelectFilter::make('school')->relationship('school', 'name'),
                TernaryFilter::make('sen_certified')->label('SEN Certified'),
            ])
            ->defaultSort('last_name')
            ->recordActions([
                EditAction::make()
                    ->url(fn (Teacher $record): string => self::getUrl('edit-basic-info', ['record' => $record])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('toggle_deployment_status')
                        ->label('Mark deployed/undeployed')
                        ->action(fn (Collection $records) => $records->each(function ($item) {
                            $item->update(['is_deployed' => ! $item->is_deployed]);
                        })
                        ),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTeachers::route('/'),
            'create' => CreateTeacher::route('/create'),
            'view' => ViewTeacher::route('/{record}'),
            'edit-basic-info' => EditTeacherBasicInfo::route('/{record}/basic-info'),
            'edit-qualifications' => EditTeacherQualifications::route('/{record}/qualifications'),
        ];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            EditTeacherBasicInfo::class,
            EditTeacherQualifications::class,
        ]);
    }

    /**
     * @return array<int, Component>
     */
    public static function basicInfoSchema(): array
    {
        return [
            Grid::make(2)->schema([
                TextInput::make('teacher_no')
                    ->label('Teacher Number')
                    ->rule('string')
                    ->maxLength(255)
                    ->required()
                    ->unique(ignoreRecord: true),

                Select::make('teacher_type')
                    ->label('Teacher Type')
                    ->options(TeacherType::class)
                    ->default(TeacherType::CLASS_TEACHER->value)
                    ->required()
                    ->preload(),

                Select::make('school_id')
                    ->relationship('school', 'name')
                    ->label('School')
                    ->preload()
                    ->searchable()
                    ->native(false)
                    ->required(),

                TextInput::make('first_name')
                    ->rule('string')
                    ->maxLength(255)
                    ->required(),

                TextInput::make('last_name')
                    ->rule('string')
                    ->maxLength(255)
                    ->required(),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255)
                    ->helperText('Required for school coordinators.')
                    ->required(fn (Get $get): bool => in_array($get('teacher_type'), [
                        TeacherType::SCHOOL_COORDINATOR->value,
                    ], true)),

                Select::make('class')
                    ->label('Class')
                    ->options(LearnerClass::class)
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set): void {
                        $set('learners', []);
                    }),

                Select::make('learners')
                    ->label('Learners in Class')
                    ->relationship('learners', 'id', function ($query, Get $get) {
                        $class = $get('class');

                        if (blank($class)) {
                            $query->whereRaw('1 = 0');

                            return;
                        }

                        $query->where('learners.class', $class);
                    })
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->getVisibleName())
                    ->multiple()
                    ->preload()
                    ->searchable(['first_name', 'middle_name', 'last_name'])
                    ->pivotData(fn (Get $get) => ['class' => $get('class')])
                    ->visible(fn (Get $get): bool => filled($get('class')))
                    ->helperText('Optional. Select learners from the chosen class.')
                    ->columnSpanFull(),

                Select::make('condition_ids')
                    ->label('Special Needs / Disabilities')
                    ->relationship('conditions', 'name')
                    ->preload()
                    ->helperText('Leave blank for None.')
                    ->multiple()
                    ->searchable()
                    ->columnSpanFull(),
            ]),
        ];
    }

    /**
     * @return array<int, Component>
     */
    public static function qualificationsSchema(): array
    {
        return [
            Grid::make(2)->schema([
                // TextInput::make('qualification')
                //     ->label('Qualification')
                //     ->rule('string')
                //     ->maxLength(255)
                //     ->nullable(),

                Textarea::make('special_education_background')
                    ->label('Special Education background')
                    ->rule('string')
                    ->maxLength(1000)
                    ->nullable()
                    ->columnSpanFull(),

                Toggle::make('sen_certified')
                    ->label('SEN Certified'),

                Toggle::make('training_on_inclusion')
                    ->label('Training on Inclusion'),

                TagsInput::make('skills')
                    ->label('Skills in Inclusive Pedagogies')
                    ->separator(',')
                    ->splitKeys(['Enter', 'Tab', ','])
                    ->trim()
                    ->nestedRecursiveRules(['max:255'])
                    ->nullable(),

                TextInput::make('in_service_trainings_attended')
                    ->label('Number of in-service trainings attended')
                    ->integer()
                    ->minValue(0)
                    ->nullable(),

                Textarea::make('other_qualifications')
                    ->label('Other Qualifications')
                    ->rule('string')
                    ->maxLength(1000)
                    ->nullable(),

                // TextInput::make('cpd_hours')
                //     ->integer()
                //     ->label('CPD Hours')
                //     ->minValue(0)
                //     ->maxValue(200)
                //     ->default(0),

                // DatePicker::make('last_cpd_date')
                //     ->label('Last CPD Date')
                //     ->nullable(),

                // Toggle::make('is_deployed')
                //     ->label('Is Deployed')
                //     ->default(false),
            ]),
        ];
    }
}
