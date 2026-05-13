<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use App\Support\AuditLogger;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $roles = $this->record->roles()->pluck('name')->sort()->values()->all();

        if ($roles === []) {
            return;
        }

        AuditLogger::log('role_assigned', $this->record, [
            'module' => 'Users',
            'old' => ['roles' => []],
            'attributes' => ['roles' => $roles],
        ]);
    }
}
