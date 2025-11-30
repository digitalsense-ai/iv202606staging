<?php

namespace App\Http\Controllers\vatcheck;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use \App\Classes\CommonClass;

class VATCheckController extends Controller
{  
    public $authUser;

    public $commonClass;
   
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {                    
            $this->commonClass = new CommonClass();
            $this->authUser = $this->commonClass->getAuthUser();   
            
            return $next($request);
        });
    }   

    /* -- GET /vatcheck -- */   
    public function index(Request $request, $vat_reg_id)
    {      
      try 
      {
        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser, 'declaration');   
        /* --end PAGE CONFIG -- */   
                           
        $vatreg = $this->commonClass->getSpecificVatRegQuery($vat_reg_id); 
        $client_id = $vatreg->client_id;
        
        /* -- GET ANYEXCEL TEMPLATES -- */
        $anyexcel_templates = $this->commonClass->getAnyExcelTemplates();
        /* --/ GET ANYEXCEL TEMPLATES -- */

        /* -- GET UNMATCHED INVOICES -- */
        $unmatched_invoices = [];//$this->getCVRComplianceUser();
        /* --end GET UNMATCHED INVOICES -- */
        
        /* -- RETURN VIEW -- */
        return view('content.vatcheck.index', [
          'pageConfigs' => $pageConfigs, 
          'authUser' => $this->authUser, 
        
          'anyexcel_templates' => $anyexcel_templates, 
          'unmatched_invoices' => $unmatched_invoices, 
          'vatreg' => $vatreg, 
          'vat_reg_id' => $vat_reg_id, 
          'i' => 0, 
          'client_id' => $client_id
        ]); 
        /* --end RETURN VIEW -- */              
      } 
      catch (\Exception $e) 
      {        
        /* -- GET UNMATCHED INVOICES -- */
        $unmatched_invoices = [];
        /* --end GET UNMATCHED INVOICES -- */

        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'error-log', 
          [
            'status' => 'Error',
            'controller' => 'VAT Check Controller',
            'method' => 'index',
            'message' => $e->getMessage()
          ]
        );
        /* --end LOG -- */

        /* -- RETURN JSON -- */
        return response()->json([   
          'status' => 'Error',                  
          'unmatched_invoices' => $unmatched_invoices,
          'message' => $e->getMessage()
        ]);
        /* --end RETURN JSON -- */ 
      }  
    }
    /* --end GET /vatcheck -- */ 

    /* -- POST vatcheck -- */
    public function readVATCheck(Request $request)
    {     
      try    
      {                     
        $file = $request->file('file');
                   
        $vatcheck = $this->commonClass->readVATCheckFile($file);
                
        $this->commonClass->addLog($this->authUser, 'vatcheck-read-file');
        
        /* -- GET UNMATCHED INVOICES -- */
        $unmatched_invoices = [];
        /* --end GET UNMATCHED INVOICES -- */

        /* -- RETURN JSON -- */            
        return response()->json([   
          'status' => 'success',        
          'unmatched_invoices' => $unmatched_invoices,          
          'message' => 'uploaded'            
        ],200);
        /* --end RETURN JSON -- */       
      }
      catch (\Exception $e) 
      {
        /* -- GET UNMATCHED INVOICES -- */
        $unmatched_invoices = [];
        /* --end GET UNMATCHED INVOICES -- */

        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'error-log', 
          [
            'status' => 'Error',
            'controller' => 'VAT Check Controller',
            'method' => 'readVATCheck',
            'message' => $e->getMessage()
          ]
        );
        /* --end LOG -- */

        /* -- RETURN JSON -- */
        return response()->json([   
          'status' => 'Error',        
          'unmatched_invoices' => $unmatched_invoices,
          'message' => $e->getMessage()
        ]);
        /* --end RETURN JSON -- */ 
      }
    }
    /* --end POST vatcheck -- */
}
