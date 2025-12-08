<?php

namespace App\Http\Controllers\vat;

use App\Http\Controllers\Controller;
use App\Models\VATRegistration;
use App\Models\VATRegistrationMain;
use App\Models\VATReturns;
use App\Models\VATReturnFiles;
use App\Models\Client;
use App\Models\System;
use App\Models\Receipt;
use App\Models\User;
use App\Models\Pivs;
use App\Models\Documents;
use App\Models\ImportVatFiles;
use App\Models\ImportVatComments;
use App\Models\SubmittingFields;
use App\Models\SubmittingFieldsNO;
use App\Models\SubmittingFieldsCH;
use App\Models\CashAccountStatement;
use App\Models\DutyDefermentAccount;
use App\Models\FilesEmailNote;
use App\Models\Invoices;
use App\Models\EmailNotification;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Illuminate\Mail\Markdown;

use GuzzleHttp\Client as GuzzleClient;

use \App\Classes\CommonClass;
use \App\Classes\ApiClass;
use \App\Classes\HMRCApiClass;
use \App\Classes\DynamicsApiClass;
use \App\Classes\EconomicApiClass;
use \App\Classes\UnicontaApiClass;
use \App\Classes\ShopifyApiClass;

use App\Services\TableToExcelService;

use App\File;
use App\Folder;

use PDF;
use Mail;
use App\Mail\Draft as DraftEmail;
use App\Mail\LockGB as LockGBEmail;
use App\Mail\LockNO as LockNOEmail;
use App\Mail\Pivs as PivsEmail;
use App\Mail\Documents as DocumentsEmail;
use App\Mail\C79 as C79Email;
use App\Mail\ImportVatFile as ImportVatFileEmail;
use App\Mail\CashAccountStatement as CashAccountStatementEmail;
use App\Mail\DutyDefermentAccount as DutyDefermentAccountEmail;
use App\Mail\Reopen as ReopenEmail;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

use App\Traits\DecryptTrait;

class VATRegistrationController extends Controller
{  
    use DecryptTrait;
    
    public $authUser;
    public $vatRegIds;

    public $commonClass;
    public $apiClass;
    public $hmrcApiClass;
    public $dynamicsApiClass;
    public $economicApiClass;
    public $unicontaApiClass;
    public $shopifyApiClass;
    
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {           
            $this->commonClass = new CommonClass();
            $this->authUser = $this->commonClass->getAuthUser();         

            //GET CLIENT IDs based on VAT Reg. for Team User           
            if($this->authUser->role == 'team-user')
            {
              $vatregs = VATRegistration::leftJoin('dv_user_vat_registration', function($join) {
                          $join->on('dv_vat_registration.id', '=', 'dv_user_vat_registration.vat_reg_id');                      
                        })                   
                        ->select([                           
                            DB::raw('coalesce(group_concat(distinct dv_vat_registration.id separator ","),"") AS vatreg_ids'),
                        ])
                        ->where('dv_user_vat_registration.user_id', $this->authUser->user_id)   
                        ->where('dv_vat_registration.client_id', $request->client_id)               
                        ->first();
             
              $this->vatRegIds = explode(',',$vatregs->vatreg_ids);
            }
            else
            {
              $vatregs = VATRegistration::select([                           
                                DB::raw('coalesce(group_concat(distinct dv_vat_registration.id separator ","),"") AS vatreg_ids'),
                            ])
                        ->where('dv_vat_registration.client_id', $request->client_id)   
                        ->first();

              $this->vatRegIds = explode(',',$vatregs->vatreg_ids);
            }

            $this->apiClass = new ApiClass();             
            $this->hmrcApiClass = new HMRCApiClass();    
            $this->dynamicsApiClass = new DynamicsApiClass();
            $this->economicApiClass = new EconomicApiClass();
            $this->unicontaApiClass = new UnicontaApiClass();
            $this->shopifyApiClass = new ShopifyApiClass();

            return $next($request);
        });
    }    
   
    /*URL GET vat-return/receipt/$vat_id */
    public function loadFromOneDrive(Request $request, $vat_id)
    { 
      try {       
        $client = VATRegistration::rightJoin('dv_clients', function($join) {
                    $join->on('dv_vat_registration.client_id', '=', 'dv_clients.id');
                  })
                  ->rightJoin('dv_receipts', function($join) {
                    $join->on('dv_vat_registration.id', '=', 'dv_receipts.vat_reg_id');
                  })                          
                  ->select('dv_clients.id AS client_id','dv_clients.client_name', 'dv_clients.vatno', 'dv_receipts.*')
                  ->where('dv_vat_registration.id', $vat_id)
                  ->get(); 
                
        return $client;                                    
      }
      catch (Exception $e) {
        return  $e->getMessage();
      }
    }

    /*URL POST vat-return/receipt/$vat_id */
    public function uploadReceipt(Request $request, $vat_id)
    { 
      try {              
        $vatreg = VATRegistration::rightJoin('dv_clients', function($join) {
                    $join->on('dv_vat_registration.client_id', '=', 'dv_clients.id');
                  })                      
                  ->select('dv_clients.id AS client_id','dv_clients.client_name', 'dv_clients.vatno', 'dv_vat_registration.*',
                    'dv_vat_registration.id AS vat_reg_id'
                  )
                  ->where('dv_vat_registration.status', 4)
                  ->orWhere('dv_vat_registration.status', 5)
                  ->where('dv_vat_registration.id', $vat_id)
                  ->first(); //'Ready to submit' (to upload receipt) 
               
        $system = $this->commonClass->getSystemInfoLazy(); 
        $systemapi = $system->systemapi->first();         
        
        if(isset($vatreg))       
          return $this->apiClass->uploadFileToOneDriveLazy($request, $vatreg, $this->authUser, $systemapi);     
        else                                            
          return false;
      }
      catch (Exception $e) {
        return  $e->getMessage();
      }
    }

    /*URL DELETE vat-return/receipt/$file_id */
    public function deleteFromOneDrive(Request $request, $id)
    { 
      try {         
        if($id != null) 
        {
          $client = VATRegistration::leftJoin('dv_clients', function($join) {
                        $join->on('dv_vat_registration.client_id', '=', 'dv_clients.id');                      
                      })                       
                      ->rightJoin('dv_receipts', function($join) {
                        $join->on('dv_receipts.vat_reg_id', '=', 'dv_vat_registration.id');                     
                      })      
                      ->select('dv_clients.id AS client_id','dv_clients.client_name','dv_clients.lrep_email', 'dv_clients.vatno', 'dv_vat_registration.*', 'dv_receipts.*')                    
                      ->where('dv_receipts.id', $id)                        
                      ->first();

          $vatRegHeading = Carbon::parse($client->service_start)->format('M Y') . ' ' . $client->country . ' ' . $client->general_periods;    
          $vat_reg_id = $client->vat_reg_id;
          
          $system = $this->commonClass->getSystemInfo();        
          
          $deleteResult = $this->apiClass->deleteFromOneDrive($client, $this->authUser, $system);   

          $receipt = Receipt::where('id', $id)->delete();
          
          $updateStatus = VATRegistration::where('id', $vat_reg_id)                            
                          ->update(
                            [
                                  'status' => 4, 
                                  'receipt_by' => NULL, 
                                  'receipt_at' => NULL                                  
                            ]
                          );//From 'Submitted' to 'Ready to Submit' (after team user deleted receipts)  
          
          $this->commonClass->addLog($this->authUser, 'receipt-delete', 
            [
              'Client Name' => $client->client_name,
              'VAT Reg' => $vatRegHeading
            ]
          ); 

          return $deleteResult;  
        }

        return false;                           
      }
      catch (Exception $e) {
        return  $e->getMessage();
      }
    }

    /*GeneratePDF*/
    public function GeneratePDF($vatno, $data)
    {
        $logo_path= public_path() . '/assets/img/logo/logo-gray.png';
        $logo_type=pathinfo($logo_path,PATHINFO_EXTENSION);
        $logo_data=file_get_contents($logo_path);
        $logo='data:image/'.$logo_type. ';base64,'. base64_encode($logo_data);

        $icn_purchase_path= public_path() . '/assets/img/icons/icn-purchase.png';
        $icn_purchase_type=pathinfo($icn_purchase_path,PATHINFO_EXTENSION);
        $icn_purchase_data=file_get_contents($icn_purchase_path);
        $icn_purchase='data:image/'.$icn_purchase_type. ';base64,'. base64_encode($icn_purchase_data);

        $icn_sale_path= public_path() . '/assets/img/icons/icn-sale.png';
        $icn_sale_type=pathinfo($icn_sale_path,PATHINFO_EXTENSION);
        $icn_sale_data=file_get_contents($icn_sale_path);
        $icn_sale='data:image/'.$icn_sale_type. ';base64,'. base64_encode($icn_sale_data);
        
        $pdf = PDF::loadView('pdf.vat-new', compact('logo', 'icn_purchase', 'icn_sale', 'data'))
                ->setOptions(['defaultFont' => 'Courier','isHtml5ParserEnabled'=>true,'isRemoteEnabled'=>true]);  
        
        $pdf->getDomPDF()->getCanvas()->get_cpdf()->setEncryption(strtoupper($vatno));

        return $pdf->output();
    }

    /*URL POST submittingfields/$vat_reg_id/export */
    public function exportToExcelSubmittingFields(Request $request, $vat_reg_id)
    { 
      try {          
        $spreadSheet = new Spreadsheet();
        $sheet = $spreadSheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Box');
        $sheet->setCellValue('B1', 'Value');

        $range = 'A1:B1';       
        $style = [
            'font'  => [
                'bold'  => true,
                'color' => array('rgb' => 'FFFFFF'),                
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => '4F81BD']
            ],
        ];
        $sheet->getStyle($range)->applyFromArray($style);       
        
        $sheet->setCellValue('A2', "Box 1");
        $sheet->setCellValue('B2', $request->box1);   

        $sheet->setCellValue('A3', "Box 2");
        $sheet->setCellValue('B3', $request->box2);   

        $sheet->setCellValue('A4', "Box 3");
        $sheet->setCellValue('B4', $request->box3);   

        $sheet->setCellValue('A5', "Box 4");
        $sheet->setCellValue('B5', $request->box4);   

        $sheet->setCellValue('A6', "Box 5");
        $sheet->setCellValue('B6', $request->box5);   

        $sheet->setCellValue('A7', "Box 6");
        $sheet->setCellValue('B7', $request->box6);   

        $sheet->setCellValue('A8', "Box 7");
        $sheet->setCellValue('B8', $request->box7);   

        $sheet->setCellValue('A9', "Box 8");
        $sheet->setCellValue('B9', $request->box8);

        $sheet->setCellValue('A10', "Box 9");
        $sheet->setCellValue('B10', $request->box9);           
        
        //$Excel_writer = new Xls($spreadSheet);
        $Excel_writer = new Xlsx($spreadSheet);        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="SubmittingFields.xlsx"');
        header('Cache-Control: max-age=0');
        ob_end_clean();
        $Excel_writer->save('php://output');
        exit();
      }
      catch (Exception $e) {
        return  $e->getMessage();
      }
    }

    /*URL POST submittingfields/$vat_reg_id */
    public function postSubmittingFields(Request $request, $vat_reg_id)
    { 
      try {                  
        $vatreg = $this->commonClass->getSpecificVatRegQuery($vat_reg_id, false);
        
        $vatregmain_status = $vatreg->vatregmain->status;

        if($vatregmain_status)
        {
          $vatRegHeading = Carbon::parse($vatreg->service_start)->format('M Y') . ' ' . $vatreg->country . ' ' . $vatreg->general_periods;

          if ($vat_reg_id) 
          {        
            if($vatreg->country == 'GB')    
            { 
              $submittingFields = SubmittingFields::updateOrCreate(
                ['vat_reg_id' => $vat_reg_id],
                [                           
                  'box_1' => $request->input('submittingfields_box_1'),
                  'box_2' => $request->input('submittingfields_box_2'),
                  'box_3' => $request->input('submittingfields_box_3'),
                  'box_4' => $request->input('submittingfields_box_4'),
                  'box_5' => $request->input('submittingfields_box_5'),
                  'box_6' => $request->input('submittingfields_box_6'),
                  'box_7' => $request->input('submittingfields_box_7'),
                  'box_8' => $request->input('submittingfields_box_8'),
                  'box_9' => $request->input('submittingfields_box_9')
                ]
              ); 
             
              $this->commonClass->addLog($this->authUser, 'vatreturn-submitting-fields-add', 
                [
                  'Client Name' => $vatreg->client->client_name,
                  'VAT Reg' => $vatRegHeading
                ]
              ); 
            } //GB
            else if($vatreg->country == 'NO')    
            {
              $submittingFields = SubmittingFieldsNO::updateOrCreate(
                ['vat_reg_id' => $vat_reg_id],
                [                                           
                  'box_3' => $request->input('submittingfields_box_3'),
                  'box_31' => $request->input('submittingfields_box_31'),
                  'box_33' => $request->input('submittingfields_box_33'),
                  'box_5' => $request->input('submittingfields_box_5'),
                  'box_6' => $request->input('submittingfields_box_6'),

                  'box_52' => $request->input('submittingfields_box_52'),

                  'box_1' => $request->input('submittingfields_box_1'),
                  'box_11' => $request->input('submittingfields_box_11'),
                  'box_13' => $request->input('submittingfields_box_13'),

                  'box_32' => $request->input('submittingfields_box_32'),
                  'box_12' => $request->input('submittingfields_box_12'),

                  'box_51' => $request->input('submittingfields_box_51'),
                  'box_91' => $request->input('submittingfields_box_91'),
                  'box_92' => $request->input('submittingfields_box_92'),
                  
                  'box_86' => $request->input('submittingfields_box_86'),
                  'box_87' => $request->input('submittingfields_box_87'),
                  'box_88' => $request->input('submittingfields_box_88'),
                  'box_89' => $request->input('submittingfields_box_89'),

                  'box_81' => $request->input('submittingfields_box_81'),                
                  'box_14' => $request->input('submittingfields_box_14'),                
                  'box_82' => $request->input('submittingfields_box_82'),
                  'box_15' => $request->input('submittingfields_box_15'),
                  'box_83' => $request->input('submittingfields_box_83'),
                  'box_84' => $request->input('submittingfields_box_84'),
                  'box_85' => $request->input('submittingfields_box_85')
                ]
              ); 
             
              $this->commonClass->addLog($this->authUser, 'vatreturn-submitting-fields-NO-add', 
                [
                  'Client Name' => $vatreg->client->client_name,
                  'VAT Reg' => $vatRegHeading
                ]
              ); 
            } //NO
            else if($vatreg->country == 'CH')    
            {
              $submittingFields = SubmittingFieldsCH::updateOrCreate(
                ['vat_reg_id' => $vat_reg_id],
                [                                           
                  'box_200' => $request->input('submittingfields_box_200'),
                  'box_205' => $request->input('submittingfields_box_205'),

                  'box_220' => $request->input('submittingfields_box_220'),
                  'box_221' => $request->input('submittingfields_box_221'),
                  'box_225' => $request->input('submittingfields_box_225'),
                  'box_230' => $request->input('submittingfields_box_230'),
                  'box_235' => $request->input('submittingfields_box_235'),
                  'box_280' => $request->input('submittingfields_box_280'),
                  'box_289' => $request->input('submittingfields_box_289'),

                  'box_299' => $request->input('submittingfields_box_299'),

                  'box_303' => $request->input('submittingfields_box_303'),
                  'box_303_1' => $request->input('submittingfields_box_303_1'),
                  'box_313' => $request->input('submittingfields_box_313'),
                  'box_313_1' => $request->input('submittingfields_box_313_1'),
                  'box_343' => $request->input('submittingfields_box_343'),
                  'box_343_1' => $request->input('submittingfields_box_343_1'),

                  'box_379' => $request->input('submittingfields_box_379'),
                  
                  'box_383' => $request->input('submittingfields_box_383'),
                  'box_383_1' => $request->input('submittingfields_box_383_1'),

                  'box_399' => $request->input('submittingfields_box_399'),

                  'box_400' => $request->input('submittingfields_box_400'),
                  'box_405' => $request->input('submittingfields_box_405'),
                  'box_410' => $request->input('submittingfields_box_410'),
                  'box_415' => $request->input('submittingfields_box_415'),
                  
                  'box_420' => $request->input('submittingfields_box_420'),
                  'box_479' => $request->input('submittingfields_box_479'),
                  
                  'box_500' => $request->input('submittingfields_box_500'),                
                  'box_510' => $request->input('submittingfields_box_510')               
                ]
              ); 
             
              $this->commonClass->addLog($this->authUser, 'vatreturn-submitting-fields-CH-add', 
                [
                  'Client Name' => $vatreg->client->client_name,
                  'VAT Reg' => $vatRegHeading
                ]
              ); 
            } //CH

            return response()->json([
              'status'          => 200,
              'message'          => "Submitting fields saved",
              'vat_reg_id' => $vat_reg_id,
              'data' => $submittingFields
            ]); 
          }   
          else
          {
            return response()->json([
              'status'          => 400,
              'message'          => "Inactive VAT Reg.",
              'vat_reg_id' => $vat_reg_id            
            ]); 
          }
        } 
        else
        {
          return response()->json([
            'status'          => 400,
            'message'          => "No such vat reg.",
            'vat_reg_id' => $vat_reg_id            
          ]); 
        }
      }
      catch (Exception $e) {
        return  $e->getMessage();
      }
    }

    /*Comment*/    
    /*URL POST vat-return/comment/$vat_id */
    public function uploadCommentWithFilesToOneDrive(Request $request, $vat_id)
    { 
      try {
        $client = VATRegistration::rightJoin('dv_clients', function($join) {
                    $join->on('dv_vat_registration.client_id', '=', 'dv_clients.id');
                  })                      
                  ->select('dv_clients.id AS client_id','dv_clients.client_name', 'dv_clients.vatno', 'dv_vat_registration.*',
                    'dv_vat_registration.id AS vat_reg_id'
                  )
                  ->where('dv_vat_registration.status', 6)
                  ->where('dv_vat_registration.id', $vat_id)
                  ->first();
        
        $system = $this->commonClass->getSystemInfo();
        
        if(isset($client))
          return $this->apiClass->uploadCommentWithFilesToOneDrive($request, $client, $this->authUser, $system); 
        else                                            
          return false;
      }
      catch (Exception $e) {
        return  $e->getMessage();
      }
    }
    
    /*URL send-comment-email/$vat_id */
    public function sendCommentEmailToClientUser(Request $request)
    {
      try {  
        $comment_id = $request->comment_id; 
        $vat_reg_id = $request->vat_reg_id;  

        $send_to = $request->send_to;
        $chk_cc = $request->chk_cc;

        // $vatregs = VATRegistration::leftJoin('dv_clients', function($join) {
        //               $join->on('dv_vat_registration.client_id', '=', 'dv_clients.id');                      
        //             })   
        //             ->rightJoin('dv_vatreturn_comments', function($join) {
        //               $join->on('dv_vatreturn_comments.vat_reg_id', '=', 'dv_vat_registration.id');                     
        //             })                  
        //             ->rightJoin('dv_vatreturn_comment_files', function($join) {
        //               $join->on('dv_vatreturn_comment_files.comment_id', '=', 'dv_vatreturn_comments.id');                     
        //             })      
        //             ->select('dv_clients.id AS client_id','dv_clients.client_name','dv_clients.lrep_email', 'dv_clients.vatno', 'dv_vatreturn_comments.comment', 'dv_vatreturn_comment_files.*')
        //             ->where('dv_vat_registration.status', 6)                      
        //             ->where('dv_vatreturn_comments.id', $comment_id)
        //             ->get();//Locked 

        $vatreg = VATRegistration::with([
                                'client', 'vatreturncomments', 'vatreturncomments.vatreturncommentfile'
                            ])                               
                            // ->whereHas('vatreturncomments', function ($query) use ($comment_id) {
                            //     $query->where('id', $comment_id);                      
                            // })
                            ->where('id', $vat_reg_id)
                            ->where('status', 6)
                            ->first();

        if($vatreg)
        {                      
            $system = $this->commonClass->getSystemInfoLazy(); 
            $systemapi = $system->systemapi->first();

            $client_name = '';          
            $vatno = '';
            $service_start = '';
            $country = '';
            $general_periods = '';           
            $vatRegHeading = '';
            $comment_files = [];
            $payment_info = '';
           
            $client = $vatreg->client; 
            $client_name = $client->client_name;                        
            $vatno = $client->vatno;

            $team_user_firstname = $vatreg->team_user_firstname;   
            $team_user_designation = $vatreg->team_user_designation;  

            $service_start = $vatreg->service_start;
            $country = $vatreg->country;
            $general_periods = $vatreg->general_periods;
            $currency_code = $vatreg->currency_code;

            $vatRegHeading = Carbon::parse($vatreg->service_start)->format('M Y') . ' ' . $vatreg->country . ' ' . $vatreg->general_periods;
            
            $vatreturn_comment = '';
            foreach($vatreg->vatreturncomments as $key => $vatreturncomment)
            {
              if($vatreturncomment->id == $comment_id)
              {
                $vatreturn_comment = $vatreturncomment->comment;

                if(count($vatreturncomment->vatreturncommentfile) > 0)
                {dd($vatreturncomment->vatreturncommentfile);
                  $downloadUrl = $this->apiClass->loadFromOneDriveLazy($vatreturncomment->vatreturncommentfile, $systemapi);
                  if(isset($downloadUrl->error))   
                    $comment_files[$key] = ['text' => "Comment files for " . $vatRegHeading,'url' => '']; 
                  else           
                    $comment_files[$key] = ['text' => "Comment files for " . $vatRegHeading,'url' => $downloadUrl]; 
                }
              }
            }
              
            // foreach($vatregs as $key=>$vatreg)
            // { 
            //   if($key == 0)
            //   {
                //$client_name = $vatreg->client_name;               
                //$vatno = $vatreg->vatno;

                //$service_start = $vatreg->service_start;
                //$country = $vatreg->country;
                //$general_periods = $vatreg->general_periods;
                //$currency_code = $vatreg->currency_code;

                //$vatRegHeading = Carbon::parse($vatreg->service_start)->format('M Y') . ' ' . $vatreg->country . ' ' . $vatreg->general_periods;                            
            //   }                                      
            // }

            //Get send_to client names
            $client_user = $this->commonClass->getUserNameBasedOnEmail($send_to);

            $data = [             
              'subject' => 'Re-open VAT Return folder',
              'lang' => $client_user->lang,
              'app_name' => config('app.name'),
              'vat_heading' => $vatRegHeading,
              'client' => [
                'client_name' => $client_name,
                'client_firstname' => $client_user->firstname,
                'client_lastname' => $client_user->lastname,
                'team_user_firstname' => $team_user_firstname,
                'team_user_designation' => $team_user_designation
              ],                 
              'message' => $vatreturn_comment,
              'attachment' => $comment_files, 
              'align' => 'left'
            ];
           
            $email_data = new ReopenEmail($data);

            $mailsent = Mail::to($send_to)
                          //->cc($chk_cc)
                          ->send($email_data);
            
            if($mailsent)
            {                
              $email_headers = $mailsent->getOriginalMessage()->getHeaders();
              $message_id = $email_headers->getHeaderBody('X-SES-Message-ID');                     
              if ($message_id)
              {
                $email_sent_to = $email_headers->getHeaderBody('To');
                $email_sent_ccs = $email_headers->getHeaderBody('Cc');
                $email_sent_subject = $email_headers->getHeaderBody('Subject');

                $emailNotification = new EmailNotification;
                $emailNotification->vat_reg_id = $vat_reg_id; 
                $emailNotification->message_id = $message_id;   
                $emailNotification->subject = $email_sent_subject;     
                $emailNotification->name = ($client_user) ? $client_user->firstname . ' ' . $client_user->lastname : '';                
                $emailNotification->email = ($email_sent_to) ? $email_sent_to[0]->getAddress() : '';     
                $emailNotification->sent_by = $this->authUser->user_id;   
                
                $emailNotification->save(); 

                if($chk_cc)
                {
                  foreach($chk_cc as $cc)
                  {   
                    //Get cc client names
                    $client_user_cc = $this->commonClass->getUserNameBasedOnEmail($cc);
                    $data['lang'] = $client_user_cc->lang;
                    $data['client']['client_firstname'] = $client_user_cc->firstname;
                    $data['client']['client_lastname'] = $client_user_cc->lastname;

                    //Email markdown        
                    $email_data_cc = new ReopenEmail($data);

                    $mailsent_cc = Mail::to($cc)                               
                                  ->send($email_data_cc);

                    if($mailsent_cc)
                    {
                      $email_headers_cc = $mailsent_cc->getOriginalMessage()->getHeaders();
                      $message_id_cc = $email_headers_cc->getHeaderBody('X-SES-Message-ID');                     
                      if ($message_id_cc)
                      {
                        $email_sent_to = $email_headers_cc->getHeaderBody('To');
                        //$email_sent_ccs = $email_headers_cc->getHeaderBody('Cc');
                        $email_sent_subject = $email_headers_cc->getHeaderBody('Subject');

                        $emailNotificationCC = new EmailNotification;
                        $emailNotificationCC->vat_reg_id = $vat_reg_id; 
                        $emailNotificationCC->message_id = $message_id_cc;   
                        $emailNotificationCC->subject = $email_sent_subject; 
                        $emailNotificationCC->send_type = 'cc';     
                        $emailNotificationCC->name = ($client_user_cc) ? $client_user_cc->firstname . ' ' . $client_user_cc->lastname : '';                
                        $emailNotificationCC->email = ($email_sent_to) ? $email_sent_to[0]->getAddress() : '';     
                        $emailNotificationCC->sent_by = $this->authUser->user_id;   
                        
                        $emailNotificationCC->save();
                      }
                    } // if email sent
                  }//for CC  
                }//if CC                 
              }
            } 
            // $updateStatus = VATRegistration::where('id', $vat_id) 
            //                   ->where('status', 5)         
            //                   ->update(
            //                     [
            //                           'status' => 6, 
            //                           'locked_by' => $this->authUser->user_id, 
            //                           'locked_at' => now() 
            //                     ]
            //                   );//From 'Submitted' to 'Lock' (sent email to selected client users)  

            
            // $this->commonClass->addLog($this->authUser, 'vatreturn-lock-email', 
            //   [
            //     'Client Name' => $client_name, 
            //     'VAT Reg' => $data['vat_heading'], 
            //     'Client User' => $request->send_to, 
            //     'Client User(CC)' => $request->chk_cc
            //   ]
            // ); 

            $updateStatus = VATRegistration::where('id', $vat_reg_id) 
                              ->where('status', 6)         
                              ->update(
                                [
                                      'status' => 5, 
                                      'locked_by' => NULL, 
                                      'locked_at' => NULL,
                                      'payment_date' => NULL
                                ]
                              );//From 'Lock' to 'Submitted' (sent email to selected client users)    

            
            $this->commonClass->addLog($this->authUser, 'vatreturn-reopen', 
              [
                'Client Name' => $client_name, 
                'VAT Reg' => $data['vat_heading'], 
                'Client User' => $request->send_to, 
                'Client User(CC)' => $request->chk_cc
              ]
            );   

            return response()->json([
              'success'          => true,
              'message' => "Email sent",
              'data' => $data
            ]);                
        } 
        else
          return response()->json([
              'success'          => false,
              'message' => "Email already sent"
            ]);                                
      }
      catch (Exception $e) {
        return  $e->getMessage();
      }
    }

    /*URL GET history/$id/download */
    public function downloadFromOneDrive(Request $request, $id)
    { 
      try {          
        $client = VATRegistration::leftJoin('dv_clients', function($join) {
                      $join->on('dv_vat_registration.client_id', '=', 'dv_clients.id');                      
                    })                       
                    ->rightJoin('dv_vatreturn_comment_files', function($join) {
                      $join->on('dv_vatreturn_comment_files.vat_reg_id', '=', 'dv_vat_registration.id');                     
                    })      
                    ->select('dv_clients.id AS client_id','dv_clients.client_name','dv_clients.lrep_email', 'dv_clients.vatno', 'dv_vat_registration.*', 'dv_vatreturn_comment_files.comment_id', 'dv_vatreturn_comment_files.folder_id', 'dv_vatreturn_comment_files.file_id', 'dv_vatreturn_comment_files.file_name', 'dv_vatreturn_comment_files.file_size')                    
                    ->where('dv_vatreturn_comment_files.id', $id)                        
                    ->first();
                      
        $system = $this->commonClass->getSystemInfoLazy(); 
        $systemapi = $system->systemapi->first();       
             
        $downloadfile = $this->apiClass->loadFromOneDriveLazy($client, $systemapi);  

        if(isset($downloadfile->error)) 
          return '';
        else          
          return $downloadfile['download_url'];
      }
      catch (Exception $e) {
        return  $e->getMessage();
      }
    }

    //Import VAT   
    /*URL POST import-vat/$import_vat_id */
    public function postImportVat(Request $request, $import_vat_id)
    { 
      try {                  
        
        if ($import_vat_id) 
        {            
          $client = VATRegistration::leftJoin('dv_clients', function($join) {
                      $join->on('dv_vat_registration.client_id', '=', 'dv_clients.id');                      
                    })
                    ->leftJoin('dv_import_vat_files', function($join) {
                      $join->on('dv_vat_registration.id', '=', 'dv_import_vat_files.vat_reg_id');                      
                    })
                    ->select('dv_clients.id AS client_id','dv_clients.client_name','dv_clients.lrep_email', 'dv_clients.vatno', 'dv_vat_registration.*','dv_import_vat_files.month_year',
                      'dv_vat_registration.id AS vat_reg_id',
                      DB::raw('(CASE                         
                        WHEN dv_vat_registration.general_periods = "monthly" THEN 1 
                        WHEN dv_vat_registration.general_periods = "bi-monthly" THEN 2
                        WHEN dv_vat_registration.general_periods = "quarterly" THEN 3 
                        WHEN dv_vat_registration.general_periods = "half-yearly" THEN 6 
                        WHEN dv_vat_registration.general_periods = "yearly" THEN 12                      
                        ELSE "" END) AS frequency'
                      )
                    )                    
                    ->where('dv_import_vat_files.id', $import_vat_id)
                    ->first();

          $vat_reg_id  = $client->vat_reg_id;
          $vatRegHeading = Carbon::parse($client->service_start)->format('M Y') . ' ' . $client->country . ' ' . $client->general_periods;

  
          $importVat = ImportVatFiles::updateOrCreate(
            ['id' => $import_vat_id],
            [                           
              'fee_number' => $request->input('import_vat_fee_number'),
              'statistical_number' => $request->input('import_vat_statistical_number'),
              'e_fee_number' => $request->input('import_vat_e_fee_number'),
              'e_statistical_number' => $request->input('import_vat_e_statistical_number')              
            ]
          ); 
         
          $this->commonClass->addLog($this->authUser, 'vatreturn-import-vat-numbers-update', 
            [
              'Client Name' => $client->client_name,
              'VAT Reg' => $vatRegHeading,
              'month' => Carbon::parse('01-'.$client->month_year)->format('M Y')
            ]
          ); 

          return response()->json([
            'status'          => 200,
            'message'          => "Import VAT saved",
            'import_vat_id' => $import_vat_id,
            'data' => $importVat
          ]);    
        } 
        else
        {
          return response()->json([
            'status'          => 400,
            'message'          => "No such import vat",
            'import_vat_id' => $import_vat_id            
          ]); 
        }
      }
      catch (Exception $e) {
        return  $e->getMessage();
      }
    }

    //Import VAT Comment
    /*URL POST import-vat-files/$import_vat_id/comment */
    public function postImportVatFileComment(Request $request, $import_vat_id, $import_vat_line_no)
    {       
        if ($import_vat_id && $import_vat_line_no) 
        {                
          $importvatcomment = ImportVatComments::updateOrCreate(  
            [
              'import_vat_id' => $import_vat_id,
              'line_no' => $import_vat_line_no
            ],          
            [
                'import_vat_id' => $import_vat_id, 
                'line_no' => $import_vat_line_no, 
                'comment' => $request->import_vat_comment_quill,
                'created_by' => $this->authUser->user_id                 
            ]
          );   

          $client = $this->commonClass->getVatReturnImportVat($import_vat_id, $import_vat_line_no);                                  

          $vatRegHeading = Carbon::parse($client->service_start)->format('M Y') . ' ' . $client->country . ' ' . $client->general_periods;        
        
          $this->commonClass->addLog($this->authUser, 'import-vat-file-comment', 
            [
              'Client Name' => $client->client_name, 
              'VAT Reg' => $vatRegHeading,
              'month' => Carbon::parse('01-'.$client->month_year)->format('M Y'),
              'Line No.' => $client->line_no
            ]
          );       
          
          return response()->json(
            [
              'status' => 200,
              'message' => 'Created',
              'import_vat_id' => $import_vat_id
            ]
          ); 
        } 
    }

    /*URL DELETE import-vat-files/$import_vat_id/comment */
    public function deleteImportVatFileComment(Request $request, $import_vat_id)
    { 
      try {
        $import_vat_comment_line_no = $request->import_vat_comment_line_no;

        $client = $this->commonClass->getVatReturnImportVat($import_vat_id, $import_vat_comment_line_no);     
               
        $deleteImportVatFileComment = ImportVatComments::where('import_vat_id', $import_vat_id)
                                      ->where('line_no', $import_vat_comment_line_no)
                                      ->delete();
        
        $vatRegHeading = Carbon::parse($client->service_start)->format('M Y') . ' ' . $client->country . ' ' . $client->general_periods;        
        
        $this->commonClass->addLog($this->authUser, 'import-vat-file-comment-delete', 
          [
            'Client Name' => $client->client_name, 
            'VAT Reg' => $vatRegHeading,
            'month' => Carbon::parse('01-'.$client->month_year)->format('M Y')
          ]
        );
       
        return response()->json(
          [
            'status' => 'deleted',
            'message' => 'Deleted',
            'import_vat_id' => $import_vat_id
          ]
        );                    
      }
      catch (Exception $e) {
        return  $e->getMessage();
      }
    }

    /*URL PUT import-vat-files/$import_vat_id/updatesendemail */
    public function updateSendEmail(Request $request, $import_vat_id)
    {    
      if ($import_vat_id) 
      {
        $client = $this->commonClass->getVatReturnImportVat($import_vat_id);   
        $update = ImportVatFiles::where('id', $import_vat_id)->update(
            [                                        
              'send_email' => ($request->status == "true") ? 1 : 0
            ]
        );
      
        $vatRegHeading = Carbon::parse($client->service_start)->format('M Y') . ' ' . $client->country . ' ' . $client->general_periods;    
        $this->commonClass->addLog($this->authUser, 'import-vat-file-update-sendemail', 
          [
            'Client Name' => $client->client_name,
            'VAT Reg' => $vatRegHeading,
            'month' => Carbon::parse('01-'.$client->month_year)->format('M Y'),
            'Status Text' => $request->statustext
          ]
        );
                        
        // user updated
        return response()->json($request->statustext.'d');       
      } 
      else 
      {
          // user already exist
          $this->commonClass->addLog($this->authUser, 'client-error');
          
          return response()->json(
            [
              'message' => "cannot ".$request->statustext
            ]
          , 422);
      }     
    }  

    public function getContacts(Request $request)
    {      
        $client_id = $request->client_id;
        $columns = [
          0 => 'id',        
          1 => 'name',
          2 => 'email',              
          3 => 'telephone',
          4 => 'status'         
        ];

        $search = "";

        $userquery = $this->commonClass->getClientContacts($client_id);

        $totalData = $userquery->count();

        $totalFiltered = $totalData;

        if($request->input('length') == -1)        
          $limit = $totalData;                   
        else 
          $limit = $request->input('length');
        
        $start = $request->input('start');
     
        if($request->input('order.0.column') == 0)
          $order = 'users.'.$columns[$request->input('order.0.column')];        
        else
          $order = $columns[$request->input('order.0.column')];        
        $dir = $request->input('order.0.dir');
      
        if (empty($request->input('search.value'))) { 
                  
          $clientuserlists = $userquery->offset($start)
                            ->limit($limit)
                            ->orderBy($order, $dir)
                            ->get();  
  
        } else {
          $search = $request->input('search.value');                
                  
          $clientuserlists = $userquery->offset($start)
                            ->limit($limit)
                            ->orderBy($order, $dir)
                            ->get();  
        }

        $data = [];

        if (!empty($clientuserlists)) {
          // providing a dummy id instead of database ids
          $ids = $start;

          foreach ($clientuserlists as $user) {                                
                $nestedData['id'] = $user->id;
                $nestedData['fake_id'] = ++$ids;                              
                $nestedData['name'] = ($user->firstname . ' ' . $user->lastname);     
                $nestedData['firstname'] = $user->firstname;
                $nestedData['lastname'] = $user->lastname;          
                $nestedData['email'] = $user->email;                     
                $nestedData['telephone'] = ($user->telephone == null) ? '-' : $user->telephone;                  
                $nestedData['status'] = ($user->status == null) ? '0' : '1';      
                                                                                 
                $data[] = $nestedData;           
          }
        }
             
          return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => intval($totalData),
            'recordsFiltered' => intval($totalFiltered),
            'code' => 200,
            'data' => $data,
          ]);
    }

    /* -- FILE UPLOAD -- */
    /*URL GET file/$vat_reg_id */
    public function loadFile(Request $request)
    {
        try 
        {
            $vat_reg_id = $request->vat_reg_id;
            $client_id = $request->client_id;
            $file_type = $request->file_type;
            $file_type_title = $request->file_type_title;

            $client = $this->commonClass->getVATReg($vat_reg_id);

            if($file_type == 'pivs')  
              $result = $this->commonClass->getPivsFiles($vat_reg_id); 
            else if($file_type == 'documents')
              $result = $this->commonClass->getVatReturnDocuments($vat_reg_id);
            else if($file_type == 'c79')
              $result = $this->commonClass->getVatReturnC79Documents($vat_reg_id);
            else if($file_type == 'cas')
              $result = $this->commonClass->getCashAccountStatementFiles($vat_reg_id);
            else if($file_type == 'dda')
              $result = $this->commonClass->getDutyDefermentAccountFiles($vat_reg_id);
            else if($file_type == 'ivf')       
              $result = $this->commonClass->getVatReturnImportVatFiles($vat_reg_id);              
            else if($file_type == 'ci')       
              $result = $this->commonClass->getVatReturnCommercialInvoiceFiles($vat_reg_id); 
            else if($file_type == 'vatreturn')       
              $result = $this->commonClass->getVatReturnFiles($vat_reg_id);    
            else if($file_type == 'vatcontrol')       
              $result = $this->commonClass->getVatControlFiles($vat_reg_id);  
            else if($file_type == 'ircontrol')       
              $result = $this->commonClass->getImportReconciliationControlFiles($vat_reg_id);
     
            if(empty($result))
                return response()->json(
                    [
                        'status' => 200,
                        'view' => ''
                    ]
                );          
            else
            {
                $files = $result;

                $authUser = $this->authUser;
                $client_users = ($file_type == 'ci') ? '' : $this->commonClass->getClientUsersForEmail($client_id, $file_type);

                $vatreg = $client;
                $vat_reg_main = $vatreg->vatregmain;
                $vatregmain_status = $vat_reg_main->status;

                $missing_commercial_invoices = '';
                if($file_type == 'ci')
                { 
                  $invoices = ($vatreg->invoices) ? $vatreg->invoices : [];                                                     
                  foreach ($files as $key => $commercial_invoices_file)
                  {
                    if($commercial_invoices_file->sale_invoice_nos != '')
                    {     
                      $sale_invoice_nos = explode(',', $commercial_invoices_file->sale_invoice_nos);

                      if(count($invoices) == 0)
                      {
                        if($missing_commercial_invoices == '')
                          $missing_commercial_invoices = $commercial_invoices_file->sale_invoice_nos;
                        else
                          $missing_commercial_invoices .= ', ' . $commercial_invoices_file->sale_invoice_nos;     
                      } /* --end if MISSING COMMERCIAL INVOICES -- */  
                      else        
                      {
                        $filtered_invoices = $invoices->filter(function ($invoice, $key) use($sale_invoice_nos) {
                            return (in_array($invoice->invoice_no, $sale_invoice_nos)) ? null : $invoice->invoice_no;
                        });                         
                      } /* --end else MISSING COMMERCIAL INVOICES -- */             
                    } /* --end if COMMERCIAL INVOICES -- */
                  } /* --end for COMMERCIAL INVOICES -- */                  
                } /* --end if FILE TYPE CI -- */
                
                $view = view('_partials._content._vatreturn.file-list-lazy', compact('files', 'authUser', 'client', 'client_users', 'client_id', 'vat_reg_id', 'file_type_title', 'file_type', 'vatreg', 'vat_reg_main', 'missing_commercial_invoices', 'vatregmain_status'))->render();
                
                if($file_type == 'documents' || $file_type == 'vatreturn' || $file_type == 'vatcontrol' || $file_type == 'ircontrol')
                {
                    $i = 0;
                    
                    $doc_modal = view('_partials._modals.modal-file-upload-single-lazy', compact('authUser', 'client', 'client_users', 'client_id', 'vat_reg_id', 'file_type_title', 'file_type', 'i', 'vatreg'))->render();    

                    if($file_type == 'vatreturn' || $file_type == 'vatcontrol' || $file_type == 'ircontrol')
                    {                        
                        $_with = ['vatregmain', 'anyexceltemplate'];
                        $vatreg = $this->commonClass->getVatRegLazy($vat_reg_id, null, $_with); 
                        $vatregmain_status = $vatreg->vatregmain->status;
                        if($file_type == 'vatreturn')
                          $vatreturnfiles = ($vatreg->vatreturnfiles) ? $vatreg->vatreturnfiles : [];   
                        else if($file_type == 'vatcontrol')
                          $vatcontrolfiles = ($vatreg->vatcontrolfiles) ? $vatreg->vatcontrolfiles : []; 
                        else if($file_type == 'ircontrol')
                          $ircontrolfiles = ($vatreg->ircontrolfiles) ? $vatreg->ircontrolfiles : []; 
                        $anyexceltemplate = $vatreg->anyexceltemplate; 

                        /* -- GET ANYEXCEL TEMPLATES -- */
                        $anyexcel_templates = $this->commonClass->getAnyExcelTemplates();
                        /* --end GET ANYEXCEL TEMPLATES -- */

                        if(isset($vatreturnfiles))
                          $anyexcel_template_select = view('_partials._content._vatreturn.anyexcel-template-select', compact('file_type', 'vat_reg_id', 'anyexcel_templates', 'anyexceltemplate', 'vatreturnfiles', 'vatreg', 'vatregmain_status'))->render();
                        else if(isset($vatcontrolfiles))
                          $anyexcel_template_select = view('_partials._content._vatreturn.anyexcel-template-select', compact('file_type', 'vat_reg_id', 'anyexcel_templates', 'anyexceltemplate', 'vatcontrolfiles', 'vatreg', 'vatregmain_status'))->render();
                        else if(isset($ircontrolfiles))
                          $anyexcel_template_select = view('_partials._content._vatreturn.anyexcel-template-select', compact('file_type', 'vat_reg_id', 'anyexcel_templates', 'anyexceltemplate', 'ircontrolfiles', 'vatreg', 'vatregmain_status'))->render();

                        return response()->json(
                            [
                                'status' => 200,
                                'view' => $view,
                                'doc_modal' => $doc_modal,                              
                                'anyexcel_template_select' => $anyexcel_template_select
                            ]
                        );
                    }                    
                    else  
                        return response()->json(
                            [
                                'status' => 200,
                                'view' => $view,
                                'doc_modal' => $doc_modal
                            ]
                        );
                }                
                else
                    return response()->json(
                        [
                            'status' => 200,
                            'view' => $view
                        ]
                    );
            }
        }//try
        catch (Exception $e) 
        {
            return  $e->getMessage();
        }//catch
    }

    /*URL POST file/$vat_reg_id */
    public function uploadFileToOneDrive(Request $request, $vat_reg_id)
    {         
      try 
      {         
        $vatreg = $this->commonClass->getSpecificVatRegQuery($vat_reg_id);               
           
        $system = $this->commonClass->getSystemInfoLazy(); 
        $systemapi = $system->systemapi->first();      
               
        if(isset($vatreg))  
        {
          $vatregmain_status = $vatreg->vatregmain->status;

          if($vatregmain_status)
          {
            if($request->file('file'))  
            {        
                $uploadedfile = $this->apiClass->uploadFileToOneDriveLazy($request, $vatreg, $this->authUser, $systemapi);      
                
                return $uploadedfile;   
            }
          }
        }
        else                                            
          return false;
      }//try
      catch (Exception $e) {
        return  $e->getMessage();
      }//catch
    } 

    /*URL POST disregard-task/$vat_reg_id */
    public function disregardTask(Request $request, $vat_reg_id)
    {         
      try 
      {           
        $client = $this->commonClass->getVATReg($vat_reg_id);                  
        
        $system = $this->commonClass->getSystemInfo();        
        
        if(isset($client))
        {
          $vat_reg_id = $client->vat_reg_id;
          $month_year = $request->month_year;
          $file_type = $request->file_type;
          $file_type_title = $request->file_type_title;
         
          $vatRegHeading = Carbon::parse($client->service_start)->format('M Y') . ' ' . $client->country . ' ' . $client->general_periods;  

          $file_table = $this->commonClass->queryTableForFile($file_type);

          $whereCondition = [
              'vat_reg_id' => $vat_reg_id,
              'month_year' => $month_year           
          ];

          $updateFields = [
              'vat_reg_id' => $vat_reg_id,
              'folder_id' => NULL, 
              'file_id' => NULL, 
              'file_name' => NULL,
              'file_size' => NULL,              
              'created_by' => $this->authUser->user_id
            ];

          if($file_type == 'pivs' || $file_type == 'cas' || $file_type == 'dda')  
          {             
            $updateFields['month_year'] = Carbon::parse('01-'.$month_year)->format('m-Y');
            $updateFields['month_total'] = 0;
            $updateFields['status'] = 0;
          }            
          else if($file_type == 'c79')
          {                   
            $whereCondition['doc_type'] = 'C79';

            $updateFields['month_year'] = Carbon::parse('01-'.$month_year)->format('m-Y');
            $updateFields['doc_type'] = $file_type_title;
            $updateFields['doc_numbers'] = 0;
            $updateFields['status'] = 0;
          }  
          else if($file_type == 'ivf')
          {            
            $updateFields['month_year'] = Carbon::parse('01-'.$month_year)->format('m-Y');
            $updateFields['fee_number'] = 0;
            $updateFields['statistical_number'] = 0;
            $updateFields['adjustment_no'] = 0;
            $updateFields['invoice_total'] = 0;
          } 

          $updateTable = $file_table->updateOrCreate(
                              $whereCondition,
                              $updateFields
                              ); 
          
          $this->commonClass->addLog($this->authUser, 'task-disregard', 
            [
              'Client Name' => $client->client_name, 
              'VAT Reg' => $vatRegHeading,
              'month' => ($month_year == null) ? '-' : Carbon::parse('01-'.$month_year)->format('M Y'),
              'file_type_title' => $file_type_title
            ]
          );

          return response()->json([
              'status'          => "disregarded",
              'vat_reg_id' => $vat_reg_id,
              'updateTable' => $updateTable,
            ]);
        }
        else                                            
          return false;
      }//try
      catch (Exception $e) {
        return  $e->getMessage();
      }//catch
    } 

    /*URL DELETE file/$file_id */
    public function deleteFileFromOneDrive(Request $request, $file_id)
    { 
      try 
      {   
         $file_type = $request->file_type;
      
         $file_table = $this->commonClass->queryTableForFile($file_type);

        $file_type_name = $file_type;
        if($file_type == 'ivf')
          $file_type_name = "importvatfiles";
        else if($file_type == 'vatreturn')
          $file_type_name = "vatreturnfiles";
        else if($file_type == 'vatcontrol')
          $file_type_name = "vatcontrolfiles"; 
        else if($file_type == 'ircontrol')
          $file_type_name = "ircontrolfiles";        
        else if($file_type == 'ci')
          $file_type_name = "commercialinvoicesfiles";         

        $vatreg = $this->commonClass->getVatRegFilesLazy($file_type_name, $file_id);    
        $client = $vatreg->client;
        $file = $vatreg[$file_type_name]->filter(function ($file, $key) use($file_id) {         
            return $file->id == $file_id; 
        })->first();  

        $vatRegHeading = Carbon::parse($vatreg->service_start)->format('M Y') . ' ' . $vatreg->country . ' ' . $vatreg->general_periods; 

        $system = $this->commonClass->getSystemInfoLazy(); 
        $systemapi = $system->systemapi->first();  
        
        $deleteResult = $this->apiClass->deleteFromOneDriveLazy($file, $systemapi, $file_type);         

        if($file_type == 'pivs' || $file_type == 'cas' || $file_type == 'dda')  
        { 
          $updateTable = $file_table->where('id', $file_id)                            
                          ->update(
                            [
                                  'folder_id' => NULL, 
                                  'file_id' => NULL, 
                                  'file_name' => NULL,
                                  'file_size' => NULL                                        
                            ]
                          );  
        }      
        else
        {
          $deleteTable = $file_table->where('id', $file_id)->delete();

          if($file_type != 'vatcontrol' && $file_type != 'ircontrol')
            $deleteInvoiceTable = Invoices::where('vat_reg_id', $vatreg->id)->delete();          
        }
        
        if($file_type == 'documents')  
        {
          $this->commonClass->addLog($this->authUser, 'file-delete', 
            [
              'Client Name' => $client->client_name, 
              'VAT Reg' => $vatRegHeading,              
              'doc_type' => $file->doc_type,
              'file_type_title' => $request->file_type_title
            ]
          );
        }
        else
          $this->commonClass->addLog($this->authUser, 'file-delete', 
            [
              'Client Name' => $client->client_name, 
              'VAT Reg' => $vatRegHeading,
              'month' => ($file->month_year == null) ? '-' : Carbon::parse('01-'.$file->month_year)->format('M Y'),
              'file_type_title' => $request->file_type_title
            ]
          );
        
        return $deleteResult;                          
      }//try
      catch (Exception $e) {
        return  $e->getMessage();
      }//catch
    }

    /*URL GET file/$file_id/download */
    public function downloadFileFromOneDrive(Request $request, $file_id)
    { 
      try 
      { 
        $original_file = json_decode($request->original_file);
        $file_type = $request->file_type;
        $o_file_id = $request->o_file_id;
              
        $file_type_name = $file_type;
        if($file_type == 'ivf')
          $file_type_name = "importvatfiles";
        else if($file_type == 'vatreturn')
        {
          $file_type_name = "vatreturnfiles";
          if($o_file_id != "")
          {
            $file_type_name = "vatreturnofiles";
            $file_id = $o_file_id;
          }
        }
        else if($file_type == 'vatcontrol')
        {
          $file_type_name = "vatcontrolfiles";
          if($o_file_id != "")
          {
            $file_type_name = "vatcontrolofiles";
            $file_id = $o_file_id;
          }
        }
        else if($file_type == 'ircontrol')
        {
          $file_type_name = "ircontrolfiles";
          if($o_file_id != "")
          {
            $file_type_name = "ircontrolofiles";
            $file_id = $o_file_id;
          }
        }
        else if($file_type == 'ci')
          $file_type_name = "commercialinvoicesfiles"; 
        else if($file_type == 'mailbox')
          $file_type_name = "mailboxfiles"; 
        else if($file_type == 'swissimportreconciliationfiles')
        {
          $file_type_name = "importreconciliationswissfiles";  
          if($o_file_id != "") 
            $file_id = $o_file_id;      
        }
        else if($file_type == 'receipt')
        {
          $file_type_name = "receipt";  
          if($o_file_id != "") 
            $file_id = $o_file_id;      
        }

        if($file_type == 'mailbox')
        {         
          $file = $this->commonClass->getMailboxFilesLazy($file_id);            
        } 
        else if($file_type == 'cargo_mailbox')
        { 
          $file = $this->commonClass->getCargoDeclarationFileDirectLazy($file_id);            
        }
        else if($file_type == 'importreconciliationfiles')        
          $file = $this->commonClass->getImportReconciliationFilesLazy($file_id);   
        else if($file_type == 'name' || $file_type == 'address')
          $file = $this->commonClass->getClientQAFiles($file_id);   
        else if($file_type == 'company')
        { 
          $file = $this->commonClass->getCompanyFilesLazy($file_id);              
        }
        else
        {
          $vatreg = $this->commonClass->getVatRegFilesLazy($file_type_name, $file_id);            
          if($o_file_id == "")    
          {   
            $file = $vatreg[$file_type_name]->filter(function ($file, $key) use($file_id) {         
                return $file->id == $file_id; 
            })->first();  
          }
          else
          {         
            $file = $vatreg[$file_type_name]->filter(function ($file, $key) use($o_file_id) {         
                return $file->id == $o_file_id; 
            })->first(); 
          }
        }

        $system = $this->commonClass->getSystemInfoLazy(); 
        $systemapi = $system->systemapi->first();  

        $downloadfile = $this->apiClass->loadFromOneDriveLazy($file, $systemapi, $original_file);  
         
        if(isset($downloadfile->error))   
          return '';
        else  
        {
          if($file_type == 'importreconciliationfiles')
          {
            $sales_invoice_xml = $this->commonClass->generateSalesInvoicePdfFromXml($downloadfile);
            
            $data = [          
                'xmlContent' => $sales_invoice_xml               
            ];  
            $pdf = PDF::loadView('content.declaration.sales-invoice-pdf', $data);       

            $pdf->setPaper('A4', 'portrait');

            return $pdf->stream($sales_invoice_xml['invoice_no'] . '.pdf');   
          } 
          else  
          {
            if(isset($request->view_type))
            {              
              $response = Http::withHeaders([
                  'User-Agent' => 'Mozilla/5.0',
              ])->timeout(20)->withOptions(['allow_redirects' => true])->get($downloadfile['download_url']);
             
              if (!$response->ok()) {
                  return response()->json(['error' => 'Failed to fetch PDF'], 500);
              }

              $contentType = $response->header('Content-Type') ?? 'application/octet-stream';             
              $filename = 'file.' . explode('/', $contentType)[1];

              return Response::make($response->body(), 200, [
                  'Content-Type' => $contentType,                 
                  'Content-Disposition' => 'inline; filename="' . $filename . '"',
              ]);
            }
            else
              return $downloadfile['download_url'];
          }
        }
      }//try
      catch (Exception $e) {
        return  $e->getMessage();
      }//catch
    }   

    /*URL GET file/$file_id/refresh */
    public function refreshFileToLoadDatas(Request $request, $file_id)
    { 
      try 
      {
        $file_type = $request->file_type;

        $file_type_name = $file_type;
        if($file_type == 'ci')
          $file_type_name = "commercialinvoicesfiles"; 

        $vatreg = $this->commonClass->getVatRegFilesLazy($file_type_name, $file_id);       
          
        $file = $vatreg[$file_type_name]->filter(function ($file, $key) use($file_id) {         
            return $file->id == $file_id; 
        })->first();         

        $system = $this->commonClass->getSystemInfoLazy(); 
        $systemapi = $system->systemapi->first();  

        $downloadfile = $this->apiClass->loadFromOneDriveLazy($file, $systemapi);  
         
        if(isset($downloadfile->error))   
          return '';
        else  
        {                 
          $number = $this->commonClass->extractTextViaOpenAi($file_type, $downloadfile);  
          
          $whereUpdate = ['id' => $file_id];

          $updateFields['sale_invoice_nos'] = $number['sale_invoice_nos'];
          $updateFields['invoice_count'] = $number['invoice_count'];          
          $updateFields['created_by'] = $this->authUser->user_id;

          $file_table = $this->commonClass->queryTableForFile($file_type);
          $updateTable = $file_table->updateOrCreate(
            $whereUpdate,
            $updateFields
          );

          return $updateTable;
        }
      }
      catch (Exception $e) {
        return  $e->getMessage();
      }//catch
    }

    /*URL PUT file/$file_id */
    public function updateFileNumber(Request $request, $file_id)
    { 
      try 
      {   
        if(isset($request->file_type_for_number)) 
        {
          $file_type = $request->file_type_for_number;

          $file_table = $this->commonClass->queryTableForFile($file_type);
          
          $updateNumber = $file_table->where('id', $file_id)->first();
          if($file_type == 'documents' || $file_type == 'c79')  
            $updateNumber->doc_numbers = $request->file_number; 
          else
            $updateNumber->month_total = $request->file_number; 
          $updateNumber->save();                                    
          
          return $updateNumber;
        } //NUMBER
        else if(isset($request->file_type_for_file_name)) 
        {
          $file_type = $request->file_type_for_file_name;

          $file_table = $this->commonClass->queryTableForFile($file_type);
          
          $updateFileName = $file_table->where('id', $file_id)->first();
          $updateFileName->o_file_name = $request->file_name; 
          
          $updateFileName->save();                                    
          
          return $updateFileName;
        } //FILE NAME
      }//try
      catch (Exception $e) {
        return  $e->getMessage();
      }//catch
    }

    /*URL POST file-email/$vat_reg_id */
    public function sendEmailFileToClientUser(Request $request, $vat_reg_id)
    {
      try 
      {
        $send_test = $request->send_test;
        $re_send = ($request->re_send) ? $request->re_send : 0;

        $file_type = $request->file_type;
        $file_type_title = $request->file_type_title;
        $no_docs = $request->no_docs;

        $month_year = $request->month_year;
       
        $send_to = ($send_test) ? $this->authUser->email : $request->send_to;
        $email_message = isset($request->email_message) ? $request->email_message : "";
        $chk_cc = $request->chk_cc;

        $vatregs = $this->queryClientUsersForFile($file_type, $vat_reg_id, $month_year, $re_send);

        if($file_type == 'cas')
        {
          if($request->table_to_excel)
          {         
            $service = new TableToExcelService();
            $filePath = $service->convert($request->table_to_excel);

            if($filePath)  
            {        
              $system = $this->commonClass->getSystemInfoLazy(); 
              $systemapi = $system->systemapi->first();

              $filecontent = file_get_contents($filePath);

              $request_pass = [
                  'file' => $filecontent,                                
                  'file_type' => 'cas',
                  'file_type_title' => 'Cash Account Statement',
                  'month_year' => $month_year,
                  'file_extension' => 'xlsx',
              ]; 
              $vatreg = $this->commonClass->getSpecificVatRegQuery($vat_reg_id);
              $uploadedfile = $this->apiClass->uploadFileToOneDriveLazy($request_pass, $vatreg, $this->authUser, $systemapi);

              if($uploadedfile)
                $vatregs = $this->queryClientUsersForFile($file_type, $vat_reg_id, $month_year, $re_send);
            }
          }
        }

        if(count($vatregs) == 0)
        {
          if($send_test)
            $vatregs = $this->queryClientUsersForFileForTestEmail($file_type, $vat_reg_id, $month_year);
        }
     
        if(count($vatregs) > 0)
        {                        
            $system = $this->commonClass->getSystemInfoLazy(); 
            $systemapi = $system->systemapi->first();     

            $client_name = '';  
            $team_user_firstname = '';        
            $team_user_designation = '';  
            $vatno = '';
            $service_start = '';
            $country = '';
            $general_periods = '';           
            $vatRegHeading = '';
            $files = [];
            $specific_file_id = '';

            //for Draft
            $sales = [];
            $purchase = [];
            foreach($vatregs as $key=>$vatreg)
            { 
              if($key == 0)
              {
                $client_name = $vatreg->client_name;
                $team_user_firstname = $vatreg->team_user_firstname;   
                $team_user_designation = $vatreg->team_user_designation;            
                $vatno = $vatreg->vatno;

                $service_start = $vatreg->service_start;
                $country = $vatreg->country;
                $general_periods = $vatreg->general_periods;
                $currency_code = $vatreg->currency_code;
                       
                if($file_type == 'draft' || $file_type == 'lock')    
                {            
                  $vatRegHeading = $country . ' ' . Carbon::parse($service_start)->format('M y') . '-' . 
                                    Carbon::parse($service_start)->addMonth(($vatreg->frequency)-1)->format('M y');

                  if($file_type == 'draft')    
                  {                  
                    if($vatreg->invoice_type == 'sale')
                        $sales[] = [                          
                            "net_amount" => $this->decryptValue($vatreg->net_amount),
                            "vat_percentage" => ($vatreg->vat_percentage == 0) ? '0%' : (round($vatreg->vat_percentage) . '%'),
                            "vat_amount" => $this->decryptValue($vatreg->vat_amount),
                            "currency_code" => $currency_code
                        ];

                    if($vatreg->invoice_type == 'purchase')
                        $purchase[] = [                          
                            "net_amount" => $this->decryptValue($vatreg->net_amount),
                            "vat_percentage" => ($vatreg->vat_percentage == 0) ? '0%' : (round($vatreg->vat_percentage) . '%'),
                            "vat_amount" => $this->decryptValue($vatreg->vat_amount),
                            "currency_code" => $currency_code
                        ];
                  }

                  if($file_type == 'lock') 
                    $payment_info = $this->commonClass->getPaymentInfo($country);
                }
                else
                {
                  if($file_type == 'dda') 
                    $vatRegHeading = Carbon::parse('01-'.$month_year)->format('M Y');
                  else
                    $vatRegHeading = $country . ' ' . Carbon::parse('01-'.$month_year)->format('M Y');
                }

                $specific_file_id = $vatreg->specific_file_id;
              }
             
              if($file_type != 'draft')
              {
                if($vatreg->folder_id != null)   
                {                   
                  $downloadUrl = $this->apiClass->loadFromOneDriveLazy($vatreg, $systemapi);

                  if(isset($downloadUrl->error))   
                  {
                    if($file_type == 'documents')           
                      $files[$key] = ['text' => $file_type_title . " for " . $vatreg->doc_type,'url' => '']; 
                    else if($file_type == 'lock')           
                      $files[$key] = ['text' => "Receipt for " . $vatRegHeading ,'url' => '']; 
                    else
                      $files[$key] = ['text' => $file_type_title . " for " . Carbon::parse('01-'.$month_year)->format('M Y'),'url' => ''];
                  } 
                  else  
                  {
                    if($file_type == 'documents')           
                      $files[$key] = ['text' => $file_type_title . " for " . $vatreg->doc_type,'url' => $downloadUrl]; 
                    else if($file_type == 'lock')  
                      $files[$key] = ['text' => "Receipt for " . $vatRegHeading,'url' => $downloadUrl];  
                    else
                      $files[$key] = ['text' => $file_type_title . " for " . Carbon::parse('01-'.$month_year)->format('M Y'),'url' => $downloadUrl];
                  } 
                }
                else
                {
                  if($file_type != 'documents')                
                    $files[$key] = ['text' => "No doc uploaded for " . Carbon::parse('01-'.$month_year)->format('M Y'), 'url' =>''];
                }
              }
            }

            //Get send_to client names
            $client_user = $this->commonClass->getUserNameBasedOnEmail($send_to);

            $data = [                           
              'subject' => $vatRegHeading,  
              'lang' => $client_user->lang,
              'app_name' => config('app.name'),
              'client' => [
                'client_name' => $client_name,
                'client_firstname' => $client_user->firstname,
                'client_lastname' => $client_user->lastname,
                'team_user_firstname' => $team_user_firstname,
                'team_user_designation' => $team_user_designation
              ],                 
              'message' => $email_message,
              'attachment' => $files, 
              'align' => 'left'
            ];
            
            if($file_type == 'draft')
            {
              $data['client']['currency_code'] = $currency_code;
              $data['client']['sale'] = $sales;
              $data['client']['purchase'] = $purchase;   
             
              $data['url'] = URL::temporarySignedRoute('client.vat.email.confirm', now()->addWeeks(2), ['vat_reg_id' => $vat_reg_id]);
            }
            else if($file_type == 'lock')
            {    
              if($country == 'NO')          
                $data['subject'] = __('Copy of recipt and paymentinformation for Norwegian MVA '.$request->payment_date, [], $data['lang']); 
              else
                $data['subject'] = __('Reported today - Payable amount to be registered on authorities Account:'.$request->payment_date, [], $data['lang']); 

              $data['country'] = $country;
              $data['vat_heading'] = $vatRegHeading;
              $data['payment_date'] = $request->payment_date;

              $data['payment_info'] = [               
                'bankname' => $payment_info->bankname,
                'address' => $payment_info->address,
                'city' => $payment_info->city,
                'country' => $payment_info->country,
                'postcode' => $payment_info->postcode,
                'sortcode' => $payment_info->sortcode,
                'accountno' => $payment_info->accountno,
                'accountname' => $payment_info->accountname,
                'paymentref' => $payment_info->paymentref,
                'bic' => $payment_info->bic,
                'iban' => $payment_info->iban
              ]; 
            }            

            //Store email_note in table for timeline
            if($email_message != "")
            {              
              $email_note = FilesEmailNote::updateOrCreate(                          
                            [     
                              'vat_reg_id' => $vat_reg_id,
                              'file_type' => $file_type,
                              'file_id' => $specific_file_id,
                              'email_note' => $email_message,                                       
                              'created_by' => $this->authUser->user_id,
                            ]); 
            }

            if($file_type == 'pivs')  
              $email_data = new PivsEmail($data);
            else if($file_type == 'documents')
              $email_data = new DocumentsEmail($data);
            else if($file_type == 'c79')
              $email_data = new C79Email($data);
            else if($file_type == 'cas')
              $email_data = new CashAccountStatementEmail($data);
            else if($file_type == 'dda')
              $email_data = new DutyDefermentAccountEmail($data);
            else if($file_type == 'ivf')
              $email_data = new ImportVatFileEmail($data);
            else if($file_type == 'draft')
              $email_data = new DraftEmail($data);
            else if($file_type == 'lock')
            {
              if($country == 'GB')
                $email_data = new LockGBEmail($data);
              else
                $email_data = new LockNOEmail($data);
            }

            $mailsent = Mail::to($send_to)
              //->cc($chk_cc)
              ->send($email_data);
            
            if($mailsent)
            {                
              $email_headers = $mailsent->getOriginalMessage()->getHeaders();
              $message_id = $email_headers->getHeaderBody('X-SES-Message-ID');                     
              if ($message_id)
              {
                $email_sent_to = $email_headers->getHeaderBody('To');
                $email_sent_ccs = $email_headers->getHeaderBody('Cc');
                $email_sent_subject = $email_headers->getHeaderBody('Subject');

                $emailNotification = new EmailNotification;
                $emailNotification->vat_reg_id = $vat_reg_id; 
                $emailNotification->message_id = $message_id;   
                $emailNotification->subject = $email_sent_subject;     
                $emailNotification->name = ($client_user) ? $client_user->firstname . ' ' . $client_user->lastname : '';                
                $emailNotification->email = ($email_sent_to) ? $email_sent_to[0]->getAddress() : '';     
                $emailNotification->sent_by = $this->authUser->user_id;   
                
                $emailNotification->save(); 

                if($chk_cc)
                {
                  foreach($chk_cc as $cc)
                  {   
                    //Get cc client names
                    $client_user_cc = $this->commonClass->getUserNameBasedOnEmail($cc);
                    $data['lang'] = $client_user_cc->lang;
                    $data['client']['client_firstname'] = $client_user_cc->firstname;
                    $data['client']['client_lastname'] = $client_user_cc->lastname;                   

                    //Email markdown        
                    if($file_type == 'pivs')                  
                      $email_data_cc = new PivsEmail($data);
                    else if($file_type == 'documents')                  
                      $email_data_cc = new DocumentsEmail($data);
                    else if($file_type == 'c79')
                      $email_data_cc = new C79Email($data);
                    else if($file_type == 'cas')
                      $email_data_cc = new CashAccountStatementEmail($data);
                    else if($file_type == 'dda')
                      $email_data_cc = new DutyDefermentAccountEmail($data);
                    else if($file_type == 'ivf')
                      $email_data_cc = new ImportVatFileEmail($data);
                    else if($file_type == 'draft')
                      $email_data_cc = new DraftEmail($data);
                    else if($file_type == 'lock')
                    {
                      if($country == 'GB')
                        $email_data_cc = new LockGBEmail($data);
                      else
                        $email_data_cc = new LockNOEmail($data);
                    }

                    $mailsent_cc = Mail::to($cc)                               
                                  ->send($email_data_cc);

                    if($mailsent_cc)
                    {
                      $email_headers_cc = $mailsent_cc->getOriginalMessage()->getHeaders();
                      $message_id_cc = $email_headers_cc->getHeaderBody('X-SES-Message-ID');                     
                      if ($message_id_cc)
                      {
                        $email_sent_to = $email_headers_cc->getHeaderBody('To');
                        //$email_sent_ccs = $email_headers_cc->getHeaderBody('Cc');
                        $email_sent_subject = $email_headers_cc->getHeaderBody('Subject');

                        $emailNotificationCC = new EmailNotification;
                        $emailNotificationCC->vat_reg_id = $vat_reg_id; 
                        $emailNotificationCC->message_id = $message_id_cc;   
                        $emailNotificationCC->subject = $email_sent_subject; 
                        $emailNotificationCC->send_type = 'cc';     
                        $emailNotificationCC->name = ($client_user_cc) ? $client_user_cc->firstname . ' ' . $client_user_cc->lastname : '';                
                        $emailNotificationCC->email = ($email_sent_to) ? $email_sent_to[0]->getAddress() : '';     
                        $emailNotificationCC->sent_by = $this->authUser->user_id;   
                        
                        $emailNotificationCC->save();
                      }
                    } // if email sent
                  }//for CC  
                }//if CC                 
              }

              if(!$send_test)              
              {
                if($file_type == 'draft')
                {
                  $approved_by = User::where('email', $send_to)->first();  
             
                  $updateStatus = VATRegistration::where('id', $vat_reg_id) 
                                    ->where('status', 2)         
                                    ->update(
                                      [
                                            'status' => 3, 
                                            'email_by' => $this->authUser->user_id, 
                                            'email_at' => now(),
                                            'approved_by' => $approved_by->id, 
                                      ]
                                    );//From 'Draft' to 'Pending Review' (sent email to selected client users)  

                  $this->commonClass->addLog($this->authUser, 'vatreturn-draft-email', 
                    [
                      'Client Name' => $client_name,
                      'VAT Reg' => $data['subject'],
                      'Client User' => $send_to, 
                      'Client User(CC)' => $chk_cc
                    ]
                  );                  
                } 
                else if($file_type == 'lock')
                {
                  $updateStatus = VATRegistration::where('id', $vat_reg_id) 
                                ->where('status', 5)         
                                ->update(
                                  [
                                        'status' => 6, 
                                        'payment_date' => Carbon::parse($request->payment_date)->format('Y-m-d'),
                                        'locked_by' => $this->authUser->user_id, 
                                        'locked_at' => now() 
                                  ]
                                );//From 'Submitted' to 'Lock' (sent email to selected client users)  

                  $this->commonClass->addLog($this->authUser, 'vatreturn-lock-email', 
                    [
                      'Client Name' => $client_name, 
                      'VAT Reg' => $data['vat_heading'], 
                      'Client User' => $send_to, 
                      'Client User(CC)' => $chk_cc
                    ]
                  );              
                }
                else
                {         
                  if($no_docs)                                
                    $this->commonClass->addLog($this->authUser, 'file-nodoc-email', 
                      [
                        'Client Name' => $client_name, 
                        'VAT Reg' => $data['subject'], 
                        'Client User' => $send_to, 
                        'Client User(CC)' => $chk_cc, 
                        'Month' => $month_year,
                        'file_type_title' => $file_type_title
                      ]
                    );
                  else                                        
                    $this->commonClass->addLog($this->authUser, 'file-doc-email', 
                      [
                        'Client Name' => $client_name, 
                        'VAT Reg' => $data['subject'], 
                        'Client User' => $send_to, 
                        'Client User(CC)' => $chk_cc, 
                        'Month' => $month_year,
                        'file_type_title' => $file_type_title
                      ]
                    );
                }
              }

              return response()->json([
                'success' => true,
                'message' => "Email sent"               
              ]);  
          }
          else{
            return response()->json([
              'success' => false,
              'message' => "Error in sending Email"
            ]);
          }                        
        } 
        else
          return response()->json([
              'success' => false,
              'message' => "No files attached. Cannot send email"
            ]);                                
      }
      catch (Exception $e) {
        return  $e->getMessage();
      }
    }    

    /*URL GET cancel-pending-review/$vat_reg_id */
    public function cancelPendingReview(Request $request, $vat_reg_id)
    {    
      try {          
        $vatreg = $this->commonClass->getVatRegLazy($vat_reg_id);
      
        $vatRegHeading = $vatreg->country . ' ' . Carbon::parse($vatreg->service_start)->format('M Y');

        $updateStatus = VATRegistration::where('id', $vat_reg_id) 
          ->where('status', 3)         
          ->update(
            [
              'status' => 2, 
              'email_by' => null, 
              'email_at' => null,
              'approved_by' => null, 
            ]
          );//From 'Pending Review' to 'Draft'

        $this->commonClass->addLog($this->authUser, 'vatreturn-cancel-pending-review', 
          [
            'Client Name' => $vatreg->client->client_name,
            'VAT Reg' => $vatRegHeading               
          ]
        );
        
        return response()->json([
          'success' => true,
          'message' => "Pending Review Cancelled"       
        ]);                  
      } 
      catch (Exception $e) 
      {
        return  $e->getMessage();
      }                                  
    }

    public function queryClientForFile($file_type, $file_id)
    {   
      try 
      {      
        $query = VATRegistration::leftJoin('dv_clients', function($join) {
                      $join->on('dv_vat_registration.client_id', '=', 'dv_clients.id');                      
                    });

        $specificTableQuery = $query;
        if($file_type == 'pivs')  
        {                  
          $specificTableQuery = $query->rightJoin('dv_pivs_files', function($join) {
                                  $join->on('dv_pivs_files.vat_reg_id', '=', 'dv_vat_registration.id');                     
                                })
                                ->select('dv_clients.id AS client_id','dv_clients.client_name','dv_clients.lrep_email', 'dv_clients.vatno', 'dv_vat_registration.*', 'dv_pivs_files.folder_id', 'dv_pivs_files.file_id', 'dv_pivs_files.file_name', 'dv_pivs_files.file_size', 'dv_pivs_files.month_year', 'dv_pivs_files.status AS pivs_status')                    
                                ->where('dv_pivs_files.id', $file_id);
        }
        else if($file_type == 'documents' || $file_type == 'c79')
        {
          $specificTableQuery = $query->rightJoin('dv_documents', function($join) {
                                  $join->on('dv_documents.vat_reg_id', '=', 'dv_vat_registration.id');                     
                                })      
                                ->select('dv_clients.id AS client_id','dv_clients.client_name','dv_clients.lrep_email', 'dv_clients.vatno', 'dv_vat_registration.*', 'dv_documents.folder_id', 'dv_documents.file_id', 'dv_documents.file_name', 'dv_documents.file_size', 'dv_documents.doc_type', 'dv_documents.doc_numbers')                    
                                ->where('dv_documents.id', $file_id);
        }
        else if($file_type == 'cas')
        {
          $specificTableQuery = $query->rightJoin('dv_cash_acc_stmt_files', function($join) {
                                  $join->on('dv_cash_acc_stmt_files.vat_reg_id', '=', 'dv_vat_registration.id');                     
                                })      
                                ->select('dv_clients.id AS client_id','dv_clients.client_name','dv_clients.lrep_email', 'dv_clients.vatno', 'dv_vat_registration.*', 'dv_cash_acc_stmt_files.folder_id', 'dv_cash_acc_stmt_files.file_id', 'dv_cash_acc_stmt_files.file_name', 'dv_cash_acc_stmt_files.file_size', 'dv_cash_acc_stmt_files.month_year', 'dv_cash_acc_stmt_files.status AS cash_account_statement_status')                    
                                ->where('dv_cash_acc_stmt_files.id', $file_id);
        }
        else if($file_type == 'dda')
        {
          $specificTableQuery = $query->rightJoin('dv_duty_defer_acc_files', function($join) {
                                  $join->on('dv_duty_defer_acc_files.vat_reg_id', '=', 'dv_vat_registration.id');                     
                                })      
                                ->select('dv_clients.id AS client_id','dv_clients.client_name','dv_clients.lrep_email', 'dv_clients.vatno', 'dv_vat_registration.*', 'dv_duty_defer_acc_files.folder_id', 'dv_duty_defer_acc_files.file_id', 'dv_duty_defer_acc_files.file_name', 'dv_duty_defer_acc_files.file_size', 'dv_duty_defer_acc_files.month_year', 'dv_duty_defer_acc_files.status AS duty_deferment_account_status')                    
                                ->where('dv_duty_defer_acc_files.id', $file_id);
        }
        else if($file_type == 'ivf')
        {
          $specificTableQuery = $query->rightJoin('dv_import_vat_files', function($join) {
                                  $join->on('dv_import_vat_files.vat_reg_id', '=', 'dv_vat_registration.id');                     
                                })     
                                ->select('dv_clients.id AS client_id','dv_clients.client_name','dv_clients.lrep_email', 'dv_clients.vatno', 'dv_vat_registration.*', 'dv_import_vat_files.folder_id', 'dv_import_vat_files.file_id', 'dv_import_vat_files.file_name', 'dv_import_vat_files.file_size', 'dv_import_vat_files.month_year', 'dv_import_vat_files.fee_number', 'dv_import_vat_files.statistical_number')                    
                                ->where('dv_import_vat_files.id', $file_id);
        }
        else if($file_type == 'ci')
        {
          $specificTableQuery = $query->rightJoin('dv_commercial_invoice_files', function($join) {
                                  $join->on('dv_commercial_invoice_files.vat_reg_id', '=', 'dv_vat_registration.id');                     
                                })     
                                ->select('dv_clients.id AS client_id','dv_clients.client_name','dv_clients.lrep_email', 'dv_clients.vatno', 'dv_vat_registration.*', 'dv_commercial_invoice_files.folder_id', 'dv_commercial_invoice_files.file_id', 'dv_commercial_invoice_files.file_name', 'dv_commercial_invoice_files.file_size', 'dv_commercial_invoice_files.sale_invoice_nos', 'dv_commercial_invoice_files.invoice_count', 'dv_commercial_invoice_files.invoice_total')                    
                                ->where('dv_commercial_invoice_files.id', $file_id);
        }

        $client = $specificTableQuery->first();

        return $client;
      }//try
      catch (Exception $e) {
        return  $e->getMessage();
      }//catch
    }    

    public function queryClientUsersForFile($file_type, $vat_reg_id, $month_year, $re_send)
    {   
      try 
      {              
        $query = VATRegistration::leftJoin('dv_clients', function($join) {
                      $join->on('dv_vat_registration.client_id', '=', 'dv_clients.id');                      
                    });

        if($file_type == 'pivs')  
        {                  
          $specificTableQuery = $query->rightJoin('dv_pivs_files', function($join) {
                              $join->on('dv_pivs_files.vat_reg_id', '=', 'dv_vat_registration.id');                     
                            })
                            ->leftJoin('users', function($join) {
                              $join->on('users.id', '=', 'dv_pivs_files.created_by');
                            });                            
        }
        else if($file_type == 'documents' || $file_type == 'c79')  
        {                  
          $specificTableQuery = $query->rightJoin('dv_documents', function($join) {
                                  $join->on('dv_documents.vat_reg_id', '=', 'dv_vat_registration.id');                     
                                })
                                ->leftJoin('users', function($join) {
                                  $join->on('users.id', '=', 'dv_documents.created_by');
                                });                           
        }        
        else if($file_type == 'cas')
        { 
          $specificTableQuery = $query->rightJoin('dv_cash_acc_stmt_files', function($join) {
                                  $join->on('dv_cash_acc_stmt_files.vat_reg_id', '=', 'dv_vat_registration.id');                     
                                })
                                ->leftJoin('users', function($join) {
                                  $join->on('users.id', '=', 'dv_cash_acc_stmt_files.created_by');
                                });
        }
        else if($file_type == 'dda')
        {
          $specificTableQuery = $query->rightJoin('dv_duty_defer_acc_files', function($join) {
                                  $join->on('dv_duty_defer_acc_files.vat_reg_id', '=', 'dv_vat_registration.id');                     
                                })
                                ->leftJoin('users', function($join) {
                                  $join->on('users.id', '=', 'dv_duty_defer_acc_files.created_by');
                                }); 
        }
        else if($file_type == 'ivf')
        {
          $specificTableQuery = $query->rightJoin('dv_import_vat_files', function($join) {
                                  $join->on('dv_import_vat_files.vat_reg_id', '=', 'dv_vat_registration.id');                     
                                })
                                ->leftJoin('users', function($join) {
                                  $join->on('users.id', '=', 'dv_import_vat_files.created_by');
                                }); 
        }
        else if($file_type == 'ci')
        {
          $specificTableQuery = $query->rightJoin('dv_commercial_invoice_files', function($join) {
                                  $join->on('dv_commercial_invoice_files.vat_reg_id', '=', 'dv_vat_registration.id');                     
                                })
                                ->leftJoin('users', function($join) {
                                  $join->on('users.id', '=', 'dv_commercial_invoice_files.created_by');
                                }); 
        }
        else if($file_type == 'draft')
        {
          $specificTableQuery = $query->leftJoin('dv_client_api', function($join) {
                                  $join->on('dv_client_api.client_id', '=', 'dv_clients.id');                     
                                })   
                                ->leftJoin('dv_vat_returns', function($join) {
                                  $join->on('dv_vat_returns.vat_reg_id', '=', 'dv_vat_registration.id');                     
                                })      
                                ->leftJoin('dv_user_vat_registration', function($join) {
                                  $join->on('dv_vat_registration.id', '=', 'dv_user_vat_registration.vat_reg_id');                      
                                })      
                                ->leftJoin('users', function($join) {
                                  $join->on('users.id', '=', 'dv_user_vat_registration.user_id');
                                });                              
        }
        else if($file_type == 'lock')
        {
          $specificTableQuery = $query->rightJoin('dv_receipts', function($join) {
                                  $join->on('dv_receipts.vat_reg_id', '=', 'dv_vat_registration.id');                     
                                }) 
                                ->leftJoin('users', function($join) {
                                  $join->on('users.id', '=', 'dv_vat_registration.receipt_by');
                                });                              
        }

        $userTableQuery = $specificTableQuery->leftJoin('dv_users', function($join) {
          $join->on('users.id', '=', 'dv_users.user_id');
        });

        if($file_type == 'pivs')  
        {                  
          $selectQuery = $userTableQuery->select('dv_clients.id AS client_id','dv_clients.client_name','dv_clients.lrep_email', 'dv_clients.vatno', 'dv_vat_registration.*', 'dv_pivs_files.folder_id', 'dv_pivs_files.file_id', 'dv_pivs_files.file_name', 'dv_pivs_files.file_size', 'dv_pivs_files.month_year', 'dv_pivs_files.status AS pivs_status',
                      DB::raw('(CASE                         
                        WHEN dv_vat_registration.general_periods = "monthly" THEN 1 
                        WHEN dv_vat_registration.general_periods = "bi-monthly" THEN 2
                        WHEN dv_vat_registration.general_periods = "quarterly" THEN 3 
                        WHEN dv_vat_registration.general_periods = "half-yearly" THEN 6 
                        WHEN dv_vat_registration.general_periods = "yearly" THEN 12                      
                        ELSE "" END) AS frequency'
                      ),
                      'dv_users.firstname AS team_user_firstname',
                      'dv_users.designation AS team_user_designation'
                      , 'dv_pivs_files.id AS specific_file_id'
                    )
                    ->where('dv_pivs_files.month_year', $month_year);
        }
        else if($file_type == 'documents' || $file_type == 'c79')  
        {                  
          $selectQuery = $userTableQuery->select('dv_clients.id AS client_id','dv_clients.client_name','dv_clients.lrep_email', 'dv_clients.vatno', 'dv_vat_registration.*', 'dv_documents.folder_id', 'dv_documents.file_id', 'dv_documents.file_name', 'dv_documents.file_size', 'dv_documents.doc_type', 'dv_documents.doc_numbers',
                      DB::raw('(CASE                         
                        WHEN dv_vat_registration.general_periods = "monthly" THEN 1 
                        WHEN dv_vat_registration.general_periods = "bi-monthly" THEN 2
                        WHEN dv_vat_registration.general_periods = "quarterly" THEN 3 
                        WHEN dv_vat_registration.general_periods = "half-yearly" THEN 6 
                        WHEN dv_vat_registration.general_periods = "yearly" THEN 12                      
                        ELSE "" END) AS frequency'
                      ),
                      'dv_users.firstname AS team_user_firstname',
                      'dv_users.designation AS team_user_designation' 
                      , 'dv_documents.id AS specific_file_id'
                    );                                            

          if($file_type == 'documents')                              
            $selectQuery = $selectQuery->where('dv_documents.doc_type', '<>', 'C79');
          else
            $selectQuery = $selectQuery->where('dv_documents.doc_type', 'C79')
                            ->where('dv_documents.month_year', $month_year);
        }       
        else if($file_type == 'cas')
        {
          $selectQuery = $userTableQuery->select('dv_clients.id AS client_id','dv_clients.client_name','dv_clients.lrep_email', 'dv_clients.vatno', 'dv_vat_registration.*', 'dv_cash_acc_stmt_files.folder_id', 'dv_cash_acc_stmt_files.file_id', 'dv_cash_acc_stmt_files.file_name', 'dv_cash_acc_stmt_files.file_size', 'dv_cash_acc_stmt_files.month_year', 'dv_cash_acc_stmt_files.status AS cash_account_statement_status',
                      DB::raw('(CASE                         
                        WHEN dv_vat_registration.general_periods = "monthly" THEN 1 
                        WHEN dv_vat_registration.general_periods = "bi-monthly" THEN 2
                        WHEN dv_vat_registration.general_periods = "quarterly" THEN 3 
                        WHEN dv_vat_registration.general_periods = "half-yearly" THEN 6 
                        WHEN dv_vat_registration.general_periods = "yearly" THEN 12                      
                        ELSE "" END) AS frequency'
                      ),
                      'dv_users.firstname AS team_user_firstname',
                      'dv_users.designation AS team_user_designation'
                      , 'dv_cash_acc_stmt_files.id AS specific_file_id'
                    )
                    ->where('dv_cash_acc_stmt_files.month_year', $month_year);      
        }
        else if($file_type == 'dda')
        {
          $selectQuery = $userTableQuery->select('dv_clients.id AS client_id','dv_clients.client_name','dv_clients.lrep_email', 'dv_clients.vatno', 'dv_vat_registration.*', 'dv_duty_defer_acc_files.folder_id', 'dv_duty_defer_acc_files.file_id', 'dv_duty_defer_acc_files.file_name', 'dv_duty_defer_acc_files.file_size', 'dv_duty_defer_acc_files.month_year', 'dv_duty_defer_acc_files.status AS duty_deferment_account_status',
                      DB::raw('(CASE                         
                        WHEN dv_vat_registration.general_periods = "monthly" THEN 1 
                        WHEN dv_vat_registration.general_periods = "bi-monthly" THEN 2
                        WHEN dv_vat_registration.general_periods = "quarterly" THEN 3 
                        WHEN dv_vat_registration.general_periods = "half-yearly" THEN 6 
                        WHEN dv_vat_registration.general_periods = "yearly" THEN 12                      
                        ELSE "" END) AS frequency'
                      ),
                      'dv_users.firstname AS team_user_firstname',
                      'dv_users.designation AS team_user_designation'
                      , 'dv_duty_defer_acc_files.id AS specific_file_id'
                    )
                    ->where('dv_duty_defer_acc_files.month_year', $month_year);
        }
        else if($file_type == 'ivf')
        {
          $selectQuery = $userTableQuery->select('dv_clients.id AS client_id','dv_clients.client_name','dv_clients.lrep_email', 'dv_clients.vatno', 'dv_vat_registration.*', 'dv_import_vat_files.folder_id', 'dv_import_vat_files.file_id', 'dv_import_vat_files.file_name', 'dv_import_vat_files.file_size', 'dv_import_vat_files.month_year', 'dv_import_vat_files.fee_number', 'dv_import_vat_files.statistical_number', 'dv_import_vat_files.upload_type', 'dv_import_vat_files.file_type',
                      DB::raw('(CASE                         
                        WHEN dv_vat_registration.general_periods = "monthly" THEN 1 
                        WHEN dv_vat_registration.general_periods = "bi-monthly" THEN 2
                        WHEN dv_vat_registration.general_periods = "quarterly" THEN 3 
                        WHEN dv_vat_registration.general_periods = "half-yearly" THEN 6 
                        WHEN dv_vat_registration.general_periods = "yearly" THEN 12                      
                        ELSE "" END) AS frequency'
                      ),
                      'dv_users.firstname AS team_user_firstname',
                      'dv_users.designation AS team_user_designation'
                      , 'dv_import_vat_files.id AS specific_file_id'     
                    )
                    ->where('dv_import_vat_files.month_year', $month_year);
        }
        else if($file_type == 'ci')
        {
          $selectQuery = $userTableQuery->select('dv_clients.id AS client_id','dv_clients.client_name','dv_clients.lrep_email', 'dv_clients.vatno', 'dv_vat_registration.*', 'dv_commercial_invoice_files.folder_id', 'dv_commercial_invoice_files.file_id', 'dv_commercial_invoice_files.file_name', 'dv_commercial_invoice_files.file_size', 'dv_commercial_invoice_files.sale_invoice_nos', 'dv_commercial_invoice_files.invoice_count', 'dv_commercial_invoice_files.invoice_total',
                      DB::raw('(CASE                         
                        WHEN dv_vat_registration.general_periods = "monthly" THEN 1 
                        WHEN dv_vat_registration.general_periods = "bi-monthly" THEN 2
                        WHEN dv_vat_registration.general_periods = "quarterly" THEN 3 
                        WHEN dv_vat_registration.general_periods = "half-yearly" THEN 6 
                        WHEN dv_vat_registration.general_periods = "yearly" THEN 12                      
                        ELSE "" END) AS frequency'
                      ),
                      'dv_users.firstname AS team_user_firstname',
                      'dv_users.designation AS team_user_designation'
                      , 'dv_commercial_invoice_files.id AS specific_file_id'     
                    )
                    ;                 
        }
        else if($file_type == 'draft')
        {
          $selectQuery = $userTableQuery->select('dv_clients.id AS client_id','dv_clients.client_name','dv_clients.lrep_email', 
            'dv_clients.vatno', 'dv_client_api.currency_code', 'dv_vat_registration.*', 'dv_vat_returns.*',
                      DB::raw('(CASE                         
                        WHEN dv_vat_registration.general_periods = "monthly" THEN 1 
                        WHEN dv_vat_registration.general_periods = "bi-monthly" THEN 2
                        WHEN dv_vat_registration.general_periods = "quarterly" THEN 3 
                        WHEN dv_vat_registration.general_periods = "half-yearly" THEN 6 
                        WHEN dv_vat_registration.general_periods = "yearly" THEN 12                      
                        ELSE "" END) AS frequency'
                      ),
                      'dv_users.firstname AS team_user_firstname',
                      'dv_users.designation AS team_user_designation'                     
                    );
          
          $selectQuery->where(function ($query) {
               $query->where('dv_vat_registration.status', 2)
                     ->orWhere('dv_vat_registration.status', 3);
           });
        }
        else if($file_type == 'lock')
        {
          $selectQuery = $userTableQuery->select('dv_clients.id AS client_id','dv_clients.client_name','dv_clients.lrep_email', 'dv_clients.vatno', 'dv_vat_registration.*', 'dv_receipts.*',
                      DB::raw('(CASE                         
                        WHEN dv_vat_registration.general_periods = "monthly" THEN 1 
                        WHEN dv_vat_registration.general_periods = "bi-monthly" THEN 2
                        WHEN dv_vat_registration.general_periods = "quarterly" THEN 3 
                        WHEN dv_vat_registration.general_periods = "half-yearly" THEN 6 
                        WHEN dv_vat_registration.general_periods = "yearly" THEN 12                      
                        ELSE "" END) AS frequency'
                      ),
                      'dv_users.firstname AS team_user_firstname',
                      'dv_users.designation AS team_user_designation'
                      , 'dv_receipts.id AS specific_file_id'
                    )
                    ->where('dv_vat_registration.status', 5);
        }

        $vatregs = $selectQuery->where('dv_vat_registration.id', $vat_reg_id)->get();

        return $vatregs;
      }//try
      catch (Exception $e) {
        return  $e->getMessage();
      }//catch
    }    

    public function queryClientUsersForFileForTestEmail($file_type, $vat_reg_id, $month_year)
    {   
      try 
      {              
        $query = VATRegistration::leftJoin('dv_clients', function($join) {
                      $join->on('dv_vat_registration.client_id', '=', 'dv_clients.id');                      
                    });

        $specificTableQuery = $query->leftJoin('users', function($join) {
                              $join->on('users.id', '=', 'dv_vat_registration.created_by');
                            });   

        $userTableQuery = $specificTableQuery->leftJoin('dv_users', function($join) {
          $join->on('users.id', '=', 'dv_users.user_id');
        });

        $selectQuery = $userTableQuery->select('dv_clients.id AS client_id','dv_clients.client_name','dv_clients.lrep_email', 'dv_clients.vatno', 'dv_vat_registration.*',
                      DB::raw('(CASE                         
                        WHEN dv_vat_registration.general_periods = "monthly" THEN 1 
                        WHEN dv_vat_registration.general_periods = "bi-monthly" THEN 2
                        WHEN dv_vat_registration.general_periods = "quarterly" THEN 3 
                        WHEN dv_vat_registration.general_periods = "half-yearly" THEN 6 
                        WHEN dv_vat_registration.general_periods = "yearly" THEN 12                      
                        ELSE "" END) AS frequency'
                      ),
                      'dv_users.firstname AS team_user_firstname',
                      'dv_users.designation AS team_user_designation'                      
                    );

        if($file_type == 'draft')
        {        
          $selectQuery = $selectQuery->where(function ($query) {
               $query->where('dv_vat_registration.status', 2)
                     ->orWhere('dv_vat_registration.status', 3);
           });
        }
        else if($file_type == 'lock')
        {
          $selectQuery = $selectQuery->where('dv_vat_registration.status', 5);
        }

        $vatregs = $selectQuery->where('dv_vat_registration.id', $vat_reg_id)->get();

        return $vatregs;
      }//try
      catch (Exception $e) {
        return  $e->getMessage();
      }//catch
    }        

    /*Excel - Lazy*/
    //VAT RETURN FILES
    /*URL GET vat-return/filelazy/$vat_reg_id */
    public function loadVatReturnFileLazy(Request $request)
    {
        $vat_reg_id = $request->vat_reg_id;
        $client_id = $request->client_id;
                    
        $vatreg = $this->commonClass->getSpecificVatRegQuery($vat_reg_id);          
        
        if(!isset($vatreg))        
          return response()->json(
            [
              'status' => 200,
              'view' => ''
            ]
          );          
        else
        {           
            $authUser = $this->authUser;
            $client_users = $this->commonClass->getClientUsersForEmail($client_id);

            $view = view('_partials._content._vatreturn.uploaded-excel-files-list-lazy', compact('vatreg', 'authUser', 'client_users'))->render();
                
            return response()->json(
              [
                'status' => 200,
                'view' => $view                
              ]
            );
        }
    }

    /*URL POST vat-return/filelazy/$vat_reg_id */
    public function uploadVatReturnFileToOneDriveLazy(Request $request, $vat_reg_id)
    {         
      try {                          
        $vatreg = $this->commonClass->getSpecificVatRegQuery($vat_reg_id);               
             
        $system = $this->commonClass->getSystemInfoLazy(); 
        $systemapi = $system->systemapi->first();      
              
        if(isset($vatreg))  
        {
          $vatregmain_status = $vatreg->vatregmain->status;

          if($vatregmain_status)
          {
            if($request->file('vatreturn_file'))            
              return $this->apiClass->uploadFileToOneDriveLazy($request, $vatreg, $this->authUser, $systemapi); 
          }     
          else                                            
            return false;
        }
        else                                            
          return false;
      }
      catch (Exception $e) {
        return  $e->getMessage();
      }
    }

    /*URL GET vat-return/filelazy/$file_id/download */
    public function downloadVatReturnFileFromOneDriveLazy(Request $request, $id)
    { 
      try {      
        $vatreturnfile = $this->commonClass->getVatReturnFileLazy($id);
                 
        $system = $this->commonClass->getSystemInfoLazy(); 
        $systemapi = $system->systemapi->first();
       
        $excelfile = $this->apiClass->loadFromOneDriveLazy($vatreturnfile, $systemapi);  

        if(isset($excelfile->error))   
           return '';
        else         
          return $excelfile['download_url'];
      }
      catch (Exception $e) {
        return  $e->getMessage();
      }
    }

    /*URL DELETE vat-return/filelazy/$file_id */
    public function deleteVatReturnFileFromOneDriveLazy(Request $request, $id)
    { 
      try {   
        $vatreturnfile = $this->commonClass->getVatReturnFileLazy($id);
       
        if($vatreturnfile)
        {
          $vatreg = $vatreturnfile->vatreg;
          $vat_reg_id = $vatreturnfile->vat_reg_id;
          $client = $vatreg->client;
       
          $system = $this->commonClass->getSystemInfoLazy(); 
          $systemapi = $system->systemapi->first();  
                  
          $deleteResult = $this->apiClass->deleteFromOneDriveLazy($vatreturnfile, $systemapi, 'vatreturn');   
          
          $deleteFile = VATReturnFiles::where('id', $id)->delete();    

          $deleteVatReturn = VATReturns::where('vat_reg_id', $vat_reg_id)->delete();
          $deleteInvoice = Invoices::where('vat_reg_id', $vat_reg_id)->delete();
          
          $vatRegHeading = Carbon::parse($vatreg->service_start)->format('M Y') . ' ' . $vatreg->country . ' ' . $vatreg->general_periods;        
          
          $this->commonClass->addLog($this->authUser, 'vatreturn-delete', 
            [
              'Client Name' => $client->client_name, 
              'VAT Reg' => $vatRegHeading
            ]
          );
          
          return $deleteResult;     
        }                     
      }
      catch (Exception $e) {
        return  $e->getMessage();
      }
    }
    /* --/ FILE UPLOAD -- */  

    /* -- BULK UPLOAD -- */ 
    public function bulkUploadIndex()
    {      
      $pageConfigs = $this->commonClass->getPageConfig($this->authUser);   
      
      $file_type_name = 'importvatfiles';
      
      $_with = ['client', 'client.userclient', 'client.userclient.user', 'client.userclient.user.dvuser', $file_type_name];      
      $_where = []; 
      $_whereHas = [];         
      $_orderBy = [
        'id' => 'ASC'
      ];     
      $_final = 'get'; 
      $vatregs = $this->commonClass->getLazy('vatreg', $_with, $_where, $_whereHas, $_orderBy, $_final); 
    
      return view('content.tasks.bulkupload', 
          [
              'pageConfigs' => $pageConfigs, 
              'authUser' => $this->authUser,
              'vatregs' => $vatregs                        
          ]         
      );     
    }

    public function bulkUpload(Request $request)
    {    
      try 
      {    
        $files = $request->file('file');
       
        if($files)   
        {                 
          foreach($files as $key => $file)
          {   
            if (!$file->isValid()) {
                $error = $file->getErrorMessage();               
            }

            $fileoriginalname = $file->getClientOriginalName();

            $filename = pathinfo($fileoriginalname, PATHINFO_FILENAME);
            $extension = pathinfo($fileoriginalname, PATHINFO_EXTENSION);

            $orgno_from_to = explode('_',$filename);
  
            if(count($orgno_from_to) == 3)
            {
              $org_no = $orgno_from_to[0];
              $from_date = Carbon::parse($orgno_from_to[1])->format('Y-m-d');
              $to_date = Carbon::parse($orgno_from_to[2])->format('Y-m-d');

              $month_year = Carbon::parse($from_date)->format('m-Y');

              //get VAT reg. main with country NO for organization no. 
              $_where = [
                'country' => ['operator' => '=', 'value' => 'NO'],             
              ]; 
              $vatregmains = $this->commonClass->getVatRegMainLazy(null, $_where);

              if($vatregmains)
              {
                $vatregmain_filter = $vatregmains->filter(function($vatregmain, $key) use($org_no) {
                  return $vatregmain->org_no == $org_no;
                });

                if(count($vatregmain_filter) == 0)
                {                   
                  $file_type_name = 'importvatfiles';

                  $_with = ['client', 'client.userclient', 'client.userclient.user', 'client.userclient.user.dvuser', $file_type_name];      
                  $_where = []; 
                  $_whereHas = [];         
                  $_orderBy = [
                    'id' => 'ASC'
                  ];     
                  $_final = 'get'; 
                  $vatregs = $this->commonClass->getLazy('vatreg', $_with, $_where, $_whereHas, $_orderBy, $_final); 
                  return response()->json([
                    'message'=>'no_org_no', 
                    'file' => $fileoriginalname,
                    'vatregs' => $vatregs
                  ], 400);  
                }    
                else
                {
                  $vatregmain = $vatregmain_filter->first();
                  $vatregmainid = $vatregmain->id;
                  //get VAT reg.
                  $_where = [
                    'vat_reg_main_id' => ['operator' => '=', 'value' => $vatregmainid]                   
                  ]; 
                 
                  $allvatregs = $this->commonClass->getVatRegLazy(null, $_where); 
                 
                  $vatreg_filter = $allvatregs->filter(function ($vatreg)  use ($from_date, $to_date) {
                    $frequency = $this->commonClass->getFrequency($vatreg->general_periods);      

                    $start_date = $vatreg->service_start;      
                    $end_date = Carbon::parse($vatreg->service_start)->addMonth($frequency)->format('Y-m-d');

                    return  (
                              ($from_date >= $start_date && $from_date <= $end_date) &&
                              ($to_date >= $start_date && $to_date <= $end_date)
                            )                           
                            ;
                  }); 

                  if(count($vatreg_filter) == 0)  
                  {                                     
                    $file_type_name = 'importvatfiles';
            
                    $_with = ['client', 'client.userclient', 'client.userclient.user', 'client.userclient.user.dvuser', $file_type_name];      
                    $_where = []; 
                    $_whereHas = [];         
                    $_orderBy = [
                      'id' => 'ASC'
                    ];     
                    $_final = 'get'; 
                    $vatregs = $this->commonClass->getLazy('vatreg', $_with, $_where, $_whereHas, $_orderBy, $_final); 
                    return response()->json([
                      'message'=>'no_folder', 
                      'file' => $fileoriginalname,
                      'vatregs' => $vatregs
                    ], 400); 
                  }  
                  else  
                  {
                    $vatregs = $vatreg_filter->first();
                    $vat_reg_id = $vatregs->id;                   
                    
                    $vatreg = $this->commonClass->getSpecificVatRegQuery($vat_reg_id);               
                        
                    $system = $this->commonClass->getSystemInfoLazy(); 
                    $systemapi = $system->systemapi->first();      
                                      
                    if(isset($vatreg))     
                    { 
                      $vatregmain_status = $vatreg->vatregmain->status;

                      if($vatregmain_status)
                      {
                        $request_pass = [
                          'file' => $file,                        
                          'file_type' => 'ivf',
                          'file_type_title' => 'Import VAT',
                          'month_year' => $month_year
                        ];
                        $uploadedfile = $this->apiClass->uploadFileToOneDriveLazy($request_pass, $vatreg, $this->authUser, $systemapi, 'bulk');
                       
                        $file_type_name = 'importvatfiles';
                      
                        $_with = ['client', 'client.userclient', 'client.userclient.user', 'client.userclient.user.dvuser', $file_type_name];      
                        $_where = []; 
                        $_whereHas = [];         
                        $_orderBy = [
                          'id' => 'ASC'
                        ];     
                        $_final = 'get'; 
                        $vatregs = $this->commonClass->getLazy('vatreg', $_with, $_where, $_whereHas, $_orderBy, $_final); 
                        return response()->json([
                          'message'=>'success', 
                          'file' => $fileoriginalname, 
                          'uploaded_file' => $uploadedfile,
                          'vatregs' => $vatregs
                        ], 200);
                      }//Active VAT reg. main
                      else
                      {
                        $file_type_name = 'importvatfiles';                        
                        $_with = ['client', 'client.userclient', 'client.userclient.user', 'client.userclient.user.dvuser', $file_type_name];      
                        $_where = []; 
                        $_whereHas = [];         
                        $_orderBy = [
                          'id' => 'ASC'
                        ];     
                        $_final = 'get'; 
                        $vatregs = $this->commonClass->getLazy('vatreg', $_with, $_where, $_whereHas, $_orderBy, $_final); 
                       
                        return response()->json([
                          'message'=> 'error', 
                          'err_message'=> "Inactive VAT reg.", 
                          'file' => $fileoriginalname,
                          'vatregs' => $vatregs
                        ], 400); 
                      }//InActive VAT reg. main
                    }
                  }

                }//specific org_no
              }//vatregmains with NO country
            }//org_no from file
          }//for loop                      
        }
        else
          return false;        
      }//try
      catch (\Exception $e) 
      {        
        $file_type_name = 'importvatfiles';
      
        $_with = ['client', 'client.userclient', 'client.userclient.user', 'client.userclient.user.dvuser', $file_type_name];      
        $_where = []; 
        $_whereHas = [];         
        $_orderBy = [
          'id' => 'ASC'
        ];     
        $_final = 'get'; 
        $vatregs = $this->commonClass->getLazy('vatreg', $_with, $_where, $_whereHas, $_orderBy, $_final); 
                  
        //return  $e->getMessage();
        return response()->json([
          'message'=> 'error', 
          'err_message'=> $e->getMessage(), 
          'file' => $fileoriginalname,
          'vatregs' => $vatregs
        ], 400); 
      }//catch
    }

    /*URL POST bulk-email */
    public function sendBulkEmailFileToClientUser(Request $request)
    {
      try 
      {
        $bulk_result = [];
        $selected_rows = $request->selected_rows;

        foreach($selected_rows as $key=>$row)
        {
          $vat_reg_id = $row['vat_reg_id'];
         
          $re_send = 0;

          $file_type = 'ivf';
          $file_type_title = 'Import VAT';
          
          $month_year = $row['month_year'];
         
          $users = $row['users'];
          $chk_cc = [];
          $send_to = [];
          foreach($users as $key=>$user)
          {            
            if($key == 0)
              $send_to = $user['email'];
            else
              $chk_cc[] = $user['email'];
          }
                    
          $vatregs = $this->queryClientUsersForFile($file_type, $vat_reg_id, $month_year, $re_send);

          if(count($vatregs) > 0)
          {                      
            $system = $this->commonClass->getSystemInfoLazy(); 
            $systemapi = $system->systemapi->first();      

            $client_name = '';  
            $team_user_firstname = '';        
            $team_user_designation = '';  
            $vatno = '';
            $service_start = '';
            $country = '';
            $general_periods = '';           
            $vatRegHeading = '';
            $files = [];
            $specific_file_id = '';
                       
            foreach($vatregs as $key=>$vatreg)
            { 
              if($key == 0)
              {
                $client_name = $vatreg->client_name;
                $team_user_firstname = $vatreg->team_user_firstname;   
                $team_user_designation = $vatreg->team_user_designation;            
                $vatno = $vatreg->vatno;

                $service_start = $vatreg->service_start;
                $country = $vatreg->country;
                $general_periods = $vatreg->general_periods;
                $currency_code = $vatreg->currency_code;
                       
                $vatRegHeading = $country . ' ' . Carbon::parse('01-'.$month_year)->format('M Y');

                $specific_file_id = $vatreg->specific_file_id;
              }
                         
              if($vatreg->file_type == 'pdf' || $vatreg->file_type == 'xml')   
              {
                if($vatreg->folder_id != null)   
                {                   
                  $downloadUrl = $this->apiClass->loadFromOneDriveLazy($vatreg, $systemapi);

                  if(isset($downloadUrl->error))                     
                      $files[$key] = ['text' => $file_type_title . " for " . Carbon::parse('01-'.$month_year)->format('M Y'),'url' => ''];
                  else                   
                      $files[$key] = ['text' => $file_type_title . " for " . Carbon::parse('01-'.$month_year)->format('M Y'),'url' => $downloadUrl];                  
                }
                else
                {
                  if($file_type != 'documents')                
                    $files[$key] = ['text' => "No doc uploaded for " . Carbon::parse('01-'.$month_year)->format('M Y'), 'url' =>''];
                }
              }
            }

            //Get send_to client names
            $client_user = $this->commonClass->getUserNameBasedOnEmail($send_to);

            $data = [                           
              'subject' => $vatRegHeading,  
              'lang' => $client_user->lang,
              'app_name' => config('app.name'),
              'client' => [
                'client_name' => $client_name,
                'client_firstname' => $client_user->firstname,
                'client_lastname' => $client_user->lastname,
                'team_user_firstname' => $team_user_firstname,
                'team_user_designation' => $team_user_designation
              ],                 
              'message' => '',
              'attachment' => $files, 
              'align' => 'left'
            ];                                

            if($file_type == 'ivf')
              $email_data = new ImportVatFileEmail($data);           

            // Render the markdown email content and estimte the email size with attchment
            $file_type_name = 'importvatfile';
            $markdown = new Markdown(view(), config('mail.markdown'));          
            // $emailHtml = $markdown->render('emails.'. $client_user->lang . (($client_user->lang == 'dk') ? '-' : ''). $file_type_name, compact('data'))->toHtml();
            $emailHtml = $markdown->render('emails.'. (($client_user->lang) ? (($client_user->lang == 'en') ? '' : ($client_user->lang.'-'))  : '') . $file_type_name, compact('data'))->toHtml();
            
            $estimatedSize = $this->commonClass->getEmailSizeEstimate($emailHtml, $data['attachment']);
            
            $allow_email = true;
            if ($estimatedSize > 10485760)    
            {     
              $allow_email = false;   
             
              $bulk_result[$vat_reg_id] = [
                'success' => false,
                'message' => "Email too large. Consider reducing attachment size."
              ];
            }

            if($allow_email)  
            {
              $mailsent = Mail::to($send_to)
                //->cc($chk_cc)
                ->send($email_data);
              
              if($mailsent)
              {                 
                $email_headers = $mailsent->getOriginalMessage()->getHeaders();
                $message_id = $email_headers->getHeaderBody('X-SES-Message-ID');                     
                if ($message_id)
                {
                  $email_sent_to = $email_headers->getHeaderBody('To');
                  $email_sent_subject = $email_headers->getHeaderBody('Subject');

                  $emailNotification = new EmailNotification;
                  $emailNotification->vat_reg_id = $vat_reg_id; 
                  $emailNotification->message_id = $message_id;   
                  $emailNotification->subject = $email_sent_subject;     
                  $emailNotification->name = ($client_user) ? $client_user->firstname . ' ' . $client_user->lastname : '';                
                  $emailNotification->email = ($email_sent_to) ? $email_sent_to[0]->getAddress() : '';     
                  $emailNotification->sent_by = $this->authUser->user_id;   
                  
                  $emailNotification->save();   

                  if($chk_cc)
                  {
                    foreach($chk_cc as $cc)
                    {   
                      //Get cc client names
                      $client_user_cc = $this->commonClass->getUserNameBasedOnEmail($cc);
                      $data['lang'] = $client_user_cc->lang;
                      $data['client']['client_firstname'] = $client_user_cc->firstname;
                      $data['client']['client_lastname'] = $client_user_cc->lastname;
                    
                      //Email markdown  
                      if($file_type == 'ivf')
                        $email_data_cc = new ImportVatFileEmail($data);  
                     
                      $mailsent_cc = Mail::to($cc)                               
                                    ->send($email_data_cc);

                      if($mailsent_cc)
                      {
                        $email_headers_cc = $mailsent_cc->getOriginalMessage()->getHeaders();
                        $message_id_cc = $email_headers_cc->getHeaderBody('X-SES-Message-ID');                     
                        if ($message_id_cc)
                        {
                          $email_sent_to = $email_headers_cc->getHeaderBody('To');                     
                          $email_sent_subject = $email_headers_cc->getHeaderBody('Subject');

                          $emailNotificationCC = new EmailNotification;
                          $emailNotificationCC->vat_reg_id = $vat_reg_id; 
                          $emailNotificationCC->message_id = $message_id_cc;   
                          $emailNotificationCC->subject = $email_sent_subject; 
                          $emailNotificationCC->send_type = 'cc';     
                          $emailNotificationCC->name = ($client_user_cc) ? $client_user_cc->firstname . ' ' . $client_user_cc->lastname : '';                
                          $emailNotificationCC->email = ($email_sent_to) ? $email_sent_to[0]->getAddress() : '';     
                          $emailNotificationCC->sent_by = $this->authUser->user_id;   
                          
                          $emailNotificationCC->save();
                        }
                      } // if email sent
                    }//for CC     
                  }//if CC                  
                }
                       
                $update_email_send = ImportVatFiles::where('vat_reg_id', $vat_reg_id)
                  ->where('month_year', $month_year)
                  ->update(
                    [                                        
                      'send_email' => 1
                    ]
                );
                                                                  
                  $this->commonClass->addLog($this->authUser, 'file-doc-email', 
                    [
                      'Client Name' => $client_name, 
                      'VAT Reg' => $data['subject'], 
                      'Client User' => $send_to, 
                      'Client User(CC)' => $chk_cc, 
                      'Month' => $month_year,
                      'file_type_title' => $file_type_title
                    ]
                  );                
               
                $bulk_result[$vat_reg_id] = [
                  'success' => true,
                  'message' => "Email sent for " . $client_name . " for the month of " . Carbon::parse('01-'.$month_year)->format('M Y')
                ];               
            }
            else{
              $bulk_result[$vat_reg_id] = [
                'success' => false,
                'message' => "Error in sending Email for " . $client_name . " for the month of " . Carbon::parse('01-'.$month_year)->format('M Y')
              ];             
            }  
          } // allow_email                            
        } 
        else
          $bulk_result[$vat_reg_id] = [
              'success' => false,
              'message' => "No files attached. Cannot send email"
            ];          
        }//SELECTED ROWS   

        $file_type_name = 'importvatfiles';      
        $_with = ['client', 'client.userclient', 'client.userclient.user', 'client.userclient.user.dvuser', $file_type_name];      
        $_where = []; 
        $_whereHas = [];         
        $_orderBy = [
          'id' => 'ASC'
        ];     
        $_final = 'get'; 
        $vatregs = $this->commonClass->getLazy('vatreg', $_with, $_where, $_whereHas, $_orderBy, $_final); 

        return response()->json([
          'bulk_result' => $bulk_result,
          'vatregs' => $vatregs
        ]);                                     
      }//TRY
      catch (Exception $e) 
      {
        return  $e->getMessage();
      }
    }
    /* --/ BULK UPLOAD -- */ 

    /* -- DISREGARD PERIOD -- */ 
    /* -- POST /disregard-period/{vat_reg_id} -- */  
    public function disregardPeriod(Request $request, $vat_reg_id)
    {
      try
      {  
        /* -- GET VAT REG. -- */        
        $vatreg = $this->commonClass->getVatRegLazy($vat_reg_id);
        $vatregmain_status = $vatreg->vatregmain->status;

        if($vatregmain_status)
        {
          $client = $vatreg->client;
          $vat_period = ($vatreg->frequency > 1) ? (\Carbon\Carbon::parse($vatreg->service_start)->format('M y') . '-' . \Carbon\Carbon::parse($vatreg->service_start)->addMonth(($vatreg->frequency-1))->format('M y')) : (\Carbon\Carbon::parse($vatreg->service_start)->format('M y'));
          /* --end GET VAT REG. -- */

          /* -- UPDATE VAT REG. IS_DISREGARD / IS_DISREGARD_IMPORT_RE  -- */ 
          $product_type_text = '';
          if($request->product_type == 1 || $request->product_type == 4) 
          {    
            $vatreg->is_disregard = ($vatreg->is_disregard) ? 0 : 1;
            $product_type_text = 'VAT Returns';
          }
          else if($request->product_type == 2)
          {
            $vatreg->is_disregard_import_re = ($vatreg->is_disregard_import_re) ? 0 : 1;
            $product_type_text = 'Import Reconciliation';
          }

          $vatreg->save();
          /* --end UPDATE VAT REG. IS_DISREGARD / IS_DISREGARD_IMPORT_RE  -- */  

          /* -- LOG -- */
          $this->commonClass->addLog($this->authUser, 'disregard-period',
            [
              'client' => $client->client_name,
              'period' => $vat_period,
              'product_type_text' => $product_type_text
            ]
          );
          /* --end LOG -- */

          /* -- RETURN JSON -- */
          return response()->json([
            'status' => 200,
            'message' => 'disregarded',
            'product_type_text' => $product_type_text
          ]);
          /* --end RETURN JSON -- */
        }
        else
        {
          /* -- RETURN JSON -- */
          return response()->json([
            'status' => 400,
            'message' => 'InActive VAT reg.'
          ]);
          /* --end RETURN JSON -- */
        }
      }      
      catch (\Exception $e) 
      {           
        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'error-log', 
          [
            'status' => 'Error',
            'controller' => 'VAT Registration Controller',
            'method' => 'disregardPeriod',
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
    /* --end POST /disregard-period/{vat_reg_id} -- */
    /* --/ DISREGARD PERIOD -- */ 

    /* -- EXCEL COLUMN MAPPING TEMPLATE -- */ 
    /* -- POST /excel-column-mapping-template/{vat_reg_id} -- */  
    public function excelColumnMappingTemplate(Request $request, $vat_reg_id)
    {
        try
        {   
            $file = $request->file('file');
       
            if($file)   
            {
                if (!$file->isValid()) {
                    $error = $file->getErrorMessage();               
                }

                /* UPLOAD THE ORGINAL FILE */
                $vatreg = $this->commonClass->getSpecificVatRegQuery($vat_reg_id);               
               
                $system = $this->commonClass->getSystemInfoLazy(); 
                $systemapi = $system->systemapi->first();      
                //DON'T DELETE 
                if(isset($vatreg))  
                   $original_file = $this->apiClass->uploadFileToOneDriveLazy($request, $vatreg, $this->authUser, $systemapi);
                /* UPLOAD THE ORGINAL FILE */

                $fileoriginalname = $file->getClientOriginalName();

                $filename = pathinfo($fileoriginalname, PATHINFO_FILENAME);
                $extension = pathinfo($fileoriginalname, PATHINFO_EXTENSION);

                /* -- GET EXCEL COLUMNS -- */
                $excel_columns = $this->commonClass->listExcelColumns();
                /* --/ GET EXCEL COLUMNS -- */

                // Load the spreadsheet
                $spreadsheet = IOFactory::load($file);

                // Get the first worksheet in the spreadsheet                
                $worksheet_tab_li_modal = '';
                $worksheet_tab_content_modal = '';
                $worksheets = [];
                $sheetCount = $spreadsheet->getSheetCount();                
                for ($sheet_no = 0; $sheet_no < $sheetCount; $sheet_no++) 
                {
                    $worksheet = $spreadsheet->getSheet($sheet_no);

                    $sheetName = $worksheet->getTitle();

                    $highestColumn = $worksheet->getHighestColumn();                    
                    $columnIndex = Coordinate::columnIndexFromString($highestColumn);

                    $activeSheet = ($sheet_no == 0) ? true : false;

                    $add_tabs = false;

                    $worksheet_tab_li_modal .= view('_partials._modals.modal-excel-column-template-worksheet-tab-li', compact('sheetName', 'columnIndex', 'activeSheet', 'sheet_no', 'vat_reg_id', 'add_tabs'))->render(); 

                    $worksheet_tab_content_modal .= view('_partials._modals.modal-excel-column-template-worksheet-tab-content', compact('sheetName', 'columnIndex', 'activeSheet', 'sheet_no', 'vat_reg_id', 'excel_columns', 'add_tabs'))->render();  

                    $worksheets[$i] = [
                        'sheetName' => $sheetName,
                        'columnIndex' => $columnIndex
                    ];
                }
               
                return response()->json([                
                    'status' => 200,
                    'message' => 'success',
                   
                    'worksheets' => $worksheets,
                    'vat_reg_id' => $vat_reg_id,
                    'worksheet_tab_li_modal' => $worksheet_tab_li_modal,
                    'worksheet_tab_content_modal' => $worksheet_tab_content_modal,                 
                ]);
            }
           
            /* -- RETURN JSON -- */
            return response()->json([
                'status' => 200,
                'message' => 'mapped'
            ]);
            /* --end RETURN JSON -- */
        }      
        catch (\Exception $e) 
        {           
            /* -- LOG -- */
            $this->commonClass->addLog($this->authUser, 'error-log', 
                [
                'status' => 'Error',
                'controller' => 'VAT Registration Controller',
                'method' => 'excelColumnTemplateMapping',
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
    /* --end POST /excel-column-mapping-template/{vat_reg_id} -- */  

    /* -- PUT /excel-column-mapping-template/{vat_reg_id} -- */  
    public function updateExcelTemplateId(Request $request, $vat_reg_id)
    {      
        try
        {               
            /* -- UPDATE TEMPLATE ID IN VAT REG. -- */
            $vatreg = $this->commonClass->getVatRegLazy($vat_reg_id);    
            $excel_column_template_id = $request->excel_column_template_id;
            
            $vatreg->excel_column_template_id = $excel_column_template_id;
            $vatreg->save();
            /* --end UPDATE TEMPLATE ID IN VAT REG. -- */

            /* -- GET VAT REG. -- */ 
            $vatreg = $this->commonClass->getVatRegLazy($vat_reg_id);

            $client = $vatreg->client;
            $vat_period = ($vatreg->frequency > 1) ? (\Carbon\Carbon::parse($vatreg->service_start)->format('M y') . '-' . \Carbon\Carbon::parse($vatreg->service_start)->addMonth(($vatreg->frequency-1))->format('M y')) : (\Carbon\Carbon::parse($vatreg->service_start)->format('M y'));
            $excel_column_template = $vatreg->excelcolumntemplate;
            /* --end GET VAT REG. -- */

            /* -- LOG -- */
            $this->commonClass->addLog($this->authUser, 'vatreg-excel-template-update',
              [
                'client' => $client->client_name,
                'period' => $vat_period,
                'Template Name' => $excel_column_template->name,
              ]
            );
            /* --end LOG -- */

            /* -- RETURN JSON -- */
            return response()->json([
                'status' => 200,
                'message' => 'updated'
            ]);
            /* --end RETURN JSON -- */
        }      
        catch (\Exception $e) 
        {           
            /* -- LOG -- */
            $this->commonClass->addLog($this->authUser, 'error-log', 
                [
                'status' => 'Error',
                'controller' => 'VAT Registration Controller',
                'method' => 'updateExcelTemplateId',
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
    /* --end PUT /excel-column-mapping-template/{vat_reg_id} -- */      
}
