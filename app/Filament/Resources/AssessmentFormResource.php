<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssessmentFormResource\Pages;
use App\Filament\Resources\AssessmentFormResource\Pages\ListAssessmentForms;
use App\Models\AssessmentForm;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AssessmentFormResource extends Resource
{
    protected static ?string $model = AssessmentForm::class;

    protected static string|\UnitEnum|null $navigationGroup = '🎓 Learner Support';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->rule('string')
                    ->maxLength(255)
                    ->required(),

                TextInput::make('url')
                    ->label('Link')
                    ->url()
                    ->maxLength(255)
                    ->required(),

                Textarea::make('description')
                    ->rows(5)
                    ->rule('string')
                    ->maxLength(1000)
                    ->required()
                    ->columnSpan(2),

                Toggle::make('active')
                    ->visible($schema->getOperation() !== 'create')
                    ->label('Active'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('url')
                    ->label('Link'),
                IconColumn::make('active'),
                TextColumn::make('responses_count')
                    ->alignCenter()
                    ->numeric(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAssessmentForms::route('/'),
            'create' => Pages\CreateAssessmentForm::route('/create'),
            'edit' => Pages\EditAssessmentForm::route('/{record}/edit'),
        ];
    }
}
