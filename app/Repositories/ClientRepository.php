<?php

namespace App\Repositories;

use App\DTO\ClientDTO;
use App\DTO\VatRegDTO;
use App\Models\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Log;

class ClientRepository
{
    /**
     * @return ClientDTO[]
     */
    public function all(): array
    {     
        Cache::forget('clients.dto');

        return Cache::remember('clients.dto', 3600, function () {

            return Client::query()
                    ->select('id', 'client_name')                    
                    ->with([
                        'vatregmain' => function ($query) {
                            $query->select([
                                'id',
                                'client_id',      // required for relation mapping
                                'country',
                                'org_no',
                                'vat_no'
                            ]);
                        },
                    ])
                    ->orderBy('client_name')
                    ->get()
                    ->map(function ($client) {

                        $firstWord = Str::of($client->client_name)
                            ->trim()
                            ->explode(' ')
                            ->first();

                        $firstWord = Str::lower(trim($firstWord));

                        /**
                         * Ignore useless short first words
                         */
                        $ignoredWords = [
                            'a',
                            'an',
                            'the',
                            'as',
                            'a/s',
                            'aps',
                            'p/s',
                            'test',
                            'client',
                            'demo',
                        ];

                        if (
                            mb_strlen($firstWord) <= 2 ||
                            in_array($firstWord, $ignoredWords, true)
                        ) {
                            $firstWord = null;
                        }
                        
                        return new ClientDTO(
                            id: $client->id,
                            name: $client->client_name,
                            keys: array_filter([
                                $firstWord,
                                Str::lower($client->client_name),
                            ]),
                            vatRegs: $client->vatregmain
                                ->map(function ($v) {

                                    $country = Str::lower($v->country ?? '');

                                    $orgNo = match ($country) {
                                        'ch', 'gb' => $v->vat_no ?? null,
                                        default    => $v->org_no ?? null, // NO and others
                                    };

                                    $orgNo = preg_replace('/\D+/', '', $orgNo);

                                    if (!$orgNo)
                                        return null; // skip invalid record

                                    return new VatRegDTO(
                                        orgNo: $orgNo,
                                        countryCode: $country,
                                    );
                                })
                                ->filter()   // removes nulls safely
                                ->values()
                                ->all()                      
                        );
                    })
                    ->all(); // return DTOs, not arrays
        });
    }
}