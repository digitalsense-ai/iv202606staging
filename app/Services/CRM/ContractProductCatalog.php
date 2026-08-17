<?php

namespace App\Services\CRM;

use Illuminate\Support\Collection;

class ContractProductCatalog
{
    private const COUNTRIES = [
        'DK' => 'Denmark',
        'UK' => 'United Kingdom',
    ];

    private const PRODUCTS = [
        'DK' => [
            ['service' => 'Registration', 'description' => 'Includes implementation process, MitID, NemKonto and Digital Post', 'standard_price' => 15000.00, 'interval' => 'One-off'],
            ['service' => 'Transfer with workshop', 'description' => 'Transfer from another service provider incl. workshop', 'standard_price' => 6000.00, 'interval' => 'One-off'],
            ['service' => 'Transfer without workshop', 'description' => 'Transfer from another service provider without workshop', 'standard_price' => 3000.00, 'interval' => 'One-off'],
            ['service' => 'Hosting Basic', 'description' => 'Includes VAT return', 'standard_price' => 2500.00, 'interval' => 'Monthly'],
            ['service' => 'EU Sales List per item', 'description' => 'EC Sales List filing', 'standard_price' => 1200.00, 'interval' => 'Add-on'],
            ['service' => 'Intrastat per filing', 'description' => "Max. 10 item lines/HS codes - mandatory if the client's trade exceeds the thresholds set by EU Member States.", 'standard_price' => 750.00, 'interval' => 'Add-on'],
            ['service' => 'Intrastat additional item lines', 'description' => 'Per item beyond the first 10 item lines/HS codes', 'standard_price' => 20.00, 'interval' => 'Add-on'],
            ['service' => 'Customs credit', 'description' => 'Add-on service', 'standard_price' => 3000.00, 'interval' => 'Add-on'],
            ['service' => 'VAT reconciliation at annual closing', 'description' => 'Year-end / additional reconciliation', 'standard_price' => 2495.00, 'interval' => 'Add-on'],
            ['service' => 'Transfer/closure of registration', 'description' => 'Transfer to new service provider / closure of registration', 'standard_price' => 3000.00, 'interval' => 'One-off'],
        ],
        'UK' => [
            ['service' => 'Registration', 'description' => 'Registration (VAT number) incl. implementation process', 'standard_price' => 15000.00, 'interval' => 'One-off'],
            ['service' => 'EORI number', 'description' => 'Establishment of EORI number', 'standard_price' => 1500.00, 'interval' => 'One-off'],
            ['service' => 'Transfer with workshop', 'description' => 'Transfer from another service provider incl. workshop', 'standard_price' => 6000.00, 'interval' => 'One-off'],
            ['service' => 'Hosting Basic', 'description' => 'Includes VAT return', 'standard_price' => 2500.00, 'interval' => 'Monthly'],
            ['service' => 'Premium', 'description' => 'Extended review incl. import reconciliation', 'standard_price' => 3800.00, 'interval' => 'Monthly'],
            ['service' => 'Customs credit', 'description' => 'Add-on service', 'standard_price' => 3000.00, 'interval' => 'Add-on'],
            ['service' => 'Cash Account', 'description' => 'Addition of EORI numbers and payment guidance', 'standard_price' => 995.00, 'interval' => 'One-off'],
            ['service' => 'Cash account statement', 'description' => 'Monthly extract from HMRC', 'standard_price' => 150.00, 'interval' => 'Monthly'],
            ['service' => 'VAT reconciliation at annual closing', 'description' => 'Year-end / additional reconciliation', 'standard_price' => 2495.00, 'interval' => 'Add-on'],
            ['service' => 'Transfer/closure of registration', 'description' => 'Transfer to new service provider / closure of registration', 'standard_price' => 3000.00, 'interval' => 'One-off'],
        ],
    ];

    public function forCountry(?string $countryCode): Collection
    {
        $countryCode = $this->normalizeCountryCode($countryCode);

        return collect(self::PRODUCTS[$countryCode] ?? [])
            ->map(fn (array $product) => (object) [
                'name' => $product['service'],
                'service' => $product['service'],
                'description' => $product['description'],
                'price' => $product['standard_price'],
                'standard_price' => $product['standard_price'],
                'frequency' => $product['interval'],
                'interval' => $product['interval'],
                'country_code' => $countryCode,
                'country_name' => self::COUNTRIES[$countryCode] ?? $countryCode,
            ]);
    }

    public function find(?string $countryCode, string $service): ?object
    {
        return $this->forCountry($countryCode)->firstWhere('service', $service);
    }

    public function countryName(?string $countryCode): ?string
    {
        $countryCode = $this->normalizeCountryCode($countryCode);

        return self::COUNTRIES[$countryCode] ?? null;
    }

    public function normalizeCountryCode(?string $countryCode): string
    {
        $countryCode = strtoupper(trim((string) $countryCode));

        if (array_key_exists($countryCode, self::PRODUCTS)) {
            return $countryCode;
        }

        $countryCode = match ($countryCode) {
            'DENMARK' => 'DK',
            'UNITED KINGDOM', 'GREAT BRITAIN', 'GB' => 'UK',
            default => $countryCode,
        };

        return array_key_exists($countryCode, self::PRODUCTS) ? $countryCode : 'DK';
    }
}
