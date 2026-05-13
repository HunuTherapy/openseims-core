<?php

namespace App\Filament\Resources\LearnerResource\RelationManagers;

use App\Enums\DiagnosisStatus;
use App\Enums\DisabilityOnset;
use App\Enums\SeverityLevel;
use App\Models\LearnerCondition;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class LearnerConditionsRelationManager extends RelationManager
{
    protected static string $relationship = 'learnerConditions';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('condition_id')
                ->relationship('condition', 'name')
                ->placeholder(__('Condition Name'))
                ->required()
                ->preload()
                // ->columnSpan(3)
                ->searchable()
                ->native(false)
                ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
            Select::make('severity_level')
                // ->hiddenLabel()
                // ->columnSpan(2)
                ->placeholder(__('Severity Level'))
                ->options(SeverityLevel::class)
                ->preload()
                ->native(false),

            Toggle::make('is_primary')
                ->label('Primary')
                ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Check this if this is the dominant disability for the learner.')
                ->fixIndistinctState()
                ->inline(false),

            Select::make('status')
                ->placeholder(__('Status'))
                ->options(DiagnosisStatus::class)
                ->preload()
                ->native(false),

            Select::make('disability_onset')
                ->placeholder(__('Disability Onset'))
                ->options(DisabilityOnset::class)
                ->preload()
                ->native(false),

            Textarea::make('notes')
                ->rows(2)
                ->columnSpan(3)
                ->placeholder(__('Additional notes'))
                ->rule('string')
                ->maxLength(1000),

        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('condition.name')
                    ->label('Condition')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('assessment.reference')
                    ->label('Assessment')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                // IconColumn::make('is_primary')
                //     ->label('Primary')
                //     ->boolean(),
                ToggleColumn::make('is_primary')
                    ->beforeStateUpdated(function (LearnerCondition $record) {
                        LearnerCondition::where('id', '!=', $record->id)->update(['is_primary' => false]);
                    })
                    ->label('Primary'),

                // ToggleColumn::make('is_primary')
                //     ->beforeStateUpdated(function (LearnerCondition $record, $state) {
                //         if ($state) {
                //             // Set all other conditions for this learner to not primary
                //             LearnerCondition::where('learner_id', $record->learner_id)
                //                 ->where('id', '!=', $record->id)
                //                 ->update(['is_primary' => false]);
                //         }
                //     });
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('severity_level')
                    ->label('Severity Level')
                    ->sortable(),
                TextColumn::make('disability_onset')
                    ->label('Onset')
                    ->sortable(),
                TextColumn::make('assigned_at')
                    ->date()
                    ->sortable(),
                TextColumn::make('notes')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
