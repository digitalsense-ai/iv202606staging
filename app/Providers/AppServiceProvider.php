<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Encryption\Encrypter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() : void
    {
        $this->defineDigitaVatModelEncrypter();
    }

    private function defineDigitaVatModelEncrypter(): void
    {
        // Our custom encryption key:
        $key = 'sdR58sKO6305nzzDQLWEMVplqwuys5Ej';

        $encrypter = new Encrypter(
            key: $key,
            cipher: config('app.cipher'),
        );

        Model::encryptUsing($encrypter);
    }    
}
