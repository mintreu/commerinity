<?php

declare(strict_types=1);

namespace App\Support\Geo;

final class DistrictNameMapper
{
    /**
     * Canonical district list for West Bengal.
     *
     * @return array<int, string>
     */
    public static function westBengalDistricts(): array
    {
        return [
            'Alipurduar',
            'Bankura',
            'Birbhum',
            'Cooch Behar',
            'Dakshin Dinajpur',
            'Darjeeling',
            'Hooghly',
            'Howrah',
            'Jalpaiguri',
            'Jhargram',
            'Kalimpong',
            'Kolkata',
            'Malda',
            'Murshidabad',
            'Nadia',
            'North 24 Parganas',
            'Paschim Bardhaman',
            'Paschim Medinipur',
            'Purba Bardhaman',
            'Purba Medinipur',
            'Purulia',
            'South 24 Parganas',
            'Uttar Dinajpur',
        ];
    }

    public static function toDisplayName(string $name): string
    {
        $clean = trim($name);
        $clean = preg_replace('/\s+/', ' ', $clean) ?? $clean;

        return ucwords(strtolower($clean));
    }

    public static function canonicalize(string $stateCode, ?string $districtName): ?string
    {
        $districtName = trim((string) $districtName);
        if ($districtName === '') {
            return null;
        }

        if (strtoupper($stateCode) !== 'WB') {
            return self::toDisplayName($districtName);
        }

        $normalized = self::normalize($districtName);

        $canonicalByNormalized = [];
        foreach (self::westBengalDistricts() as $district) {
            $canonicalByNormalized[self::normalize($district)] = $district;
        }

        $aliases = [
            'COOCH BIHAR' => 'Cooch Behar',
            'NORTH DINAJPUR' => 'Uttar Dinajpur',
            'SOUTH DINAJPUR' => 'Dakshin Dinajpur',
            'BARDDHAMAN' => 'Purba Bardhaman',
            'BARDHAMAN' => 'Purba Bardhaman',
            'EAST MEDINIPUR' => 'Purba Medinipur',
            'WEST MEDINIPUR' => 'Paschim Medinipur',
            'MALDAH' => 'Malda',
        ];

        if (isset($canonicalByNormalized[$normalized])) {
            return $canonicalByNormalized[$normalized];
        }

        if (isset($aliases[$normalized])) {
            return $aliases[$normalized];
        }

        return null;
    }

    public static function isReliableWestBengalDataset(array $rawDistrictNames): bool
    {
        if (count($rawDistrictNames) < 15) {
            return false;
        }

        foreach ($rawDistrictNames as $name) {
            if (self::canonicalize('WB', (string) $name) === null) {
                return false;
            }
        }

        return true;
    }

    private static function normalize(string $name): string
    {
        $name = strtoupper(trim($name));
        $name = str_replace(['.', '-', '_'], ' ', $name);
        $name = preg_replace('/\s+/', ' ', $name) ?? $name;

        return $name;
    }
}

