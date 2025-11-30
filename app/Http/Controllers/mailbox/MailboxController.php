<?php

namespace App\Http\Controllers\mailbox;

use App\Http\Controllers\Controller;
use App\Models\VATRegistration;
use App\Models\VATReturns;
use App\Models\Client;
use App\Models\System;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Str;

use \App\Classes\CommonClass;
use \App\Classes\ApiClass;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Worksheet;

class MailboxController extends Controller
{  
    public $authUser;

    public $commonClass;
    public $apiClass;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {                    
            $this->commonClass = new CommonClass();
            $this->authUser = $this->commonClass->getAuthUser();   

            $this->apiClass = new ApiClass();             

            return $next($request);
        });
    }

    /**
    * Redirect to index view.
    *
    */
    public function index(Request $request)
    {        
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser, 'invoice');
            
        //GET mailbox.        
        $mailboxfiles = $this->commonClass->getMailboxFilesLazy();    
        
        /* -- GET ANYEXCEL TEMPLATES -- */
        $anyexcel_templates = $this->commonClass->getAnyExcelTemplates();
        /* --/ GET ANYEXCEL TEMPLATES -- */ 

        $this->commonClass->addLog($this->authUser, 'invoice-view', 
          [
            'Loggedin User' => (isset($this->authUser->firstname) && isset($this->authUser->lastname)) ? ($this->authUser->firstname . ' ' . $this->authUser->lastname) : $this->authUser->name,            
          ]
        );

        return view('content.mailbox.index', 
          [
            'pageConfigs' => $pageConfigs, 
            'authUser' => $this->authUser, 
            
            'mailboxfiles' => $mailboxfiles, 
            'anyexcel_templates' => $anyexcel_templates           
          ]
        );      
    }

    /* -- POST /assign -- */
    public function assign(Request $request)
    {        
        try
        {                 
            /* -- GET MAILBOX FILE -- */
            $mailbox_file_id = $request->mailbox_file_id;

            if($mailbox_file_id)   
            {
                $mailboxfile = $this->commonClass->getMailboxFilesLazy($mailbox_file_id);

                if($mailboxfile)   
                {
                    $system = $this->commonClass->getSystemInfoLazy(); 
                    $systemapi = $system->systemapi->first();

                    $downloadurl = $this->apiClass->loadFromOneDriveLazy($mailboxfile, $systemapi);

                    $original_file = $downloadurl['download_url'];
                    $original_file_name = $downloadurl['name'];
                    $original_file_extension = $downloadurl['file_extension'];

                    $contents = (strpos($original_file, "https://") !== false) ? file_get_contents($original_file) : $original_file;
                                        
                    $vat_reg_id =  $request->vat_reg_id;
                    $template_id =  $request->template_id;

                    $vatreg = $this->commonClass->getVatRegLazy($vat_reg_id);
                
                    /* -- UPDATE TEMPLATE ID IN VAT REG. AND MAIN -- */
                    $vatregmain = $vatreg->vatregmain;
                    $vatregmain->anyexcel_template_id = ($template_id == 0) ? NULL : $template_id;
                    $vatregmain->save();

                    $vatreg->anyexcel_template_id = ($template_id == 0) ? NULL : $template_id;
                    $vatreg->save();
                    /* --end UPDATE TEMPLATE ID IN VAT REG. AND MAIN -- */
                 
                    if(isset($vatreg))  
                    {
                        $vatregmain_status = $vatreg->vatregmain->status;

                        if($vatregmain_status)   
                        { 
                            $file_details = [
                                'file' => $contents,                                
                                'file_type' => 'vatreturn',
                                'file_type_title' => 'Excel/XML',
                                'file_extension' => $original_file_extension,
                                'o_file_name' => $mailboxfile->o_file_name,
                                'original_file' => 1,
                                'mailbox' => 1
                            ];     
                           
                            $uploadedfile = $this->apiClass->uploadFileToOneDriveLazy($file_details, $vatreg, $this->authUser, $systemapi);       
                                                       
                            /* -- UPDATE MAILBOX FILE STATUS -- */
                            $mailboxfile->status = 1;
                            $mailboxfile->save();
                            /* --end UPDATE MAILBOX FILE STATUS -- */
                        }
                    }
                }
            }        
            /* -- end MAILBOX FILE -- */

            $mailboxfiles = $this->commonClass->getMailboxFilesLazy();    

            /* -- RETURN JSON -- */
            return response()->json(
              [
                'status' => 200,             
                'mailboxfiles' => $mailboxfiles
              ]
            );
            /* --end RETURN JSON -- */
        }      
        catch (\Exception $e) 
        { 
            /* -- LOG -- */
            $this->commonClass->addLog($this->authUser, 'error-log', 
              [
                'status' => 'Error',
                'controller' => 'Mailbox Controller',
                'method' => 'assign',
                'message' => $e->getMessage()
              ]
            );
            /* --end LOG -- */

            /* -- RETURN JSON -- */
            return response()->json([   
              'status' => 'Error',                 
              'message' => $e->getMessage()
            ]);
            /* --end RETURN JSON -- */ 
        }      
    }
    /* --end POST /assign -- */

    /* -- DELETE /dismiss -- */
    public function dismiss(Request $request)
    {        
        try
        {                
            /* -- GET MAILBOX FILE -- */
            $mailbox_file_id = $request->mailbox_file_id;

            if($mailbox_file_id)   
            {
                $mailboxfile = $this->commonClass->getMailboxFilesLazy($mailbox_file_id);

                if($mailboxfile)   
                {                                                                            
                    /* -- UPDATE MAILBOX FILE STATUS -- */
                    $mailboxfile->status = 0;
                    $mailboxfile->save();
                    /* --end UPDATE MAILBOX FILE STATUS -- */                    
                }
            }        
            /* -- end MAILBOX FILE -- */

            $mailboxfiles = $this->commonClass->getMailboxFilesLazy();    

            /* -- RETURN JSON -- */
            return response()->json(
              [
                'status' => 200,             
                'mailboxfiles' => $mailboxfiles
              ]
            );
            /* --end RETURN JSON -- */
        }      
        catch (\Exception $e) 
        { 
            /* -- LOG -- */
            $this->commonClass->addLog($this->authUser, 'error-log', 
              [
                'status' => 'Error',
                'controller' => 'Mailbox Controller',
                'method' => 'dismiss',
                'message' => $e->getMessage()
              ]
            );
            /* --end LOG -- */

            /* -- RETURN JSON -- */
            return response()->json([   
              'status' => 'Error',                 
              'message' => $e->getMessage()
            ]);
            /* --end RETURN JSON -- */ 
        }      
    }
    /* --end POST /assign -- */
}
