<?php

namespace App\Services;

use GuzzleHttp\Client;

class AzureDocumentIntelligenceService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client(['timeout' => 60]);
    }

    public function analyze(string $filePath, string $fileName, string $modelId): string
    {       
        $endpoint = rtrim(config('services.azure_form.endpoint'), '/');        
        $url = "{$endpoint}/documentintelligence/documentModels/{$modelId}:analyze?api-version=" .
               config('services.azure_di.version');

        $response = $this->client->post($url, [
            'headers' => [
                'Ocp-Apim-Subscription-Key' => config('services.azure_form.key'),
                'Content-Type' => 'application/pdf',
            ],
            'body' => fopen($filePath, 'r'),            
        ]);
       
        return $response->getHeaderLine('Operation-Location');
    }

    public function poll(string $operationUrl): array
    {
        $response = $this->client->get($operationUrl, [
            'headers' => [
                'Ocp-Apim-Subscription-Key' => config('services.azure_form.key'),
            ],
        ]);
        
        return json_decode($response->getBody(), true);
    }
}