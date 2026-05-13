<?php

namespace App\Filament\Resources;

use App\Enums\SchoolLevel;
use App\Enums\SchoolType;
use App\Filament\Resources\SchoolResource\Pages\CreateSchool;
use App\Filament\Resources\SchoolResource\Pages\EditSchool;
use App\Filament\Resources\SchoolResource\Pages\ListSchools;
use App\Filament\Resources\SchoolResource\Pages\ViewSchool;
use App\Models\District;
use App\Models\Region;
use App\Models\School;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SchoolResource extends Resource
{
    protected static ?string $model = School::class;

    protected static string|\UnitEnum|null $navigationGroup = '🏫 Schools & Staff';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-library';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)->schema([
                TextInput::make('emis_code')
                    ->label('EMIS Code')
                    ->unique(ignoreRecord: true)
                    ->minLength(8)
                    ->maxLength(13)
                    ->required(),

                TextInput::make('name')
                    ->rule('string')
                    ->maxLength(255)
                    ->required(),

                Select::make('region_id')
                    ->label('Region')
                    ->options(fn (): array => Region::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->live()
                    ->dehydrated(false)
                    ->afterStateHydrated(function (Set $set, ?School $record): void {
                        $set('region_id', $record?->district?->region_id);
                    })
                    ->afterStateUpdated(function (Set $set): void {
                        $set('district_id', null);
                    })
                    ->required(),

                Select::make('district_id')
                    ->label('District')
                    ->options(function (Get $get): array {
                        $regionId = $get('region_id');

                        if (blank($regionId)) {
                            return [];
                        }

                        return District::query()
                            ->where('region_id', $regionId)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all();
                    })
                    ->searchable()
                    ->required(),

                Select::make('school_type')
                    ->label('School Type')
                    ->options(SchoolType::class)
                    ->preload()
                    ->native(false)
                    ->required(),

                Select::make('school_level')
                    ->options(SchoolLevel::class)
                    ->preload()
                    ->native(false)
                    ->required(),

                Toggle::make('is_inclusive')
                    ->label('Is Inclusive'),

                Toggle::make('resource_teacher')
                    ->label('Resource Teacher'),

                TextInput::make('number_of_teachers')
                    ->label('Number of Teachers')
                    ->numeric()
                    ->minValue(0),

                Repeater::make('accessibility')
                    ->label('Accessibility Features')
                    ->defaultItems(0)
                    ->schema([
                        TextInput::make('feature')
                            ->label('Feature')
                            ->required(),
                        Select::make('value')
                            ->label('Yes/No')
                            ->options([
                                'yes' => 'Yes',
                                'no' => 'No',
                            ])
                            ->native(false)
                            ->required(),
                    ])
                    ->columns(2)
                    ->nullable(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('10s')
            ->modifyQueryUsing(function ($query) {
                return $query->withCount('teachers')
                    ->withCount([
                        'teachers as sen_certified_teachers_count' => function ($query) {
                            $query->where('sen_certified', true);
                        },
                    ]);
            })
            ->columns([
                TextColumn::make('emis_code')->label('EMIS Code')->sortable()->searchable(),
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('district.name')->label('District')->sortable()->searchable(),
                TextColumn::make('district.region.name')->label('Region')->sortable()->searchable(),
                TextColumn::make('school_type')->label('School Type'),
                ToggleColumn::make('is_inclusive')->label('Is Inclusive'),
                // TextColumn::make('teachers_count')
                TextColumn::make('number_of_teachers')
                    ->label('Number of Teachers')
                    ->numeric()
                    ->alignCenter(),
                TextColumn::make('sen_certified_teachers_count')
                    ->label('Number of SEN-Certified Teachers')
                    ->numeric()
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('region_id')
                    ->label('Region')
                    ->options(fn (): array => Region::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->query(function ($query, array $data) {
                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        return $query->whereHas('district', fn ($districtQuery) => $districtQuery->where('region_id', $data['value']));
                    }),
                SelectFilter::make('district_id')
                    ->label('District')
                    ->options(fn (): array => District::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->query(function ($query, array $data) {
                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        return $query->where('district_id', $data['value']);
                    }),
                SelectFilter::make('school_type')
                    ->options(SchoolType::class)
                    ->native(false),
            ])
            ->defaultSort('name')
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
            'index' => ListSchools::route('/'),
            'create' => CreateSchool::route('/create'),
            'view' => ViewSchool::route('/{record}'),
            'edit' => EditSchool::route('/{record}/edit'),
        ];
    }
}
