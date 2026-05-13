<?php

namespace App\Filament\Admin\Resources\OfficerResource\Pages;

use App\Filament\Admin\Resources\OfficerResource;
use App\Models\District;
use App\Models\Officer;
use App\Support\OfficerProvisioning;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreateOfficer extends CreateRecord
{
    protected static string $resource = OfficerResource::class;

    protected ?string $appRole = null;

    protected ?int $regionId = null;

    protected ?int $districtId = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->appRole = $data['app_role'] ?? null;
        $this->regionId = filled($data['region_id'] ?? null) ? (int) $data['region_id'] : null;
        $this->districtId = filled($data['district_id'] ?? null) ? (int) $data['district_id'] : null;

        unset($data['app_role'], $data['region_id'], $data['district_id']);

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $district = District::query()
            ->whereKey($this->districtId)
            ->where('region_id', $this->regionId)
            ->first();

        if (! $district) {
            throw ValidationException::withMessages([
                'district_id' => 'District does not belong to the selected region.',
            ]);
        }

        $role = OfficerProvisioning::normalizeAppRole($this->appRole);

        if (! $role) {
            throw ValidationException::withMessages([
                'app_role' => 'Role is invalid.',
            ]);
        }

        /** @var Officer $officer */
        $officer = app(OfficerProvisioning::class)->createOfficerWithUser($data, $role, $district);

        return $officer;
    }

    protected function getRedirectUrl(): string
    {
        return OfficerResource::getUrl('view', ['record' => $this->record]);
    }
}
