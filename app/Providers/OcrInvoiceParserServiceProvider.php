<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Parsers\ClientInvoiceParser;
use App\Parsers\BergToysInvoiceParser;
use App\Parsers\KiteInvoiceParser;
use App\Parsers\RexholmInvoiceParser;
use App\Parsers\SecondFemaleInvoiceParser;
use App\Parsers\DanFormInvoiceParser;
use App\Parsers\DefaultInvoiceParser;

class OcrInvoiceParserServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ClientInvoiceParser::class, function () {
            return new ClientInvoiceParser([
                new KiteInvoiceParser(),
                new BergToysInvoiceParser(),
                new RexholmInvoiceParser(),
                new SecondFemaleInvoiceParser(),
                new DanFormInvoiceParser(),
                new DefaultInvoiceParser(),
            ]);
        });
    }
}