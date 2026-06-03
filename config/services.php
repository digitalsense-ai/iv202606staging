<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        'options' => [
            'ConfigurationSetName' => env('AWS_SES_CONFIG_SET', 'email-notifications'),
        ],
    ],

    'azure_form' => [
        'endpoint' => env('AZURE_FORM_ENDPOINT'),
        'key' => env('AZURE_FORM_KEY'),
    ],

    'azure_cu' => [
        'endpoint' => env('AZURE_CU_ENDPOINT'),
        'key' => env('AZURE_CU_KEY'),
        'version' => env('AZURE_CU_API_VERSION'),
    ],

    'azure_di' => [
        'endpoint' => env('AZURE_DI_ENDPOINT'),
        'key' => env('AZURE_DI_KEY'),
        'version' => env('AZURE_DI_API_VERSION'),
    ],

    'ms' => [
        'client_id' => env('MS_CLIENT_ID'),
        'tenant_id' => env('MS_TENANT_ID'),
        'redirect_uri' => env('MS_REDIRECT_URI'),
        'client_secret' => env('MS_CLIENT_SECRET'),
        'mailbox' => env('MS_MAILBOX'),
    ],
];
