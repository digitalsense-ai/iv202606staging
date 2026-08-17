<?php

namespace App\Helpers;

class EnvironmentHelper
{
    public static function getEnvironment(): string
    {
        $url = strtolower(config('app.url'));

        return match ($url) {
            config('app.dv_live_url') => 'live',
            config('app.dv_staging_url') => 'staging',
            default => config('app.env'),
        };
    }
}