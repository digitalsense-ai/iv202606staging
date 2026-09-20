<?php

namespace App\Services;

use App\Models\VATRegistrationMain;
use Illuminate\Support\Collection;

class OcrClientNameResolver
{
    private ?Collection $namesByClientNumber = null;

    public function resolve(?string $clientNumber, ?string $snapshotName = null): ?string
    {
        $normalized = $this->normalizeClientNumber($clientNumber);

        if ($normalized === null) {
            return $snapshotName;
        }

        return $this->namesByClientNumber()->get($normalized, $snapshotName);
    }

    private function namesByClientNumber(): Collection
    {
        if ($this->namesByClientNumber !== null) {
            return $this->namesByClientNumber;
        }

        $names = collect();

        VATRegistrationMain::query()
            ->select(['id', 'client_id', 'org_no', 'vat_no'])
            ->with('client:id,client_name')
            ->get()
            ->each(function (VATRegistrationMain $registration) use ($names): void {
                $clientName = $registration->client?->client_name;

                if (!$clientName) {
                    return;
                }

                foreach ([$registration->org_no, $registration->vat_no] as $clientNumber) {
                    $normalized = $this->normalizeClientNumber($clientNumber);

                    if ($normalized !== null) {
                        $names->put($normalized, $clientName);
                    }
                }
            });

        return $this->namesByClientNumber = $names;
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