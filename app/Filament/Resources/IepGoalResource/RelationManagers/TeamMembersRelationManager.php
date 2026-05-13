<?php

namespace App\Filament\Resources\IepGoalResource\RelationManagers;

use App\Models\IepTeamMember;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TeamMembersRelationManager extends RelationManager
{
    protected static string $relationship = 'iepTeamMembers';

    protected static ?string $title = 'IEP Team Members';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Checkbox::make('is_guest')
                    ->label('This member is not in the dropdown')
                    ->live()
                    ->columnSpanFull(),

                Select::make('user_id')
                    ->relationship('user', 'id', fn (Builder $query) => $query->whereDoesntHave('roles', fn (Builder $query) => $query->where('name', 'national_admin')))
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                    ->label('Select Team Member')
                    ->preload()
                    ->visible(fn (Get $get) => ! $get('is_guest'))
                    ->required(fn (Get $get) => ! $get('is_guest'))
                    ->native(false),

                TextInput::make('guest_name')
                    ->label('Enter name manually')
                    ->placeholder('Type full name…')
                    ->rule('string')
                    ->maxLength(255)
                    ->visible(fn (Get $get) => $get('is_guest'))
                    ->required(fn (Get $get) => $get('is_guest')),

                Select::make('role')
                    ->label('Role')
                    ->options([
                        'teacher' => 'Teacher',
                        'school_coordinator' => 'School Coordinator',
                        'therapist' => 'Therapist',
                        'counselor' => 'Counselor',
                        'parent' => 'Parent/Guardian',
                        'administrator' => 'Administrator',
                        'sped_coordinator' => 'SPED Co-ordinator',
                        'other' => 'Other',
                    ])
                    ->native(false)
                    ->required()
                    ->live(),

                TextInput::make('custom_role')
                    ->label('Specify Role')
                    ->placeholder('Enter role')
                    ->rule('string')
                    ->maxLength(255)
                    ->visible(fn (Get $get) => $get('role') === 'other')
                    ->required(fn (Get $get) => $get('role') === 'other'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('role')
            ->columns([
                TextColumn::make('user.name')
                    ->state(fn ($record) => $record->is_guest
                        ? $record->guest_name
                        : $record->user->name
                    )
                    ->label('Name'),
                TextColumn::make('role')
                    ->formatStateUsing(fn ($state, IepTeamMember $record) => match ($state) {
                        'teacher' => 'Teacher',
                        'school_coordinator' => 'School Coordinator',
                        'therapist' => 'Therapist',
                        'counselor' => 'Counselor',
                        'parent' => 'Parent/Guardian',
                        'administrator' => 'Administrator',
                        'sped_coordinator' => 'SPED Co-ordinator',
                        'other' => $record->custom_role,
                        'default' => 'No role defined',
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add new IEP Team Member'),
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
}
