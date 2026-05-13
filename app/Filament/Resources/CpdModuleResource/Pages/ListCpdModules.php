<?php

namespace App\Filament\Resources\CpdModuleResource\Pages;

use App\Filament\Resources\CpdModuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCpdModules extends ListRecords
{
    protected static string $resource = CpdModuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
