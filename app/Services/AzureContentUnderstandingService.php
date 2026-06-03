<?php

namespace App\Services;

use GuzzleHttp\Client;

class AzureContentUnderstandingService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client(['timeout' => 60]);
    }

    public function analyze(string $filePath, string $fileName, string $analyzerId): string
    {
        $endpoint = rtrim(config('services.azure_cu.endpoint'), '/');
        $url = "{$endpoint}/contentunderstanding/analyzers/{$analyzerId}:analyze?api-version=" .
               config('services.azure_cu.version');

        $response = $this->client->post($url, [
            'headers' => [
                'Ocp-Apim-Subscription-Key' => config('services.azure_cu.key'),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'inputs' => [[
                    'name' => $fileName,
                    'contentType' => 'application/pdf',
                    'data' => base64_encode(file_get_contents($filePath)),
                ]]
            ],
        ]);

        return $response->getHeaderLine('Operation-Location');
    }

    public function poll(string $operationUrl): array
    {
        $response = $this->client->get($operationUrl, [
            'headers' => [
                'Ocp-Apim-Subscription-Key' => config('services.azure_cu.key'),
            ],
        ]);

        return json_decode($response->getBody(), true);
    }
}