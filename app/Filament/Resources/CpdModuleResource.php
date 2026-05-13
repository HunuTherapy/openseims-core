<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CpdModuleResource\Pages\CreateCpdModule;
use App\Filament\Resources\CpdModuleResource\Pages\EditCpdModule;
use App\Filament\Resources\CpdModuleResource\Pages\ListCpdModules;
use App\Filament\Resources\CpdModuleResource\RelationManagers\IePracticesRelationManager;
use App\Models\CpdModule;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CpdModuleResource extends Resource
{
    protected static ?string $model = CpdModule::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document';
    // protected static ?string $navigationLabel = 'CPD Modules';

    protected static string|\UnitEnum|null $navigationGroup = 'CPD Management';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                // Select::make('ie_practice_incorporated')
                //     ->label('IE Practice Incorporated')
                //     ->options([
                //         0 => 'No',
                //         1 => 'Yes',
                //     ])
                //     ->required(),
                // Forms\Components\RelationManager::make('iePractices')
                //     ->relationship('iePractices')
                //     ->label('IE Practices')
                //     ->preload()
                //     ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
                // TextColumn::make('ie_practice_incorporated')->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            IePracticesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCpdModules::route('/'),
            'create' => CreateCpdModule::route('/create'),
            'edit' => EditCpdModule::route('/{record}/edit'),
        ];
    }
}
