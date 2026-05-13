<?php

namespace App\Filament\Resources\SupervisionResource\Pages;

use App\Filament\Resources\SupervisionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSupervision extends CreateRecord
{
    protected static string $resource = SupervisionResource::class;

    protected ?string $heading = 'Create Supervision Report';
}
