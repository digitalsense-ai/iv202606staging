<?php

namespace App\Traits;

use Illuminate\Encryption\Encrypter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

trait DecryptTrait
{
    public function decryptValue($value)
    {
        // Our custom encryption key:
        $key = 'sdR58sKO6305nzzDQLWEMVplqwuys5Ej';

        $encrypter = new Encrypter(
            key: $key,
            cipher: config('app.cipher'),
        );

        return $encrypter->decryptString($value); 
    }    
}