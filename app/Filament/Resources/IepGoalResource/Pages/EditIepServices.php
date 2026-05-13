<?php

namespace App\Filament\Resources\IepGoalResource\Pages;

use App\Filament\Resources\IepGoalResource;
use App\Models\DeviceType;
use App\Models\ServiceType;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class EditIepServices extends EditRecord
{
    protected static string $resource = IepGoalResource::class;

    public static function getNavigationLabel(): string
    {
        return 'Edit IEP Services';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Fieldset::make()
                ->label('Related services')
                ->schema([
                    CheckboxList::make('related_services')
                        ->label('Related Services')
                        ->hiddenLabel()
                        ->options(ServiceType::pluck('name', 'code')->toArray())
                        ->columns(2)
                        ->live(),

                    TextInput::make('related_services_other')
                        ->label('Specify Other Related Service')
                        ->rule('string')
                        ->maxLength(255)
                        ->visible(fn (callable $get) => in_array('other', $get('related_services') ?? []))
                        ->required(fn (callable $get) => in_array('other', $get('related_services') ?? [])),
                ]),

            Select::make('device_type_ids')
                ->label('Assistive Devices')
                ->multiple()
                ->searchable()
                ->options(function (Get $get) {
                    $selectedServiceCodes = $get('related_services') ?? [];

                    // Fetch device types related to selected service codes
                    return DeviceType::whereHas('serviceTypes', function ($query) use ($selectedServiceCodes) {
                        $query->whereIn('code', $selectedServiceCodes);
                    })
                        ->pluck('name', 'id')
                        ->toArray();
                })
                ->afterStateHydrated(function (callable $set) {
                    $set('device_type_ids', $this->record->learner?->deviceTypes->pluck('id')->toArray() ?? []);
                })
                ->columnSpanFull(),
        ]);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['device_type_ids']);

        return $data;
    }

    protected function afterSave(): void
    {
        // Once the IEP Goal record exists, pull the form state and sync devices
        $data = $this->form->getState();

        $deviceIds = $data['device_type_ids'] ?? [];

        $learner = $this->record->learner;

        if ($learner) {
            $learner->deviceTypes()->syncWithPivotValues($deviceIds, ['requested_at' => now()]);
        }
    }

    public function getRelationManagers(): array
    {
        return [];
    }
}
