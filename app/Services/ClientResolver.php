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
                    $resolve_org = $this->resolveOrg($client, $orgNo, null);
                    return [
                        'name'   => $client->name,
                        'org_no' => ($resolve_org) ? $resolve_org['orgNo'] : null,
                        'country_code' => ($resolve_org) ? $resolve_org['countryCode'] : null,
                    ];
                }
            }

            // IMPORTANT: stop here if orgNo provided but not found
            // return [
            //     'name'   => null,
            //     'org_no' => $orgNo,
            // ];
            return [
                'name'   => $name,
                'org_no' => null,
                'country_code' => ($countryCode) ?? null,
                'og_org_no' => $orgNo,
            ];
        }

        /**
         * 2. NAME MATCH ONLY when orgNo is missing
         */
        if ($nameLower) {
            foreach ($clients as $client) {
                if ($client->hasKey($nameLower)) { 
                    $resolve_org = $this->resolveOrg($client, null, $countryCode);                   
                    return [
                        'name'   => $client->name,                       
                        'org_no' => ($resolve_org) ? $resolve_org['orgNo'] : null,
                        'country_code' => ($resolve_org) ? $resolve_org['countryCode'] : null,
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
            'country_code' => ($countryCode) ?? null
        ];
    }

    private function resolveOrg(ClientDTO $client, ?string $orgNo, ?string $countryCode): ?array
    {
        foreach ($client->vatRegs as $vat) {
            if ($orgNo && $vat->orgNo === $orgNo) {
                return [
                    'orgNo' => $vat->orgNo,
                    'countryCode' => $vat->countryCode
                ];
            }
        }

        if ($countryCode) {
            foreach ($client->vatRegs as $vat) {
                if ($vat->countryCode === $countryCode) {
                    return [
                        'orgNo' => $vat->orgNo,
                        'countryCode' => $vat->countryCode
                    ];
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