<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF;

use \App\Classes\CommonClass;

class PDFController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function generatePDF()
    {
        //return $this->generatePDFWithView();

        return $this->generatePDFFromXML();        
    }

    public function generatePDFWithView()
    {
        $logo_white_path= public_path() . '/assets/img/logo/intravat-logo-white.png';
        $logo_white_type=pathinfo($logo_white_path,PATHINFO_EXTENSION);
        $logo_white_data=file_get_contents($logo_white_path);
        $logo_white='data:image/'.$logo_white_type. ';base64,'. base64_encode($logo_white_data);

        $logo_path= public_path() . '/assets/img/logo/intravat-logo.png';
        $logo_type=pathinfo($logo_path,PATHINFO_EXTENSION);
        $logo_data=file_get_contents($logo_path);
        $logo='data:image/'.$logo_type. ';base64,'. base64_encode($logo_data);
       
        $vat_reg_id = 76;

        $commonClass = new CommonClass();
        $vatreg = $commonClass->getSpecificVatRegQuery($vat_reg_id); 
        $tab_name = "previewreport";               
        
        $client = $vatreg->client;
        
        $vatreturns = $vatreg->vatreturns;
        
        $pivs_files = ($vatreg->pivs) ? $vatreg->pivs : [];  
        $c79_documents = ($vatreg->c79) ? $vatreg->c79: []; 

        $importvatfiles = $vatreg->importvatfiles; 

        $declarationContent = view('_partials._content._previewreport.declaration-pdf', compact('vat_reg_id', 'vatreg', 'client', 'tab_name', 'importvatfiles'))->render();

        $comInvoiceContent = view('_partials._content._previewreport.cominvoice-pdf', compact('logo', 'logo_white', 'vat_reg_id', 'vatreg', 'client', 'tab_name', 'importvatfiles'))->render();

        $overviewContent = view('_partials._content._vatreturn.vatreturn-overview-pdf', compact('logo', 'logo_white', 'vat_reg_id', 'vatreg', 'client', 'tab_name', 'vatreturns', 'pivs_files', 'c79_documents'))->render();

        $data = [
            'title' => 'Welcome to intravat.com',
            'logo' => $logo,
            'declarationContent' => $declarationContent,
            'comInvoiceContent' => $comInvoiceContent,
            'overviewContent' => $overviewContent,
        ];               

        $pdf = PDF::loadView('myPDF', $data);       

        return $pdf->download('testingfont.pdf');
    }

    public function generatePDFFromXML()
    {
        $downloadfile = [           
            'download_url' => 'http://localhost:8000/testxml.xml',
            'file_extension' => 'xml'
        ];

        $commonClass = new CommonClass();
        $sales_invoice_xml = $commonClass->generateSalesInvoicePdfFromXml($downloadfile, true);

        $data = [          
            'xmlContent' => $sales_invoice_xml               
        ];  
        $pdf = PDF::loadView('content.declaration.sales-invoice-pdf', $data);       

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream($sales_invoice_xml['invoice_no'] . '.pdf');   
    }

}
