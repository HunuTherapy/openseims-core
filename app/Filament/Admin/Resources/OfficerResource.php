<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OfficerResource\Pages\CreateOfficer;
use App\Filament\Admin\Resources\OfficerResource\Pages\ListOfficers;
use App\Filament\Admin\Resources\OfficerResource\Pages\ViewOfficer;
use App\Filament\Admin\Resources\OfficerResource\RelationManagers\SchoolsRelationManager;
use App\Models\District;
use App\Models\Officer;
use App\Models\Region;
use App\Models\School;
use App\Support\OfficerProvisioning;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;

class OfficerResource extends Resource
{
    protected static ?string $model = Officer::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-plus';

    protected static string|\UnitEnum|null $navigationGroup = '🏫 Schools & Staff';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->rule('string')
                    ->maxLength(255)
                    ->required(),

                Select::make('app_role')
                    ->label('Role')
                    ->options(fn (): array => app(OfficerProvisioning::class)->roleOptions())
                    ->formatStateUsing(fn ($state, ?Officer $record): ?string => OfficerProvisioning::normalizeAppRole(
                        $record?->user?->roles()->value('name') ?? $record?->role
                    ))
                    ->required(),

                Toggle::make('formal_training')
                    ->label('Formal Training')
                    ->required(),

                TextInput::make('phone')
                    ->rule('regex:/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                    ->helperText('Use digits, spaces, +, or hyphens. Example: +233 24 123 4567.')
                    ->validationMessages([
                        'regex' => 'Enter a valid phone number (e.g., +233 24 123 4567).',
                    ])
                    ->formatStateUsing(fn (?string $state, ?Officer $record): string => static::maskContactValue($state, $record))
                    ->rule(fn (?Officer $record) => Rule::unique('officers', 'phone')->ignore($record?->getKey()))
                    ->required(),

                TextInput::make('email')
                    ->email()
                    ->maxLength(255)
                    ->formatStateUsing(fn (?string $state, ?Officer $record): string => static::maskContactValue($record?->user?->email, $record))
                    ->rule(fn (?Officer $record) => Rule::unique('users', 'email')->ignore($record?->user_id))
                    ->required(),

                Select::make('region_id')
                    ->label('Region')
                    ->options(fn (): array => Region::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->formatStateUsing(fn ($state, ?Officer $record): ?int => $record?->user?->region_id)
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set): void {
                        $set('district_id', null);
                    }),

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
                    ->formatStateUsing(fn ($state, ?Officer $record): ?int => $record?->user?->district_id)
                    ->searchable()
                    ->required(),

                Hidden::make('role')
                    ->dehydrated(false),

                Toggle::make('is_deployed')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('10s')
            ->columns([
                TextColumn::make('name')
                    ->tooltip('Full name of the SpED officer.')
                    ->searchable(),

                TextColumn::make('role')
                    ->searchable(),

                TextColumn::make('phone')
                    ->tooltip('Visible only to HQ admin users for data privacy.')
                    ->formatStateUsing(fn (?string $state, Officer $record): string => static::maskContactValue($state, $record))
                    ->searchable(),

                TextColumn::make('user.email')
                    ->tooltip('Visible only to HQ admin users for data privacy.')
                    ->formatStateUsing(fn (?string $state, Officer $record): string => static::maskContactValue($state, $record))
                    ->searchable(),

                TextColumn::make('user.region.name')
                    ->label('Region')
                    ->tooltip('Geographical assignment of the officer.')
                    ->searchable(),

                TextColumn::make('user.district.name')
                    ->label('District')
                    ->tooltip('Geographical assignment of the officer.')
                    ->searchable(),

                IconColumn::make('is_deployed')
                    ->boolean()
                    ->label('Deployed'),
            ])
            ->filters([
                SelectFilter::make('region_id')
                    ->label('Region')
                    ->options(fn (): array => Region::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->query(function ($query, array $data) {
                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        return $query->whereHas('user', fn ($userQuery) => $userQuery->where('region_id', $data['value']));
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->hidden(Filament::getId() !== 'admin'),
                Action::make('assign_school')
                    ->icon('heroicon-o-academic-cap')
                    ->fillForm(fn (Officer $record): array => [
                        'schools' => $record->schools()->pluck('schools.id')->all(),
                    ])
                    ->schema(function (Schema $schema) {
                        return $schema
                            ->components([
                                Select::make('schools')
                                    ->preload()
                                    ->multiple()
                                    ->options(function (Officer $record): array {
                                        return School::query()
                                            ->whereHas('district', fn ($districtQuery) => $districtQuery->where('region_id', $record->user?->region_id))
                                            ->orderBy('name')
                                            ->pluck('name', 'id')
                                            ->all();
                                    }),
                            ]);
                    })
                    ->action(function (Officer $record, array $data): void {
                        $record->schools()->sync($data['schools'] ?? []);
                    })
                    ->color('success')
                    ->hidden(Filament::getId() !== 'admin'),
                DeleteAction::make()
                    ->hidden(Filament::getId() !== 'admin'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('toggle_deployment_status')
                        ->label('Mark deployed/undeployed')
                        ->action(fn (Collection $records) => $records->each(function ($item) {
                            $item->update(['is_deployed' => ! $item->is_deployed]);
                        })
                        ),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            SchoolsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOfficers::route('/'),
            'create' => CreateOfficer::route('/create'),
            //            'edit' => Pages\EditOfficer::route('/{record}/edit'),
            'view' => ViewOfficer::route('/{record}'),
        ];
    }

    protected static function maskContactValue(?string $state, ?Officer $record): string
    {
        if (blank($state)) {
            return '';
        }

        $viewer = auth()->user();

        if ($viewer?->canViewOfficerContactOf($record?->user)) {
            return $state;
        }

        return str_repeat('*', strlen($state));
    }
}
