<?php

namespace App\Support;

class GeographyData
{
    /**
     * @return array<string, array<int, string>>
     */
    public static function regions(): array
    {
        return collect(range(1, 16))
            ->mapWithKeys(fn (int $regionNumber): array => [
                sprintf('Region %02d', $regionNumber) => collect(range(1, 12))
                    ->map(fn (int $districtNumber): string => sprintf('District %02d-%02d', $regionNumber, $districtNumber))
                    ->all(),
            ])
            ->all();
    }

    /**
     * @return array<int, array{name: string, districts: array<int, string>}>
     */
    public static function seedPayload(): array
    {
        return collect(self::regions())
            ->map(fn (array $districts, string $region): array => [
                'name' => $region,
                'districts' => $districts,
            ])
            ->values()
            ->all();
    }
}
