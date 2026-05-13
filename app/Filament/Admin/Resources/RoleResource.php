<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RoleResource\Pages\ListRoles;
use App\Filament\Admin\Resources\RoleResource\Pages\ViewRole;
use App\Models\Role;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-key';

    protected static string|\UnitEnum|null $navigationGroup = 'Admin';

    public static function shouldRegisterNavigation(): bool
    {
        return Filament::getId() === 'admin';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('display_name')
                    ->label('Display name')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('guard_name')
                    ->label('Guard')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([])
            ->emptyStateActions([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Role')
                    ->schema([
                        TextEntry::make('display_name')->label('Display name'),
                        TextEntry::make('name')->label('System name'),
                        TextEntry::make('guard_name')->label('Guard'),
                        TextEntry::make('description')->label('Description')->markdown(),
                        TextEntry::make('supervisorRole.display_name')
                            ->label('Reports to')
                            ->getStateUsing(fn (Role $record): ?string => $record->supervisorRole?->display_name ?? $record->supervisorRole?->name),
                    ])
                    ->columns(2),

                Section::make('Permissions')
                    ->schema([
                        TextEntry::make('permissions')
                            ->hiddenLabel()
                            ->state(fn (Role $record): array => $record->permissions->pluck('name')->sort()->values()->all())
                            ->badge()
                            ->wrap(),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'view' => ViewRole::route('/{record}'),
        ];
    }
}
