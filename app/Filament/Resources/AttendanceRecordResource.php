<?php

namespace App\Filament\Resources;

use App\Enums\LearnerClass;
use App\Filament\Resources\AttendanceRecordResource\Pages\CreateAttendanceRecord;
use App\Filament\Resources\AttendanceRecordResource\Pages\EditAttendanceRecord;
use App\Filament\Resources\AttendanceRecordResource\Pages\ListAttendanceRecords;
use App\Filament\Resources\AttendanceRecordResource\Pages\ViewAttendanceRecord;
use App\Models\AttendanceRecord;
use App\Models\Learner;
use App\Models\Teacher;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class AttendanceRecordResource extends Resource
{
    protected static ?string $model = AttendanceRecord::class;

    protected static string|\UnitEnum|null $navigationGroup = '🎓 Learner Support';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('teacher_id')
                ->relationship('teacher', 'id')
                ->getOptionLabelFromRecordUsing(fn (Teacher $record) => "{$record->first_name} {$record->last_name}")
                ->label('Teacher')
                ->preload()
                ->required()
                ->searchable(['first_name', 'last_name', 'teacher_no'])
                ->native(false),

            Select::make('class')
                ->label('Class')
                ->options(LearnerClass::class)
                ->preload()
                ->required()
                ->native(false),

            Select::make('learner_id')
                ->relationship('learner', 'id') // use a real column for DB query
                ->getOptionLabelFromRecordUsing(fn (Learner $record) => $record->getVisibleName())
                ->label('Learner')
                ->preload()
                ->required()
                ->searchable(['first_name', 'middle_name', 'last_name'])
                ->native(false),

            DatePicker::make('date')
                ->native(false)
                ->required(),

            Toggle::make('present')
                ->label('Present')
                ->live(),

            TextInput::make('reason')
                ->label('Reason for Absence')
                ->maxLength(255)
                ->nullable()
                ->required(fn ($get) => $get('present') === false)
                ->dehydratedWhenHidden()
                ->visible(fn ($get) => $get('present') === false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('10s')
            ->columns([
                TextColumn::make('teacher.full_name')
                    ->label('Teacher')
                    ->getStateUsing(fn ($record) => "{$record->teacher->first_name} {$record->teacher->last_name}")
                    ->sortable()
                    ->searchable(),

                TextColumn::make('class')
                    ->label('Class')
                    ->sortable(),

                TextColumn::make('learner.full_name')
                    ->label('Learner')
                    ->getStateUsing(fn (AttendanceRecord $record) => $record->learner?->getVisibleName() ?? sprintf('Learner #%d', $record->learner_id))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('date')
                    ->date()
                    ->sortable(),

                IconColumn::make('present')
                    ->boolean()
                    ->label('Present'),

                TextColumn::make('reason')
                    ->label('Reason')
                    ->limit(40),
            ])
            ->filters([
                SelectFilter::make('teacher')
                    ->relationship('teacher', 'id')
                    ->getOptionLabelFromRecordUsing(fn (Teacher $record) => "{$record->first_name} {$record->last_name}"),

                SelectFilter::make('class')
                    ->options(LearnerClass::class),

                SelectFilter::make('learner')
                    ->relationship('learner', 'id')
                    ->getOptionLabelFromRecordUsing(fn (Learner $record) => $record->getVisibleName()),

                TernaryFilter::make('present')
                    ->label('Was Present?'),
            ])
            ->defaultSort('date', 'desc')
            // if needed, use groups() instead and reference the desired default group here
            ->defaultGroup(
                Group::make('class')
                    ->label('')
                    ->getKeyFromRecordUsing(fn (AttendanceRecord $record): string => "{$record->class}|{$record->date->format('Y-m-d')}")
                    ->getTitleFromRecordUsing(fn (AttendanceRecord $record): string => "Attendance list for class {$record->class} taken on {$record->date->format('Y-m-d')} (click to view details)")
                    ->groupQueryUsing(fn ($query) => $query->groupBy('class', 'date'))
                    ->collapsible(),
            )
            ->collapsedGroupsByDefault()
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
            'index' => ListAttendanceRecords::route('/'),
            'create' => CreateAttendanceRecord::route('/create'),
            'edit' => EditAttendanceRecord::route('/{record}/edit'),
            'view' => ViewAttendanceRecord::route('/{record}'),
        ];
    }
}
