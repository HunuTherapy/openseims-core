<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssessmentResource\Pages\CreateAssessment;
use App\Filament\Resources\AssessmentResource\Pages\EditAssessment;
use App\Filament\Resources\AssessmentResource\Pages\ListAssessments;
use App\Filament\Resources\AssessmentResource\Pages\ViewAssessment;
use App\Models\Assessment;
use App\Models\Learner;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AssessmentResource extends Resource
{
    protected static ?string $model = Assessment::class;

    protected static string|\UnitEnum|null $navigationGroup = '🎓 Learner Support';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('learner_id')
                ->relationship('learner', 'id') // use a real column for DB query
                ->getOptionLabelFromRecordUsing(fn (Learner $record) => $record->getVisibleName())
                ->label('Learner')
                ->preload()
                ->required()
                ->searchable(['first_name', 'middle_name', 'last_name'])
                ->native(false),

            Select::make('form_id')
                ->relationship('form', 'name')
                ->label('Assessment Form')
                ->preload()
                ->required()
                ->searchable()
                ->native(false),

            Select::make('assessor_id')
                ->relationship('assessor', 'name', fn (Builder $query) => $query->whereDoesntHave('roles', fn (Builder $query) => $query->where('name', 'national_admin')))
                ->label('Assessor')
                ->preload()
                ->required()
                ->searchable()
                ->native(false),

            DatePicker::make('assessment_date')
                ->required(),

            KeyValue::make('raw_scores')
                ->label('Raw Scores')
                ->keyLabel('Metric')
                ->valueLabel('Value'),

            Textarea::make('overall_result')
                ->label('Overall Result'),

            Textarea::make('next_step')
                ->label('Next Step / Recommendation'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('learner.full_name')
                    ->label('Learner')
                    ->getStateUsing(fn (Assessment $record) => $record->learner?->getVisibleName() ?? sprintf('Learner #%d', $record->learner_id))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('form.name')->label('Form'),
                TextColumn::make('assessor.name')->label('Assessor'),
                TextColumn::make('assessment_date')->date()->sortable(),
                TextColumn::make('overall_result')->limit(30),
            ])
            ->filters([
                SelectFilter::make('learner')
                    ->relationship('learner', 'id')
                    ->getOptionLabelFromRecordUsing(fn (Learner $record) => $record->getVisibleName()),
                SelectFilter::make('form')->relationship('form', 'name'),
            ])
            ->defaultSort('assessment_date', 'desc')
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
            'index' => ListAssessments::route('/'),
            'create' => CreateAssessment::route('/create'),
            'edit' => EditAssessment::route('/{record}/edit'),
            'view' => ViewAssessment::route('/{record}'),
        ];
    }
}
