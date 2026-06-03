<?php

namespace App\DTO;

class VatRegDTO
{
    public function __construct(
        public string $orgNo,
        public ?string $countryCode = null,
        //public bool $isPrimary = false,
    ) {}
}