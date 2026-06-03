<?php

namespace App\DTO;

class ClientDTO
{
    /**
     * @param VatRegDTO[] $vatRegs
     * @param string[] $keys
     */
    public function __construct(
        public int $id,
        public string $name,
        public array $keys = [],
        public array $vatRegs = [],
    ) {}

    public function hasKey(string $input): bool
    {
        foreach ($this->keys as $key) {
            if (str_contains($input, $key)) {
                return true;
            }
        }

        return false;
    }
}