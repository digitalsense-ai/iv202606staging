<?php

namespace App\Classes;

use Einvoicing\Identifier;
use Einvoicing\Invoice;
use Einvoicing\InvoiceLine;
use Einvoicing\Party;
use Einvoicing\Presets;
use Einvoicing\Writers\UblWriter;

use Einvoicing\Exceptions\ValidationException;
use Einvoicing\Readers\UblReader;

use DateTime;
use Storage;
use Illuminate\Support\Carbon;

class InvoiceClass
{        
  public function generateInvoice()
  {       
    // Create PEPPOL invoice instance
    $inv = new Invoice(Presets\Peppol::class);
    $inv->setNumber('F-202000012')
        ->setIssueDate(new DateTime('2020-11-01'))
        ->setDueDate(new DateTime('2020-11-30'));

    // Set seller
    $seller = new Party();
    $seller->setElectronicAddress(new Identifier('9482348239847239874', '0088'))
        ->setCompanyId(new Identifier('AH88726', '0183'))
        ->setName('Seller Name Ltd.')
        ->setTradingName('Seller Name')
        ->setVatNumber('ESA00000000')
        ->setAddress(['Fake Street 123', 'Apartment Block 2B'])
        ->setCity('Springfield')
        ->setCountry('DE');
    $inv->setSeller($seller);

    // Set buyer
    $buyer = new Party();
    //$buyer->setElectronicAddress(new Identifier('ES12345', '0002'))
    $buyer->setElectronicAddress(new Identifier('ABS1234', '0002'))
        ->setName('Buyer Name Ltd.')
        ->setCountry('FR');
    $inv->setBuyer($buyer);
    // Set other invoice details
$inv->setBuyerReference('BUYER-REF-12345');
$inv->setPurchaseOrderReference('PO-45678');

    // Add a product line
    $line = new InvoiceLine();
    $line->setName('Product Name')
        ->setPrice(100)
        ->setVatRate(16)
        ->setQuantity(1);
    $inv->addLine($line);

    $line2 = new InvoiceLine();
    $line2->setName('Product Name 2')
        ->setPrice(250)
        ->setVatRate(18)
        ->setQuantity(2);
    $inv->addLine($line2);

    // Export invoice to a UBL document
    header('Content-Type: text/xml');
    $writer = new UblWriter();
   
    $result = $writer->export($inv);   
    Storage::put('public/invoices/testinvoice.xml', $result);
  }  

  public function readinvoice($file_name)
  { 
    $reader = new UblReader();
    
    $storage_path = storage_path('app/public/invoices/');    
    $document = file_get_contents($storage_path . $file_name);
    $inv = $reader->import($document);
    try {
        $result = $inv->validate();

        return $result;
    } catch (ValidationException $e) {
        dd($e);
        // Invoice is not EN 16931 complaint 
    }
  }    
}
