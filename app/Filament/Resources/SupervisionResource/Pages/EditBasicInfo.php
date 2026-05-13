<?php

namespace App\Filament\Resources\SupervisionResource\Pages;

use App\Filament\Resources\SupervisionResource;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class EditBasicInfo extends EditRecord
{
    protected static string $resource = SupervisionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('School details')
                ->schema([
                    Select::make('school_id')
                        ->relationship('school', 'id')
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                        ->label('Select School')
                        ->preload()
                        ->required()
                        ->live()
                        ->searchable()
                        ->native(false)
                        ->columnSpanFull(),

                    Placeholder::make('school_level')
                        ->label('School Level')
                        ->content(function (Get $get, $record) {
                            return $record->school?->school_level;
                        }),

                    Placeholder::make('school_type')
                        ->label('School Type')
                        ->content(function (Get $get, $record) {
                            return $record->school?->school_type->getLabel();
                        }),

                    DatePicker::make('visit_date')->required()->maxDate(now())->native(false),
                ])
                ->columns(3),

            Section::make('Supervisor details')
                ->schema([
                    Select::make('supervisor_id')
                        ->relationship('supervisor', 'name', fn (Builder $query) => $query->whereDoesntHave('roles', fn (Builder $query) => $query->where('name', 'national_admin')))
                        ->required()
                        ->label('Supervisor Name'),
                    Select::make('supervisor_role')
                        ->options(SupervisionResource::getSupervisorRoleOptions())
                        ->required(),
                ])
                ->columns(2),

            Section::make('Recipient details')
                ->schema([
                    Select::make('recipient_id')
                        ->relationship('recipient', 'name')
                        ->required(),
                ])
                ->columns(2),
        ]);
    }
}
