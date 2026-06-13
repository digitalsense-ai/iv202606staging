<?php

namespace App\Support;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class OcrDatabase
{
    public const CONNECTION = 'ocr';

    public static function connection(): ConnectionInterface
    {
        return DB::connection(config('database.ocr_connection', self::CONNECTION));
    }

    public static function table(string $table): Builder
    {
        return self::connection()->table($table);
    }
}
