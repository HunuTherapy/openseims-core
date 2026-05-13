<?php

namespace App\Filament\Resources\AttendanceRecordResource\Pages;

use App\Enums\LearnerClass;
use App\Filament\Imports\AttendanceRecordImporter;
use App\Filament\Resources\AttendanceRecordResource;
use App\Models\AttendanceRecord;
use App\Models\Learner;
use App\Models\Teacher;
use Filament\Actions\ImportAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;

class CreateAttendanceRecord extends CreateRecord
{
    protected static string $resource = AttendanceRecordResource::class;

    protected function getRedirectUrl(): string
    {
        return AttendanceRecordResource::getUrl('index');
    }

    // if button should appear above form, use getHeaderActions() instead and remove default actions contained herein
    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCreateAnotherFormAction(),
            $this->getCancelFormAction(),
            ImportAction::make('uploadAttendance')
                ->label('Upload CSV')
                ->visible(fn (): bool => AttendanceRecordResource::canCreate())
                ->importer(AttendanceRecordImporter::class)
                ->color('primary')
                ->maxRows(5000)
                ->fileRules([
                    File::types(['csv']),
                ]),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Attendance Details')
                ->schema([
                    Grid::make(2)->schema([
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
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('attendance_entries', [
                                Str::uuid()->toString() => [
                                    'learner_id' => null,
                                    'present' => true,
                                    'reason' => null,
                                ],
                            ])),

                        DatePicker::make('date')
                            ->native(false)
                            ->required(),
                    ]),
                ]),

            Section::make('Learners')
                ->schema([
                    Repeater::make('attendance_entries')
                        ->label('Learners')
                        ->schema([
                            Select::make('learner_id')
                                ->label('Learner')
                                ->options(function (Get $get): array {
                                    $class = $get('../../class');

                                    if (blank($class)) {
                                        return [];
                                    }

                                    $entries = $get('../../attendance_entries') ?? [];
                                    $selected = collect($entries)
                                        ->pluck('learner_id')
                                        ->filter()
                                        ->all();

                                    $current = $get('learner_id');
                                    if ($current) {
                                        $selected = array_values(array_diff($selected, [$current]));
                                    }

                                    return Learner::query()
                                        ->where('class', $class)
                                        ->when($selected, fn ($query) => $query->whereNotIn('id', $selected))
                                        ->orderBy('last_name')
                                        ->orderBy('first_name')
                                        ->get()
                                        ->mapWithKeys(fn (Learner $learner) => [$learner->id => $learner->getVisibleName()])
                                        ->all();
                                })
                                ->searchable()
                                ->preload()
                                ->required()
                                ->native(false),

                            Toggle::make('present')
                                ->label('Present')
                                ->default(true)
                                ->live(),

                            Textarea::make('reason')
                                ->label('Reason for Absence')
                                ->maxLength(255)
                                ->nullable()
                                ->required(fn (Get $get) => $get('present') === false)
                                ->dehydratedWhenHidden()
                                ->visible(fn (Get $get) => $get('present') === false),
                        ])
                        ->defaultItems(1)
                        ->minItems(1)
                        ->addable()
                        ->deletable()
                        ->reorderable(false)
                        ->columns(3),
                ]),
        ]);
    }

    protected function handleRecordCreation(array $data): Model
    {
        $entries = $data['attendance_entries'] ?? [];
        unset($data['attendance_entries']);

        $createdRecords = [];

        foreach ($entries as $entry) {
            $isPresent = (bool) ($entry['present'] ?? true);

            $createdRecords[] = AttendanceRecord::query()->create([
                'teacher_id' => $data['teacher_id'],
                'class' => $data['class'],
                'learner_id' => $entry['learner_id'],
                'date' => $data['date'],
                'present' => $isPresent,
                'reason' => $isPresent ? null : ($entry['reason'] ?? null),
            ]);
        }

        return $createdRecords[0];
    }
}
