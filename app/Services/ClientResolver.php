<?php

namespace App\Services;

use App\DTO\ClientDTO;
use Illuminate\Support\Facades\Log;
class ClientResolver
{
    /**
     * @param ClientDTO[] $clients
     */
    public function resolve(
        array $clients,
        ?string $name,
        ?string $orgNo,
        ?string $countryCode
    ): array {

        $nameLower = $name ? strtolower(trim($name)) : null;

        /**
         * 1. STRONGEST MATCH: orgNo (ONLY if exists)
         */
        if ($orgNo) {
            foreach ($clients as $client) {
                if ($this->clientHasOrg($client, $orgNo)) {
                    return [
                        'name'   => $client->name,
                        'org_no' => $orgNo,
                    ];
                }
            }

            // IMPORTANT: stop here if orgNo provided but not found
            return [
                'name'   => null,
                'org_no' => $orgNo,
            ];
        }

        /**
         * 2. NAME MATCH ONLY when orgNo is missing
         */
        if ($nameLower) {
            foreach ($clients as $client) {
                if ($client->hasKey($nameLower)) {                    
                    return [
                        'name'   => $client->name,
                        'org_no' => $this->resolveOrg($client, null, $countryCode),
                    ];
                }
            }
        }

        /**
         * 3. FINAL FALLBACK
         */
        return [
            'name'   => $name,
            'org_no' => null,
        ];
    }

    private function resolveOrg(ClientDTO $client, ?string $orgNo, ?string $countryCode): ?string
    {
        foreach ($client->vatRegs as $vat) {
            if ($orgNo && $vat->orgNo === $orgNo) {
                return $vat->orgNo;
            }
        }

        if ($countryCode) {
            foreach ($client->vatRegs as $vat) {
                if ($vat->countryCode === $countryCode) {
                    return $vat->orgNo;
                }
            }
        }

        //return $client->vatRegs[0]->orgNo ?? null;
        return null;
    }

    private function clientHasOrg(ClientDTO $client, string $orgNo): bool
    {
        foreach ($client->vatRegs as $vat) {
            if ($vat->orgNo === $orgNo) {
                return true;
            }
        }

        return false;
    }
}