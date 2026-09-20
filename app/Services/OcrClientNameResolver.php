<?php

namespace App\Services;

use App\Models\VATRegistrationMain;
use Illuminate\Support\Collection;

class OcrClientNameResolver
{
    private ?Collection $identitiesByClientNumber = null;

    public function resolve(?string $clientNumber, ?string $snapshotName = null): ?string
    {
        $normalized = $this->normalizeClientNumber($clientNumber);

        if ($normalized === null) {
            return $snapshotName;
        }

        return $this->resolveIdentity($normalized, $snapshotName)['client_name'];
    }

    public function resolveIdentity(
        ?string $clientNumber,
        ?string $snapshotName = null
    ): array {
        $normalized = $this->normalizeClientNumber($clientNumber);
        $identity = $normalized === null
            ? null
            : $this->identitiesByClientNumber()->get($normalized);

        $clientName = $identity['client_name'] ?? $snapshotName;
        $countryCode = $identity['country_code'] ?? null;

        return [
            'client_no' => $normalized,
            'client_name' => $clientName,
            'country_code' => $countryCode,
            //'client_label' => $this->formatLabel($clientName, $countryCode, $normalized),
            'client_label' => $this->formatLabel($clientName, $countryCode),
        ];
    }

    // public function formatLabel(
    //     ?string $clientName,
    //     ?string $countryCode,
    //     ?string $clientNumber
    // ): ?string {
    public function formatLabel(
        ?string $clientName,
        ?string $countryCode        
    ): ?string {
        if (!$clientName) {
            return null;
        }

        $parts = [$clientName];

        if ($countryCode) {
            $parts[] = strtoupper($countryCode);
        }

        // // Country alone is not unique (for example, two Norwegian VAT
        // // registrations can have the same client name), so retain the stable
        // // registration number in the visible label as the final discriminator.
        // if ($clientNumber) {
        //     $parts[] = $clientNumber;
        // }

        return implode(' - ', $parts);
    }

    private function identitiesByClientNumber(): Collection
    {
        if ($this->identitiesByClientNumber !== null) {
            return $this->identitiesByClientNumber;
        }

        $identities = collect();

        VATRegistrationMain::query()
            ->select(['id', 'client_id', 'org_no', 'vat_no', 'country'])
            ->with('client:id,client_name')
            ->get()
            ->each(function (VATRegistrationMain $registration) use ($identities): void {
                $clientName = $registration->client?->client_name;

                if (!$clientName) {
                    return;
                }

                foreach ([$registration->org_no, $registration->vat_no] as $clientNumber) {
                    $normalized = $this->normalizeClientNumber($clientNumber);

                    if ($normalized !== null) {
                        $identities->put($normalized, [
                            'client_name' => $clientName,
                            'country_code' => $registration->country
                                ? strtoupper($registration->country)
                                : null,
                        ]);
                    }
                }
            });

        return $this->identitiesByClientNumber = $identities;
    }

    public function normalizeClientNumber(?string $clientNumber): ?string
    {
        if ($clientNumber === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $clientNumber);
        $normalized = $digits !== ''
            ? $digits
            : strtoupper(preg_replace('/[^A-Z0-9]/i', '', $clientNumber));

        return $normalized === '' ? null : $normalized;
    }
}