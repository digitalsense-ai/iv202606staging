<?php

namespace App\Http\Controllers\previewreport;

use App\Http\Controllers\Controller;
use App\Models\VATRegistration;

use \App\Classes\CommonClass;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use PDF;

class PreviewReportController extends Controller
{        
    public $commonClass;

    public function __construct()
    {        
        $this->middleware(function ($request, $next) {           
            $this->commonClass = new CommonClass();
            
            return $next($request);
        });
    }
    
    /*URL preview-report/$vat_reg_id */
    public function index(Request $request, $vat_reg_id)
    {
      try {    
        $vatreg = $this->commonClass->getSpecificVatRegQuery($vat_reg_id, true); 

        return view('content.previewreport.index', [          
          'vat_reg_id' => $vat_reg_id, 
          'vatreg' => $vatreg,  
          'approved_by_firstname' => isset($approved_by) ? $approved_by->approved_by_firstname : '',
          'approved_by_lastname' => isset($approved_by) ? $approved_by->approved_by_lastname : ''             
        ]);            
      }
      catch (Exception $e) {
        return  $e->getMessage();
      }
    }

    /* -- POST /preview-report/{vat_reg_id}/export -- */
    public function exportPdfPreviewReport($vat_reg_id)
    {
      $font_path= storage_path('fonts\Rubik-Regular.ttf');

        $logo_white_path= public_path() . '/assets/img/logo/intravat-logo-white.png';
        $logo_white_type=pathinfo($logo_white_path,PATHINFO_EXTENSION);
        $logo_white_data=file_get_contents($logo_white_path);
        $logo_white='data:image/'.$logo_white_type. ';base64,'. base64_encode($logo_white_data);

        $logo_path= public_path() . '/assets/img/logo/intravat-logo.png';
        $logo_type=pathinfo($logo_path,PATHINFO_EXTENSION);
        $logo_data=file_get_contents($logo_path);
        $logo='data:image/'.$logo_type. ';base64,'. base64_encode($logo_data);
       
        $vatreg = $this->commonClass->getSpecificVatRegQuery($vat_reg_id); 
        $tab_name = "previewreport";               
       
        $client = $vatreg->client;
       
        $vatreturns = $vatreg->vatreturns;
        
        $pivs_files = ($vatreg->pivs) ? $vatreg->pivs : [];  
        $c79_documents = ($vatreg->c79) ? $vatreg->c79: []; 

        $importvatfiles = $vatreg->importvatfiles; 
        $importreconciliationcominvoices = $vatreg->importreconciliationcominvoices; 

        $row_per_page = 22;

        $declarationContent = view('_partials._content._previewreport.declaration-pdf', compact('logo', 'logo_white', 'vat_reg_id', 'vatreg', 'client', 'tab_name', 'importvatfiles'))->render();

        $comInvoiceContent = view('_partials._content._previewreport.cominvoice-pdf', compact('logo', 'logo_white', 'vat_reg_id', 'vatreg', 'client', 'tab_name', 'importvatfiles', 'importreconciliationcominvoices', 'row_per_page'))->render();

        $overviewContent = view('_partials._content._vatreturn.vatreturn-overview-pdf', compact('logo', 'logo_white', 'vat_reg_id', 'vatreg', 'client', 'tab_name', 'vatreturns', 'pivs_files', 'c79_documents'))->render();       

        $data = [          
            'logo' => $logo,
            'logo_white' => $logo_white,
            'vatreg' => $vatreg,
            'client' => $client,
            'tab_name' => $tab_name,
            'declarationContent' => $declarationContent,
            'comInvoiceContent' => $comInvoiceContent,
            'overviewContent' => $overviewContent,
            'font_path' => $font_path
        ];  
        $pdf = PDF::loadView('content.previewreport.pdf', $data);    
                
        $pdf->setPaper('A4', 'portrait');  // Ensure you're setting the paper size correctly

        // Output the PDF as a response
        return $pdf->stream('previewreport.pdf');       
    }
}
