<?php

namespace App\Filament\Resources\SupervisionResource\Pages;

use App\Filament\Resources\SupervisionResource;
use App\Models\SupervisionReport;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MySupervisionReports extends ListRecords
{
    protected static string $resource = SupervisionResource::class;

    protected ?string $heading = 'My reports';

    protected ?string $subheading = 'Reports addressed to me.';

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasPermissionTo('view assigned reports'), 403);

        parent::mount();
    }

    protected function getTableQuery(): Builder
    {
        $user = auth()->user();

        if (! $user) {
            return SupervisionReport::query()->whereRaw('1 = 0');
        }

        return SupervisionReport::query()
            ->where('recipient_id', $user->id);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('school.name')->label('School')->sortable()->searchable(),
                TextColumn::make('supervisor.name')->label('Supervisor Name')->sortable(),
                TextColumn::make('visit_date')->label('Visit Date')->date()->sortable(),
            ])
            ->filters([
                SelectFilter::make('school_id')->relationship('school', 'name'),
                TernaryFilter::make('resolved'),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('visit_date', 'desc');
    }
}
