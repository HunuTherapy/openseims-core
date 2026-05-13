<?php

namespace App\Filament\Resources\SupervisionResource\Pages;

use App\Filament\Resources\SupervisionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSupervisions extends ListRecords
{
    protected static string $resource = SupervisionResource::class;

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasPermissionTo('view all reports'), 403);

        parent::mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
