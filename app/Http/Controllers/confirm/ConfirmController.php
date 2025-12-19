<?php

namespace App\Http\Controllers\confirm;

use App\Http\Controllers\Controller;
use App\Models\VATRegistration;
use App\Models\EmailNotification;
use \App\Classes\CommonClass;
use \App\Classes\EmailBoxApiClass;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use PDF;
use App\Events\VATReturnEvent;

class ConfirmController extends Controller
{        
    public $commonClass;
    public $emailBoxApiClass;

    public function __construct()
    {        
        $this->middleware(function ($request, $next) {           
            $this->commonClass = new CommonClass();
            $this->emailBoxApiClass =  new EmailBoxApiClass();
            
            return $next($request);
        });
    }

    public function handleAwsNotification(Request $request)
    {     
      Log::info(request()->json()->all());
      $data = $request->json()->all();

      if ($data)
      {
        if ($data['Type'] == 'SubscriptionConfirmation')
        {
          file_get_contents($data['SubscribeURL']);
        } 
        elseif ($data['Type'] == 'Notification')
        {
          $message = json_decode($data['Message'], true);
          //Log::info($message);

          if ($message == 'test'){
            return response('OK', 200);
          }
          $message_id = $message['mail']['messageId'];
          switch($message['eventType'])
          {
            case 'Bounce':
              $bounce = $message['bounce'];              
              $recipients = $bounce['bouncedRecipients'];
              foreach ($recipients as $recipient)
              {
                $emailAddress = $recipient['emailAddress'];
                $email = EmailNotification::where('message_id', $message_id)->where('email', $emailAddress)->first();
                if($email)
                {
                  $email->status = ($this->isAutoReply($message)) ? 'auto_reply' : 'bounced';
                  $email->bounced_on = Carbon::parse($bounce['timestamp'])->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
                  $email->save();

                  //Forward the Auto reply email to info@intravat.com
                  if($email->status == 'auto_reply')
                  {
                    $this->emailBoxApiClass->forwardAutoReplyEmail($emailAddress);

                    $this->commonClass->addLog(NULL, 'reminder-forwared-auto-reply'); 
                  }
                  //Forward the Auto reply email to info@intravat.com
                }
              }              
              break;

            case 'Complaint':
              $complaint = $message['complaint'];
              $recipients = $complaint['complainedRecipients'];
              foreach ($recipients as $recipient)
              {
                $emailAddress = $recipient['emailAddress'];
                $email = EmailNotification::where('message_id', $message_id)->where('email', $emailAddress)->first();
                if($email)
                {
                  $email->status = 'complaint';
                  $email->complaint_on = Carbon::parse($complaint['timestamp'])->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
                  $email->save();
                }
              }              
              break;

            case 'Open':
              $open = $message['open'];
              $email = EmailNotification::where('message_id', $message_id)->first();
              if($email)
              {
                $email->status = 'opened';
                $email->opened_on = Carbon::parse($open['timestamp'])->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
                $email->save();
              }
              break;

            case 'Click':
              $click = $message['click'];
              $email = EmailNotification::where('message_id', $message_id)->first();
              if($email)
              {
                $email->status = 'clicked';
                $email->clicked_on = Carbon::parse($click['timestamp'])->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
                $email->save();
              }
              break;

            case 'Delivery':
              $delivery = $message['delivery'];   
              $recipients = $delivery['recipients']; 
              foreach ($recipients as $recipient)
              {
                $email = EmailNotification::where('message_id', $message_id)->where('email', $recipient)->first();
                if($email)
                {
                  $email->status = 'delivered';
                  $email->delivered_on = Carbon::parse($delivery['timestamp'])->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
                  $email->save();
                }
              }
              break;  

            default:
              // Do Nothing
              break;
          }
        }
        return response('OK', 200);
      }
      return response('ERROR', 400);
    }

    private function isAutoReply(array $message): bool
    {
      /*
        if (!isset($message['mail']['headers'])) {
            return false;
        }

        foreach ($message['mail']['headers'] as $header) {
            $name = strtolower($header['name']);
            $value = strtolower($header['value']);

            // Primary auto-reply indicators
            if ($name === 'auto-submitted' && $value === 'auto-replied') return true;
            if ($name === 'x-auto-response-suppress') return true;
            if ($name === 'precedence' && $value === 'auto_reply') return true;

            // Secondary indicators (less strict)
            if (str_contains($value, 'out of office')) return true;
            if (str_contains($value, 'autoreply')) return true;
            if (str_contains($value, 'auto-reply')) return true;
            if (str_contains($value, 'vacation')) return true;
        }

        return false;
      */

      if (
        ($message['eventType'] ?? null) !== 'Bounce'
      ) {
        return false;
      }

      $type = strtolower($message['bounce']['bounceType'] ?? '');
      $subtype = strtolower($message['bounce']['bounceSubType'] ?? '');
      $diagnostic = $message['bounce']['bouncedRecipients'][0]['diagnosticCode'] ?? '';

      // Auto-replies in SES are always undetermined/undetermined
      return $type === 'undetermined'
        && $subtype === 'undetermined'
        && empty($diagnostic);      
    }

    public function handleCCTracking(Request $request)
    {
      $tracking_type = $request->query('tracking_type');
      $tracking_id = $request->query('tracking_id');
      $email = EmailNotification::where('tracking_id', $tracking_id)->first();

      // Implement logic to track open event using $token
      switch($tracking_type)
      {
        case 'open':
          if($email)
          {
            $email->status = 'opened';
            $email->opened_on = Carbon::now()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
            $email->save();

            // Return a transparent 1x1 pixel GIF to comply with email standards
            $pixel = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
            return response($pixel, 200)
                ->header('Content-Type', 'image/gif');
          }
          break;
        case 'click':
          break;  
        default:              
          break;
      }
      
    }
    
    /*URL confirm-numbers/$vat_id */
    public function confirmNumbersFromClient(Request $request, $vat_id)
    {
      try { 
        $e_message = '';
        $s_message = '';
        $w_message = '';

        if(!Auth::user())
        {
          if (!URL::signatureHasNotExpired($request))           
            $e_message = 'The URL has expired.';
        }
              
          $approved_by = VATRegistration::leftJoin('dv_clients', function($join) {
                            $join->on('dv_vat_registration.client_id', '=', 'dv_clients.id');                      
                          })  
                          ->leftJoin('dv_users AS approved_by', function($join) {
                            $join->on('dv_vat_registration.approved_by', '=', 'approved_by.user_id');                      
                          })
                          ->select('dv_clients.client_name', 'dv_clients.vatno', 'dv_vat_registration.*',
                           'approved_by.firstname AS approved_by_firstname', 
                           'approved_by.lastname AS approved_by_lastname', 'approved_by.is_deleted')      
                          ->where('dv_vat_registration.id', $vat_id)
                          ->where('dv_vat_registration.status', 3)         
                          ->first();

          if($approved_by)
          {  
            $alreadyExists = VATRegistration::leftJoin('dv_clients', function($join) {
                                $join->on('dv_vat_registration.client_id', '=', 'dv_clients.id');                      
                              })                                        
                              ->whereNotNull('dv_vat_registration.declined_reason')
                              ->where('dv_vat_registration.id', $vat_id)   
                              ->first();
                  
            if($alreadyExists)            
              $w_message = 'Numbers declined already';                          
          }
          else     
          {                    
            //return abort(404, 'Page not found.');
            $approved_by = VATRegistration::leftJoin('dv_clients', function($join) {
                            $join->on('dv_vat_registration.client_id', '=', 'dv_clients.id');                      
                          })  
                          ->leftJoin('dv_users AS approved_by', function($join) {
                            $join->on('dv_vat_registration.approved_by', '=', 'approved_by.user_id');                      
                          })
                          ->select('dv_clients.client_name', 'dv_clients.vatno', 'dv_vat_registration.*',
                           'approved_by.firstname AS approved_by_firstname', 
                           'approved_by.lastname AS approved_by_lastname', 'approved_by.is_deleted')      
                          ->where('dv_vat_registration.id', $vat_id)
                          ->where('dv_vat_registration.status', '>', 3)         
                          ->first();                         
          }

          if($approved_by)
          {
            if($approved_by->is_deleted)  
              return abort(403, 'You are not authorized to view this page as you are not the active user.');
          }

          $message = [
            'error_message' => $e_message,
            'success_message' => $s_message, 
            'warning_message' => $w_message,                 
          ]; 
                            
        $vatreg = $this->commonClass->getSpecificVatRegQuery($vat_id);         

        if($vatreg)
        {
          $vatregmain_status = $vatreg->vatregmain->status;

          if($vatregmain_status)
          {
            $client = $vatreg->client;            
            $client->approved_by = $approved_by;
          }
          else
            return abort(420, 'In active VAT Reg.');
        }
        else
          return abort(404, 'Page not found.');
                 
        $tab_name = "confirm";              
        $vat_reg_id = $vatreg->vat_reg_id; 
    
        $client_id = $client->client_id;
        $client_users = $client->userclient;  

        $vat_reg_main = $vatreg->vatregmain;
        $client_api = $vat_reg_main->clientapi;

        $vatreturns = $vatreg->vatreturns;
        $vatreturnfiles = ($vatreg->vatreturnfiles) ? $vatreg->vatreturnfiles : []; 

        $pivs_files = ($vatreg->pivs) ? $vatreg->pivs : [];  
        $c79_documents = ($vatreg->c79) ? $vatreg->c79: [];  

        $import_vat_files = ($vatreg->importvatfiles) ? $vatreg->importvatfiles : [];
        if($import_vat_files)
        {
          $import_vat_files_all = $import_vat_files;
          
          $filtered_import_vat_files = $import_vat_files_all->filter(function ($import_vat_file, $key) {         
              return $import_vat_file->file_type == 'xml'; 
          });

          $import_vat_files = $filtered_import_vat_files;          
        }

        $show_vatreturn = 0;            
        if(($client_api === null)) 
        {
            if((count($vatreturnfiles) > 0) || count($vatreturns) > 0)
                $show_vatreturn = 1;
            else
                $show_vatreturn = 0;   
        } 
        else
        {
            if(count($vatreturns) == 0)
                $show_vatreturn = 0;
            else
                $show_vatreturn = 1;  
        } 

        $currencycode = ''; 
        $currencylocale = 'en_US';        
        if($vatreg->country == "DK")
        {
            $currencycode = "DKK";
            $currencylocale = 'da_DK';
        }
        elseif($vatreg->country == "NO") 
        { 
            $currencycode = "NOK";       
            $currencylocale = 'da_DK';
        }
        elseif($vatreg->country == "SE") 
        { 
            $currencycode = "SEK";
            $currencylocale = 'sv_SE';
        }
        elseif($vatreg->country == "GB")
        {
            $currencycode = "GBP";
            $currencylocale = 'en_GB';
        }
        elseif($vatreg->country == "IN")  
        {
            $currencycode = "INR";
            $currencylocale = 'en_IN';
        }
        elseif($vatreg->country == "FR")  
        {
            $currencycode = "EUR";
            $currencylocale = 'fr_FR';
        }
        elseif($vatreg->country == "CH")  
        {
            $currencycode = "CHF";
            $currencylocale = 'fr_FR';
        }
              
        $totalnet = 0;
        $purchasetotalnet = 0;
        $salestotalnet = 0;

        $totalvat = 0;
        $purchasetotalvat = 0;
        $salestotalvat = 0;       

        if($vatreg->country == 'NO')
        {
            $sales_standard_totalvat = 0; 
            $sales_medium_totalvat = 0; 
            $sales_low_totalvat = 0;
            $sales_zero_totalvat = 0;
            $sales_fish_totalvat = 0;

            $sales_standard_totalnet = 0;
            $sales_medium_totalnet = 0;
            $sales_low_totalnet = 0;
            $sales_zero_totalnet = 0;
            $sales_fish_totalnet = 0;

            $purchases_standard_totalvat = 0; 
            $purchases_medium_totalvat = 0; 
            $purchases_low_totalvat = 0;
            $purchases_zero_totalvat = 0;
            $purchases_fish_totalvat = 0;

            $purchases_standard_totalnet = 0;
            $purchases_medium_totalnet = 0;
            $purchases_low_totalnet = 0;
            $purchases_zero_totalnet = 0;
            $purchases_fish_totalnet = 0;           
        }

        if($vatreg->country == 'NO')       
         return view('auth.vatreturn.confirm', [
          'message' => $message, 

          'vatregmain_status' => $vatregmain_status, 
          
          'vat_reg_id' => $vat_id, 
          'client' => $client, 
       
          'tab_name' => $tab_name,
          'vatreg' => $vatreg,
          'client_api' => $client_api, 
          'vatreturns' => $vatreturns,
          'pivs_files' => $pivs_files,
          'c79_documents' => $c79_documents,

          'import_vat_files' => $import_vat_files,

          'totalnet' => $totalnet,
          'purchasetotalnet' => $purchasetotalnet,
          'salestotalnet' => $salestotalnet,
          'totalvat' => $totalvat,
          'purchasetotalvat' => $purchasetotalvat,
          'salestotalvat' => $salestotalvat,

          'sales_standard_totalvat' => $sales_standard_totalvat,
          'sales_medium_totalvat' => $sales_medium_totalvat,
          'sales_low_totalvat' => $sales_low_totalvat,
          'sales_zero_totalvat' => $sales_zero_totalvat,
          'sales_fish_totalvat' => $sales_fish_totalvat,

          'sales_standard_totalnet' => $sales_standard_totalnet,
          'sales_medium_totalnet' => $sales_medium_totalnet,
          'sales_low_totalnet' => $sales_low_totalnet,
          'sales_zero_totalnet' => $sales_zero_totalnet,
          'sales_fish_totalnet' => $sales_fish_totalnet,

          'purchases_standard_totalvat' => $purchases_standard_totalvat,
          'purchases_medium_totalvat' => $purchases_medium_totalvat,
          'purchases_low_totalvat' => $purchases_low_totalvat,
          'purchases_zero_totalvat' => $purchases_zero_totalvat,
          'purchases_fish_totalvat' => $purchases_fish_totalvat,

          'purchases_standard_totalnet' => $purchases_standard_totalnet,
          'purchases_medium_totalnet' => $purchases_medium_totalnet,
          'purchases_low_totalnet' => $purchases_low_totalnet,
          'purchases_zero_totalnet' => $purchases_zero_totalnet,
          'purchases_fish_totalnet' => $purchases_fish_totalnet,

          'approved_by_firstname' => ($approved_by) ? $approved_by->approved_by_firstname : '',
          'approved_by_lastname' => ($approved_by) ? $approved_by->approved_by_lastname : ''
        ]);    
       else
        return view('auth.vatreturn.confirm', [
          'message' => $message, 

          'vatregmain_status' => $vatregmain_status, 
          
          'vat_reg_id' => $vat_id, 
          'client' => $client, 
        
          'tab_name' => $tab_name,
          'vatreg' => $vatreg,
          'client_api' => $client_api, 
          'vatreturns' => $vatreturns,
          'pivs_files' => $pivs_files,
          'c79_documents' => $c79_documents,

          'import_vat_files' => $import_vat_files,

          'totalnet' => $totalnet,
          'purchasetotalnet' => $purchasetotalnet,
          'salestotalnet' => $salestotalnet,
          'totalvat' => $totalvat,
          'purchasetotalvat' => $purchasetotalvat,
          'salestotalvat' => $salestotalvat,

          'approved_by_firstname' => ($approved_by) ? $approved_by->approved_by_firstname : '',
          'approved_by_lastname' => ($approved_by) ? $approved_by->approved_by_lastname : ''         
        ]);            
      }
      catch (Exception $e) {
        return  $e->getMessage();
      }
    }

    /*URL accept-numbers/$vat_id */
    public function acceptNumbersFromClient(Request $request, $vat_id)
    {
      try {            
        $e_message = '';
        $s_message = '';
        $w_message = '';
       
        $vatreg = VATRegistration::with('client')->where('dv_vat_registration.id', $vat_id)->first();
        $vatno = $vatreg->client->vatno;

        //$vatno = $request->company_vat;

        if(isset($request->confirm_data))
        {
          $approved_by = VATRegistration::leftJoin('dv_clients', function($join) {
                            $join->on('dv_vat_registration.client_id', '=', 'dv_clients.id');                      
                          })
                          ->leftJoin('dv_users AS approved_by_dv_users', function($join) {
                            $join->on('dv_vat_registration.approved_by', '=', 'approved_by_dv_users.user_id');                      
                          })  
                          ->where('dv_clients.vatno', $vatno)
                          ->where('dv_vat_registration.id', $vat_id)
                          ->first();

          if($approved_by)
          {            
              $updateStatus = VATRegistration::where('id', $vat_id) 
                                ->where('status', 3)         
                                ->update(
                                  [
                                        'status' => 4,                                         
                                        'approved_at' => now()
                                  ]
                                );//From 'Pending Review' to 'Ready to Submit' (after client user confirmed the numbers)
                                                      
              if($updateStatus > 0)      
              {                 
                $s_message = 'Numbers approved';                   
                
                $approved_by = VATRegistration::leftJoin('dv_clients', function($join) {
                            $join->on('dv_vat_registration.client_id', '=', 'dv_clients.id');                      
                          })
                          ->leftJoin('dv_users AS approved_by_dv_users', function($join) {
                            $join->on('dv_vat_registration.approved_by', '=', 'approved_by_dv_users.user_id');                      
                          })  
                          ->where('dv_clients.vatno', $vatno)
                          ->where('dv_vat_registration.id', $vat_id)
                          ->first();

                $vatRegHeading = Carbon::parse($approved_by->service_start)->format('M Y') . ' ' . $approved_by->country . ' ' . $approved_by->general_periods;                        
                $this->commonClass->addLog(NULL, 'vatreturn-approve-numbers', 
                  [                    
                    'VAT Reg' => $vatRegHeading
                  ]
                ); 

                event(new VATReturnEvent($vat_id, 'Numbers has been approved.'));  
              }
              else           
                $w_message = 'Numbers approved already';                                                       
          }
          else            
              $e_message = 'Invalid company VAT.';   
        }
        else            
          $e_message = 'Please check the confirm box.';

          $message = [
            'error_message' => $e_message,
            'success_message' => $s_message, 
            'warning_message' => $w_message,                 
          ]; 
                   
          return response()->json([            
            'message' => $message, 
            'vat_reg_id' => $vat_id,
            'vatno' => $vatno,
            'approved_by' => isset($approved_by) ? $approved_by : null
          ]);   
      }
      catch (Exception $e) {
        return  $e->getMessage();
      }
    }

    /*URL decline-numbers/$vat_id */
    public function declineNumbersFromClient(Request $request, $vat_id)
    {
      try {            
        $e_message = '';
        $s_message = '';
        $w_message = '';
                
        $declined_by = VATRegistration::leftJoin('dv_clients', function($join) {
                          $join->on('dv_vat_registration.client_id', '=', 'dv_clients.id');                      
                        })                        
                        ->where('dv_vat_registration.id', $vat_id)
                        ->first();

          if($declined_by)
          {           
            $alreadyExists = VATRegistration::leftJoin('dv_clients', function($join) {
                                $join->on('dv_vat_registration.client_id', '=', 'dv_clients.id');                      
                              })                                        
                              ->whereNull('dv_vat_registration.declined_reason')
                              ->where('dv_vat_registration.id', $vat_id)   
                              ->first();
                  
            if($alreadyExists)      
            {     
              $updateStatus = VATRegistration::where('id', $vat_id) 
                              ->where('status', 3)         
                              ->update(
                                [                                      
                                      'declined_at' => now(),
                                      'declined_reason' => $request->reason_for_decline_numbers
                                ]
                              );//From 'Pending Review' to 'Declined Review' (after client user declined the numbers)

              $s_message = 'Numbers declined';
             
              $declined_by = VATRegistration::leftJoin('dv_clients', function($join) {
                            $join->on('dv_vat_registration.client_id', '=', 'dv_clients.id');                      
                          })
                          ->leftJoin('dv_users AS declined_by_dv_users', function($join) {
                            $join->on('dv_vat_registration.approved_by', '=', 'declined_by_dv_users.user_id');                      
                          })                            
                          ->where('dv_vat_registration.id', $vat_id)
                          ->first();


              $vatRegHeading = Carbon::parse($declined_by->service_start)->format('M Y') . ' ' . $declined_by->country . ' ' . $declined_by->general_periods;                        
              $this->commonClass->addLog(NULL, 'vatreturn-decline-numbers', 
                [                   
                  'VAT Reg' => $vatRegHeading
                ]
              ); 

              event(new VATReturnEvent($vat_id, 'Numbers has been declined.'));  
            }
            else           
              $w_message = 'Numbers declined already';                                                       
          }
          else            
              $e_message = 'Invalid VAT Reg.';      

          $message = [
            'error_message' => $e_message,
            'success_message' => $s_message, 
            'warning_message' => $w_message,                 
          ]; 
                   
          return response()->json([            
            'message' => $message, 
            'vat_reg_id' => $vat_id,
            'declined_by' => $declined_by
          ]);   
      }
      catch (Exception $e) {
        return  $e->getMessage();
      }
    }

    /* -- POST /pdf-confirm-view/{vat_reg_id}/export -- */
    public function exportPdfConfirmView($vat_reg_id)
    {
        $logo_path= public_path() . '/assets/img/logo/intravat-logo.png';
        $logo_type=pathinfo($logo_path,PATHINFO_EXTENSION);
        $logo_data=file_get_contents($logo_path);
        $logo='data:image/'.$logo_type. ';base64,'. base64_encode($logo_data);
        
        $vatreg = $this->commonClass->getSpecificVatRegQuery($vat_reg_id); 
        $tab_name = "confirm";               
        $vat_reg_id = $vatreg->vat_reg_id; 

        $client = $vatreg->client;
        $client_id = $client->client_id;
        $client_users = $client->userclient;  

        $vat_reg_main = $vatreg->vatregmain;
        $client_api = $vat_reg_main->clientapi;

        $vatreturns = $vatreg->vatreturns;
        $vatreturnfiles = ($vatreg->vatreturnfiles) ? $vatreg->vatreturnfiles : []; 

        $pivs_files = ($vatreg->pivs) ? $vatreg->pivs : [];  
        $c79_documents = ($vatreg->c79) ? $vatreg->c79: [];  

        $import_vat_files = ($vatreg->importvatfiles) ? $vatreg->importvatfiles : [];
        if($import_vat_files)
        {
          $import_vat_files_all = $import_vat_files;
          
          $filtered_import_vat_files = $import_vat_files_all->filter(function ($import_vat_file, $key) {         
              return $import_vat_file->file_type == 'xml'; 
          });

          $import_vat_files = $filtered_import_vat_files;          
        }

        $pdf = PDF::loadView('auth.vatreturn.confirm-pdf', compact('logo', 'vatreg', 'tab_name', 'vat_reg_id', 'client', 'client_id', 'client_users', 'vat_reg_main', 'client_api', 'vatreturns', 'vatreturnfiles', 'pivs_files', 'c79_documents', 'import_vat_files'))
        ;

        return $pdf->download('confirmview.pdf'); // download pdf file         
    }
}
