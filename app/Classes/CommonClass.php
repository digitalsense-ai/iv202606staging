<?php

namespace App\Classes;

use App\Models\CargoDeclarationFiles;
use App\Models\CashAccountStatement;
use App\Models\Client;
use App\Models\ClientApi;
use App\Models\ClientCvr;
use App\Models\ClientFiles;
use App\Models\ClientQA;
use App\Models\ClientQAFiles;
use App\Models\ClientComment;
use App\Models\CommercialInvoiceFiles;
use App\Models\CompanyTeamUser;
use App\Models\CRMLead;
use App\Models\CRMQuote;
use App\Models\CRMReminder;
use App\Models\Documents;
use App\Models\DutyDefermentAccount;
use App\Models\DVUser;
use App\Models\EmailNotification;
//use App\Models\ExcelColumnTemplates;
use App\Models\AnyExcelTemplates;
use App\Models\ExchangeRates;
use App\Models\FilesEmailNote;
use App\Models\ImportReconciliationFiles;
use App\Models\ImportReconciliationSwissFiles;
use App\Models\ImportReconciliationComInvoices;
use App\Models\ImportReconciliationControlFiles;
use App\Models\ImportReconciliationControlOFiles;
use App\Models\ImportReconciliationNotes;
use App\Models\ImportReconciliationSalesInvoices;
use App\Models\ImportReconciliationSalesInvoicesData;
use App\Models\ImportVatComments;
use App\Models\ImportVatFiles;
use App\Models\Invoices;
use App\Models\InvoiceOcrPdf;
use App\Models\MailBoxFiles;
use App\Models\NotificationSettings;
use App\Models\PaymentInfo;
use App\Models\Pivs;
use App\Models\Receipt;
use App\Models\Reminder;
use App\Models\ReminderActionOption;
use App\Models\ReminderHistory;
use App\Models\SubmittingFields;
use App\Models\SubmittingFieldsNO;
use App\Models\SubmittingFieldsCH;
use App\Models\System;
use App\Models\SystemApis;
use App\Models\SystemTaskDate;
use App\Models\User;
use App\Models\UserClient;
use App\Models\UserVATRegistration;
use App\Models\VATRegistration;
use App\Models\VATRegistrationMain;
use App\Models\VATRegistrationMainAccNos;
use App\Models\VATRegistrationMainCasDdaMonths;
use App\Models\VATReturnCommentFiles;
use App\Models\VATReturnComments;
use App\Models\VATControlFiles;
use App\Models\VATControlOFiles;
use App\Models\VATReturnFiles;
use App\Models\VATReturnOFiles;
use App\Models\VATReturnNotes;
use App\Models\VATReturns;

use App\Jobs\InsertInvoices;
use App\Jobs\InsertComSalesInvoices;
use App\Events\ImportReconciliationComSalesInvoicesJobProgressEvent;
use App\Events\OcrInvoicesSyncEvent;
use App\Jobs\InsertComSalesInvoicesFromOcr;
use App\Jobs\ProcessReminderEmailJob;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;

use Spatie\PdfToText\Pdf as PdfExtract;
use App\Traits\DecryptTrait;

use \NumberFormatter;
use Str;
use Storage;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

use Mail;
use App\Mail\ReminderCasNotUploaded as ReminderCasNotUploadedEmail;
use App\Mail\ReminderDdaNotUploaded as ReminderDdaNotUploadedEmail;
use App\Mail\ReminderNoDataInFolder as ReminderNoDataInFolderEmail;
use App\Mail\ReminderPivsNotUploaded as ReminderPivsNotUploadedEmail;
use App\Mail\ReminderUploadMissed as ReminderUploadMissedEmail;
use App\Mail\ReminderGeneral as ReminderGeneral;
use App\Mail\CRMNoQuoteReminder;

use OpenAI\Laravel\Facades\OpenAI;
use HelgeSverre\ReceiptScanner\Facades\ReceiptScanner;
use HelgeSverre\ReceiptScanner\Facades\Text;
use HelgeSverre\ReceiptScanner\Enums\Model;

use GuzzleHttp\Client as GuzzleClient;

class CommonClass
{    
    use DecryptTrait;

    /* -- PAGE CONFIG -- */
    public function getPageConfig($authUser, $page = NULL)
    {      
      if($page === 'invoice' || $page === 'declaration' || $page === 'analyzepdf-search')
        $pageConfigs = ['myLayout' => 'horizontal', 'myTheme' => 'theme-default', 'menuShow' => false, 'footerShow' => false, 'contentLayout' => 'wide'];   
      else
      {       
        if($authUser->role == 'team-user')// || $authUser->role == 'client-user'
          $pageConfigs = ['myLayout' => 'horizontal', 'myTheme' => 'theme-default']; 
        else if($authUser->role == 'client-user')
          $pageConfigs = ['myTheme' => 'theme-bordered'];        
        else
          $pageConfigs = ['myTheme' => 'theme-semi-dark'];  
      }

      return $pageConfigs;                
    }
    /* --/ PAGE CONFIG -- */

    //GET Auth User
    public function getAuthUser($logged_user_id = null, $select_role = false)
    {          
      if($logged_user_id == null)
        $logged_user_id = Auth::user()->id;

      $authUser = User::leftJoin('dv_users', function($join) {
                              $join->on('users.id', '=', 'dv_users.user_id');                      
                            })                           
                            ->leftJoin('personal_access_tokens', function($join) {
                                $join->on('users.id', '=', 'personal_access_tokens.tokenable_id')
                                     ->where('personal_access_tokens.id', function($query) {
                                         $query->select(DB::raw('MAX(id)'))
                                               ->from('personal_access_tokens')
                                               ->whereColumn('tokenable_id', 'users.id');
                                     });
                            })
                            ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id') 
                            ->select('users.id AS id', 'users.id AS user_id', 'users.name AS name', 'users.email AS email',             
                             'roles.name as role', 'dv_users.lang AS lang',  'dv_users.status AS status', 'dv_users.telephone AS telephone',
                             'dv_users.firstname', 'dv_users.lastname',
                             'personal_access_tokens.token AS apitoken',
                             DB::raw('(CASE                         
                                WHEN roles.name = "super-admin" THEN "Super admin"
                                WHEN roles.name = "company-admin" THEN "Company admin"
                                WHEN roles.name = "team-user" THEN "Team user"
                                WHEN roles.name = "client-user" THEN "Client user"                                          
                                ELSE "" END) AS rolename'
                              )
                            )                            
                            ->where('users.id', $logged_user_id)                          
                            ->distinct()
                            ->get();
      
      if(count($authUser) > 1)
      {
        $authUsers = $authUser;
        if(!$select_role)
        {
          $default_role = session('current_role')->name ?? $authUser->first()->role;
          if($default_role)
          {
            $authUser = $authUsers->filter(function ($user) use ($default_role) {
                            return $user->role === $default_role;
                        })->first();          
          }
          else
            $authUser = $authUser->first();
        }
      }
      else
        $authUser = $authUser->first();
      
      if(!$select_role)
        $authUser->profile_photo_url = User::defaultProfilePhotoUrl($authUser);      
                                  
      return $authUser;
    }

    public function getFrequency($general_periods)
    {
      $frequency = 0;
      if($general_periods == "monthly")
        $frequency = 1;
      else if($general_periods == "bi-monthly")
        $frequency = 2;
      else if($general_periods == "quarterly")
        $frequency = 3;
      else if($general_periods == "half-yearly")
        $frequency = 6;
      else if($general_periods == "yearly")
        $frequency = 12;
      
      return $frequency; 
    }

    public function fileTypesData()
    {
      $fileTypes = [
        "draft" => "VAT approver",            
        "pivs" => "Postponed import VAT statement", 
        "documents" => "Documents", 
        "c79" => "C79",             
        "ivf" => "Import VAT File", 
        "cas" => "Cash Account Statement", 
        "dda" => "Duty Deferment Account",             
        "reminders" => "Reminders"
      ];

      return $fileTypes; 
    } 

    public function invoiceColumnNamesData()
    {
      $invoiceColumnNames = [           
        'taxcode' => ['index' => 3 ,'name' => 'Tax code'],
        'invoicedate' => ['index' => 4 ,'name' => 'Invoice Date'],
        'accno' => ['index' => 5 ,'name' => 'Account Number'],
        'invoiceno' => ['index' => 6 ,'name' => 'Invoice Number'],
        'currencycode' => ['index' => 7 ,'name' => 'Currency Code'], 
        'totalnet' => ['index' => 8 ,'name' => 'Total NET (invoice currency)'], 
        'vatrate' => ['index' => 9 ,'name' => 'VAT rate'], 
        'totalvat' => ['index' => 10 ,'name' => 'Total VAT (invoice currency)'], 
        'totalgross' => ['index' => 11 ,'name' => 'Total GROSS (invoice currency)'], 
        'localcurrencycode' => ['index' => 12 ,'name' => 'Local currency code'], 
        'exchangerate' => ['index' => 13 ,'name' => 'Exchange rate'], 
        'localtotalnet' => ['index' => 14 ,'name' => 'Total NET (local currency)'], 
        'localtotalvat' => ['index' => 15 ,'name' => 'Total VAT (local currency)'], 
        'localtotalgross' => ['index' => 16 ,'name' => 'Total GROSS (local currency)'], 
        'n' => ['index' => 17 ,'name' => 'N'], 
        'o' => ['index' => 18 ,'name' => 'O'], 
        'p' => ['index' => 19 ,'name' => 'P'], 
        'q' => ['index' => 20 ,'name' => 'Q'], 
        'cname' => ['index' => 21 ,'name' => 'Name'], 
        'cvatno' => ['index' => 22 ,'name' => 'VAT number (if applicable)'], 
        'cstreet' => ['index' => 23 ,'name' => 'Street'], 
        'chouseofficeno' => ['index' => 24 ,'name' => 'House and office no.'], 
        'ccity' => ['index' => 25 ,'name' => 'City'], 
        'cpostalcode' => ['index' => 26 ,'name' => 'Postal code'], 
        'ccountrycode' => ['index' => 27 ,'name' => 'Country code'], 
        'pdf' => ['index' => 28 ,'name' => 'PDF'],        
      ];

      return $invoiceColumnNames; 
    }

    //GET System Info
    public function getSystemInfo($api_name = 'Microsoft Graph', $api_env = 'Sandbox')
    {            
      $system = System::leftJoin('dv_system_apis', function($join) {
                    $join->on('dv_system.id', '=', 'dv_system_apis.system_id');                      
                  })
                  ->select('dv_system.system_name', 'dv_system_apis.*')
                  ->where('dv_system_apis.api_name', $api_name)
                  ->where('dv_system_apis.api_env', $api_env)
                  ->where('dv_system.status', 1)->first();

      return $system;                
    }

    //GET Payment Info
    public function getPaymentInfo($country)
    {      
      $payment_info = PaymentInfo::where('countrycode', $country)->first(); 

      return $payment_info;                
    }

    //GET Stats Client
    public function getStatsClient()
    {      
      $clients = Client::     
                  select(                   
                    DB::raw('count(dv_clients.status) as count, dv_clients.status')
                  )                                                    
                  ->groupBy('dv_clients.status')                 
                  ->get();

      return $clients;                
    }

    //GET Stats VAT Reg. Main
    public function getStatsVATRegMain()
    {      
      $vatregsmain = VATRegistrationMain::     
                  select(                   
                    DB::raw('count(dv_vat_registration_main.status) as count, dv_vat_registration_main.status')
                  )                                                    
                  ->groupBy('dv_vat_registration_main.status')                 
                  ->get();

      return $vatregsmain;                
    }

    //Folder Name
    public function replaceSpecialCharForFolderName($folderName)
    {           
      $string = preg_replace('/[^A-Za-z0-9\ -]/', '', $folderName);
      
      return preg_replace('/ +/', ' ', $string); 
    }

    //GET Client IDs with `dv_vat_registration` and `dv_user_vat_registration`
    public function getClientIdsFromVatReg($authUser)
    {  
      DB::statement('SET SESSION group_concat_max_len = 1000000');    
      $vatregs = VATRegistration::leftJoin('dv_user_vat_registration', function($join) {
                    $join->on('dv_vat_registration.id', '=', 'dv_user_vat_registration.vat_reg_id');                      
                  })                   
                  ->select([                           
                    DB::raw('coalesce(group_concat(distinct dv_vat_registration.client_id separator ","),"") AS client_ids'),
                  ])
                  ->where('dv_user_vat_registration.user_id', $authUser->user_id)               
                  ->first();

      $clientIds = explode(',',$vatregs->client_ids);

      return $clientIds;                
    }

    //GET Client IDs with `dv_clients` and `dvuser_client`
    public function getClientIdsForClientUser($authUser)
    {      
      DB::statement('SET SESSION group_concat_max_len = 1000000');
      $clientuserclientids = Client::leftJoin('dv_user_client', function($join) {
                    $join->on('dv_clients.id', '=', 'dv_user_client.client_id');                      
                  })                   
                  ->select([                           
                    DB::raw('coalesce(group_concat(distinct dv_user_client.client_id separator ","),"") AS client_ids'),
                  ])
                  ->where('dv_user_client.user_id', $authUser->user_id)               
                  ->first();

      $clientIds = explode(',',$clientuserclientids->client_ids);

      return $clientIds;                
    }

    //GET Client IDs with `dv_clients`
    public function getClientIdsFromClient()
    {      
      DB::statement('SET SESSION group_concat_max_len = 1000000');
      $vatregs = Client::select([                           
                      DB::raw('coalesce(group_concat(distinct dv_clients.id separator ","),"") AS client_ids'),
                  ])
                  ->first();

      $clientIds = explode(',',$vatregs->client_ids);

      return $clientIds;                
    }

    //GET API Connection 
    public function getVATRegMainApiConnection($vat_reg_main_id)
    {                       
      $api_connection = ClientApi::leftJoin('dv_vat_registration_main', function($join) {                    
                              $join->on('dv_vat_registration_main.id', '=', 'dv_client_api.vat_reg_main_id');                     
                            })   
                            ->where('dv_vat_registration_main.id', $vat_reg_main_id)     
                            ->first();

      return $api_connection;                
    }

    //GET Client User to approve numbers in email   
    public function getClientUsersForEmail($client_id, $file_type = null)
    {           
      if($file_type == null)
      {
        //With Notification Settings
        $client_users = UserClient::rightJoin('dv_notification_settings', function($join) {
                          $join->on('dv_notification_settings.user_id', '=', 'dv_user_client.user_id');
                          $join->where('dv_notification_settings.email_notification', '=', 1);
                        })  
                        ->leftJoin('dv_users', function($join) {
                          $join->on('dv_users.user_id', '=', 'dv_user_client.user_id');
                        }) 
                        ->leftJoin('users', function($join) {
                          $join->on('users.id', '=', 'dv_users.user_id');
                        })
                        ->select('dv_users.user_id', 'users.email', 'dv_users.firstname', 'dv_users.lastname')
                        ->distinct()
                        ->where('dv_user_client.client_id', $client_id)
                        ->get();
      }
      else
      {        
        //With Notification Settings
        $client_users = UserClient::rightJoin('dv_notification_settings', function($join) use ($file_type) {
                          $join->on('dv_notification_settings.user_id', '=', 'dv_user_client.user_id');
                          $join->where('dv_notification_settings.file_type', '=', $file_type);
                          $join->where('dv_notification_settings.email_notification', '=', 1);
                        })  
                        ->leftJoin('dv_users', function($join) {
                          $join->on('dv_users.user_id', '=', 'dv_user_client.user_id');
                        }) 
                        ->leftJoin('users', function($join) {
                          $join->on('users.id', '=', 'dv_users.user_id');
                        })
                        ->select('dv_users.user_id', 'users.email', 'dv_users.firstname', 'dv_users.lastname')
                        ->distinct()
                        ->where('dv_user_client.client_id', $client_id)
                        ->get();    
      }
     
      return $client_users;                
    }

    //GET Client Users for contacts
    public function getClientContacts($client_id)
    {            
      $client_users = UserClient::leftJoin('dv_users', function($join) {
                        $join->on('dv_users.user_id', '=', 'dv_user_client.user_id');
                      })  
                      ->leftJoin('users', function($join) {
                        $join->on('users.id', '=', 'dv_users.user_id');
                      }) 
                      ->select('dv_users.user_id', 'users.email', 'dv_users.firstname', 'dv_users.lastname', 
                        'dv_users.status', 'dv_users.telephone')
                      ->where('dv_user_client.client_id', $client_id)
                      ;
             
      return $client_users;                
    }

    //GET User names based on send_to email
    public function getUserNameBasedOnEmail($email)
    {      
       $client_user = User::rightJoin('dv_users', function($join) {
                      $join->on('users.id', '=', 'dv_users.user_id');
                    })                                  
                    ->select('dv_users.firstname', 'dv_users.lastname', 'dv_users.lang')
                    ->where('users.email', $email)                                   
                    ->first();

      return $client_user;                
    }

    //GET PIVS files    
    public function getPivsFiles($vat_reg_id)
    {      
      $pivs = Pivs::where('vat_reg_id', $vat_reg_id)                                       
                    ->orderBy('month_year', 'ASC')
                    ->get();

      return $pivs;                
    }

    //GET VAT Return files    
    public function getVatReturnFiles($vat_reg_id)
    {      
      $vatReturnFiles = VATReturnFiles::where('vat_reg_id', $vat_reg_id)                    
                    ->orderBy('id', 'ASC')
                    ->get();

      return $vatReturnFiles;                
    }

    //GET VAT Control files    
    public function getVatControlFiles($vat_reg_id)
    {      
      $vatControlFiles = VATControlFiles::where('vat_reg_id', $vat_reg_id)                    
                          ->orderBy('id', 'ASC')
                          ->get();

      return $vatControlFiles;                
    }

    //GET Import Reconciliation Control files    
    public function getImportReconciliationControlFiles($vat_reg_id)
    {      
      $importReconciliationControlFiles = ImportReconciliationControlFiles::where('vat_reg_id', $vat_reg_id)                    
                          ->orderBy('id', 'ASC')
                          ->get();

      return $importReconciliationControlFiles;                
    }

    //GET Documents    
    public function getVatReturnDocuments($vat_reg_id)
    {      
      $documents = Documents::where('doc_type', '<>', 'C79')
                    ->where('vat_reg_id', $vat_reg_id)                                       
                    ->orderBy('id', 'ASC')
                    ->get();

      return $documents;                
    }

    //GET C79 Document 
    public function getVatReturnC79Documents($vat_reg_id)
    {      
      $documents = Documents::where('doc_type', 'C79')
                    ->where('vat_reg_id', $vat_reg_id)                                       
                    ->orderBy('id', 'ASC')
                    ->get();

      return $documents;                
    }

    //GET Import VAT Files
    public function getVatReturnImportVatFiles($vat_reg_id)
    {      
      $importvatfiles = ImportVatFiles::where('vat_reg_id', $vat_reg_id) 
                          ->where('file_type', 'xml')                                      
                          ->orderBy('id', 'ASC')
                          ->get();     
                   
      $system = $this->getSystemInfoLazy(); 
      $systemapi = $system->systemapi->first();

      $apiClass = new ApiClass();     
      foreach($importvatfiles as $importvatfile)       
      {       
        if($importvatfile->file_id != NULL)
        {      
          $importvatfileName = $apiClass->loadFromOneDriveLazy($importvatfile, $systemapi);          
          if(isset($importvatfileName->error))   
          {

          } 
          else    
            $importvatfile->xml = $apiClass->xmlExtractByLine($importvatfile,$importvatfileName['download_url']);    
        }                    
      }

      return $importvatfiles;                
    }

    //GET Commercial Invoice Files
    public function getVatReturnCommercialInvoiceFiles($vat_reg_id)
    {      
      $commercialinvoicefiles = CommercialInvoiceFiles::where('vat_reg_id', $vat_reg_id)                           
                          ->orderBy('id', 'ASC')
                          ->get();                                   

      return $commercialinvoicefiles;                
    }

    //GET Import VAT Comments
    public function getVatReturnImportVatComments($import_vat_id, $line_no)
    {           
      $importvatcomments = ImportVatComments::where('import_vat_id', $import_vat_id)
                            ->where('line_no', $line_no)
                            ->orderBy('dv_import_vat_comments.import_vat_id', 'ASC')
                            ->orderBy('dv_import_vat_comments.line_no', 'ASC')
                            ->first();
                                 
      return $importvatcomments;
    }

    //GET Import VAT with Client
    public function getVatReturnImportVat($import_vat_id, $import_vat_line_no = null)
    {      
      $query = Client::leftJoin('dv_vat_registration', function($join) {
                            $join->on('dv_vat_registration.client_id', '=', 'dv_clients.id');
                          }) 
                          ->leftJoin('dv_import_vat_files', function($join) {
                            $join->on('dv_import_vat_files.vat_reg_id', '=', 'dv_vat_registration.id');
                          }) 
                          ->leftJoin('dv_import_vat_comments', function($join) {
                            $join->on('dv_import_vat_comments.import_vat_id', '=', 'dv_import_vat_files.id');
                          })       
                          ->where('dv_import_vat_files.id', $import_vat_id)                        
                          ;

      if($import_vat_line_no == null)
        $importvat = $query->first();
      else
        $importvat = $query->where('dv_import_vat_comments.line_no', $import_vat_line_no)->first();

      return $importvat;                
    }

    //GET Cash Account Statement files    
    public function getCashAccountStatementFiles($vat_reg_id)
    {      
      $cash_account_statement = CashAccountStatement::where('vat_reg_id', $vat_reg_id)                                       
                    ->orderBy('month_year', 'ASC')
                    ->get();

      return $cash_account_statement;                
    }

    //GET Duty Deferment Account files    
    public function getDutyDefermentAccountFiles($vat_reg_id)
    {      
      $duty_deferment_account = DutyDefermentAccount::where('vat_reg_id', $vat_reg_id)                                       
                    ->orderBy('month_year', 'ASC')
                    ->get();

      return $duty_deferment_account;                
    }  

    //GET VAT Return Timeline    
    public function getVatReturnTimeline($vat_reg_id)
    {           
      $superAdmin = DVUser::leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'dv_users.user_id')
                            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')  
                            ->where('dv_users.id', 1)->first(); 
                                                  
      $createdTimeline = VATRegistration::select(          
                              DB::raw("'secondary' AS color"),     
                              DB::raw("'left' AS direction"),         
                              DB::raw("'Draft created' AS subject"),
                              DB::raw('group_concat(DATE_FORMAT(dv_vat_registration.service_start, "%M %Y"), " " , dv_vat_registration.country, " " , dv_vat_registration.general_periods separator " ") AS message'),                              
                              DB::raw("'System' AS firstname"),
                              DB::raw("'' AS lastname"),
                              'dv_vat_registration.created_at AS created_at',
                              DB::raw("'$superAdmin->name' AS role"),
                              DB::raw("'$superAdmin->telephone' AS telephone")                          
                            )
                            ->where('dv_vat_registration.id', $vat_reg_id) 
                            ->groupBy('created_at')
                            ->get();  
      foreach($createdTimeline as $user)     
        $user->profile_photo_url = User::defaultProfilePhotoUrl($user, true);                      
      
      $draftTimeline = VATRegistration::leftJoin('dv_user_vat_registration', function($join) {
                              $join->on('dv_vat_registration.id', '=', 'dv_user_vat_registration.vat_reg_id');
                            })                              
                            ->leftJoin('dv_users', function($join) {
                              $join->on('dv_user_vat_registration.user_id', '=', 'dv_users.user_id');                      
                            })       
                            ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'dv_users.user_id')
                            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')  
                                                  
                            ->select(                              
                              DB::raw("'secondary' AS color"),     
                              DB::raw("'left' AS direction"),
                              DB::raw("'Draft assigned' AS subject"),                              
                              DB::raw("'System assigned draft' AS message"),      
                              'dv_users.firstname AS firstname', 'dv_users.lastname AS lastname',
                              'dv_user_vat_registration.created_at AS created_at', 'roles.name as role',
                              'dv_users.telephone AS telephone'                              
                            )
                            ->where('dv_vat_registration.id', $vat_reg_id)                           
                            ->get();
      foreach($draftTimeline as $user)     
        $user->profile_photo_url = User::defaultProfilePhotoUrl($user, true);                             

      $emailTimeline = VATRegistration::leftJoin('dv_users', function($join) {
                              $join->on('dv_vat_registration.email_by', '=', 'dv_users.user_id');                      
                            })       
                            ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'dv_users.user_id')
                            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')  

                            ->leftJoin('dv_users AS approved_by', function($join) {
                              $join->on('dv_vat_registration.approved_by', '=', 'approved_by.user_id');                      
                            })
                            ->leftJoin('model_has_roles AS approved_by_role', 'approved_by_role.model_id', '=', 'approved_by.user_id')
                            ->leftJoin('roles AS approved_by_roles', 'approved_by_roles.id', '=', 'approved_by_role.role_id') 
                                                  
                            ->select(                              
                              DB::raw("'secondary' AS color"),     
                              DB::raw("'left' AS direction"),
                              DB::raw("'Email sent' AS subject"),    
                              DB::raw('group_concat(dv_users.firstname, " " , dv_users.lastname, " sent email" separator " ") AS message'),                              
                              'approved_by.firstname AS firstname', 'approved_by.lastname AS lastname',
                              'dv_vat_registration.email_at AS created_at', 'roles.name as role',
                              'dv_users.telephone AS telephone'
                            )
                            ->where('dv_vat_registration.id', $vat_reg_id)  
                            ->groupBy('firstname', 'lastname', 'created_at', 'role', 'telephone')
                            ->get();
      foreach($emailTimeline as $user)     
        $user->profile_photo_url = User::defaultProfilePhotoUrl($user, true);                             
      
      $approveTimeline = VATRegistration::leftJoin('dv_users', function($join) {
                              $join->on('dv_vat_registration.approved_by', '=', 'dv_users.user_id');                      
                            })       
                            ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'dv_users.user_id')
                            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')  
                                                  
                            ->select(                              
                              DB::raw("'success' AS color"),     
                              DB::raw("'right' AS direction"),
                              DB::raw("'Numbers reviewed' AS subject"),
                              DB::raw("'Numbers approved' AS message"),      
                              'dv_users.firstname AS firstname', 'dv_users.lastname AS lastname',
                              'dv_vat_registration.approved_at AS created_at', 'roles.name as role',
                              'dv_users.telephone AS telephone',                              
                            )
                            ->where('dv_vat_registration.id', $vat_reg_id)                             
                            ->get();
      foreach($approveTimeline as $user)     
        $user->profile_photo_url = User::defaultProfilePhotoUrl($user, true);                             

      $declineTimeline = VATRegistration::leftJoin('dv_users', function($join) {
                              $join->on('dv_vat_registration.approved_by', '=', 'dv_users.user_id');                      
                            })       
                            ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'dv_users.user_id')
                            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')  
                                                  
                            ->select(                            
                              DB::raw("'success' AS color"),     
                              DB::raw("'right' AS direction"),
                              DB::raw("'Numbers declined' AS subject"),
                              'dv_vat_registration.declined_reason AS message',
                              'dv_users.firstname AS firstname', 'dv_users.lastname AS lastname',
                              'dv_vat_registration.declined_at AS created_at', 'roles.name as role',
                              'dv_users.telephone AS telephone'
                            )
                            ->where('dv_vat_registration.id', $vat_reg_id)                           
                            ->get();
      foreach($declineTimeline as $user)     
        $user->profile_photo_url = User::defaultProfilePhotoUrl($user, true);                             
                                                  
      
      $receiptTimeline = VATRegistration::leftJoin('dv_users', function($join) {
                              $join->on('dv_vat_registration.receipt_by', '=', 'dv_users.user_id');                      
                            })       
                            ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'dv_users.user_id')
                            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id') 

                            ->leftJoin('dv_receipts', 'dv_receipts.vat_reg_id', '=', 'dv_vat_registration.id')  
                                                  
                            ->select(                              
                              DB::raw("'secondary' AS color"),     
                              DB::raw("'left' AS direction"),
                              DB::raw("'Receipt submitted' AS subject"),
                              DB::raw("'Receipt uploaded' AS message"),      
                              'dv_users.firstname AS firstname', 'dv_users.lastname AS lastname',
                              'dv_vat_registration.receipt_at AS created_at', 'roles.name as role',
                              'dv_users.telephone AS telephone',                                                          
                              DB::raw("'receipt' AS filetype"),
                              'dv_receipts.id AS fileid'
                            )
                            ->where('dv_vat_registration.id', $vat_reg_id)                            
                            ->get();
      foreach($receiptTimeline as $user)     
        $user->profile_photo_url = User::defaultProfilePhotoUrl($user, true);                            

      $receiptFiles = VATRegistration::leftJoin('dv_receipts', 'dv_receipts.vat_reg_id', '=', 'dv_vat_registration.id')
                            ->select(
                              DB::raw("'receipt' AS filetype"),
                              'dv_receipts.*',
                              'dv_receipts.id AS fileid',                            
                            )
                            ->where('dv_vat_registration.id', $vat_reg_id)                              
                            ->get();
                                              
      $lockTimeline = VATRegistration::leftJoin('dv_users', function($join) {
                              $join->on('dv_vat_registration.locked_by', '=', 'dv_users.user_id');                      
                            })       
                            ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'dv_users.user_id')
                            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')  
                                                  
                            ->select(                              
                              DB::raw("'secondary' AS color"),     
                              DB::raw("'left' AS direction"), 
                              DB::raw("'Folder locked' AS subject"),
                              DB::raw("'Folder locked' AS message"),  
                              'dv_vat_registration.payment_date',    
                              'dv_users.firstname AS firstname', 'dv_users.lastname AS lastname',
                              'dv_vat_registration.locked_at AS created_at', 'roles.name as role',
                              'dv_users.telephone AS telephone'
                            )
                            ->where('dv_vat_registration.id', $vat_reg_id)                              
                            ->get(); 
      foreach($lockTimeline as $user)     
        $user->profile_photo_url = User::defaultProfilePhotoUrl($user, true);                                                 

      $vatreturnfilesTimeline = VATReturnFiles::leftJoin('dv_users', function($join) {
                              $join->on('dv_vatreturn_files.created_by', '=', 'dv_users.user_id');                      
                            })
                            ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'dv_users.user_id')
                            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')  
                            
                            ->select(                             
                              DB::raw("'secondary' AS color"),     
                              DB::raw("'left' AS direction"), 
                              DB::raw("'Excel/XML added' AS subject"),
                              
                              DB::raw('"Excel/XML uploaded " AS message'),
                              'dv_users.firstname AS firstname', 'dv_users.lastname AS lastname',
                              'dv_vatreturn_files.created_at AS created_at', 'roles.name as role',
                              'dv_users.telephone AS telephone',

                              'dv_vatreturn_files.file_name AS file_name',                                        
                              DB::raw("'vatreturnfiles' AS filetype")
                            )
                            ->where('dv_vatreturn_files.vat_reg_id', $vat_reg_id)                             
                            ->groupBy('firstname', 'lastname', 'created_at', 'role', 'telephone', 'dv_vatreturn_files.id', 'dv_vatreturn_files.file_name')
                            ->orderBy('dv_vatreturn_files.id', 'DESC')    
                            ->get();   
      foreach($vatreturnfilesTimeline as $user)     
        $user->profile_photo_url = User::defaultProfilePhotoUrl($user, true);    

      $pivsTimeline = Pivs::leftJoin('dv_users', function($join) {
                              $join->on('dv_pivs_files.created_by', '=', 'dv_users.user_id');                      
                            })
                            ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'dv_users.user_id')
                            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')  
                            
                            ->select(                             
                              DB::raw("'secondary' AS color"),     
                              DB::raw("'left' AS direction"), 
                              
                              DB::raw('(CASE                         
                                WHEN ISNULL(dv_pivs_files.file_id) THEN "Disregarded task"                                                
                                ELSE "Document added" END) AS subject'
                              ),

                              DB::raw('"Postponed import vat statement for " AS message'),
                              'dv_users.firstname AS firstname', 'dv_users.lastname AS lastname',
                              'dv_pivs_files.created_at AS created_at', 'roles.name as role',
                              'dv_users.telephone AS telephone',

                              'dv_pivs_files.file_name AS file_name',            
                              'dv_pivs_files.month_year AS monthyear',                             
                              DB::raw("'pivs' AS filetype")
                            )
                            ->where('dv_pivs_files.vat_reg_id', $vat_reg_id)                             
                            ->groupBy('firstname', 'lastname', 'created_at', 'role', 'telephone', 'dv_pivs_files.id', 'dv_pivs_files.file_name', 'dv_pivs_files.month_year')
                            ->orderBy('dv_pivs_files.id', 'DESC')    
                            ->get();   
      foreach($pivsTimeline as $user)     
        $user->profile_photo_url = User::defaultProfilePhotoUrl($user, true);                              
              
      $documentsTimeline = Documents::leftJoin('dv_users', function($join) {
                              $join->on('dv_documents.created_by', '=', 'dv_users.user_id');                      
                            })
                            ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'dv_users.user_id')
                            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')  
                            
                            ->select(                             
                              DB::raw("'secondary' AS color"),     
                              DB::raw("'left' AS direction"), 
                             
                              DB::raw('(CASE                         
                                WHEN ISNULL(dv_documents.file_id) THEN "Disregarded task"                                                
                                ELSE "Documents submitted" END) AS subject'
                              ),
                           
                              DB::raw('(CASE                         
                                WHEN ISNULL(dv_documents.file_id) THEN group_concat(dv_documents.doc_type, " not uploaded")                                               
                                ELSE group_concat(dv_documents.doc_type, " uploaded") END) AS message'
                              ),

                              'dv_users.firstname AS firstname', 'dv_users.lastname AS lastname',
                              'dv_documents.created_at AS created_at', 'roles.name as role',
                              'dv_users.telephone AS telephone',

                              'dv_documents.file_name AS file_name',            
                              'dv_documents.month_year AS monthyear',                             
                              DB::raw("'documents' AS filetype")
                            )
                            ->where('dv_documents.vat_reg_id', $vat_reg_id)                             
                            ->groupBy('firstname', 'lastname', 'created_at', 'role', 'telephone', 'dv_documents.id', 'dv_documents.file_name', 'dv_documents.month_year')
                            ->orderBy('dv_documents.id', 'DESC')    
                            ->get();  
      foreach($documentsTimeline as $user)     
        $user->profile_photo_url = User::defaultProfilePhotoUrl($user, true);   

      $importVatFilesTimeline = ImportVatFiles::leftJoin('dv_users', function($join) {
                              $join->on('dv_import_vat_files.created_by', '=', 'dv_users.user_id');                      
                            })
                            ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'dv_users.user_id')
                            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')  
                            
                            ->select(                             
                              DB::raw("'secondary' AS color"),     
                              DB::raw("'left' AS direction"), 
                            
                              DB::raw('(CASE                         
                                WHEN ISNULL(dv_import_vat_files.file_id) THEN "Disregarded task"                                                
                                ELSE "Import vat submitted" END) AS subject'
                              ),

                              DB::raw('"Import vat for " AS message'),
                              'dv_users.firstname AS firstname', 'dv_users.lastname AS lastname',
                              'dv_import_vat_files.created_at AS created_at', 'roles.name as role',
                              'dv_users.telephone AS telephone',

                              'dv_import_vat_files.file_name AS file_name',            
                              'dv_import_vat_files.month_year AS monthyear',                             
                              DB::raw("'importvatfiles' AS filetype")
                            )
                            ->where('dv_import_vat_files.vat_reg_id', $vat_reg_id)                             
                            ->groupBy('firstname', 'lastname', 'created_at', 'role', 'telephone', 'dv_import_vat_files.id', 'dv_import_vat_files.file_name', 'dv_import_vat_files.month_year')
                            ->orderBy('dv_import_vat_files.id', 'DESC')    
                            ->get();  
      foreach($importVatFilesTimeline as $user)     
        $user->profile_photo_url = User::defaultProfilePhotoUrl($user, true);   

      $importVatFileCommentsTimeline = ImportVatComments::leftJoin('dv_import_vat_files', function($join) {
                              $join->on('dv_import_vat_comments.import_vat_id', '=', 'dv_import_vat_files.id');                      
                            })
                            ->leftJoin('dv_users', function($join) {
                              $join->on('dv_import_vat_comments.created_by', '=', 'dv_users.user_id');                      
                            })
                            ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'dv_users.user_id')
                            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')  

                            ->select(                             
                              DB::raw("'secondary' AS color"),     
                              DB::raw("'left' AS direction"), 
                              DB::raw("'Import vat comment' AS subject"), 
                              'dv_import_vat_comments.comment AS message',
                              'dv_users.firstname AS firstname', 'dv_users.lastname AS lastname',
                              'dv_import_vat_comments.created_at AS created_at', 'roles.name as role',
                              'dv_users.telephone AS telephone',
                                                
                              DB::raw("'importvatcomments' AS filetype"),
                              'dv_import_vat_comments.import_vat_id AS fileid',
                              'dv_import_vat_comments.line_no AS lineno',
                              'dv_import_vat_comments.id AS comment_id'
                            )
                            ->where('dv_import_vat_files.vat_reg_id', $vat_reg_id)                                                         
                            ->groupBy('firstname', 'lastname', 'created_at', 'role', 'telephone', 'dv_import_vat_comments.id', 'dv_import_vat_comments.comment')
                            ->orderBy('dv_import_vat_comments.id', 'DESC')
                            ->get();
      foreach($importVatFileCommentsTimeline as $user)     
        $user->profile_photo_url = User::defaultProfilePhotoUrl($user, true);                              
    
      $cashAccountStatementTimeline = CashAccountStatement::leftJoin('dv_users', function($join) {
                              $join->on('dv_cash_acc_stmt_files.created_by', '=', 'dv_users.user_id');                      
                            })
                            ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'dv_users.user_id')
                            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')  
                            
                            ->select(                             
                              DB::raw("'secondary' AS color"),     
                              DB::raw("'left' AS direction"), 
                             
                              DB::raw('(CASE                         
                                WHEN ISNULL(dv_cash_acc_stmt_files.file_id) THEN "Disregarded task"                                                
                                ELSE "Document added" END) AS subject'
                              ),

                              DB::raw('"Cash account statement for " AS message'),
                              'dv_users.firstname AS firstname', 'dv_users.lastname AS lastname',
                              'dv_cash_acc_stmt_files.created_at AS created_at', 'roles.name as role',
                              'dv_users.telephone AS telephone',

                              'dv_cash_acc_stmt_files.file_name AS file_name',            
                              'dv_cash_acc_stmt_files.month_year AS monthyear',                             
                              DB::raw("'cash-account-statement' AS filetype")
                            )
                            ->where('dv_cash_acc_stmt_files.vat_reg_id', $vat_reg_id)                             
                            ->groupBy('firstname', 'lastname', 'created_at', 'role', 'telephone', 'dv_cash_acc_stmt_files.id', 'dv_cash_acc_stmt_files.file_name', 'dv_cash_acc_stmt_files.month_year')
                            ->orderBy('dv_cash_acc_stmt_files.id', 'DESC')    
                            ->get();   
      foreach($cashAccountStatementTimeline as $user)     
        $user->profile_photo_url = User::defaultProfilePhotoUrl($user, true); 

      $dutyDefermentAccountTimeline = DutyDefermentAccount::leftJoin('dv_users', function($join) {
                              $join->on('dv_duty_defer_acc_files.created_by', '=', 'dv_users.user_id');                      
                            })
                            ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'dv_users.user_id')
                            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')  
                            
                            ->select(                             
                              DB::raw("'secondary' AS color"),     
                              DB::raw("'left' AS direction"), 
                             
                              DB::raw('(CASE                         
                                WHEN ISNULL(dv_duty_defer_acc_files.file_id) THEN "Disregarded task"                                                
                                ELSE "Document added" END) AS subject'
                              ),

                              DB::raw('"Duty deferment account for " AS message'),
                              'dv_users.firstname AS firstname', 'dv_users.lastname AS lastname',
                              'dv_duty_defer_acc_files.created_at AS created_at', 'roles.name as role',
                              'dv_users.telephone AS telephone',

                              'dv_duty_defer_acc_files.file_name AS file_name',            
                              'dv_duty_defer_acc_files.month_year AS monthyear',                             
                              DB::raw("'duty-deferment-account' AS filetype")
                            )
                            ->where('dv_duty_defer_acc_files.vat_reg_id', $vat_reg_id)                             
                            ->groupBy('firstname', 'lastname', 'created_at', 'role', 'telephone', 'dv_duty_defer_acc_files.id', 'dv_duty_defer_acc_files.file_name', 'dv_duty_defer_acc_files.month_year')
                            ->orderBy('dv_duty_defer_acc_files.id', 'DESC')    
                            ->get();   
      foreach($dutyDefermentAccountTimeline as $user)     
        $user->profile_photo_url = User::defaultProfilePhotoUrl($user, true); 

      $commentsTimeline = VATReturnComments::leftJoin('dv_users', function($join) {
                              $join->on('dv_vatreturn_comments.created_by', '=', 'dv_users.user_id');                      
                            })
                            ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'dv_users.user_id')
                            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')  
                            
                            ->select(                             
                              DB::raw("'secondary' AS color"),     
                              DB::raw("'left' AS direction"), 
                              DB::raw("'Folder re-opened' AS subject"), 
                              'dv_vatreturn_comments.comment AS message',
                              'dv_users.firstname AS firstname', 'dv_users.lastname AS lastname',
                              'dv_vatreturn_comments.created_at AS created_at', 'roles.name as role',
                              'dv_users.telephone AS telephone',
                                                
                              DB::raw("'comments' AS filetype"),
                              'dv_vatreturn_comments.id AS fileid',
                            )
                            ->where('dv_vatreturn_comments.vat_reg_id', $vat_reg_id)                             
                            ->groupBy('firstname', 'lastname', 'created_at', 'role', 'telephone', 'dv_vatreturn_comments.id', 'dv_vatreturn_comments.comment')
                            ->orderBy('dv_vatreturn_comments.id', 'DESC')    
                            ->get();
      foreach($commentsTimeline as $user)     
        $user->profile_photo_url = User::defaultProfilePhotoUrl($user, true);                             

      $commentFiles = VATRegistration::leftJoin('dv_vatreturn_comments', 'dv_vatreturn_comments.vat_reg_id', '=', 'dv_vat_registration.id')
                            ->leftJoin('dv_vatreturn_comment_files', 'dv_vatreturn_comment_files.comment_id', '=', 'dv_vatreturn_comments.id')         
                            ->select(
                              DB::raw("'comments' AS filetype"),
                              'dv_vatreturn_comment_files.*', 
                              'dv_vatreturn_comment_files.comment_id AS fileid'                           
                            )
                            ->where('dv_vat_registration.id', $vat_reg_id)                              
                            ->get();
     
      $filesEmailNoteTimeline = FilesEmailNote::leftJoin('dv_users', function($join) {
                              $join->on('dv_files_email_note.created_by', '=', 'dv_users.user_id');                      
                            })
                            ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'dv_users.user_id')
                            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')  
                            
                            ->select(                             
                              DB::raw("'secondary' AS color"),     
                              DB::raw("'left' AS direction"), 
                              DB::raw("'Email note added' AS subject"), 
                              'dv_files_email_note.email_note AS message',
                              'dv_users.firstname AS firstname', 'dv_users.lastname AS lastname',
                              'dv_files_email_note.created_at AS created_at', 'roles.name as role',
                              'dv_users.telephone AS telephone',
                                                
                              DB::raw("'emailnote' AS filetype"),
                              'dv_files_email_note.id AS fileid',
                            )
                            ->where('dv_files_email_note.vat_reg_id', $vat_reg_id)                             
                            ->groupBy('firstname', 'lastname', 'created_at', 'role', 'telephone', 'dv_files_email_note.id', 'dv_files_email_note.email_note')
                            ->orderBy('dv_files_email_note.id', 'DESC')    
                            ->get();
      foreach($filesEmailNoteTimeline as $user)     
        $user->profile_photo_url = User::defaultProfilePhotoUrl($user, true); 

      $awsEmailNotificationTimeline = EmailNotification::leftJoin('dv_users', function($join) {
                              $join->on('dv_email_notifications.sent_by', '=', 'dv_users.user_id');                      
                            })       
                            ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'dv_users.user_id')
                            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
                            ->leftJoin('dv_reminder_action_option', 'dv_reminder_action_option.id', '=', 'dv_email_notifications.reminder_action_id')    
                                                  
                            ->select(       
                              DB::raw('(CASE                         
                                WHEN dv_email_notifications.status = "delivered" THEN "warning" 
                                WHEN dv_email_notifications.status = "opened" THEN "success"
                                WHEN dv_email_notifications.status = "clicked" THEN "success"
                                WHEN (dv_email_notifications.status = "bounced" OR dv_email_notifications.status = "auto_reply") THEN "danger"
                                WHEN dv_email_notifications.status = "sent" THEN "primary"
                                WHEN dv_email_notifications.status = "pending" THEN "warning"
                                WHEN dv_email_notifications.status = "complaint" THEN "danger"                      
                                ELSE "secondary" END) AS color'
                              ), 
                              DB::raw("'left' AS direction"), 
                              DB::raw('(CASE                         
                                WHEN dv_email_notifications.status = "delivered" THEN delivered_on
                                WHEN dv_email_notifications.status = "opened" THEN opened_on
                                WHEN dv_email_notifications.status = "clicked" THEN clicked_on
                                WHEN (dv_email_notifications.status = "bounced" OR dv_email_notifications.status = "auto_reply") THEN bounced_on                                
                                WHEN (dv_email_notifications.status = "sent" OR dv_email_notifications.status = "pending") THEN 
                                (CASE WHEN (sent_on IS NULL) THEN dv_email_notifications.created_at ELSE sent_on END)
                                WHEN dv_email_notifications.status = "complaint" THEN complaint_on           
                                ELSE sent_on END) AS created_at'
                              ), 
                              DB::raw('(CASE                         
                                WHEN dv_email_notifications.status = "delivered" THEN (CASE                         
                                WHEN dv_email_notifications.send_type = "cc" THEN "CC Email delivered" ELSE "Email delivered" END)
                                WHEN dv_email_notifications.status = "opened" THEN (CASE                         
                                WHEN dv_email_notifications.send_type = "cc" THEN "CC Email opened" ELSE "Email opened" END)
                                WHEN dv_email_notifications.status = "clicked" THEN (CASE                         
                                WHEN dv_email_notifications.send_type = "cc" THEN "CC Email clicked" ELSE "Email clicked" END)
                                WHEN dv_email_notifications.status = "bounced" THEN (CASE                         
                                WHEN dv_email_notifications.send_type = "cc" THEN "CC Email bounced" ELSE "Email bounced" END)
                                WHEN dv_email_notifications.status = "auto_reply" THEN (CASE                         
                                WHEN dv_email_notifications.send_type = "cc" THEN "CC Email bounced (Auto-reply)" ELSE "Email bounced (Auto-reply)" END)
                                WHEN dv_email_notifications.status = "sent" THEN (CASE                         
                                WHEN dv_email_notifications.send_type = "cc" THEN "CC Email sent" ELSE "Email sent" END)
                                WHEN dv_email_notifications.status = "pending" THEN (CASE                         
                                WHEN dv_email_notifications.send_type = "cc" THEN "CC Email sent" ELSE "Email sent" END)
                                WHEN dv_email_notifications.status = "complaint" THEN (CASE                         
                                WHEN dv_email_notifications.send_type = "cc" THEN "CC Email complaint" ELSE "Email complaint" END)                      
                                ELSE (CASE                         
                                WHEN dv_email_notifications.send_type = "cc" THEN "CC Email sent" ELSE "Email sent" END) 
                                END) AS subject'
                              ),
                              DB::raw('dv_email_notifications.subject AS additional_subject'),
                              DB::raw('(CASE                         
                                WHEN dv_email_notifications.status = "delivered" THEN 
                                  CONCAT_WS("", dv_email_notifications.name, " - <a href=\"mailto:", dv_email_notifications.email, "\">", dv_email_notifications.email, "</a><br>Email delivered, ", DATE_FORMAT(dv_email_notifications.delivered_on, "%d-%m-%Y"))

                                WHEN dv_email_notifications.status = "opened" THEN 
                                  CONCAT_WS("", dv_email_notifications.name, " - <a href=\"mailto:", dv_email_notifications.email, "\">", dv_email_notifications.email, "</a><br>Email opened, ", DATE_FORMAT(dv_email_notifications.opened_on, "%d-%m-%Y"), "<br><br>",dv_email_notifications.name, " - <a href=\"mailto:", dv_email_notifications.email, "\">", dv_email_notifications.email, "</a><br>Email delivered, ", DATE_FORMAT(dv_email_notifications.delivered_on, "%d-%m-%Y"))

                                WHEN dv_email_notifications.status = "clicked" THEN 
                                  CONCAT_WS("", dv_email_notifications.name, " - <a href=\"mailto:", dv_email_notifications.email, "\">", dv_email_notifications.email, "</a><br>Email clicked, ", DATE_FORMAT(dv_email_notifications.clicked_on, "%d-%m-%Y"), "<br><br>", dv_email_notifications.name, " - <a href=\"mailto:", dv_email_notifications.email, "\">", dv_email_notifications.email, "</a><br>Email opened, ", DATE_FORMAT(dv_email_notifications.opened_on, "%d-%m-%Y"), "<br><br>",dv_email_notifications.name, " - <a href=\"mailto:", dv_email_notifications.email, "\">", dv_email_notifications.email, "</a><br>Email delivered, ", DATE_FORMAT(dv_email_notifications.delivered_on, "%d-%m-%Y"))

                                WHEN (dv_email_notifications.status = "bounced" OR dv_email_notifications.status = "auto_reply") THEN 
                                  CONCAT_WS("", dv_email_notifications.name, " - <a href=\"mailto:", dv_email_notifications.email, "\">", dv_email_notifications.email, "</a><br>Email bounced, ", DATE_FORMAT(dv_email_notifications.bounced_on, "%d-%m-%Y"))

                                WHEN dv_email_notifications.status = "sent" THEN 
                                  CONCAT_WS("", dv_email_notifications.name, " - <a href=\"mailto:", dv_email_notifications.email, "\">", dv_email_notifications.email, "</a><br>Email delivered, ", DATE_FORMAT(dv_email_notifications.delivered_on, "%d-%m-%Y"))

                                WHEN dv_email_notifications.status = "pending" THEN 
                                  CONCAT_WS("", dv_email_notifications.name, " - <a href=\"mailto:", dv_email_notifications.email, "\">", dv_email_notifications.email, "</a><br>Email delivered, ", DATE_FORMAT(dv_email_notifications.delivered_on, "%d-%m-%Y"))

                                WHEN dv_email_notifications.status = "complaint" THEN 
                                  CONCAT_WS("", dv_email_notifications.name, " - <a href=\"mailto:", dv_email_notifications.email, "\">", dv_email_notifications.email, "</a><br>Email complaint, ", DATE_FORMAT(dv_email_notifications.complaint_on, "%d-%m-%Y"))

                                ELSE CONCAT_WS("", dv_email_notifications.name, " - <a href=\"mailto:", dv_email_notifications.email, "\">", dv_email_notifications.email, "</a><br>Email delivered, ", DATE_FORMAT(dv_email_notifications.delivered_on, "%d-%m-%Y"))

                                END) AS message'
                              ), 
                                                                                      
                              'dv_users.firstname AS firstname', 'dv_users.lastname AS lastname',
                              'roles.name as role',
                              'dv_users.telephone AS telephone',
                              'dv_reminder_action_option.action_name AS reminder_action_name'
                            )
                            ->where('dv_email_notifications.vat_reg_id', $vat_reg_id)                              
                            ->get();
      foreach($awsEmailNotificationTimeline as $user)     
        $user->profile_photo_url = User::defaultProfilePhotoUrl($user, true); 

      $timelines = array_merge(
                      $createdTimeline->toarray(), 
                      $draftTimeline->toarray(), 
                      $emailTimeline->toarray(), 
                      $approveTimeline->toarray(), 
                      $declineTimeline->toarray(), 
                      $receiptTimeline->toarray(), 
                      $lockTimeline->toarray(), 
                      $vatreturnfilesTimeline->toarray(),
                      $pivsTimeline->toarray(), 
                      $documentsTimeline->toarray(),
                      $importVatFilesTimeline->toarray(),
                      $importVatFileCommentsTimeline->toarray(),
                      $cashAccountStatementTimeline->toarray(), 
                      $dutyDefermentAccountTimeline->toarray(), 
                      $commentsTimeline->toarray(),
                      $filesEmailNoteTimeline->toarray(),
                      $awsEmailNotificationTimeline->toarray()
                   );     
      $sortedTimelines = collect($timelines)->sortByDesc('created_at')->values();
      
      $files = array_merge(
                  $receiptFiles->toarray(),
                  $commentFiles->toarray()
               );    
      $sortedFiles = collect($files)->sortByDesc('created_at')->values();

      $histories = [
        'timelines' => $sortedTimelines,
        'timelinefiles' => $sortedFiles        
      ];

      return $histories;                
    }

    //GET Import Reconciliation Timeline    
    public function getImportReconciliationTimeline($vat_reg_id)
    {           
      $superAdmin = DVUser::leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'dv_users.user_id')
                            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')  
                            ->where('dv_users.id', 1)->first(); 
                                                  
      $createdTimeline = VATRegistration::select(          
                              DB::raw("'secondary' AS color"),     
                              DB::raw("'left' AS direction"),         
                              DB::raw("'Draft created' AS subject"),
                              DB::raw('group_concat(DATE_FORMAT(dv_vat_registration.service_start, "%M %Y"), " " , dv_vat_registration.country, " " , dv_vat_registration.general_periods separator " ") AS message'),                              
                              DB::raw("'System' AS firstname"),
                              DB::raw("'' AS lastname"),
                              'dv_vat_registration.created_at AS created_at',
                              DB::raw("'$superAdmin->name' AS role"),
                              DB::raw("'$superAdmin->telephone' AS telephone")                          
                            )
                            ->where('dv_vat_registration.id', $vat_reg_id) 
                            ->groupBy('created_at')
                            ->get();  
      foreach($createdTimeline as $user)     
        $user->profile_photo_url = User::defaultProfilePhotoUrl($user, true);                      
      
      $draftTimeline = VATRegistration::leftJoin('dv_user_vat_registration', function($join) {
                              $join->on('dv_vat_registration.id', '=', 'dv_user_vat_registration.vat_reg_id');
                            })                              
                            ->leftJoin('dv_users', function($join) {
                              $join->on('dv_user_vat_registration.user_id', '=', 'dv_users.user_id');                      
                            })       
                            ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'dv_users.user_id')
                            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')  
                                                  
                            ->select(                              
                              DB::raw("'secondary' AS color"),     
                              DB::raw("'left' AS direction"),
                              DB::raw("'Draft assigned' AS subject"),                              
                              DB::raw("'System assigned draft' AS message"),      
                              'dv_users.firstname AS firstname', 'dv_users.lastname AS lastname',
                              'dv_user_vat_registration.created_at AS created_at', 'roles.name as role',
                              'dv_users.telephone AS telephone'                              
                            )
                            ->where('dv_vat_registration.id', $vat_reg_id)                           
                            ->get();
      foreach($draftTimeline as $user)     
        $user->profile_photo_url = User::defaultProfilePhotoUrl($user, true);                             
     
      $importVatFilesTimeline = ImportVatFiles::leftJoin('dv_users', function($join) {
                              $join->on('dv_import_vat_files.created_by', '=', 'dv_users.user_id');                      
                            })
                            ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'dv_users.user_id')
                            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')  
                            
                            ->select(                             
                              DB::raw("'secondary' AS color"),     
                              DB::raw("'left' AS direction"),                            
                              DB::raw('(CASE                         
                                WHEN ISNULL(dv_import_vat_files.file_id) THEN "Disregarded task"                                                
                                ELSE "Import vat submitted" END) AS subject'
                              ),

                              DB::raw('"Import vat for " AS message'),
                              'dv_users.firstname AS firstname', 'dv_users.lastname AS lastname',
                              'dv_import_vat_files.created_at AS created_at', 'roles.name as role',
                              'dv_users.telephone AS telephone',

                              'dv_import_vat_files.file_name AS file_name',            
                              'dv_import_vat_files.month_year AS monthyear',                             
                              DB::raw("'importvatfiles' AS filetype")
                            )
                            ->where('dv_import_vat_files.vat_reg_id', $vat_reg_id)                             
                            ->groupBy('firstname', 'lastname', 'created_at', 'role', 'telephone', 'dv_import_vat_files.id', 'dv_import_vat_files.file_name', 'dv_import_vat_files.month_year')
                            ->orderBy('dv_import_vat_files.id', 'DESC')    
                            ->get();  
      foreach($importVatFilesTimeline as $user)     
        $user->profile_photo_url = User::defaultProfilePhotoUrl($user, true);   

      $importVatFileCommentsTimeline = ImportVatComments::leftJoin('dv_import_vat_files', function($join) {
                              $join->on('dv_import_vat_comments.import_vat_id', '=', 'dv_import_vat_files.id');                      
                            })
                            ->leftJoin('dv_users', function($join) {
                              $join->on('dv_import_vat_comments.created_by', '=', 'dv_users.user_id');                      
                            })
                            ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'dv_users.user_id')
                            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')  

                            ->select(                             
                              DB::raw("'secondary' AS color"),     
                              DB::raw("'left' AS direction"), 
                              DB::raw("'Import vat comment' AS subject"), 
                              'dv_import_vat_comments.comment AS message',
                              'dv_users.firstname AS firstname', 'dv_users.lastname AS lastname',
                              'dv_import_vat_comments.created_at AS created_at', 'roles.name as role',
                              'dv_users.telephone AS telephone',
                                                
                              DB::raw("'importvatcomments' AS filetype"),
                              'dv_import_vat_comments.import_vat_id AS fileid',
                              'dv_import_vat_comments.line_no AS lineno',
                              'dv_import_vat_comments.id AS comment_id'
                            )
                            ->where('dv_import_vat_files.vat_reg_id', $vat_reg_id)                                                         
                            ->groupBy('firstname', 'lastname', 'created_at', 'role', 'telephone', 'dv_import_vat_comments.id', 'dv_import_vat_comments.comment')
                            ->orderBy('dv_import_vat_comments.id', 'DESC')
                            ->get();
      foreach($importVatFileCommentsTimeline as $user)     
        $user->profile_photo_url = User::defaultProfilePhotoUrl($user, true);                              
    
      $importReconciliationComInvoicesTimeline = ImportReconciliationComInvoices::leftJoin('dv_users', function($join) {
                              $join->on('dv_import_reconciliation_com_invoices.created_by', '=', 'dv_users.user_id');                      
                            })
                            ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'dv_users.user_id')
                            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')  
                            
                            ->select(                             
                              DB::raw("'secondary' AS color"),     
                              DB::raw("'left' AS direction"), 
                              DB::raw("'Commercial Invoices added' AS subject"),
                              
                              DB::raw('"Commercial Invoices for " AS message'),
                              'dv_users.firstname AS firstname', 'dv_users.lastname AS lastname',
                              'dv_import_reconciliation_com_invoices.created_at AS created_at', 'roles.name as role',
                              'dv_users.telephone AS telephone',
                                  
                              DB::raw("'importreconciliationcominvoices' AS filetype"),                             
                              DB::raw("DATE_FORMAT(dv_import_reconciliation_com_invoices.invoice_date, '%m-%Y') AS monthyear")
                            )
                            ->where('dv_import_reconciliation_com_invoices.vat_reg_id', $vat_reg_id)
                            ->groupBy('firstname', 'lastname', 'created_at', 'role', 'telephone', 'monthyear')
                            ->orderBy('monthyear', 'DESC')    
                            ->get();   
      foreach($importReconciliationComInvoicesTimeline as $user)     
        $user->profile_photo_url = User::defaultProfilePhotoUrl($user, true);  

      $importReconciliationSalesInvoicesTimeline = ImportReconciliationSalesInvoices::leftJoin('dv_users', function($join) {
                              $join->on('dv_import_reconciliation_sales_invoices.created_by', '=', 'dv_users.user_id');                      
                            })
                            ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'dv_users.user_id')
                            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')  
                            
                            ->select(                             
                              DB::raw("'secondary' AS color"),     
                              DB::raw("'left' AS direction"), 
                              DB::raw("'Sales Invoices added' AS subject"),
                              
                              DB::raw('"Sales Invoices for " AS message'),
                              'dv_users.firstname AS firstname', 'dv_users.lastname AS lastname',
                              'dv_import_reconciliation_sales_invoices.created_at AS created_at', 'roles.name as role',
                              'dv_users.telephone AS telephone',
                                  
                              DB::raw("'importreconciliationsalesinvoices' AS filetype"),                             
                              DB::raw("DATE_FORMAT(dv_import_reconciliation_sales_invoices.invoice_date, '%m-%Y') AS monthyear")
                            )
                            ->where('dv_import_reconciliation_sales_invoices.vat_reg_id', $vat_reg_id)                             
                            ->groupBy('firstname', 'lastname', 'created_at', 'role', 'telephone', 'monthyear')
                            ->orderBy('monthyear', 'DESC')    
                            ->get();   
      foreach($importReconciliationSalesInvoicesTimeline as $user)     
        $user->profile_photo_url = User::defaultProfilePhotoUrl($user, true);      

      $timelines = array_merge(
                      $createdTimeline->toarray(), 
                      $draftTimeline->toarray(),                       
                      $importVatFilesTimeline->toarray(),
                      $importVatFileCommentsTimeline->toarray(),

                      $importReconciliationComInvoicesTimeline->toarray(),
                      $importReconciliationSalesInvoicesTimeline->toarray(),                     
                   );     
      $sortedTimelines = collect($timelines)->sortByDesc('created_at')->values();
           
      $files = [];   
      $sortedFiles = collect($files)->sortByDesc('created_at')->values();

      $histories = [
        'timelines' => $sortedTimelines,
        'timelinefiles' => $sortedFiles        
      ];

      return $histories;                
    }

    //GET VAT reg. Main
    public function getVATRegMainList($client_id = null)
    {           
      $query = VATRegistrationMain::leftJoin('dv_clients', function($join) {                    
                    $join->on('dv_vat_registration_main.client_id', '=', 'dv_clients.id');                     
                  })     
                  ->select('dv_clients.client_name', 'dv_vat_registration_main.id AS vat_reg_main_id', 'dv_vat_registration_main.*',
                      DB::raw('(CASE                         
                        WHEN dv_vat_registration_main.general_periods = "monthly" THEN 1 
                        WHEN dv_vat_registration_main.general_periods = "bi-monthly" THEN 2
                        WHEN dv_vat_registration_main.general_periods = "quarterly" THEN 3 
                        WHEN dv_vat_registration_main.general_periods = "half-yearly" THEN 6 
                        WHEN dv_vat_registration_main.general_periods = "yearly" THEN 12                      
                        ELSE "" END) AS frequency'
                      )
                  );

      if($client_id != null)                                                                
          $vatregsmain = $query->where('dv_vat_registration_main.client_id', $client_id)               
                  ->get();
      else
          $vatregsmain = $query->with('vatreg')->get();            

      return $vatregsmain;                
    }

    //GET Specific VAT reg. Main
    public function getVATRegMain($vat_reg_main_id)
    {            
      $vatregsmain = VATRegistrationMain::leftJoin('dv_clients', function($join) {                    
                        $join->on('dv_vat_registration_main.client_id', '=', 'dv_clients.id');                     
                      })   
                      ->leftJoin('dv_client_api', function($join) {
                        $join->on('dv_client_api.client_id', '=', 'dv_clients.id');
                        $join->on('dv_client_api.vat_reg_main_id', '=', 'dv_vat_registration_main.id');                        
                      }) 
                      ->select('dv_clients.client_name', 'dv_vat_registration_main.*', 'dv_client_api.api_name', 'dv_client_api.api_env', 'dv_client_api.api_base_url', 'dv_client_api.api_tenant_id', 'dv_client_api.api_client_id', 'dv_client_api.api_secret_key', 'dv_client_api.api_company_id', 'dv_client_api.api_token', 'dv_client_api.api_token_expire', 'dv_client_api.api_token')
                      ->where('dv_vat_registration_main.id', $vat_reg_main_id)               
                      ->first();
     
      return $vatregsmain;                
    }

    //GET Active VAT reg. Main List
    public function getActiveVATRegMain($client_id, $country)
    {      
      $vatregsmain = VATRegistrationMain::leftJoin('dv_clients', function($join) {                    
                    $join->on('dv_vat_registration_main.client_id', '=', 'dv_clients.id');                     
                  })     
                  ->select('dv_clients.client_name', 'dv_vat_registration_main.*')                                                    
                  ->where('dv_vat_registration_main.client_id', $client_id) 
                  ->where('dv_vat_registration_main.country', $country) 
                  ->where('dv_vat_registration_main.status', 1)                    
                  ->get();

      return $vatregsmain;                
    }

    //GET Specific VAT reg. Main Account  Nos
    public function getVATRegMainAccNos($vat_reg_main_id)
    {            
      $vatRegMain_accnos =VATRegistrationMainAccNos::where('vat_reg_main_id', $vat_reg_main_id)
                            ->get();
     
      return $vatRegMain_accnos;                
    }

    //GET Specific VAT reg.
    public function getVATReg($vat_reg_id)
    {      
      $vatreg = VATRegistration::leftJoin('dv_clients', function($join) {                    
                        $join->on('dv_vat_registration.client_id', '=', 'dv_clients.id');                     
                      })    
                      ->select('dv_clients.client_name', 'dv_vat_registration.*', 'dv_vat_registration.id AS vat_reg_id',
                          DB::raw('(CASE                            
                            WHEN dv_vat_registration.general_periods = "monthly" THEN 1 
                            WHEN dv_vat_registration.general_periods = "bi-monthly" THEN 2
                            WHEN dv_vat_registration.general_periods = "quarterly" THEN 3 
                            WHEN dv_vat_registration.general_periods = "half-yearly" THEN 6 
                            WHEN dv_vat_registration.general_periods = "yearly" THEN 12                      
                            ELSE "" END) AS frequency'
                          )
                      )                                    
                      ->where('dv_vat_registration.id', $vat_reg_id) 
                      ->with('vatregmain')                        
                      ->first();
      
      return $vatreg;                
    }

    //CHECK and CREATE VAT Reg. Row
    public function checkAndCreateVATReg($authUser, $client_id = null)
    {
        $vatregsmainlist = $this->getVATRegMainList($client_id);

        foreach($vatregsmainlist as $key=>$vatregsmain)
        {
          if($vatregsmain->status)
          {
            $this->recursiveVATReg($authUser, $vatregsmain->client_id, $vatregsmain);        

            /*CAS/DDA Month*/
            $vatregmainmonths = VATRegistrationMainCasDdaMonths::where('vat_reg_main_id', $vatregsmain->id)
                                  ->orderBy('id', 'DESC')            
                                  ->first(); 
            if($vatregsmain->cash_acc_stmt || $vatregsmain->duty_defer_acc)
            {
              if($vatregmainmonths)
              {
                $next_month = Carbon::parse('01-'.$vatregmainmonths->month_year)->addMonth(1)->format('Y-m');
                
                if($next_month <= Carbon::now()->format('Y-m-d'))
                {
                  $vatRegMainCasDdaMonths = VATRegistrationMainCasDdaMonths::updateOrCreate(    
                    [
                      'vat_reg_main_id' => $vatregsmain->id,                
                      'month_year' => (Carbon::parse($next_month)->format('m-Y'))
                    ],         
                    [                
                      'vat_reg_main_id' => $vatregsmain->id,                
                      'month_year' => (Carbon::parse($next_month)->format('m-Y'))                    
                    ]
                  );
                }     
              }      
            }
            /*end CAS/DDA Month*/
          }//status  
        }//for
    }
     
    public function recursiveVATReg($authUser, $client_id, $vatregsmain)
    {
      $vatregs = VATRegistration::leftJoin('dv_clients', function($join) {                    
                      $join->on('dv_vat_registration.client_id', '=', 'dv_clients.id');                     
                    })     
                    ->select('dv_clients.client_name', 'dv_vat_registration.*')
                    ->where('dv_vat_registration.client_id', $client_id)     
                    ->where('dv_vat_registration.country', $vatregsmain->country) 
                    ->where('dv_vat_registration.vat_reg_main_id', $vatregsmain->id)                     
                    ->orderBy('service_start', 'DESC')            
                    ->first();

        if(empty($vatregs))
        {
          //Check next_service_start exists in list
          $next_service_start = Carbon::parse($vatregsmain->service_start)->format('Y-m') . '-01';
          
          //Check and Create New Row         
          if($next_service_start <= Carbon::now()->format('Y-m-d'))
          {
            //Create New Row
            $passData = [
              'vat_reg_id' => '',
              'client_id' => $vatregsmain->client_id,
              'vat_reg_main_id' => $vatregsmain->vat_reg_main_id,              
              'client_name' => $vatregsmain->client_name,
              'country' => $vatregsmain->country,
              'service_start' => $vatregsmain->service_start,
              //'turnover_date' => $vatregsmain->turnover_date, //DON'T DELETE
              'turnover_date' => NULL,
              'general_periods' => $vatregsmain->general_periods,
              'cash_acc_stmt' => $vatregsmain->cash_acc_stmt,
              'duty_defer_acc' => $vatregsmain->duty_defer_acc,
              'product_type' => $vatregsmain->product_type,
            
              'anyexcel_template_id' => $vatregsmain->anyexcel_template_id              
            ];
            $insertvatregs = $this->createUpdateVatReg($authUser, $passData);

            if($insertvatregs->getData()->message == 'Registered')
            {          
              /*CAS/DDA Month*/
              if($passData['cash_acc_stmt'] || $passData['duty_defer_acc'])
              {
                for ($i = 0; $i < $vatregsmain->frequency; $i++) 
                {
                  $next_month = Carbon::parse($vatregsmain->service_start)->addMonth($i)->format('Y-m') . '-01';
                  if($next_month <= Carbon::now()->format('Y-m-d'))
                  {
                    $vatRegMainCasDdaMonths = VATRegistrationMainCasDdaMonths::updateOrCreate(    
                      [
                        'vat_reg_main_id' => $passData['vat_reg_main_id'],                
                        'month_year' => (Carbon::parse($next_month)->format('m-Y'))
                      ],         
                      [                
                          'vat_reg_main_id' => $passData['vat_reg_main_id'],                
                          'month_year' => (Carbon::parse($next_month)->format('m-Y'))                    
                      ]
                    );
                  }
                }
              }
              /*end CAS/DDA Month*/

              $passvatregs = $this->getVATReg($insertvatregs->getData()->id);
              $this->recursiveVATReg($authUser, $client_id, $passvatregs);     
            }
          }
        }
        else
        {          
          //Check next_service_start exists in list
          $next_service_start = Carbon::parse($vatregs->service_start)->addMonth($vatregsmain->frequency)->format('Y-m') . '-01';

          //Check and Create New Row          
          if($next_service_start <= Carbon::now()->format('Y-m-d'))
          {            
            $passData = [
              'vat_reg_id' => '',
              'client_id' => $vatregsmain->client_id,
              'vat_reg_main_id' => $vatregsmain->vat_reg_main_id,             
              'client_name' => $vatregsmain->client_name,
              'country' => $vatregsmain->country,
              'service_start' => $next_service_start,
              //'turnover_date' => $vatregsmain->turnover_date, //DON'T DELETE
              'turnover_date' => NULL,
              'general_periods' => $vatregsmain->general_periods,               
              'cash_acc_stmt' => isset($vatregsmain->cash_acc_stmt) ? $vatregsmain->cash_acc_stmt : $vatregsmain->vatregmain->cash_acc_stmt,
              'duty_defer_acc' => isset($vatregsmain->duty_defer_acc) ? $vatregsmain->duty_defer_acc : $vatregsmain->vatregmain->duty_defer_acc,
              'product_type' => isset($vatregsmain->product_type) ? $vatregsmain->product_type : $vatregsmain->vatregmain->product_type,
            
              'anyexcel_template_id' => $vatregsmain->anyexcel_template_id
            ];
            $checkinsertvatregs = $this->createUpdateVatReg($authUser, $passData);
           
            if($checkinsertvatregs->getData()->message == 'Registered')
            {
              /*CAS/DDA Month*/
              if($passData['cash_acc_stmt'] || $passData['duty_defer_acc'])
              {
                for ($i = 0; $i < $vatregsmain->frequency; $i++) 
                {
                  $next_month = Carbon::parse($next_service_start)->addMonth($i)->format('Y-m') . '-01';

                  if($next_month <= Carbon::now()->format('Y-m-d'))
                  {
                    $vatRegMainCasDdaMonths = VATRegistrationMainCasDdaMonths::updateOrCreate(    
                      [
                        'vat_reg_main_id' => $passData['vat_reg_main_id'],                
                        'month_year' => (Carbon::parse($next_month)->format('m-Y'))
                      ],         
                      [                
                          'vat_reg_main_id' => $passData['vat_reg_main_id'],                
                          'month_year' => (Carbon::parse($next_month)->format('m-Y'))                    
                      ]
                    );
                  }
                }
              } 
              /*end CAS/DDA Month*/

              $passvatregs = $this->getVATReg($checkinsertvatregs->getData()->id);
              $this->recursiveVATReg($authUser, $client_id, $passvatregs);           
            }
          }            
        }
    }

    public function createUpdateVatReg($authUser, $passData)
    {    
        $vatRegID = $passData['vat_reg_id'];
        
        $system = $this->getSystemInfoLazy();
        $systemtaskdates = $system->systemtaskdate;
        $pivs_taskdate = "1";
        $pivs_taskdates = $systemtaskdates->filter(function ($taskdate, $key) {
          return $taskdate->task_name == 'PIVS';
        });
        if(count($pivs_taskdates) > 0) 
          $pivs_taskdate = $pivs_taskdates->first()->task_date;

        if ($vatRegID) {
            $service_startexplode = explode('/', $passData['service_start']);
            $service_start = (count($service_startexplode) == 3) ? (Carbon::parse($service_startexplode[1].'/'.$service_startexplode[0].'/01')->format('Y-m-d')) : $passData['service_start'];

            $service_start_final = Carbon::parse($service_start)->addMonth(1)->format('Y-m');          

          // update the value
          $vatRegs = VATRegistration::updateOrCreate(
            ['id' => $vatRegID],
            [                
                'vat_reg_main_id' => $passData['vat_reg_main_id'],
                'country' => $passData['country'],
                'service_start' => $service_start,
                //'turnover_date' => Carbon::parse($passData['turnover_date'])->format('Y-m-d'), //DON'T DELETE
                'turnover_date' => NULL,
                'general_periods' => $passData['general_periods'],
                'status' => ($passData['product_type'] == 1 || $passData['product_type'] == 3 || $passData['product_type'] == 4) ? 1 : 0,
                'status_import_re' => ($passData['product_type'] == 2 || $passData['product_type'] == 3 || $passData['product_type'] == 5) ? 1 : 0,
                'next_pivs_date' => ($passData['country'] == 'GB') ? (Carbon::parse($service_start_final.'-'.$pivs_taskdate)->format('Y-m-d')) : NULL,
                'next_cas_date' => ($passData['country'] == 'GB') ? (Carbon::parse($service_start_final.'-'.$pivs_taskdate)->format('Y-m-d')) : NULL,
                'next_dda_date' => ($passData['country'] == 'NO') ? (Carbon::parse($service_start_final.'-'.$pivs_taskdate)->format('Y-m-d')) : NULL,
               
                'anyexcel_template_id' => ($passData['anyexcel_template_id']) ? $passData['anyexcel_template_id'] : NULL
            ]
          );//Draft Created (VAT reg. created)       
         
          //Assign to Users
          $assignUserToVatReg = $this->assignUserToVatReg($passData['vat_reg_main_id'], $vatRegs->id);

          $this->addLog($authUser, 'vatreg-update', 
            [
              'Client Name' => $passData['client_name']
            ]
          );
          // updated
          return response()->json('Updated');
        } else {
          // create new one if details is unique
          $vatReg = VATRegistration::where('client_id', $passData['client_id'])
                            ->where('vat_reg_main_id', $passData['vat_reg_main_id'])
                            ->where('country', $passData['country'])
                            ->where('service_start', $passData['service_start'])
                            ->where('turnover_date', $passData['turnover_date'])
                            ->where('general_periods', $passData['general_periods'])
                            ->first();

          if (empty($vatReg)) { 
            $service_startexplode = explode('/', $passData['service_start']);
            $service_start = (count($service_startexplode) == 3) ? (Carbon::parse($service_startexplode[1].'/'.$service_startexplode[0].'/01')->format('Y-m-d')) : $passData['service_start'];

            $service_start_final = Carbon::parse($service_start)->addMonth(1)->format('Y-m');
        
            $vatRegs = VATRegistration::updateOrCreate(              
              [
                'client_id' => $passData['client_id'], 
                'vat_reg_main_id' => $passData['vat_reg_main_id'],
                'country' => $passData['country'],
                'service_start' => $service_start,
                //'turnover_date' => Carbon::parse($passData['turnover_date'])->format('Y-m-d'), //DON'T DELETE
                'turnover_date' => NULL,
                'general_periods' => $passData['general_periods'],
                'status' => ($passData['product_type'] == 1 || $passData['product_type'] == 3 || $passData['product_type'] == 4) ? 1 : 0,
                'status_import_re' => ($passData['product_type'] == 2 || $passData['product_type'] == 3 || $passData['product_type'] == 5) ? 1 : 0,
                'next_pivs_date' => ($passData['country'] == 'GB') ? (Carbon::parse($service_start_final.'-'.$pivs_taskdate)->format('Y-m-d')) : NULL,
                'next_cas_date' => ($passData['country'] == 'GB') ? (Carbon::parse($service_start_final.'-'.$pivs_taskdate)->format('Y-m-d')) : NULL,
                'next_dda_date' => ($passData['country'] == 'NO') ? (Carbon::parse($service_start_final.'-'.$pivs_taskdate)->format('Y-m-d')) : NULL,
               
                'anyexcel_template_id' => ($passData['anyexcel_template_id']) ? $passData['anyexcel_template_id'] : NULL
              ]
            );//Draft Created (VAT reg. created)  
                      
            //Assign to Users
            $assignUserToVatReg = $this->assignUserToVatReg($passData['vat_reg_main_id'], $vatRegs->id);

            $this->addLog($authUser, 'vatreg-add', 
              [
                'Client Name' => $passData['client_name']
              ]
            );
                        
            return response()->json(['id' => $vatRegs->id, 'message' => "Registered"]);
            
          } else {
            /*CAS/DDA Month*/            
            if($passData['cash_acc_stmt'] || $passData['duty_defer_acc'])
            {
              for ($i = 0; $i < $vatregsmain->frequency; $i++) 
              {
                $next_month = Carbon::parse($next_service_start)->addMonth($i)->format('Y-m') . '-01';

                if($next_month <= Carbon::now()->format('Y-m-d'))
                {
                  $vatRegMainCasDdaMonths = VATRegistrationMainCasDdaMonths::updateOrCreate(    
                    [
                      'vat_reg_main_id' => $passData['vat_reg_main_id'],                
                      'month_year' => (Carbon::parse($next_month)->format('m-Y'))
                    ],         
                    [                
                        'vat_reg_main_id' => $passData['vat_reg_main_id'],                
                        'month_year' => (Carbon::parse($next_month)->format('m-Y'))                    
                    ]
                  );
                }
              }
            } 
            /*end CAS/DDA Month*/

            // already exist           
            $this->addLog($authUser, 'vatreg-exists', 
              [
                'Client Name' => $passData['client_name']
              ]
            );
            return response()->json(['message' => "already exits"], 422);
          }
        }
    }
   
    //Assign User To VatReg
    public function assignUserToVatReg($vat_reg_main_id, $vat_reg_id)
    {
      //Get User_id from previously assigned 
      $vatRegRows = VATRegistration::leftJoin('dv_user_vat_registration', function($join) {
                      $join->on('dv_user_vat_registration.vat_reg_id', '=', 'dv_vat_registration.id');
                    })
                    ->where('vat_reg_main_id', $vat_reg_main_id)
                    ->get();

      foreach($vatRegRows as $vatRegRow)
      {       
        if($vatRegRow->user_id != null)  
        {               
          $teamUsers = UserVATRegistration::updateOrCreate(  
                    [                
                      'user_id' => $vatRegRow->user_id,
                      'vat_reg_id' => $vat_reg_id
                    ],             
                    [                
                      'user_id' => $vatRegRow->user_id,
                      'vat_reg_id' => $vat_reg_id
                    ]
                  );

          $updateVATRegStatus = VATRegistration::where('id', $vat_reg_id)
                                  ->where('status', 1)             
                                  ->update(['status' => 2]);//From 'Draft Created' to 'Draft' (assigned vat reg. to team user)

          $updateVATRegStatusImportRe = VATRegistration::where('id', $vat_reg_id)
                                  ->where('status_import_re', 1)             
                                  ->update(['status_import_re' => 2]);//From 'Draft Created' to 'Draft' (assigned vat reg. to team user)                        
        }
      }
    }

    public function organizeAccountDatas($api_name, $account_data, $vatreg)
    {
      $sales = [];
      $purchase = [];      
     
      if($api_name == "FTP" || $api_name == null)
      {
        if(isset($account_data['sales']))
        { 
          $sale_unique_invoiceno = [];
          foreach($account_data['sales'] as $currencyCode=>$sale_datas)
          {                 
            foreach($sale_datas as $key=>$sale_data)
            {
              foreach($sale_data as $sale_item)
              {
                if(array_key_exists($currencyCode, $sales))
                {
                  if(array_key_exists($key, $sales[$currencyCode]))
                  {                  
                    $sales[$currencyCode][$key]['netamount'] = $this->floatvalue($sales[$currencyCode][$key]['netamount']) + $this->floatvalue($sale_item['amount']);
                    $sales[$currencyCode][$key]['totalvat'] = $this->floatvalue($sales[$currencyCode][$key]['totalvat']) + $this->floatvalue($sale_item['total_invoice_vat']);

                    if(!in_array($sale_item['invoice_no'], $sale_unique_invoiceno, true)) 
                    {                  
                      array_push($sale_unique_invoiceno, $sale_item['invoice_no']); 
                      if(isset($sales[$currencyCode][$key]['invoiceCount']))  
                        $sales[$currencyCode][$key]['invoiceCount'] = $sales[$currencyCode][$key]['invoiceCount'] + 1;  
                      else
                        $sales[$currencyCode][$key]['invoiceCount'] = 1;    
                    }
                  }
                  else
                  {
                    $sales[$currencyCode][$key]['netamount'] = $this->floatvalue($sale_item['amount']);
                    $sales[$currencyCode][$key]['totalvat'] = $this->floatvalue($sale_item['total_invoice_vat']); 
                    $sales[$currencyCode][$key]['vatpercentage'] = $sale_item['vat_percentage'] . '%'; 
                    $sales[$currencyCode][$key]['currencyCode'] = $sale_item['currency_code']; 

                    if(!in_array($sale_item['invoice_no'], $sale_unique_invoiceno, true)) 
                    {                  
                      array_push($sale_unique_invoiceno, $sale_item['invoice_no']);   
                      $sales[$currencyCode][$key]['invoiceCount'] = 1;   
                    }             
                  }
                }
                else
                {
                  $sales[$currencyCode][$key]['netamount'] = $this->floatvalue($sale_item['amount']);
                  $sales[$currencyCode][$key]['totalvat'] = $this->floatvalue($sale_item['total_invoice_vat']); 
                  $sales[$currencyCode][$key]['vatpercentage'] = $sale_item['vat_percentage'] . '%'; 
                  $sales[$currencyCode][$key]['currencyCode'] = $sale_item['currency_code'];                   
                  if(!in_array($sale_item['invoice_no'], $sale_unique_invoiceno, true)) 
                  {                  
                    array_push($sale_unique_invoiceno, $sale_item['invoice_no']);   
                    $sales[$currencyCode][$key]['invoiceCount'] = 1;
                  }              
                }
              }               
            }                                                        
          }
        }

        if(isset($account_data['purchase']))
        {
          $purchase_unique_invoiceno = [];
          foreach($account_data['purchase'] as $currencyCode=>$purchase_datas)
          {           
            foreach($purchase_datas as $key=>$purchase_data)
            {
              foreach($purchase_data as $purchase_item)
              {
                if(array_key_exists($currencyCode, $purchase))
                {
                  if(array_key_exists($key, $purchase[$currencyCode]))
                  {
                    $purchase[$currencyCode][$key]['netamount'] = $this->floatvalue($purchase[$currencyCode][$key]['netamount']) + $this->floatvalue($purchase_item['amount']);
                    $purchase[$currencyCode][$key]['totalvat'] = $this->floatvalue($purchase[$currencyCode][$key]['totalvat']) + $this->floatvalue($purchase_item['total_invoice_vat']);
                    if(!in_array($purchase_item['invoice_no'], $purchase_unique_invoiceno, true)) 
                    {                  
                      array_push($purchase_unique_invoiceno, $purchase_item['invoice_no']); 
                      if(isset($purchase[$currencyCode][$key]['invoiceCount']))
                        $purchase[$currencyCode][$key]['invoiceCount'] = $purchase[$currencyCode][$key]['invoiceCount'] + 1;
                      else
                        $purchase[$currencyCode][$key]['invoiceCount'] = 1;
                    }
                  }
                  else
                  {
                    $purchase[$currencyCode][$key]['netamount'] = $this->floatvalue($purchase_item['amount']);
                    $purchase[$currencyCode][$key]['totalvat'] = $this->floatvalue($purchase_item['total_invoice_vat']); 
                    $purchase[$currencyCode][$key]['vatpercentage'] = $purchase_item['vat_percentage'] . '%'; 
                    $purchase[$currencyCode][$key]['currencyCode'] = $purchase_item['currency_code']; 
                    if(!in_array($purchase_item['invoice_no'], $purchase_unique_invoiceno, true)) 
                    {                  
                      array_push($purchase_unique_invoiceno, $purchase_item['invoice_no']); 
                      $purchase[$currencyCode][$key]['invoiceCount'] = 1;          
                    }       
                  }
                }
                else
                {
                  $purchase[$currencyCode][$key]['netamount'] = $this->floatvalue($purchase_item['amount']);
                  $purchase[$currencyCode][$key]['totalvat'] = $this->floatvalue($purchase_item['total_invoice_vat']); 
                  $purchase[$currencyCode][$key]['vatpercentage'] = $purchase_item['vat_percentage'] . '%'; 
                  $purchase[$currencyCode][$key]['currencyCode'] = $purchase_item['currency_code'];
                  if(!in_array($purchase_item['invoice_no'], $purchase_unique_invoiceno, true)) 
                  {                  
                    array_push($purchase_unique_invoiceno, $purchase_item['invoice_no']);  
                    $purchase[$currencyCode][$key]['invoiceCount'] = 1; 
                  }                
                } 
              }             
            }                 
          }
        }  
      }//FTP
      else
      {
        $sale_unique_invoiceno = [];
        $purchase_unique_invoiceno = [];

        $exists_invoice_vatamount = [];

        /* -- SHEET EXACT HIGHEST ROW -- */
        $chunkSize = 1000;
        $startRow = 0;
        
        $original_highestRow = count($account_data);
        do {
          $endRow = min($startRow + $chunkSize - 1, $original_highestRow);

          /* --for CHUNKS OF DATAS -- */
          for ($row = $startRow; $row < $endRow; $row++) 
          {          
            $salepurchase = $account_data[$row];
            
                 
          if($api_name == "Dynamics 365")
          {            
            $tax_percentage = ($salepurchase->totalTaxAmount == 0) ? 0 : round((($salepurchase->totalTaxAmount/$salepurchase->totalAmountExcludingTax) * 100));
            $currencyCode = $salepurchase->currencyCode;

            if(isset($salepurchase->vendorId))
            {                    
              if(array_key_exists($currencyCode, $purchase))
              {               
                if(array_key_exists($tax_percentage, $purchase[$currencyCode]))
                {   
                  if(isset($salepurchase->creditMemoDate))  
                  {                   
                    $purchase[$currencyCode][$tax_percentage]['netamount'] = $purchase[$currencyCode][$tax_percentage]['netamount'] - $salepurchase->totalAmountExcludingTax;
                    $purchase[$currencyCode][$tax_percentage]['totalvat'] = $purchase[$currencyCode][$tax_percentage]['totalvat'] - $salepurchase->totalTaxAmount;
                    if(!in_array($salepurchase->number, $purchase_unique_invoiceno, true)) 
                    {                  
                      array_push($purchase_unique_invoiceno, $salepurchase->number); 
                      if(isset($purchase[$currencyCode][$tax_percentage]['invoiceCount']))
                        $purchase[$currencyCode][$tax_percentage]['invoiceCount'] = $purchase[$currencyCode][$tax_percentage]['invoiceCount'] + 1; 
                      else
                        $purchase[$currencyCode][$tax_percentage]['invoiceCount'] = 1; 
                    }  
                  }
                  else
                  {
                    $purchase[$currencyCode][$tax_percentage]['netamount'] = $purchase[$currencyCode][$tax_percentage]['netamount'] + $salepurchase->totalAmountExcludingTax;
                    $purchase[$currencyCode][$tax_percentage]['totalvat'] = $purchase[$currencyCode][$tax_percentage]['totalvat'] + $salepurchase->totalTaxAmount;
                    if(!in_array($salepurchase->number, $purchase_unique_invoiceno, true)) 
                    {                  
                      array_push($purchase_unique_invoiceno, $salepurchase->number);
                      if(isset($purchase[$currencyCode][$tax_percentage]['invoiceCount'])) 
                        $purchase[$currencyCode][$tax_percentage]['invoiceCount'] = $purchase[$currencyCode][$tax_percentage]['invoiceCount'] + 1; 
                      else
                        $purchase[$currencyCode][$tax_percentage]['invoiceCount'] = 1; 
                    }
                  }
                }
                else
                {
                  $purchase[$currencyCode][$tax_percentage]['netamount'] = $salepurchase->totalAmountExcludingTax;
                  $purchase[$currencyCode][$tax_percentage]['totalvat'] = $salepurchase->totalTaxAmount;
                  $purchase[$currencyCode][$tax_percentage]['vatpercentage'] = $tax_percentage . '%';  
                  $purchase[$currencyCode][$tax_percentage]['currencyCode'] = $salepurchase->currencyCode;
                  if(!in_array($salepurchase->number, $purchase_unique_invoiceno, true)) 
                  {                  
                    array_push($purchase_unique_invoiceno, $salepurchase->number);
                    $purchase[$currencyCode][$tax_percentage]['invoiceCount'] = 1; 
                  }
                }
              }
              else
              {
                $purchase[$currencyCode][$tax_percentage]['netamount'] = $salepurchase->totalAmountExcludingTax;
                $purchase[$currencyCode][$tax_percentage]['totalvat'] = $salepurchase->totalTaxAmount;
                $purchase[$currencyCode][$tax_percentage]['vatpercentage'] = $tax_percentage . '%';  
                $purchase[$currencyCode][$tax_percentage]['currencyCode'] = $salepurchase->currencyCode;
                if(!in_array($salepurchase->number, $purchase_unique_invoiceno, true)) 
                {                  
                  array_push($purchase_unique_invoiceno, $salepurchase->number);
                  $purchase[$currencyCode][$tax_percentage]['invoiceCount'] = 1;
                } 
              }
            }//purchase
            else
            {               
              if(array_key_exists($currencyCode, $sales))
              {                    
                if(array_key_exists($tax_percentage, $sales[$currencyCode]))
                {    
                  if(isset($salepurchase->creditMemoDate))  
                  {
                    $sales[$currencyCode][$tax_percentage]['netamount'] = $sales[$currencyCode][$tax_percentage]['netamount'] - $salepurchase->totalAmountExcludingTax;
                    $sales[$currencyCode][$tax_percentage]['totalvat'] = $sales[$currencyCode][$tax_percentage]['totalvat'] - $salepurchase->totalTaxAmount;
                    if(!in_array($salepurchase->number, $sale_unique_invoiceno, true)) 
                    {                  
                      array_push($sale_unique_invoiceno, $salepurchase->number);
                      if(isset($sales[$currencyCode][$tax_percentage]['invoiceCount']))
                        $sales[$currencyCode][$tax_percentage]['invoiceCount'] = $sales[$currencyCode][$tax_percentage]['invoiceCount'] + 1;
                      else
                        $sales[$currencyCode][$tax_percentage]['invoiceCount'] = 1;
                    } 
                  } 
                  else
                  {               
                    $sales[$currencyCode][$tax_percentage]['netamount'] = $sales[$currencyCode][$tax_percentage]['netamount'] + $salepurchase->totalAmountExcludingTax;
                    $sales[$currencyCode][$tax_percentage]['totalvat'] = $sales[$currencyCode][$tax_percentage]['totalvat'] + $salepurchase->totalTaxAmount;
                    if(!in_array($salepurchase->number, $sale_unique_invoiceno, true)) 
                    {                  
                      array_push($sale_unique_invoiceno, $salepurchase->number);
                      if(isset($sales[$currencyCode][$tax_percentage]['invoiceCount']))
                        $sales[$currencyCode][$tax_percentage]['invoiceCount'] = $sales[$currencyCode][$tax_percentage]['invoiceCount'] + 1;
                      else
                        $sales[$currencyCode][$tax_percentage]['invoiceCount'] = 1;
                    }
                  }
                }
                else
                {
                  $sales[$currencyCode][$tax_percentage]['netamount'] = $salepurchase->totalAmountExcludingTax;
                  $sales[$currencyCode][$tax_percentage]['totalvat'] = $salepurchase->totalTaxAmount;
                  $sales[$currencyCode][$tax_percentage]['vatpercentage'] = $tax_percentage . '%';  
                  $sales[$currencyCode][$tax_percentage]['currencyCode'] = $salepurchase->currencyCode;
                  if(!in_array($salepurchase->number, $sale_unique_invoiceno, true)) 
                  {                  
                    array_push($sale_unique_invoiceno, $salepurchase->number);
                    $sales[$currencyCode][$tax_percentage]['invoiceCount'] = 1;
                  } 
                } 
              }
              else
              {
                $sales[$currencyCode][$tax_percentage]['netamount'] = $salepurchase->totalAmountExcludingTax;
                $sales[$currencyCode][$tax_percentage]['totalvat'] = $salepurchase->totalTaxAmount;
                $sales[$currencyCode][$tax_percentage]['vatpercentage'] = $tax_percentage . '%';  
                $sales[$currencyCode][$tax_percentage]['currencyCode'] = $salepurchase->currencyCode;
                if(!in_array($salepurchase->number, $sale_unique_invoiceno, true)) 
                {                  
                  array_push($sale_unique_invoiceno, $salepurchase->number);
                  $sales[$currencyCode][$tax_percentage]['invoiceCount'] = 1; 
                }
              }          
            }//sales
          }//Dynamics 365
          else if($api_name == "Dynamics 365 via SmartApi")
          {
            $_tax_amount = $this->floatvalue($salepurchase['amount-including-vat']) - $this->floatvalue($salepurchase['amount']); 
            $tax_percentage = ($this->floatvalue($salepurchase['amount-including-vat']) == 0) ? 0 : round((($_tax_amount/$this->floatvalue($salepurchase['amount'])) * 100));    
                       
            $currencyCode = ($salepurchase['currency-code'] == null) ? 'DKK' : $salepurchase['currency-code'];

            if(isset($salepurchase['pay-to-country-region-code']))
            {                
              if(array_key_exists($currencyCode, $purchase))
              {               
                if(array_key_exists($tax_percentage, $purchase[$currencyCode]))
                {                    
                  $purchase[$currencyCode][$tax_percentage]['netamount'] = $purchase[$currencyCode][$tax_percentage]['netamount'] + $this->floatvalue($salepurchase['amount']);
                  $purchase[$currencyCode][$tax_percentage]['totalvat'] = $purchase[$currencyCode][$tax_percentage]['totalvat'] + $_tax_amount;
                  if(!in_array($salepurchase['no'], $purchase_unique_invoiceno, true)) 
                  {                  
                    array_push($purchase_unique_invoiceno, $salepurchase['no']);
                    if(isset($purchase[$currencyCode][$tax_percentage]['invoiceCount'])) 
                      $purchase[$currencyCode][$tax_percentage]['invoiceCount'] = $purchase[$currencyCode][$tax_percentage]['invoiceCount'] + 1; 
                    else
                      $purchase[$currencyCode][$tax_percentage]['invoiceCount'] = 1; 
                  }                  
                }
                else
                {
                  $purchase[$currencyCode][$tax_percentage]['netamount'] = $this->floatvalue($salepurchase['amount']);
                  $purchase[$currencyCode][$tax_percentage]['totalvat'] = $_tax_amount;
                  $purchase[$currencyCode][$tax_percentage]['vatpercentage'] = $tax_percentage . '%';  
                  $purchase[$currencyCode][$tax_percentage]['currencyCode'] = $currencyCode;
                  if(!in_array($salepurchase['no'], $purchase_unique_invoiceno, true)) 
                  {                  
                    array_push($purchase_unique_invoiceno, $salepurchase['no']);
                    $purchase[$currencyCode][$tax_percentage]['invoiceCount'] = 1; 
                  }
                }
              }
              else
              {
                $purchase[$currencyCode][$tax_percentage]['netamount'] = $this->floatvalue($salepurchase['amount']);
                $purchase[$currencyCode][$tax_percentage]['totalvat'] = $_tax_amount;
                $purchase[$currencyCode][$tax_percentage]['vatpercentage'] = $tax_percentage . '%';  
                $purchase[$currencyCode][$tax_percentage]['currencyCode'] = $currencyCode;
                if(!in_array($salepurchase['no'], $purchase_unique_invoiceno, true)) 
                {                  
                  array_push($purchase_unique_invoiceno, $salepurchase['no']);
                  $purchase[$currencyCode][$tax_percentage]['invoiceCount'] = 1;
                } 
              }
            }//purchase
            else
            {               
              if(array_key_exists($currencyCode, $sales))
              {                    
                if(array_key_exists($tax_percentage, $sales[$currencyCode]))
                {                              
                  $sales[$currencyCode][$tax_percentage]['netamount'] = $sales[$currencyCode][$tax_percentage]['netamount'] + $this->floatvalue($salepurchase['amount']);
                  $sales[$currencyCode][$tax_percentage]['totalvat'] = $sales[$currencyCode][$tax_percentage]['totalvat'] + $_tax_amount;
                  if(!in_array($salepurchase['no'], $sale_unique_invoiceno, true)) 
                  {                  
                    array_push($sale_unique_invoiceno, $salepurchase['no']);
                    if(isset($sales[$currencyCode][$tax_percentage]['invoiceCount']))
                      $sales[$currencyCode][$tax_percentage]['invoiceCount'] = $sales[$currencyCode][$tax_percentage]['invoiceCount'] + 1;
                    else
                      $sales[$currencyCode][$tax_percentage]['invoiceCount'] = 1;
                  }                  
                }
                else
                {
                  $sales[$currencyCode][$tax_percentage]['netamount'] = $this->floatvalue($salepurchase['amount']);
                  $sales[$currencyCode][$tax_percentage]['totalvat'] = $_tax_amount;
                  $sales[$currencyCode][$tax_percentage]['vatpercentage'] = $tax_percentage . '%';  
                  $sales[$currencyCode][$tax_percentage]['currencyCode'] = $currencyCode;
                  if(!in_array($salepurchase['no'], $sale_unique_invoiceno, true)) 
                  {                  
                    array_push($sale_unique_invoiceno, $salepurchase['no']);
                    $sales[$currencyCode][$tax_percentage]['invoiceCount'] = 1;
                  } 
                } 
              }
              else
              {
                $sales[$currencyCode][$tax_percentage]['netamount'] = $this->floatvalue($salepurchase['amount']);
                $sales[$currencyCode][$tax_percentage]['totalvat'] = $_tax_amount;
                $sales[$currencyCode][$tax_percentage]['vatpercentage'] = $tax_percentage . '%';  
                $sales[$currencyCode][$tax_percentage]['currencyCode'] = $currencyCode;
                if(!in_array($salepurchase['no'], $sale_unique_invoiceno, true)) 
                {                  
                  array_push($sale_unique_invoiceno, $salepurchase['no']);
                  $sales[$currencyCode][$tax_percentage]['invoiceCount'] = 1; 
                }
              }          
            }//sales
          }//Dynamics 365 via SmartApi
          else if($api_name == "E-conomic")
          {
            if(isset($salepurchase->account))
            {
              $tax_percentage = isset($salepurchase->ratePercentage) ? $salepurchase->ratePercentage : 0;
              
              $currencyCode = $salepurchase->currency;
              
              $invoice_no = isset($salepurchase->invoiceNumber) ? $salepurchase->invoiceNumber : '';

              if($invoice_no == '')
              {
                $_invoice_text = isset($salepurchase->text) ? $salepurchase->text : '';
                if(stripos($_invoice_text, ";") !== false) 
                {
                  $_arr_invoice_text = explode(';', $_invoice_text);
                  foreach ($_arr_invoice_text as $invoice_text)
                  {
                    if(stripos($invoice_text, "Invoice:") !== false)                       
                      $invoice_no = str_replace('Invoice:', '', $invoice_text); 
                    else if(stripos($invoice_text, "Credit:") !== false)
                      $invoice_no = str_replace('Credit:', '', $invoice_text);                        
                  }
                }
                else
                {
                  if (preg_match('/\d+/', $_invoice_text)) 
                  {                
                    preg_match_all('/\d+/', $_invoice_text, $matches);
                   
                    if(count($matches[0]) > 0)
                      $invoice_no = $matches[0][count($matches)-1];
                  }
                }
              }

              if($invoice_no == '')
              {
                if(isset($salepurchase->voucherNumber))
                  $invoice_no = $salepurchase->voucherNumber;
              }
                            
              if (!isset($exists_invoice_vatamount[$invoice_no]))
                $exists_invoice_vatamount[$invoice_no] =  ['vat_amount' => 0, 'vat_rate' => $tax_percentage];
              
              /*Account No. - MAP COLUMN*/             
              $acc_reverse = 1;
              $net_or_vat = 'net';
              $accountnos = $vatreg->vatregmain->accnos;
              $acc_invoice_type = 'sale';
              $allow = false;
              if(count($accountnos) > 0)
              {         
                foreach ($accountnos as $accountno) 
                {
                  // if(($accountno->is_auto_vat_check == 0 || $accountno->is_auto_vat_check == 2) && 
                  //         ($salepurchase->account->accountNumber == $accountno->acc_no))
                  if($salepurchase->account->accountNumber == $accountno->acc_no)
                  {
                    if($accountno->is_reverse)
                      $acc_reverse = -1;

                    if($accountno->map_column == 'net_sales')
                    {                   
                      $acc_invoice_type = 'sale';         
                      $net_or_vat = 'net';                              
                    }
                    else if($accountno->map_column == 'vat_sales') 
                    {       
                      $acc_invoice_type = 'sale';                    
                      $net_or_vat = 'vat';                              
                    }
                    else if($accountno->map_column == 'net_purchases')
                    {                   
                      $acc_invoice_type = 'purchase';         
                      $net_or_vat = 'net';                                               
                    }
                    else if($accountno->map_column == 'vat_purchases') 
                    {       
                      $acc_invoice_type = 'purchase';                    
                      $net_or_vat = 'vat';                              
                    }

                    $allow = ($accountno->is_auto_vat_check == 0 || $accountno->is_auto_vat_check == 2) ? true : false;
                  }
                }
              }                       
              /*end Account No. - MAP COLUMN*/
              
              if($allow)
              {
                /*
              $baseCurrency = $vatreg->vatregmain->clientapi->currency_code;
              $actualCurrency = '';
              $actualAmount = 0;
              if($baseCurrency == $currencyCode)
              { 
                $actualCurrency = $currencyCode;
                $actualAmount = $salepurchase->amount;
              }
              else  
              { 
                if($salepurchase->amountInBaseCurrency)
                { 
                  $actualCurrency = $baseCurrency;               
                  $actualAmount = $salepurchase->amountInBaseCurrency;
                }
                else
                {
                  $actualCurrency = $currencyCode;
                  $actualAmount = $salepurchase->amount;
                }
              }
              */
              $baseCurrency = $vatreg->vatregmain->clientapi->currency_code;                                    
              $actualAmount = $salepurchase->amount;
              $actualCurrency = $salepurchase->currency;
              
              $useBaseCurrencyAmount = $vatreg->vatregmain->clientapi->use_base_currency_amount;
              // Special case: base currency is NOK or DKK → use amountInBaseCurrency if available
              //if (in_array($baseCurrency, ['NOK']) && !empty($salepurchase->amountInBaseCurrency)) {
              if ($useBaseCurrencyAmount && !empty($salepurchase->amountInBaseCurrency)) {
                $actualAmount = $salepurchase->amountInBaseCurrency;
                $actualCurrency = $baseCurrency;
              }

              if($acc_invoice_type == 'purchase')
              {                               
                //if(array_key_exists($currencyCode, $purchase))
                if(array_key_exists($actualCurrency, $purchase))
                {                                    
                  //if(array_key_exists($tax_percentage, $purchase[$currencyCode]))
                  if(array_key_exists($tax_percentage, $purchase[$actualCurrency]))
                  {                                     
                    $update_vat_amount = true;
                    $is_current_vat_amount = false;
                    $current_vat_amount = 0;                 
                    if($tax_percentage > 0)
                    {
                      if($net_or_vat == 'net')
                      {
                        //$net_amount = $acc_reverse * $salepurchase->amount;
                        $net_amount = $acc_reverse * $actualAmount;
                        
                        $vat_amount = (($net_amount * $tax_percentage) / 100);

                        if($exists_invoice_vatamount[$invoice_no]['vat_amount'] == 0)                        
                          $exists_invoice_vatamount[$invoice_no]['vat_amount'] = $vat_amount;   
                        else
                        {
                          $exists_invoice_vatamount[$invoice_no]['vat_amount'] = $exists_invoice_vatamount[$invoice_no]['vat_amount'] + $vat_amount;

                          $is_current_vat_amount = true;
                          $current_vat_amount = $vat_amount;
                        }
                      }
                      else if($net_or_vat == 'vat')
                      {                          
                        //$vat_amount = $acc_reverse * $salepurchase->amount;                        
                        $vat_amount = $acc_reverse * $actualAmount;                        

                        //if (round((float)$vat_amount, 2) <= round((float)$exists_invoice_vatamount[$invoice_no]['vat_amount'], 2))
                        if (round((float)$vat_amount, 2) == round((float)$exists_invoice_vatamount[$invoice_no]['vat_amount'], 2))
                          $update_vat_amount = false;
                      }          
                    }   

                    // $purchase[$currencyCode][$tax_percentage]['netamount'] = ($net_or_vat == 'net') ? round(($purchase[$currencyCode][$tax_percentage]['netamount'] + ($acc_reverse * $salepurchase->amount)), 2) : round($purchase[$currencyCode][$tax_percentage]['netamount'], 2);
                    $purchase[$actualCurrency][$tax_percentage]['netamount'] = ($net_or_vat == 'net') ? round(($purchase[$actualCurrency][$tax_percentage]['netamount'] + ($acc_reverse * $actualAmount)), 2) : round($purchase[$actualCurrency][$tax_percentage]['netamount'], 2);

                    // if($tax_percentage == 0)                  
                    //   //$purchase[$currencyCode][$tax_percentage]['totalvat'] = 0;                  
                    //   $purchase[$actualCurrency][$tax_percentage]['totalvat'] = 0;                  
                    // else
                    // {
                      if($net_or_vat == 'vat')
                      {  
                        if($update_vat_amount)                      
                          // $purchase[$currencyCode][$tax_percentage]['totalvat'] = round(($purchase[$currencyCode][$tax_percentage]['totalvat'] + ($acc_reverse * $salepurchase->amount)), 2);
                          $purchase[$actualCurrency][$tax_percentage]['totalvat'] = round((($purchase[$actualCurrency][$tax_percentage]['totalvat'] ?? 0) + ($acc_reverse * $actualAmount)), 2);
                        else
                          // $purchase[$currencyCode][$tax_percentage]['totalvat'] = round($purchase[$currencyCode][$tax_percentage]['totalvat'], 2);
                          $purchase[$actualCurrency][$tax_percentage]['totalvat'] = round(($purchase[$actualCurrency][$tax_percentage]['totalvat'] ?? 0), 2);
                      }
                      else
                      {
                        if(!$is_current_vat_amount)
                          // $purchase[$currencyCode][$tax_percentage]['totalvat'] = round(($purchase[$currencyCode][$tax_percentage]['totalvat'] + $exists_invoice_vatamount[$invoice_no]['vat_amount']), 2);
                          $purchase[$actualCurrency][$tax_percentage]['totalvat'] = round((($purchase[$actualCurrency][$tax_percentage]['totalvat'] ?? 0) + $exists_invoice_vatamount[$invoice_no]['vat_amount']), 2);
                        else
                          // $purchase[$currencyCode][$tax_percentage]['totalvat'] = round(($purchase[$currencyCode][$tax_percentage]['totalvat'] + $current_vat_amount),2);
                          $purchase[$actualCurrency][$tax_percentage]['totalvat'] = round((($purchase[$actualCurrency][$tax_percentage]['totalvat'] ?? 0) + $current_vat_amount),2);
                      }
                    //}

                    // $purchase[$currencyCode][$tax_percentage]['totalvat'] = ($tax_percentage == 0) ? 0 : (($net_or_vat == 'vat') ? (($update_vat_amount) ? round(($purchase[$currencyCode][$tax_percentage]['totalvat'] + ($acc_reverse * $salepurchase->amount)), 2) : round($purchase[$currencyCode][$tax_percentage]['totalvat'], 2)) : ((!$is_current_vat_amount) ? round(($purchase[$currencyCode][$tax_percentage]['totalvat'] + $exists_invoice_vatamount[$invoice_no]['vat_amount']), 2) : round(($purchase[$currencyCode][$tax_percentage]['totalvat'] + $current_vat_amount),2) ));

                    if(!in_array($invoice_no, $purchase_unique_invoiceno, true)) 
                    {                  
                      array_push($purchase_unique_invoiceno, $invoice_no);
                      // if(isset($purchase[$currencyCode][$tax_percentage]['invoiceCount']))
                      //   $purchase[$currencyCode][$tax_percentage]['invoiceCount'] = $purchase[$currencyCode][$tax_percentage]['invoiceCount'] + 1; 
                      // else
                      //   $purchase[$currencyCode][$tax_percentage]['invoiceCount'] = 1; 
                      if(isset($purchase[$actualCurrency][$tax_percentage]['invoiceCount']))
                        $purchase[$actualCurrency][$tax_percentage]['invoiceCount'] = $purchase[$actualCurrency][$tax_percentage]['invoiceCount'] + 1; 
                      else
                        $purchase[$actualCurrency][$tax_percentage]['invoiceCount'] = 1; 
                    }
                  }
                  else
                  {                     
                    $update_vat_amount = true; 
                    $is_current_vat_amount = false; 
                    $current_vat_amount = 0;           
                    if($tax_percentage > 0)
                    {
                      if($net_or_vat == 'net')
                      {
                        //$net_amount = $acc_reverse * $salepurchase->amount;
                        $net_amount = $acc_reverse * $actualAmount;
                        
                        $vat_amount = (($net_amount * $tax_percentage) / 100);

                        if($exists_invoice_vatamount[$invoice_no]['vat_amount'] == 0)                        
                          $exists_invoice_vatamount[$invoice_no]['vat_amount'] = $vat_amount;   
                        else
                        {
                          $exists_invoice_vatamount[$invoice_no]['vat_amount'] = $exists_invoice_vatamount[$invoice_no]['vat_amount'] + $vat_amount;

                          $is_current_vat_amount = true;
                          $current_vat_amount = $vat_amount;
                        }     
                      } 
                      else if($net_or_vat == 'vat')
                      {  
                        //$vat_amount = $acc_reverse * $salepurchase->amount;
                        $vat_amount = $acc_reverse * $actualAmount;
                       
                        if (round((float)$vat_amount, 2) <= round((float)$exists_invoice_vatamount[$invoice_no]['vat_amount'], 2))
                          $update_vat_amount = false;
                      }                      
                    }   

                    // $purchase[$currencyCode][$tax_percentage]['netamount'] = ($net_or_vat == 'net') ? round(($acc_reverse * $salepurchase->amount), 2) : 0;
                    $purchase[$actualCurrency][$tax_percentage]['netamount'] = ($net_or_vat == 'net') ? round(($acc_reverse * $actualAmount), 2) : 0;

                    // if($tax_percentage == 0)                  
                    //   //$purchase[$currencyCode][$tax_percentage]['totalvat'] = 0;                  
                    //   $purchase[$actualCurrency][$tax_percentage]['totalvat'] = 0;                  
                    // else
                    // {
                      if($net_or_vat == 'vat')
                      {  
                        if($update_vat_amount)                      
                          //$purchase[$currencyCode][$tax_percentage]['totalvat'] = round(($acc_reverse * $salepurchase->amount),2);
                          $purchase[$actualCurrency][$tax_percentage]['totalvat'] = round(($acc_reverse * $actualAmount),2);
                        else
                          // $purchase[$currencyCode][$tax_percentage]['totalvat'] = round($purchase[$currencyCode][$tax_percentage]['totalvat'], 2);
                          $purchase[$actualCurrency][$tax_percentage]['totalvat'] = round($purchase[$actualCurrency][$tax_percentage]['totalvat'], 2);
                      }
                      else
                      {
                        // if(!$is_current_vat_amount)
                        //   $purchase[$currencyCode][$tax_percentage]['totalvat'] = round($exists_invoice_vatamount[$invoice_no]['vat_amount'], 2);
                        // else
                        //   $purchase[$currencyCode][$tax_percentage]['totalvat'] = round($current_vat_amount, 2);
                        if(!$is_current_vat_amount)
                          $purchase[$actualCurrency][$tax_percentage]['totalvat'] = round($exists_invoice_vatamount[$invoice_no]['vat_amount'], 2);
                        else
                          $purchase[$actualCurrency][$tax_percentage]['totalvat'] = round($current_vat_amount, 2);
                      }
                    //}

                    // $purchase[$currencyCode][$tax_percentage]['totalvat'] = ($tax_percentage == 0) ? 0 : (($net_or_vat == 'vat') ? (($update_vat_amount) ? round(($acc_reverse * $salepurchase->amount), 2) : round($purchase[$currencyCode][$tax_percentage]['totalvat'], 2)) : ((!$is_current_vat_amount) ? round($exists_invoice_vatamount[$invoice_no]['vat_amount'], 2) : round($current_vat_amount,2) ));
                    
                    // $purchase[$currencyCode][$tax_percentage]['vatpercentage'] = $tax_percentage . '%';  
                    // $purchase[$currencyCode][$tax_percentage]['currencyCode'] = $salepurchase->currency;
                    $purchase[$actualCurrency][$tax_percentage]['vatpercentage'] = $tax_percentage . '%';  
                    $purchase[$actualCurrency][$tax_percentage]['currencyCode'] = $actualCurrency;
                    if(!in_array($invoice_no, $purchase_unique_invoiceno, true)) 
                    {                  
                      array_push($purchase_unique_invoiceno, $invoice_no);
                      //$purchase[$currencyCode][$tax_percentage]['invoiceCount'] = 1; 
                      $purchase[$actualCurrency][$tax_percentage]['invoiceCount'] = 1; 
                    }                   
                  }  
                }//currencyCode
                else
                {                  
                  $update_vat_amount = true; 
                  $is_current_vat_amount = false;
                  $current_vat_amount = 0;           
                  if($tax_percentage > 0)
                  {
                    if($net_or_vat == 'net')
                    {
                      //$net_amount = $acc_reverse * $salepurchase->amount;
                      $net_amount = $acc_reverse * $actualAmount;
                      
                      $vat_amount = (($net_amount * $tax_percentage) / 100);

                      if($exists_invoice_vatamount[$invoice_no]['vat_amount'] == 0)                        
                        $exists_invoice_vatamount[$invoice_no]['vat_amount'] = $vat_amount;   
                      else
                      {
                        $exists_invoice_vatamount[$invoice_no]['vat_amount'] = $exists_invoice_vatamount[$invoice_no]['vat_amount'] + $vat_amount;

                        $is_current_vat_amount = true;
                        $current_vat_amount = $vat_amount;
                      }     
                    } 
                    else if($net_or_vat == 'vat')
                    {  
                      //$vat_amount = $acc_reverse * $salepurchase->amount;
                      $vat_amount = $acc_reverse * $actualAmount;
                     
                      //if (round((float)$vat_amount, 2) <= round((float)$exists_invoice_vatamount[$invoice_no]['vat_amount'], 2))
                      if (round((float)$vat_amount, 2) == round((float)$exists_invoice_vatamount[$invoice_no]['vat_amount'], 2))
                        $update_vat_amount = false;
                    }                     
                  }

                  // $purchase[$currencyCode][$tax_percentage]['netamount'] = ($net_or_vat == 'net') ? round(($acc_reverse * $salepurchase->amount), 2) : 0;
                  $purchase[$actualCurrency][$tax_percentage]['netamount'] = ($net_or_vat == 'net') ? round(($acc_reverse * $actualAmount), 2) : 0;

                  // $purchase[$currencyCode][$tax_percentage]['totalvat'] = ($net_or_vat == 'vat') ? round(($acc_reverse * $salepurchase->amount), 2) : 0;
                  // if($tax_percentage == 0)                  
                  //   //$purchase[$currencyCode][$tax_percentage]['totalvat'] = 0;                  
                  //   $purchase[$actualCurrency][$tax_percentage]['totalvat'] = 0;                  
                  // else
                  // {
                    if($net_or_vat == 'vat')
                    {  
                      if($update_vat_amount)                      
                        //$purchase[$currencyCode][$tax_percentage]['totalvat'] = round(($acc_reverse * $salepurchase->amount),2);
                        $purchase[$actualCurrency][$tax_percentage]['totalvat'] = round(($acc_reverse * $actualAmount),2);
                    }
                    else
                    {
                      // if(!$is_current_vat_amount)
                      //   $purchase[$currencyCode][$tax_percentage]['totalvat'] = round($exists_invoice_vatamount[$invoice_no]['vat_amount'], 2);
                      // else
                      //   $purchase[$currencyCode][$tax_percentage]['totalvat'] = round($current_vat_amount, 2);
                      if(!$is_current_vat_amount)
                        $purchase[$actualCurrency][$tax_percentage]['totalvat'] = round($exists_invoice_vatamount[$invoice_no]['vat_amount'], 2);
                      else
                        $purchase[$actualCurrency][$tax_percentage]['totalvat'] = round($current_vat_amount, 2);
                    }
                  //}

                  // $purchase[$currencyCode][$tax_percentage]['totalvat'] = ($tax_percentage == 0) ? 0 : (($net_or_vat == 'vat') ? round((($update_vat_amount) ? ($acc_reverse * $salepurchase->amount) : $purchase[$currencyCode][$tax_percentage]['totalvat']), 2) : ((!$is_current_vat_amount) ? round($exists_invoice_vatamount[$invoice_no]['vat_amount'], 2) : round($current_vat_amount, 2)));
                 
                  // $purchase[$currencyCode][$tax_percentage]['vatpercentage'] = $tax_percentage . '%';  
                  // $purchase[$currencyCode][$tax_percentage]['currencyCode'] = $salepurchase->currency;
                  $purchase[$actualCurrency][$tax_percentage]['vatpercentage'] = $tax_percentage . '%';  
                  $purchase[$actualCurrency][$tax_percentage]['currencyCode'] = $actualCurrency;
                  if(!in_array($invoice_no, $purchase_unique_invoiceno, true)) 
                  {                  
                    array_push($purchase_unique_invoiceno, $invoice_no);
                    //$purchase[$currencyCode][$tax_percentage]['invoiceCount'] = 1; 
                    $purchase[$actualCurrency][$tax_percentage]['invoiceCount'] = 1; 
                  }                     
                }//currencyCode
                
              }//purchase
              else
              {               
                //if(array_key_exists($currencyCode, $sales))
                if(array_key_exists($actualCurrency, $sales))
                {
                  //if(array_key_exists($tax_percentage, $sales[$currencyCode]))
                  if(array_key_exists($tax_percentage, $sales[$actualCurrency]))
                  {                    
                    $update_vat_amount = true;
                    $is_current_vat_amount = false;
                    $current_vat_amount = 0;
                    if($tax_percentage > 0)
                    {
                      if($net_or_vat == 'net')
                      {
                        //$net_amount = $acc_reverse * $salepurchase->amount;
                        $net_amount = $acc_reverse * $actualAmount;
                        
                        $vat_amount = (($net_amount * $tax_percentage) / 100);

                        if($exists_invoice_vatamount[$invoice_no]['vat_amount'] == 0)                        
                          $exists_invoice_vatamount[$invoice_no]['vat_amount'] = $vat_amount;   
                        else
                        {
                          $exists_invoice_vatamount[$invoice_no]['vat_amount'] = $exists_invoice_vatamount[$invoice_no]['vat_amount'] + $vat_amount;

                          $is_current_vat_amount = true;
                          $current_vat_amount = $vat_amount;
                        }
                      }
                      else if($net_or_vat == 'vat')
                      {
                        //$vat_amount = $acc_reverse * $salepurchase->amount;       
                        $vat_amount = $acc_reverse * $actualAmount;       

                        if (round((float)$vat_amount, 2) <= round((float)$exists_invoice_vatamount[$invoice_no]['vat_amount'], 2))
                          $update_vat_amount = false;
                      }                      
                    }  
                   
                    // $sales[$currencyCode][$tax_percentage]['netamount'] = ($net_or_vat == 'net') ? round(($sales[$currencyCode][$tax_percentage]['netamount'] + ($acc_reverse * $salepurchase->amount)), 2) : round($sales[$currencyCode][$tax_percentage]['netamount'], 2);
                    $sales[$actualCurrency][$tax_percentage]['netamount'] = ($net_or_vat == 'net') ? round(($sales[$actualCurrency][$tax_percentage]['netamount'] + ($acc_reverse * $actualAmount)), 2) : round($sales[$actualCurrency][$tax_percentage]['netamount'], 2);

                    if($tax_percentage == 0)                  
                      //$sales[$currencyCode][$tax_percentage]['totalvat'] = 0;                  
                      $sales[$actualCurrency][$tax_percentage]['totalvat'] = 0;                  
                    else
                    {
                      if($net_or_vat == 'vat')
                      {  
                        if($update_vat_amount)                      
                          // $sales[$currencyCode][$tax_percentage]['totalvat'] = round(($sales[$currencyCode][$tax_percentage]['totalvat'] + ($acc_reverse * $salepurchase->amount)), 2);
                          $sales[$actualCurrency][$tax_percentage]['totalvat'] = round(($sales[$actualCurrency][$tax_percentage]['totalvat'] + ($acc_reverse * $actualAmount)), 2);
                        else
                          // $sales[$currencyCode][$tax_percentage]['totalvat'] = round($sales[$currencyCode][$tax_percentage]['totalvat'], 2);
                          $sales[$actualCurrency][$tax_percentage]['totalvat'] = round($sales[$actualCurrency][$tax_percentage]['totalvat'], 2);
                      }
                      else
                      {
                        // if(!$is_current_vat_amount)
                        //   $sales[$currencyCode][$tax_percentage]['totalvat'] = round(($sales[$currencyCode][$tax_percentage]['totalvat'] + $exists_invoice_vatamount[$invoice_no]['vat_amount']), 2);
                        // else
                        //   $sales[$currencyCode][$tax_percentage]['totalvat'] = round(($sales[$currencyCode][$tax_percentage]['totalvat'] + $current_vat_amount),2);
                        if(!$is_current_vat_amount)
                          $sales[$actualCurrency][$tax_percentage]['totalvat'] = round(($sales[$actualCurrency][$tax_percentage]['totalvat'] + $exists_invoice_vatamount[$invoice_no]['vat_amount']), 2);
                        else
                          $sales[$actualCurrency][$tax_percentage]['totalvat'] = round(($sales[$actualCurrency][$tax_percentage]['totalvat'] + $current_vat_amount),2);
                      }
                    }

                    // $sales[$currencyCode][$tax_percentage]['totalvat'] = ($tax_percentage == 0) ? 0 : (($net_or_vat == 'vat') ? (($update_vat_amount) ? round(($sales[$currencyCode][$tax_percentage]['totalvat'] + ($acc_reverse * $salepurchase->amount)) , 2) : round($sales[$currencyCode][$tax_percentage]['totalvat'], 2)) : ((!$is_current_vat_amount) ? round(($sales[$currencyCode][$tax_percentage]['totalvat'] + $exists_invoice_vatamount[$invoice_no]['vat_amount']), 2) : round(($sales[$currencyCode][$tax_percentage]['totalvat'] + $current_vat_amount), 2) ));
                   
                    if(!in_array($invoice_no, $sale_unique_invoiceno, true)) 
                    {                  
                      array_push($sale_unique_invoiceno, $invoice_no);
                      // if(isset($sales[$currencyCode][$tax_percentage]['invoiceCount']))
                      //   $sales[$currencyCode][$tax_percentage]['invoiceCount'] = $sales[$currencyCode][$tax_percentage]['invoiceCount'] + 1; 
                      // else
                      //   $sales[$currencyCode][$tax_percentage]['invoiceCount'] = 1; 
                      if(isset($sales[$actualCurrency][$tax_percentage]['invoiceCount']))
                        $sales[$actualCurrency][$tax_percentage]['invoiceCount'] = $sales[$actualCurrency][$tax_percentage]['invoiceCount'] + 1; 
                      else
                        $sales[$actualCurrency][$tax_percentage]['invoiceCount'] = 1; 
                    }                    
                  }
                  else
                  {                    
                    $update_vat_amount = true;
                    $is_current_vat_amount = false;  
                    $current_vat_amount = 0;                  
                    if($tax_percentage > 0)
                    {
                      if($net_or_vat == 'net')
                      {
                        //$net_amount = $acc_reverse * $salepurchase->amount;
                        $net_amount = $acc_reverse * $actualAmount;
                        
                        $vat_amount = (($net_amount * $tax_percentage) / 100);

                        if($exists_invoice_vatamount[$invoice_no]['vat_amount'] == 0)                        
                          $exists_invoice_vatamount[$invoice_no]['vat_amount'] = $vat_amount;   
                        else
                        {
                          $exists_invoice_vatamount[$invoice_no]['vat_amount'] = $exists_invoice_vatamount[$invoice_no]['vat_amount'] + $vat_amount;

                          $is_current_vat_amount = true;
                          $current_vat_amount = $vat_amount;
                        }                
                      }
                      else if($net_or_vat == 'vat')
                      {  
                        //$vat_amount = $acc_reverse * $salepurchase->amount;
                        $vat_amount = $acc_reverse * $actualAmount;
                       
                        if (round((float)$vat_amount, 2) <= round((float)$exists_invoice_vatamount[$invoice_no]['vat_amount'], 2))
                          $update_vat_amount = false;
                      }                      
                    }  

                    // $sales[$currencyCode][$tax_percentage]['netamount'] = ($net_or_vat == 'net') ? round(($acc_reverse * $salepurchase->amount), 2) : 0;
                    $sales[$actualCurrency][$tax_percentage]['netamount'] = ($net_or_vat == 'net') ? round(($acc_reverse * $actualAmount), 2) : 0;

                    if($tax_percentage == 0)                  
                      //$sales[$currencyCode][$tax_percentage]['totalvat'] = 0;                  
                      $sales[$actualCurrency][$tax_percentage]['totalvat'] = 0;                  
                    else
                    {
                      if($net_or_vat == 'vat')
                      {  
                        if($update_vat_amount)                      
                          //$sales[$currencyCode][$tax_percentage]['totalvat'] = round(($acc_reverse * $salepurchase->amount),2);
                          $sales[$actualCurrency][$tax_percentage]['totalvat'] = round(($acc_reverse * $actualAmount),2);
                        else
                          // $sales[$currencyCode][$tax_percentage]['totalvat'] = round($sales[$currencyCode][$tax_percentage]['totalvat'], 2);
                          $sales[$actualCurrency][$tax_percentage]['totalvat'] = round($sales[$actualCurrency][$tax_percentage]['totalvat'], 2);
                      }
                      else
                      {
                        // if(!$is_current_vat_amount)
                        //   $sales[$currencyCode][$tax_percentage]['totalvat'] = round($exists_invoice_vatamount[$invoice_no]['vat_amount'], 2);
                        // else
                        //   $sales[$currencyCode][$tax_percentage]['totalvat'] = round($current_vat_amount, 2);
                        if(!$is_current_vat_amount)
                          $sales[$actualCurrency][$tax_percentage]['totalvat'] = round($exists_invoice_vatamount[$invoice_no]['vat_amount'], 2);
                        else
                          $sales[$actualCurrency][$tax_percentage]['totalvat'] = round($current_vat_amount, 2);
                      }
                    }

                    // $sales[$currencyCode][$tax_percentage]['totalvat'] = ($tax_percentage == 0) ? 0 : (($net_or_vat == 'vat') ? round((($update_vat_amount) ? ($acc_reverse * $salepurchase->amount) : $sales[$currencyCode][$tax_percentage]['totalvat']), 2) : ((!$is_current_vat_amount) ? round($exists_invoice_vatamount[$invoice_no]['vat_amount'], 2) : round($current_vat_amount, 2)));
                    
                    // $sales[$currencyCode][$tax_percentage]['vatpercentage'] = $tax_percentage . '%';  
                    // $sales[$currencyCode][$tax_percentage]['currencyCode'] = $salepurchase->currency;
                    $sales[$actualCurrency][$tax_percentage]['vatpercentage'] = $tax_percentage . '%';  
                    $sales[$actualCurrency][$tax_percentage]['currencyCode'] = $actualCurrency;
                    if(!in_array($invoice_no, $sale_unique_invoiceno, true)) 
                    {                  
                      array_push($sale_unique_invoiceno, $invoice_no);
                      //$sales[$currencyCode][$tax_percentage]['invoiceCount'] = 1; 
                      $sales[$actualCurrency][$tax_percentage]['invoiceCount'] = 1; 
                    }
                  }  
                }//currencyCode
                else
                {                  
                  $update_vat_amount = true; 
                  $is_current_vat_amount = false;
                  $current_vat_amount = 0;                 
                  if($tax_percentage > 0)
                  {
                    if($net_or_vat == 'net')
                    {
                      //$net_amount = $acc_reverse * $salepurchase->amount;
                      $net_amount = $acc_reverse * $actualAmount;
                      
                      $vat_amount = (($net_amount * $tax_percentage) / 100);

                      if($exists_invoice_vatamount[$invoice_no]['vat_amount'] == 0)                        
                        $exists_invoice_vatamount[$invoice_no]['vat_amount'] = $vat_amount;   
                      else
                      {
                        $exists_invoice_vatamount[$invoice_no]['vat_amount'] = $exists_invoice_vatamount[$invoice_no]['vat_amount'] + $vat_amount;

                        $is_current_vat_amount = true;
                        $current_vat_amount = $vat_amount;
                      }            
                    }
                    else if($net_or_vat == 'vat')
                    {  
                      //$vat_amount = $acc_reverse * $salepurchase->amount;
                      $vat_amount = $acc_reverse * $actualAmount;
                     
                      //if (round((float)$vat_amount, 2) <= round((float)$exists_invoice_vatamount[$invoice_no]['vat_amount'], 2))
                      if (round((float)$vat_amount, 2) == round((float)$exists_invoice_vatamount[$invoice_no]['vat_amount'], 2))
                        $update_vat_amount = false;
                    }                      
                  }  

                  // $sales[$currencyCode][$tax_percentage]['netamount'] = ($net_or_vat == 'net') ? round(($acc_reverse * $salepurchase->amount), 2) : 0;
                  $sales[$actualCurrency][$tax_percentage]['netamount'] = ($net_or_vat == 'net') ? round(($acc_reverse * $actualAmount), 2) : 0;

                  // $sales[$currencyCode][$tax_percentage]['totalvat'] = ($net_or_vat == 'vat') ? round(($acc_reverse * $salepurchase->amount), 2) : 0;

                  if($tax_percentage == 0)                  
                    //$sales[$currencyCode][$tax_percentage]['totalvat'] = 0;                  
                    $sales[$actualCurrency][$tax_percentage]['totalvat'] = 0;                  
                  else
                  {
                    if($net_or_vat == 'vat')
                    {  
                      if($update_vat_amount)                      
                        //$sales[$currencyCode][$tax_percentage]['totalvat'] = round(($acc_reverse * $salepurchase->amount),2);
                        $sales[$actualCurrency][$tax_percentage]['totalvat'] = round(($acc_reverse * $actualAmount),2);
                    }
                    else
                    {
                      // if(!$is_current_vat_amount)
                      //   $sales[$currencyCode][$tax_percentage]['totalvat'] = round($exists_invoice_vatamount[$invoice_no]['vat_amount'], 2);
                      // else
                      //   $sales[$currencyCode][$tax_percentage]['totalvat'] = round($current_vat_amount, 2);
                      if(!$is_current_vat_amount)
                        $sales[$actualCurrency][$tax_percentage]['totalvat'] = round($exists_invoice_vatamount[$invoice_no]['vat_amount'], 2);
                      else
                        $sales[$actualCurrency][$tax_percentage]['totalvat'] = round($current_vat_amount, 2);
                    }
                  }

                  // $sales[$currencyCode][$tax_percentage]['totalvat'] = ($tax_percentage == 0) ? 0 : (($net_or_vat == 'vat') ? round((($update_vat_amount) ? ($acc_reverse * $salepurchase->amount) : $sales[$currencyCode][$tax_percentage]['totalvat']), 2) : ((!$is_current_vat_amount) ? round($exists_invoice_vatamount[$invoice_no]['vat_amount'], 2) : round($current_vat_amount, 2)));
                 
                  // $sales[$currencyCode][$tax_percentage]['vatpercentage'] = $tax_percentage . '%';  
                  // $sales[$currencyCode][$tax_percentage]['currencyCode'] = $salepurchase->currency;
                  $sales[$actualCurrency][$tax_percentage]['vatpercentage'] = $tax_percentage . '%';  
                  $sales[$actualCurrency][$tax_percentage]['currencyCode'] = $actualCurrency;
                  if(!in_array($invoice_no, $sale_unique_invoiceno, true)) 
                  {                  
                    array_push($sale_unique_invoiceno, $invoice_no);
                    //$sales[$currencyCode][$tax_percentage]['invoiceCount'] = 1; 
                    $sales[$actualCurrency][$tax_percentage]['invoiceCount'] = 1; 
                  }                
                }//currencyCode
              }//sales
              }//allow  
            }//Account Invoices
            else
            {
              $tax_percentage = ($salepurchase->vatAmount == 0) ? 0 : round((($salepurchase->vatAmount/$salepurchase->netAmount) * 100));
              $currencyCode = $salepurchase->currency;

              if(isset($salepurchase->vendorId))
              {     
                
              }//purchase
              else
              {                     
                if(array_key_exists($currencyCode, $sales))
                {
                  if(array_key_exists($tax_percentage, $sales[$currencyCode]))
                  {                      
                    $sales[$currencyCode][$tax_percentage]['netamount'] = $sales[$currencyCode][$tax_percentage]['netamount'] + $salepurchase->netAmount;
                    $sales[$currencyCode][$tax_percentage]['totalvat'] = $sales[$currencyCode][$tax_percentage]['totalvat'] + $salepurchase->vatAmount;
                    if(!in_array($salepurchase->bookedInvoiceNumber, $sale_unique_invoiceno, true)) 
                    {                  
                      array_push($sale_unique_invoiceno, $salepurchase->bookedInvoiceNumber);
                      if(isset($sales[$currencyCode][$tax_percentage]['invoiceCount']))
                        $sales[$currencyCode][$tax_percentage]['invoiceCount'] = $sales[$currencyCode][$tax_percentage]['invoiceCount'] + 1; 
                      else
                        $sales[$currencyCode][$tax_percentage]['invoiceCount'] = 1; 
                    }
                  }
                  else
                  {
                    $sales[$currencyCode][$tax_percentage]['netamount'] = $salepurchase->netAmount;
                    $sales[$currencyCode][$tax_percentage]['totalvat'] = $salepurchase->vatAmount;
                    $sales[$currencyCode][$tax_percentage]['vatpercentage'] = $tax_percentage . '%';  
                    $sales[$currencyCode][$tax_percentage]['currencyCode'] = $salepurchase->currency;
                    if(!in_array($salepurchase->bookedInvoiceNumber, $sale_unique_invoiceno, true)) 
                    {                  
                      array_push($sale_unique_invoiceno, $salepurchase->bookedInvoiceNumber);
                      $sales[$currencyCode][$tax_percentage]['invoiceCount'] = 1; 
                    }
                  }  
                }//currencyCode
                else
                {
                  $sales[$currencyCode][$tax_percentage]['netamount'] = $salepurchase->netAmount;
                  $sales[$currencyCode][$tax_percentage]['totalvat'] = $salepurchase->vatAmount;
                  $sales[$currencyCode][$tax_percentage]['vatpercentage'] = $tax_percentage . '%';  
                  $sales[$currencyCode][$tax_percentage]['currencyCode'] = $salepurchase->currency;
                  if(!in_array($salepurchase->bookedInvoiceNumber, $sale_unique_invoiceno, true)) 
                  {                  
                    array_push($sale_unique_invoiceno, $salepurchase->bookedInvoiceNumber);
                    $sales[$currencyCode][$tax_percentage]['invoiceCount'] = 1; 
                  }
                }//currencyCode
              }//sales
            }//Invoices
          }//E-conomic
          else if($api_name == "Uniconta")
          {
            $tax_percentage = ($salepurchase->VatAmount == 0) ? 0 : round((($salepurchase->VatAmount/$salepurchase->NetAmount) * 100));
            $currencyCode = $salepurchase->Currency;

            if(!isset($salepurchase->CostValue))
            {                 
              if(array_key_exists($currencyCode, $purchase))
              {                  
                if(array_key_exists($tax_percentage, $purchase[$currencyCode]))
                {                      
                  $purchase[$currencyCode][$tax_percentage]['netamount'] = $purchase[$currencyCode][$tax_percentage]['netamount'] + $salepurchase->NetAmount;
                  $purchase[$currencyCode][$tax_percentage]['totalvat'] = $purchase[$currencyCode][$tax_percentage]['totalvat'] + $salepurchase->VatAmount;
                  if(!in_array($salepurchase->InvoiceNumber, $purchase_unique_invoiceno, true)) 
                  {                  
                    array_push($purchase_unique_invoiceno, $salepurchase->InvoiceNumber);
                    if(isset($purchase[$currencyCode][$tax_percentage]['invoiceCount']))
                      $purchase[$currencyCode][$tax_percentage]['invoiceCount'] = $purchase[$currencyCode][$tax_percentage]['invoiceCount'] + 1; 
                    else
                      $purchase[$currencyCode][$tax_percentage]['invoiceCount'] = 1; 
                  }
                }
                else
                {
                  $purchase[$currencyCode][$tax_percentage]['netamount'] = $salepurchase->NetAmount;
                  $purchase[$currencyCode][$tax_percentage]['totalvat'] = $salepurchase->VatAmount;
                  $purchase[$currencyCode][$tax_percentage]['vatpercentage'] = $tax_percentage . '%';  
                  $purchase[$currencyCode][$tax_percentage]['currencyCode'] = $salepurchase->Currency;
                  if(!in_array($salepurchase->InvoiceNumber, $purchase_unique_invoiceno, true)) 
                  {                  
                    array_push($purchase_unique_invoiceno, $salepurchase->InvoiceNumber);
                    $purchase[$currencyCode][$tax_percentage]['invoiceCount'] = 1; 
                  }
                }
              }
              else
              {
                $purchase[$currencyCode][$tax_percentage]['netamount'] = $salepurchase->NetAmount;
                $purchase[$currencyCode][$tax_percentage]['totalvat'] = $salepurchase->VatAmount;
                $purchase[$currencyCode][$tax_percentage]['vatpercentage'] = $tax_percentage . '%';  
                $purchase[$currencyCode][$tax_percentage]['currencyCode'] = $salepurchase->Currency;
                if(!in_array($salepurchase->InvoiceNumber, $purchase_unique_invoiceno, true)) 
                {                  
                  array_push($purchase_unique_invoiceno, $salepurchase->InvoiceNumber);
                  $purchase[$currencyCode][$tax_percentage]['invoiceCount'] = 1; 
                }
              }
            }//purchase
            else
            {              
              if(array_key_exists($currencyCode, $sales))
              {                  
                if(array_key_exists($tax_percentage, $sales[$currencyCode]))
                {                      
                  $sales[$currencyCode][$tax_percentage]['netamount'] = $sales[$currencyCode][$tax_percentage]['netamount'] + $salepurchase->NetAmount;
                  $sales[$currencyCode][$tax_percentage]['totalvat'] = $sales[$currencyCode][$tax_percentage]['totalvat'] + $salepurchase->VatAmount;
                  if(!in_array($salepurchase->InvoiceNumber, $sale_unique_invoiceno, true)) 
                  {                  
                    array_push($sale_unique_invoiceno, $salepurchase->InvoiceNumber);
                    if(isset($sales[$currencyCode][$tax_percentage]['invoiceCount']))
                      $sales[$currencyCode][$tax_percentage]['invoiceCount'] = $sales[$currencyCode][$tax_percentage]['invoiceCount'] + 1; 
                    else
                      $sales[$currencyCode][$tax_percentage]['invoiceCount'] = 1; 
                  }
                }
                else
                {
                  $sales[$currencyCode][$tax_percentage]['netamount'] = $salepurchase->NetAmount;
                  $sales[$currencyCode][$tax_percentage]['totalvat'] = $salepurchase->VatAmount;
                  $sales[$currencyCode][$tax_percentage]['vatpercentage'] = $tax_percentage . '%';  
                  $sales[$currencyCode][$tax_percentage]['currencyCode'] = $salepurchase->Currency;
                  if(!in_array($salepurchase->InvoiceNumber, $sale_unique_invoiceno, true)) 
                  {                  
                    array_push($sale_unique_invoiceno, $salepurchase->InvoiceNumber);
                    $sales[$currencyCode][$tax_percentage]['invoiceCount'] = 1; 
                  }
                }  
              }  
              else
              {
                $sales[$currencyCode][$tax_percentage]['netamount'] = $salepurchase->NetAmount;
                $sales[$currencyCode][$tax_percentage]['totalvat'] = $salepurchase->VatAmount;
                $sales[$currencyCode][$tax_percentage]['vatpercentage'] = $tax_percentage . '%';  
                $sales[$currencyCode][$tax_percentage]['currencyCode'] = $salepurchase->Currency;
                if(!in_array($salepurchase->InvoiceNumber, $sale_unique_invoiceno, true)) 
                {                  
                  array_push($sale_unique_invoiceno, $salepurchase->InvoiceNumber);
                  $sales[$currencyCode][$tax_percentage]['invoiceCount'] = 1; 
                }
              }       
            }//sales
          }//Uniconta
          else if($api_name == "Shopify")
          {
            $tax_percentage = ($salepurchase->total_tax == 0) ? 0 : round((($salepurchase->total_tax/$salepurchase->subtotal_price) * 100));
            $currencyCode = $salepurchase->currency;

            if(isset($salepurchase->CostValue))
            {                
              if(array_key_exists($currencyCode, $purchase)) 
              {                
                if(array_key_exists($tax_percentage, $purchase[$currencyCode]))
                {                      
                  $purchase[$currencyCode][$tax_percentage]['netamount'] = $purchase[$currencyCode][$tax_percentage]['netamount'] + $salepurchase->subtotal_price;
                  $purchase[$currencyCode][$tax_percentage]['totalvat'] = $purchase[$currencyCode][$tax_percentage]['totalvat'] + $salepurchase->total_tax;
                  if(!in_array($salepurchase->order_number, $purchase_unique_invoiceno, true)) 
                  {                  
                    array_push($purchase_unique_invoiceno, $salepurchase->order_number);
                    if(isset($purchase[$currencyCode][$tax_percentage]['invoiceCount']))
                      $purchase[$currencyCode][$tax_percentage]['invoiceCount'] = $purchase[$currencyCode][$tax_percentage]['invoiceCount'] + 1; 
                    else
                      $purchase[$currencyCode][$tax_percentage]['invoiceCount'] = 1; 
                  }  
                }
                else
                {
                  $purchase[$currencyCode][$tax_percentage]['netamount'] = $salepurchase->subtotal_price;
                  $purchase[$currencyCode][$tax_percentage]['totalvat'] = $salepurchase->total_tax;
                  $purchase[$currencyCode][$tax_percentage]['vatpercentage'] = $tax_percentage . '%';  
                  $purchase[$currencyCode][$tax_percentage]['currencyCode'] = $salepurchase->currency;
                  if(!in_array($salepurchase->order_number, $purchase_unique_invoiceno, true)) 
                  {                  
                    array_push($purchase_unique_invoiceno, $salepurchase->order_number);
                    $purchase[$currencyCode][$tax_percentage]['invoiceCount'] = 1; 
                  }
                }
              }
              else
              {
                $purchase[$currencyCode][$tax_percentage]['netamount'] = $salepurchase->subtotal_price;
                $purchase[$currencyCode][$tax_percentage]['totalvat'] = $salepurchase->total_tax;
                $purchase[$currencyCode][$tax_percentage]['vatpercentage'] = $tax_percentage . '%';  
                $purchase[$currencyCode][$tax_percentage]['currencyCode'] = $salepurchase->currency;
                if(!in_array($salepurchase->order_number, $purchase_unique_invoiceno, true)) 
                {                  
                  array_push($purchase_unique_invoiceno, $salepurchase->order_number);
                  $purchase[$currencyCode][$tax_percentage]['invoiceCount'] = 1; 
                }
              }
            }//purchase
            else
            {                
              if(array_key_exists($currencyCode, $sales))
              {                  
                if(array_key_exists($tax_percentage, $sales[$currencyCode]))
                {                      
                  $sales[$currencyCode][$tax_percentage]['netamount'] = $sales[$currencyCode][$tax_percentage]['netamount'] + $salepurchase->subtotal_price;
                  $sales[$currencyCode][$tax_percentage]['totalvat'] = $sales[$currencyCode][$tax_percentage]['totalvat'] + $salepurchase->total_tax;
                  if(!in_array($salepurchase->order_number, $sale_unique_invoiceno, true)) 
                  {                  
                    array_push($sale_unique_invoiceno, $salepurchase->order_number);
                    if(isset($sales[$currencyCode][$tax_percentage]['invoiceCount']))
                      $sales[$currencyCode][$tax_percentage]['invoiceCount'] = $sales[$currencyCode][$tax_percentage]['invoiceCount'] + 1; 
                    else
                      $sales[$currencyCode][$tax_percentage]['invoiceCount'] = 1; 
                  }
                }
                else
                {
                  $sales[$currencyCode][$tax_percentage]['netamount'] = $salepurchase->subtotal_price;
                  $sales[$currencyCode][$tax_percentage]['totalvat'] = $salepurchase->total_tax;
                  $sales[$currencyCode][$tax_percentage]['vatpercentage'] = $tax_percentage . '%';  
                  $sales[$currencyCode][$tax_percentage]['currencyCode'] = $salepurchase->currency;
                  if(!in_array($salepurchase->order_number, $sale_unique_invoiceno, true)) 
                  {                  
                    array_push($sale_unique_invoiceno, $salepurchase->order_number);
                    $sales[$currencyCode][$tax_percentage]['invoiceCount'] = 1; 
                  }
                }  
              } 
              else
              {
                $sales[$currencyCode][$tax_percentage]['netamount'] = $salepurchase->subtotal_price;
                $sales[$currencyCode][$tax_percentage]['totalvat'] = $salepurchase->total_tax;
                $sales[$currencyCode][$tax_percentage]['vatpercentage'] = $tax_percentage . '%';  
                $sales[$currencyCode][$tax_percentage]['currencyCode'] = $salepurchase->currency;
                if(!in_array($salepurchase->order_number, $sale_unique_invoiceno, true)) 
                {                  
                  array_push($sale_unique_invoiceno, $salepurchase->order_number);
                  $sales[$currencyCode][$tax_percentage]['invoiceCount'] = 1; 
                }
              }        
            }//sales
          }//Shopify   
          else if($api_name == "Billy")
          {
            $tax_percentage = ($salepurchase->tax == 0) ? 0 : round((($salepurchase->tax/$salepurchase->amount) * 100));
            $currencyCode = $salepurchase->currencyId;
            
            if(array_key_exists($currencyCode, $sales))
            {                  
              if(array_key_exists($tax_percentage, $sales[$currencyCode]))
              {                      
                $sales[$currencyCode][$tax_percentage]['netamount'] = $sales[$currencyCode][$tax_percentage]['netamount'] + $salepurchase->amount;
                $sales[$currencyCode][$tax_percentage]['totalvat'] = $sales[$currencyCode][$tax_percentage]['totalvat'] + $salepurchase->tax;
                if(!in_array($salepurchase->invoiceNo, $sale_unique_invoiceno, true)) 
                {                  
                  array_push($sale_unique_invoiceno, $salepurchase->invoiceNo);
                  if(isset($sales[$currencyCode][$tax_percentage]['invoiceCount']))
                    $sales[$currencyCode][$tax_percentage]['invoiceCount'] = $sales[$currencyCode][$tax_percentage]['invoiceCount'] + 1; 
                  else
                    $sales[$currencyCode][$tax_percentage]['invoiceCount'] = 1; 
                }
              }
              else
              {
                $sales[$currencyCode][$tax_percentage]['netamount'] = $salepurchase->amount;
                $sales[$currencyCode][$tax_percentage]['totalvat'] = $salepurchase->tax;
                $sales[$currencyCode][$tax_percentage]['vatpercentage'] = $tax_percentage . '%';  
                $sales[$currencyCode][$tax_percentage]['currencyCode'] = $salepurchase->currencyId;
                if(!in_array($salepurchase->invoiceNo, $sale_unique_invoiceno, true)) 
                {                  
                  array_push($sale_unique_invoiceno, $salepurchase->invoiceNo);
                  $sales[$currencyCode][$tax_percentage]['invoiceCount'] = 1; 
                }
              }  
            }  
            else
            {
              $sales[$currencyCode][$tax_percentage]['netamount'] = $salepurchase->amount;
              $sales[$currencyCode][$tax_percentage]['totalvat'] = $salepurchase->tax;
              $sales[$currencyCode][$tax_percentage]['vatpercentage'] = $tax_percentage . '%';  
              $sales[$currencyCode][$tax_percentage]['currencyCode'] = $salepurchase->currencyId;
              if(!in_array($salepurchase->invoiceNo, $sale_unique_invoiceno, true)) 
              {                  
                array_push($sale_unique_invoiceno, $salepurchase->invoiceNo);
                $sales[$currencyCode][$tax_percentage]['invoiceCount'] = 1; 
              }
            }                   
          }//Billy                            
        
          }/* --end for CHUNKS OF DATAS -- */

          $startRow = $endRow + 1;

        } while ($startRow <= $original_highestRow); /* --end while CHUNKS OF DATAS -- */
      } ///if !FTP    

      return [
        'sales' => $sales,
        'purchase' => $purchase
      ];
    }

    public function getFileExtension($filename)
    {
      $file_extension_arr = explode('.',$filename);
      $file_extension_length = count($file_extension_arr);
      $file_extension = ($file_extension_length > 0) ? $file_extension_arr[$file_extension_length-1] : ''; 

      return $file_extension;
    }

    public function readVatReturnFile($url, $type = NULL, $extension = '.xlsx', $data_detail = [], $invoice_rows = [])
    {     
      $contents = (strpos($url, "https://") !== false) ? file_get_contents($url) : $url;        
      $storage_path = storage_path('app/public/');
      $filename = Str::random(10). $extension;
      
      Storage::disk('public')->put($filename, $contents);     

      if(strtolower($extension) == 'xml')
      {
        $xmlString = $contents;
        $xmlObject = simplexml_load_string($xmlString);
        $json = json_encode($xmlObject);
        $phpArray = json_decode($json, true); 
               
        foreach($phpArray as $key => $invoices)
        {              
          foreach($invoices as $invoice)
          { 
            $rowName = (strtolower($invoice['@attributes']['type']) == 'sale') ? 'sales' : 'purchase';
            
            $tax_code = ($invoice['TaxCode']) ? $invoice['TaxCode'] : '';
            $vat_date = ($invoice['InvoiceDate']) ? $invoice['InvoiceDate'] : '';
            $invoice_no = ($invoice['InvoiceNumber']) ? $invoice['InvoiceNumber'] : '';
            $currency_code = ($invoice['CurrencyCode']) ? $invoice['CurrencyCode'] : '';
            $amount = ($invoice['TotalNET_InvoiceCurrency']) ? $invoice['TotalNET_InvoiceCurrency'] : '0';
            $vat_percentage = $invoice['VATRate'];
            $total_invoice_vat = ($invoice['TotalVAT_InvoiceCurrency']) ? $invoice['TotalVAT_InvoiceCurrency'] : '0';
            $amount_incl_vat = ($invoice['TotalGROSS_InvoiceCurrency']) ? $invoice['TotalGROSS_InvoiceCurrency'] : '0';
            $local_currency_code = ($invoice['LocalCurrencyCode']) ? $invoice['LocalCurrencyCode'] : '';
            $exchange_rate = ($invoice['ExchangeRate']) ? $invoice['ExchangeRate'] : '0';
            $local_amount = ($invoice['TotalNET_LocalCurrency']) ? $invoice['TotalNET_LocalCurrency'] : '0';
            $local_total_invoice_vat = ($invoice['TotalVAT_LocalCurrency']) ? $invoice['TotalVAT_LocalCurrency'] : '0';
            $local_amount_incl_vat = ($invoice['TotalGROSS_LocalCurrency']) ? $invoice['TotalGROSS_LocalCurrency'] : '0';
            
            $country_orgin = ($invoice['N']) ? $invoice['N'] : '';
            $country_destination = ($invoice['O']) ? $invoice['O'] : '';
            $referenced_invoice = ($invoice['P']) ? $invoice['P'] : '';
            $referenced_invoice_date = ($invoice['Q']) ? $invoice['Q'] : '';
            
            $account_name = ($invoice['Name']) ? $invoice['Name'] : '';
            $vat_no = ($invoice['VATNumber_ifapplicable']) ? $invoice['VATNumber_ifapplicable'] : '';

            $client_street = ($rowName == 'sales') ? $invoice['ClientStreet'] : $invoice['CustomerStreet'];
            $client_houseno = ($rowName == 'sales') ? $invoice['ClientHouseAndOfficeNo'] : $invoice['CustomerHouseAndOfficeNo'];
            $client_city = ($rowName == 'sales') ? $invoice['ClientCity'] : $invoice['CustomerCity'];
            
            $client_street = ($client_street) ? $client_street : '';
            $client_houseno = ($client_houseno) ? $client_houseno : '';
            $client_city = ($client_city) ? $client_city : '';
            $client_postcode = ($invoice['PostalCode']) ? $invoice['PostalCode'] : '';
            $client_countrycode = ($invoice['CountryCode']) ? $invoice['CountryCode'] : '';
            
            if($vat_date !== null && $invoice_no !== null && $currency_code !== null && $amount !== null && $vat_percentage !== null && $total_invoice_vat !== null && $amount_incl_vat !== null)
            {             
              if($type === null)
                $data_detail[$rowName][$currency_code][$vat_percentage][] = [
                  'type' => ($rowName == 'sales') ? 'sale' : $rowName,
                  'vat_date' => $vat_date,
                  'invoice_no' => $invoice_no,
                  'currency_code' => ($local_currency_code) ? $local_currency_code : $currency_code,
                  'amount' => ($local_amount) ? $local_amount : $amount,
                  'vat_percentage' => $vat_percentage,
                  'total_invoice_vat' => ($local_total_invoice_vat) ? $local_total_invoice_vat : $total_invoice_vat,
                  'amount_incl_vat' => ($local_amount_incl_vat) ? $local_amount_incl_vat : $amount_incl_vat,
                  'account_name' => $account_name,
                  'vat_no' => $vat_no             
                ];
              
                $invoice_rows[] = [
                    'type' => ($rowName == 'sales') ? 'sale' : $rowName,
                    'tax_code' => $tax_code,
                    'vat_date' => $vat_date,
                    'invoice_no' => $invoice_no,
                    'currency_code' => $currency_code,
                    'amount' => $amount,
                    'vat_percentage' => $vat_percentage,
                    'total_invoice_vat' => $total_invoice_vat,
                    'amount_incl_vat' => $amount_incl_vat,
                    'local_currency_code' => $local_currency_code,
                    'exchange_rate' => $exchange_rate,
                    'local_amount' => $local_amount,
                    'local_total_invoice_vat' => $local_total_invoice_vat,
                    'local_amount_incl_vat' => $local_amount_incl_vat,
                    'country_orgin' => $country_orgin,
                    'country_destination' => $country_destination,
                    'referenced_invoice' => $country_destination,
                    'referenced_invoice_date' => $referenced_invoice_date,
                    'account_name' => $account_name,
                    'vat_no' => $vat_no,
                    'client_street' => $client_street,
                    'client_houseno' => $client_houseno,
                    'client_city' => $client_city,
                    'client_postcode' => $client_postcode,
                    'client_countrycode' => $client_countrycode             
                ];
  
            }            
          }         
        }

        Storage::disk('public')->delete($filename);

        return [
          'data_detail' => $data_detail,
          'invoice_rows' => $invoice_rows
        ];
      }//xml
      else if(strtolower($extension) == 'csv')
      {
        $reader = new Csv();       
        $inputFileName = $storage_path . $filename;

        $encoding = \PhpOffice\PhpSpreadsheet\Reader\Csv::guessEncoding($inputFileName);        
        $reader->setInputEncoding($encoding);

        $spreadsheet = $reader->load($inputFileName);

        try
        {
          $worksheetData = $spreadsheet->getActiveSheet()->toArray();
       
          foreach ($worksheetData as $worksheet) 
          {
            $tax_code = $worksheet[0];            
            $_negative_symb = ($tax_code == "DSGS_CN" || $tax_code == "EXG_CN" || $tax_code == "EXS_CN") ? "-" : "";

            $rowName = ($tax_code == 'DSGS' || $tax_code == 'DSGS_CN' || $tax_code == 'EXG' || $tax_code == 'EXG_CN' || $tax_code == 'EXS' || $tax_code == 'EXS_CN') ? 'sales' : 'purchase';

            $vat_date = $worksheet[1];
            if(is_numeric($vat_date))
              $vat_date =  \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($vat_date)->format('Y-m-d');
            else
            {  
              //m/d/Y
              if ($this->isValidMdYDate($vat_date))
              {
                $_invoice_date = str_replace('/', '-', $vat_date);
                $_arr_date = explode('-', str_replace('/', '-', $_invoice_date));

                $vat_date = $_arr_date[1] . '-' . $_arr_date[0] . '-' . $_arr_date[2];
              }                                           
            }

            $invoice_no = $worksheet[2];
            $currency_code = $worksheet[3];
            $amount = ((stripos($worksheet[4], "-") !== false) ? '' : $_negative_symb) . $worksheet[4];
            $vat_percentage = $worksheet[5];
            $total_invoice_vat = ((stripos($worksheet[6], "-") !== false) ? '' : $_negative_symb) . $worksheet[6];
            $amount_incl_vat = ((stripos($worksheet[7], "-") !== false) ? '' : $_negative_symb) . $worksheet[7];
            $local_currency_code = $worksheet[8];
            $exchange_rate = $worksheet[9];
            $local_amount = ((stripos($worksheet[10], "-") !== false) ? '' : $_negative_symb) . $worksheet[10];         
            $local_total_invoice_vat = ((stripos($worksheet[11], "-") !== false) ? '' : $_negative_symb) . $worksheet[11];
            $local_amount_incl_vat = ((stripos($worksheet[12], "-") !== false) ? '' : $_negative_symb) . $worksheet[12];
                              
            $country_orgin = $worksheet[13];
            $country_destination = $worksheet[14];
            $referenced_invoice = $worksheet[15];
            $referenced_invoice_date = $worksheet[16];
            
            $account_name = $worksheet[17];
            $vat_no = $worksheet[18]; 

            $client_street = $worksheet[19];
            $client_houseno = $worksheet[19]; 
            $client_city = $worksheet[20];
            $client_postcode = $worksheet[21]; 
            $client_countrycode = $worksheet[22];    

            if($vat_date !== null && $invoice_no !== null && $currency_code !== null && $amount !== null && $vat_percentage !== null && $total_invoice_vat !== null && $amount_incl_vat !== null)
            {
              if($type === null)
                $data_detail[$rowName][$currency_code][$vat_percentage][] = [
                  'type' => ($rowName == 'sales') ? 'sale' : $rowName,
                  'vat_date' => $vat_date,
                  'invoice_no' => $invoice_no,
                  'currency_code' => ($local_currency_code) ? $local_currency_code : $currency_code,
                  'amount' => ($local_amount) ? $local_amount : $amount,
                  'vat_percentage' => $vat_percentage,
                  'total_invoice_vat' => ($local_total_invoice_vat) ? $local_total_invoice_vat : $total_invoice_vat,
                  'amount_incl_vat' => ($local_amount_incl_vat) ? $local_amount_incl_vat : $amount_incl_vat,
                  'account_name' => $account_name,
                  'vat_no' => $vat_no             
                ];
           
              $invoice_rows[] = [
                  'type' => ($rowName == 'sales') ? 'sale' : $rowName,
                  'tax_code' => $tax_code,
                  'vat_date' => $vat_date,
                  'invoice_no' => $invoice_no,
                  'currency_code' => $currency_code,
                  'amount' => $amount,
                  'vat_percentage' => $vat_percentage,
                  'total_invoice_vat' => $total_invoice_vat,
                  'amount_incl_vat' => $amount_incl_vat,
                  'local_currency_code' => $local_currency_code,
                  'exchange_rate' => $exchange_rate,
                  'local_amount' => $local_amount,
                  'local_total_invoice_vat' => $local_total_invoice_vat,
                  'local_amount_incl_vat' => $local_amount_incl_vat,
                  'country_orgin' => $country_orgin,
                  'country_destination' => $country_destination,
                  'referenced_invoice' => $country_destination,
                  'referenced_invoice_date' => $referenced_invoice_date,
                  'account_name' => $account_name,
                  'vat_no' => $vat_no,
                  'client_street' => $client_street,
                  'client_houseno' => $client_houseno,
                  'client_city' => $client_city,
                  'client_postcode' => $client_postcode,
                  'client_countrycode' => $client_countrycode             
                ];
            }
          }

          Storage::disk('public')->delete($filename);

          return [
            'data_detail' => $data_detail,
            'invoice_rows' => $invoice_rows
          ];

        } //try
        catch (\Exception $e) 
        {   dd($e);
          return "error";
        }      
      }//csv
      else
      {
        $spreadsheet = new Spreadsheet();

        $inputFileType = 'Xlsx';    
        $inputFileName = $storage_path . $filename;
              
        $reader = IOFactory::createReader($inputFileType);     
        $reader->setReadDataOnly(true);

        try 
        {
          $worksheetData = $reader->listWorksheetInfo($inputFileName);    

          //$data_detail = [];        
          foreach ($worksheetData as $worksheet) 
          {
            $sheetName = $worksheet['worksheetName'];

            if(strtolower($sheetName) == 'invs issued' || strtolower($sheetName) == 'invs received' || strtolower($sheetName) == 'sales' || strtolower($sheetName) == 'purchases')
            {
              $rowName = (strtolower($sheetName) == 'invs issued' || strtolower($sheetName) == 'sales') ? 'sales' : 'purchase';
                    
              $reader->setLoadSheetsOnly($sheetName);   
              $reader->setReadDataOnly(FALSE);           
              $spreadsheet = $reader->load($inputFileName);

              $worksheet = $spreadsheet->getActiveSheet();
              
              $highestRow = $worksheet->getHighestRow(); 
              $highestColumn = $worksheet->getHighestColumn();
                                           
              $chunkSize = 1000; // Adjust as needed
              $startRow = 2;

              $firstRowData = $worksheet->rangeToArray('A' . $startRow . ':X' . $startRow);

              $filter_firstRowData = array_filter($firstRowData, function ($item) {         
                $filter_item = array_filter($item, function($value) { return (!is_null($value) && $value !== ''); });

                if (!empty($filter_item))
                  return $filter_item;         
              });
              
              if (!empty($filter_firstRowData))
              {
                if(strtolower($filter_firstRowData[0][0]) == 'tax code' || strtolower($filter_firstRowData[0][0]) == 'taxcode' || 
                  strtolower($filter_firstRowData[0][0]) == 'tax_code' || strtolower($filter_firstRowData[0][0]) == 'tax-code' ||

                  strtolower($filter_firstRowData[0][1]) == 'invoice date' || strtolower($filter_firstRowData[0][1]) == 'invoicedate' ||
                  strtolower($filter_firstRowData[0][1]) == 'invoice_date' || strtolower($filter_firstRowData[0][1]) == 'invoice-date' ||

                  strtolower($filter_firstRowData[0][2]) == 'invoice number' || strtolower($filter_firstRowData[0][2]) == 'invoicenumber' ||
                  strtolower($filter_firstRowData[0][2]) == 'invoice_number' || strtolower($filter_firstRowData[0][2]) == 'invoice-number' ||

                  strtolower($filter_firstRowData[0][3]) == 'currency code' || strtolower($filter_firstRowData[0][3]) == 'currencycode' ||
                  strtolower($filter_firstRowData[0][3]) == 'currency_code' || strtolower($filter_firstRowData[0][3]) == 'currency-code'

                )
                    $startRow = 3;

                do {
                  $endRow = min($startRow + $chunkSize - 1, $highestRow);
                  
                  // Process chunk of rows
                  for ($row = $startRow; $row <= $endRow; $row++) 
                  {
                    $rowData = $worksheet->rangeToArray('A' . $row . ':X' . $row);
                       
                    $tax_code = trim($rowData[0][0]);
                    $vat_date = trim($rowData[0][1]);

                    if(is_numeric($vat_date))
                        $vat_date =  \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($vat_date)->format('Y-m-d');
                    else
                    {  
                      //m/d/Y
                      if ($this->isValidMdYDate($vat_date))
                      {
                        $_invoice_date = str_replace('/', '-', $vat_date);
                        $_arr_date = explode('-', str_replace('/', '-', $_invoice_date));

                        $vat_date = $_arr_date[1] . '-' . $_arr_date[0] . '-' . $_arr_date[2];
                      }                                           
                    }
                      
                    $invoice_no = trim($rowData[0][2]);
                    $currency_code = trim($rowData[0][3]);
                    $amount = trim($rowData[0][4]);

                    $vat_percentage_format = trim($rowData[0][5]); 
                    if(stripos($vat_percentage_format, "%") !== false)                                        
                      $vat_percentage = str_replace('%', '', trim($rowData[0][5]));                    
                    else
                      $vat_percentage = trim($rowData[0][5]);

                    $total_invoice_vat = trim($rowData[0][6]);
                    $amount_incl_vat = trim($rowData[0][7]);
                    $local_currency_code = trim($rowData[0][8]);
                    $exchange_rate = trim($rowData[0][9]);
                    $local_amount = trim($rowData[0][10]);    
                    $local_total_invoice_vat = trim($rowData[0][11]);
                    $local_amount_incl_vat = trim($rowData[0][12]);

                    $country_orgin = trim($rowData[0][13]);
                    $country_destination = trim($rowData[0][14]);
                    $referenced_invoice = trim($rowData[0][15]);
                    $referenced_invoice_date = trim($rowData[0][16]);
                    
                    $account_name = trim($rowData[0][17]);
                    $vat_no = trim($rowData[0][18]);

                    $client_street = trim($rowData[0][19]);
                    $client_houseno = trim($rowData[0][20]);
                    $client_city = trim($rowData[0][21]);
                    $client_postcode = trim($rowData[0][22]);
                    $client_countrycode = trim($rowData[0][23]);

                    if(($vat_date !== null && $vat_date != "") && ($invoice_no !== null && $invoice_no != "") && ($currency_code !== null && $currency_code != "") && ($amount !== null && $amount != "") && ($vat_percentage !== null && $vat_percentage != "") && ($total_invoice_vat !== null && $total_invoice_vat != "") && ($amount_incl_vat !== null && $amount_incl_vat != ""))
                    {         
                      if($type === null)
                        $data_detail[$rowName][$currency_code][$vat_percentage][] = [
                          'type' => ($rowName == 'sales') ? 'sale' : $rowName,
                          'vat_date' => $vat_date,
                          'invoice_no' => $invoice_no,                       
                          'currency_code' => ($local_currency_code) ? $local_currency_code : $currency_code,
                          'amount' => ($local_amount) ? $local_amount : $amount,
                          'vat_percentage' => $vat_percentage,
                          'total_invoice_vat' => ($local_total_invoice_vat) ? $local_total_invoice_vat : $total_invoice_vat,
                          'amount_incl_vat' => ($local_amount_incl_vat) ? $local_amount_incl_vat : $amount_incl_vat,
                          'account_name' => $account_name,
                          'vat_no' => $vat_no             
                        ];
                   
                      $invoice_rows[] = [
                          'type' => ($rowName == 'sales') ? 'sale' : $rowName,
                          'tax_code' => $tax_code,
                          'vat_date' => $vat_date,
                          'invoice_no' => $invoice_no,
                          'currency_code' => $currency_code,
                          'amount' => $amount,
                          'vat_percentage' => $vat_percentage,
                          'total_invoice_vat' => $total_invoice_vat,
                          'amount_incl_vat' => $amount_incl_vat,
                          'local_currency_code' => $local_currency_code,
                          'exchange_rate' => $exchange_rate,
                          'local_amount' => $local_amount,
                          'local_total_invoice_vat' => $local_total_invoice_vat,
                          'local_amount_incl_vat' => $local_amount_incl_vat,
                          'country_orgin' => $country_orgin,
                          'country_destination' => $country_destination,
                          'referenced_invoice' => $country_destination,
                          'referenced_invoice_date' => $referenced_invoice_date,
                          'account_name' => $account_name,
                          'vat_no' => $vat_no,
                          'client_street' => $client_street,
                          'client_houseno' => $client_houseno,
                          'client_city' => $client_city,
                          'client_postcode' => $client_postcode,
                          'client_countrycode' => $client_countrycode             
                        ];
                    }    
                  } //chunk for
                 
                  $startRow = $endRow + 1;                  
                } while ($startRow <= $highestRow);
              }//sheet has datas  
            }        
          }
          
          Storage::disk('public')->delete($filename);

          return [
            'data_detail' => $data_detail,
            'invoice_rows' => $invoice_rows
          ];

        }  //try
        catch (\Exception $e) 
        {   dd($e);
          return "error";
        }
      }//xlsx
    }

    function isValidMdYDate($dateString) {
      // Regular expression for month/dd/year format (MM/DD/YYYY)     
      $pattern = '/^(0?[1-9]|1[0-2])\/(0?[1-9]|[12][0-9]|3[01])\/(19|20)\d{2}$/';

      return preg_match($pattern, $dateString) === 1;
    }

    //GET VAT Returns from TABLE
    public function VATReturnsFromTable($vatreturns)
    {
        $sales = [];
        $purchase = [];
        foreach($vatreturns as $vatreturn)
        {
          if($vatreturn->invoice_type == "sale")
            $sales[$vatreturn->currency_code][round($vatreturn->vat_percentage,0)] = [                          
              "netamount" => is_numeric($vatreturn->net_amount) ? $vatreturn->net_amount : $this->decryptValue($vatreturn->net_amount),
              "vatpercentage" => round($vatreturn->vat_percentage,0) . '%',
              "totalvat" => is_numeric($vatreturn->vat_amount) ? $vatreturn->vat_amount : $this->decryptValue($vatreturn->vat_amount),
              "currencyCode" => $vatreturn->currency_code,
              "invoiceCount" => $vatreturn->invoice_count
            ];

          if($vatreturn->invoice_type == "purchase")
            $purchase[$vatreturn->currency_code][round($vatreturn->vat_percentage,0)] = [                          
              "netamount" => is_numeric($vatreturn->net_amount) ? $vatreturn->net_amount : $this->decryptValue($vatreturn->net_amount),
              "vatpercentage" => round($vatreturn->vat_percentage,0) . '%',
              "totalvat" => is_numeric($vatreturn->vat_amount) ? $vatreturn->vat_amount : $this->decryptValue($vatreturn->vat_amount),
              "currencyCode" => $vatreturn->currency_code,
              "invoiceCount" => $vatreturn->invoice_count
            ];
        }

        return ['sales' => $sales, 'purchase' => $purchase];
    }   

    public function updateVatReturnsValue($vatregistration, $sales, $purchase)
    {           
      $deleteVatreturns = VATReturns::where('vat_reg_id', $vatregistration->vat_reg_id)
                              ->where('invoice_type', 'sale')      
                              ->delete();  

      if(!empty($sales))      
      {
        foreach($sales as $key=>$sale_currency)
        {
          foreach($sale_currency as $sale_total)
          {
            $sale_vatreturns = VATReturns::updateOrCreate(  
              [
                'vat_reg_id' => $vatregistration->vat_reg_id, 
                'invoice_type' => 'sale', 
                'vat_percentage' => str_replace('%', '', $sale_total['vatpercentage']), 
                'currency_code' => $key
              ],                
              [
                'vat_reg_id' => $vatregistration->vat_reg_id,
                'invoice_type' => 'sale', 
                'vat_percentage' => str_replace('%', '', $sale_total['vatpercentage']),
                'vat_amount' => $sale_total['totalvat'],
                'net_amount' => $sale_total['netamount'],
                'currency_code' => $sale_total['currencyCode'],
                'invoice_count' => isset($sale_total['invoiceCount']) ? $sale_total['invoiceCount'] : 0
              ]
            );             
         
            // $checkSaleVatreturn = VATReturns::where('vat_reg_id', $vatregistration->vat_reg_id)
            //                     ->where('invoice_type', 'sale')
            //                     ->where('vat_percentage', str_replace('%', '', $sale_total['vatpercentage']))      
            //                     ->where('currency_code', $key)
            //                     ->first();

            if($sale_vatreturns)
            {
              if($sale_vatreturns->net_amount == 0)
              {
                $sale_vat_rate = str_replace('%', '', $sale_total['vatpercentage']);
                if($sale_vat_rate == 0)
                  $sale_vatreturns->delete();
                else
                {
                  $sales_net_amount = ($sale_vatreturns->vat_amount * 100) /$sale_vat_rate;

                  if($sales_net_amount == 0)              
                    $sale_vatreturns->delete();
                  else
                  {
                    $sale_vatreturns->net_amount = $sales_net_amount;
                    $sale_vatreturns->save();
                  }
                }
              }
            } //delete if the NET amount is 0 
          }//for VAT percentage
        }//for Currency
      }

      $deleteVatreturns = VATReturns::where('vat_reg_id', $vatregistration->vat_reg_id)
                              ->where('invoice_type', 'purchase')      
                              ->delete(); 

      if(!empty($purchase))
      {
        foreach($purchase as $key=>$purchase_currency)
        {
          foreach($purchase_currency as $purchase_total)
          {
            $purchase_vatreturns = VATReturns::updateOrCreate(  
              [
                'vat_reg_id' => $vatregistration->vat_reg_id, 
                'invoice_type' => 'purchase', 
                'vat_percentage' => str_replace('%', '', $purchase_total['vatpercentage']), 
                'currency_code' => $key
              ],                
              [
                'vat_reg_id' => $vatregistration->vat_reg_id,
                'invoice_type' => 'purchase', 
                'vat_percentage' => str_replace('%', '', $purchase_total['vatpercentage']),
                'vat_amount' => $purchase_total['totalvat'],
                'net_amount' => $purchase_total['netamount'],
                'currency_code' => $purchase_total['currencyCode'],
                'invoice_count' => isset($purchase_total['invoiceCount']) ? $purchase_total['invoiceCount'] : 0
              ]
            );            
          
            // $checkPurchaseVatreturn = VATReturns::where('vat_reg_id', $vatregistration->vat_reg_id)
            //                   ->where('invoice_type', 'purchase')
            //                   ->where('vat_percentage', str_replace('%', '', $purchase_total['vatpercentage']))      
            //                   ->where('currency_code', $key)
            //                   ->first();

            if($purchase_vatreturns)
            {          
              if($purchase_vatreturns->net_amount == 0)
              {
                $purchase_vat_rate = str_replace('%', '', $purchase_total['vatpercentage']);
                if($purchase_vat_rate == 0)
                {
                  if($purchase_vatreturns->vat_amount != 0)
                  {
                    // $purchase_net_amount = ($purchase_vatreturns->vat_amount * 100) / 100;

                    // if($purchase_net_amount == 0)              
                    //   $purchase_vatreturns->delete();
                    // else
                    // {
                      //$purchase_vatreturns->net_amount = $purchase_net_amount;
                      $purchase_vatreturns->net_amount = 0;
                      $purchase_vatreturns->save();
                    //}
                  }
                  else
                    $purchase_vatreturns->delete();
                }
                else
                {
                  $purchase_net_amount = ($purchase_vatreturns->vat_amount * 100) /$purchase_vat_rate;

                  if($purchase_net_amount == 0)              
                    $purchase_vatreturns->delete();
                  else
                  {
                    $purchase_vatreturns->net_amount = $purchase_net_amount;
                    $purchase_vatreturns->save();
                  }
                }
              }
            } //delete if the NET amount is 0 
          }//for VAT percentage
        }//for Currency
      }
    } 

    //UPDATE API Details
    public function updateApiDetails($clientID, $api_details)
    {       
        $clients = ClientApi::updateOrCreate(
              [
                'client_id' => $clientID,
                'vat_reg_main_id' => $api_details['vat_reg_main_id']
              ],
              [                
                'api_company_id' => $api_details['api_company_id'],
              ]
            );

        return $clients;
    }

    //Submitting Fields
    public function createUpdateSubmittingFields($authUser, $client, $postData, $responseData)
    {        
        $vat_reg_id = $client->vat_reg_id; 
        $vatRegHeading = Carbon::parse($client->service_start)->format('M Y') . ' ' . $client->country . ' ' . $client->general_periods;

        if ($vat_reg_id) 
        {            
          $submittingFields = SubmittingFields::updateOrCreate(
            ['vat_reg_id' => $vat_reg_id],
            [                           
              'period_key' => $postData['periodKey'],
              'box_1' => $postData['vatDueSales'],
              'box_2' => $postData['vatDueAcquisitions'],
              'box_3' => $postData['totalVatDue'],
              'box_4' => $postData['vatReclaimedCurrPeriod'],
              'box_5' => $postData['netVatDue'],
              'box_6' => $postData['totalValueSalesExVAT'],
              'box_7' => $postData['totalValuePurchasesExVAT'],
              'box_8' => $postData['totalValueGoodsSuppliedExVAT'],
              'box_9' => $postData['totalAcquisitionsExVAT'],                
              'status' => ($postData['finalised']) ? 1 : 0,
              'processing_date' => $responseData['processingDate'],
              'payment_indicator' => $responseData['paymentIndicator'],
              'form_bundle_number' => $responseData['formBundleNumber'],
              'charge_ref_number' => $responseData['chargeRefNumber']
            ]
          ); 
         
          $this->addLog($authUser, 'vatreturn-submitting-fields-add', 
            [
              'Client Name' => $client['client_name'],
              'VAT Reg' => $vatRegHeading
            ]
          );
         
          return true;
        } 
        else
          return false;
    }
   
    /* -- Function to recursively sort arrays by their keys -- */
    public function ksort_recursive(&$array) {
        if (is_array($array)) {
            ksort($array); // Sort the current level
            foreach ($array as &$value) {
                $this->ksort_recursive($value); // Recurse into inner arrays
            }
        }
    }
    /* --end Function to recursively sort arrays by their keys -- */
    
    public function floatvalue($val)
    {      
      $val = preg_replace('/\s+/', '', trim($val));
      if($val == "")
        $val = 0;

      $dot_index = strpos($val, ".");
      $comma_index = strpos($val, ",");

      if($dot_index === false && $comma_index === false)
      {
        return is_string($val) ? floatval($val) : $val;  
      }//no dot, no comma

      if($dot_index === false && $comma_index != false)
      {
        $val = str_replace(",",".",$val);
        
        $split_dot = explode('.', $val);
        
        if(count($split_dot) == 2)
        {          
          if(strlen($split_dot[1]) == 1 || strlen($split_dot[1]) == 2)
            return is_string($val) ? floatval($val) : $val;
          else 
          {           
            if(strlen($split_dot[1]) >= 3)  
            {        
              $number = number_format((float)$val, 2);
              return is_string($number) ? floatval($number) : $number;
            }
            else
            {
              $val = str_replace(".","",$val);
              return is_string($val) ? floatval($val) : $val;
            }             
          }          
        }
        else if(count($split_dot) > 2)
        {
          $val = str_replace(".","",$val);
          return is_string($val) ? floatval($val) : $val;
        }
        //return floatval($val);          
      } //no dot
      else if($dot_index != false && $comma_index === false)
      {
        $split_dot = explode('.', $val);

        if(count($split_dot) == 2)
        {
          if(strlen($split_dot[1]) == 1 || strlen($split_dot[1]) == 2)
            return is_string($val) ? floatval($val) : $val;
          else 
          {
            if(strlen($split_dot[1]) >= 3)  
            {        
              $number = number_format((float)$val, 2);
              return is_string($number) ? floatval($number) : $number;
            }
            else
            {
              $val = str_replace(".","",$val);
              return is_string($val) ? floatval($val) : $val; 
            }
          }          
        }       
      } //no comma
      else
      {
        if($dot_index < $comma_index)
        {             
          $numberFormatter = new NumberFormatter('it-IT',NumberFormatter::DECIMAL);
          $number = $numberFormatter->parse($val);
  
          return is_string($number) ? floatval($number) : $number;             
        } //dot decimal
        else
        {          
          $val = str_replace(",","",$val);
            
          return is_string($val) ? floatval($val) : $val;               
        } //comma decimal
      }
    }

    /* -- UPDATE Next Date -- */    
    public function updateNextDate($vat_reg_id, $next_month_year, $month_year, $number, $file_type)
    {   
      $system = $this->getSystemInfoLazy();
      $systemtaskdates = $system->systemtaskdate;
      $pivs_taskdate = "1";
      $pivs_taskdates = $systemtaskdates->filter(function ($taskdate, $key) {
        return $taskdate->task_name == 'PIVS';
      }); 
      if(count($pivs_taskdates) > 0) 
        $pivs_taskdate = $pivs_taskdates->first()->task_date;  

      $cas_taskdate = "1";
      $cas_taskdates = $systemtaskdates->filter(function ($taskdate, $key) {
        return $taskdate->task_name == 'Cash Account Statement';
      }); 
      if(count($cas_taskdates) > 0) 
        $cas_taskdate = $cas_taskdates->first()->task_date;    

      $dda_taskdate = "1";
      $dda_taskdates = $systemtaskdates->filter(function ($taskdate, $key) {
        return $taskdate->task_name == 'Duty Deferment Account';
      }); 
      if(count($dda_taskdates) > 0) 
        $dda_taskdate = $dda_taskdates->first()->task_date; 

      $vatreg = VATRegistration::select(                
                    DB::raw('(CASE                       
                      WHEN dv_vat_registration.general_periods = "monthly" THEN 1 
                      WHEN dv_vat_registration.general_periods = "bi-monthly" THEN 2
                      WHEN dv_vat_registration.general_periods = "quarterly" THEN 3 
                      WHEN dv_vat_registration.general_periods = "half-yearly" THEN 6 
                      WHEN dv_vat_registration.general_periods = "yearly" THEN 12                      
                      ELSE "" END) AS frequency'
                    )
                  )
                  ->where('dv_vat_registration.id', $vat_reg_id)
                  ->first();

      $next_month = Carbon::parse('01-'.$next_month_year)->addMonth(1)->format('Y-m');
                  
      $updateFields = []; 
      $file_table = $this->queryTableForFile($file_type);           
      if($file_type == 'pivs') 
      {                      
        $updateFields = [
          'next_pivs_date' => Carbon::parse($next_month.'-'.$pivs_taskdate)->format('Y-m-d')
        ];           
      }
      else if($file_type == 'cas') 
      {                      
        $updateFields = [
          'next_cas_date' => Carbon::parse($next_cash_account_statement_month.'-'.$cas_taskdate)->format('Y-m-d')
        ];           
      }
      else if($file_type == 'dda') 
      {                      
        $updateFields = [
          'next_dda_date' => Carbon::parse($next_duty_deferment_account_month.'-'.$dda_taskdate)->format('Y-m-d')
        ];           
      }

      $file_count = $file_table->where('vat_reg_id', $vat_reg_id)->count();           
      
      //UPDATE NUMBER   
      $updateNumber = $file_table->where('vat_reg_id', $vat_reg_id)
                                ->where('month_year', $month_year)->first(); 
      if($file_type == 'c79')                              
        $updateNumber->doc_numbers =  $number;
      else
        $updateNumber->month_total =  $number;
      $updateNumber->save();                         

      //CHECK FREQUENCY COUNT WITH FILES COUNT
      if($file_count < $vatreg->frequency)
      {        
        $updateDate = VATRegistration::where('id', $vat_reg_id)                            
                        ->update(
                          $updateFields
                        );  

        return $updateDate;                 
      }                  
      return "";
    }
    /* --/ UPDATE Next Date -- */    

    /* -- query TABLE -- */    
    public function queryTableForFile($file_type)
    {
      try 
      {    
        $file_table = "";
        
        if($file_type == 'pivs')
          $file_table = new Pivs();
        else if($file_type == 'documents' || $file_type == 'c79')
          $file_table = new Documents();       
        else if($file_type == 'cas')
          $file_table = new CashAccountStatement();
        else if($file_type == 'dda')
          $file_table = new DutyDefermentAccount();
        else if($file_type == 'ivf')
          $file_table = new ImportVatFiles();
        else if($file_type == 'vatreturn')
          $file_table = new VATReturnFiles();
        else if($file_type == 'vatreturnoriginal')
          $file_table = new VATReturnOFiles();
        else if($file_type == 'vatcontrol')
          $file_table = new VATControlFiles();
        else if($file_type == 'vatcontroloriginal')
          $file_table = new VATControlOFiles();
        else if($file_type == 'receipt')
          $file_table = new Receipt();
        else if($file_type == 'ci')
          $file_table = new CommercialInvoiceFiles();
        else if($file_type == 'mailbox')
          $file_table = new MailBoxFiles();
        else if($file_type == 'cargo_mailbox')
          $file_table = new CargoDeclarationFiles();
        else if($file_type == 'import_reconciliation')
          $file_table = new ImportReconciliationFiles();
        else if($file_type == 'swiss_import_reconciliation')
          $file_table = new ImportReconciliationSwissFiles();
        else if($file_type == 'ircontrol')
          $file_table = new ImportReconciliationControlFiles();
        else if($file_type == 'ircontroloriginal')
          $file_table = new ImportReconciliationControlOFiles();
        
        return $file_table;
      }//try
      catch (Exception $e) {
        return  $e->getMessage();
      }//catch
    }
    /* --/ query TABLE -- */   

    /* -- LOG - ADD -- */
    public function addLog($authUser, $logName, $extras =  NULL)
    {    
      $authUserName = "";
      if($authUser)
        $authUserName = (isset($authUser->firstname) && isset($authUser->lastname)) ? ($authUser->firstname . ' ' . $authUser->lastname) : $authUser->name;

      try
      {
        switch ($logName) { 
          /*REGISTER*/
          case "new-register":
            Log::info("Registration done successfully.");
            break;
          /*REGISTER*/  

          /*CLIENT*/
          case "client-list":
            Log::info($authUserName . " viewed the client list.");
            break; 
          case "client-create":
            Log::info($authUserName . " clicked the client create button.");
            break; 
          case "client-update":
            Log::info($authUserName . " updated the client details.", $extras);
            break;  
          case "client-add":
            Log::info($authUserName . " added the client details.", $extras);
            break; 
          case "client-email":          
            Log::warning("Client email already exists.", $extras);
            break; 
          case "client-view":          
            Log::info($authUserName . " viewed client details.", $extras);
            break;
          case "client-edit":          
            Log::info($authUserName . " edited client details.", $extras);
            break; 
          case "client-update-legal":
            Log::info($authUserName . " updated the client Legal Representative details.", $extras);
            break;  
          case "client-update-additional":
            Log::info($authUserName . " updated the client Additional informations.", $extras);
            break;  
          case "client-update-billing":
            Log::info($authUserName . " updated the client Billing.", $extras);
            break;     
          case "client-update-erp":
            Log::info($authUserName . " updated the client ERP details.", $extras);
            break;     
          case "client-update-status":
            Log::info($authUserName . " " . strtolower($extras['Status Text']) . "d the client.", $extras);
            break;     
          case "client-delete":
            Log::info($authUserName . " deleted client details.", $extras);
            break; 
          case "client-error":
            Log::error("Cannot update Client details.");
            break;
          case "client-error-auth":
            Log::error("Don't have permission to ". $extras['Status Text'] ." the client", $extras);
            break;     
          case "client-file-upload":
            Log::info($authUserName . " uploaded file for the client.", $extras);
            break;
          case "client-file-delete":
            Log::info($authUserName . " deleted file for the client.", $extras);
            break;  
          case "client-comment-add":
            Log::info($authUserName . " added client comment.", $extras);
            break;
          case "client-comment-delete":
            Log::info($authUserName . " deleteed client comment.", $extras);
            break;
          case "client-qa-delete":
            Log::info($authUserName . " deleted QA for the client.", $extras);
            break;    
          case "client-qa-file-delete":
            Log::info($authUserName . " deleted QA file for the client.", $extras);
            break;  
          case "client-extrafield-delete":
            Log::info($authUserName . " deleted extra field for the client.", $extras);
            break;      
          /*end CLIENT*/ 

          /*USER*/
          case "user-logged-in":
            Log::info($authUserName . " logged in successfully.");
            break;
          case "user-list":
            Log::info($authUserName . " viewed user lists.");
            break; 
          case "user-update":
            Log::info($authUserName . " updated the user details.", $extras);
            break;  
          case "user-add":
            Log::info($authUserName . " created the user.", $extras);
            break;  
          case "user-edit":          
            Log::info($authUserName . " edited the user.", $extras);
            break;  
          case "user-delete":
            Log::info($authUserName . " deleted the user.", $extras);
            break; 
          case "user-notification":
            Log::info($authUserName . " updated the email notification settings for the user.", $extras);
            break;   
          case "user-invoice-column-settings":
            Log::info($authUserName . " updated the invoice column settings.");
            break;        
          /*end USER*/ 

          /*VAT REG. MAIN*/
          case "vatregmain-add":
            Log::info($authUserName . " created the VAT Registration details for the client.", $extras);
            break;
          case "vatregmain-update":
            Log::info($authUserName . " updated the VAT Registration details for the client.", $extras);
            break; 
          case "vatregmain-edit":
            Log::info($authUserName . " edited the VAT Registration details for the client.", $extras);
            break;   
          case "vatregmain-delete":
            Log::info($authUserName . " deleted the VAT Registration details for the client.", $extras);
            break; 
          case "vatregmain-click":
            Log::info($authUserName . " clicked VAT Registration for the client.", $extras);
            break;     
          case "vatregmain-exists":
            Log::warning("VAT Registration details already exists for the client.", $extras);
            break; 
          case "vatregmain-update-status":
            Log::info($authUserName . " " . strtolower($extras['Status Text']) . "d the VAT Registration.", $extras);
            break;  
          case "vatregmain-error-status":
            Log::info("Cannot " . strtolower($extras['Status Text']) . " the VAT Registration.", $extras);
            break;  
          case "vatregmain-error-auth-status":
            Log::info("Don't have permission to " . strtolower($extras['Status Text']) . " the VAT Registration.", $extras);
            break;  
          case "vatregmain-update-cash-account-statement":
            Log::info($authUserName . " " . strtolower($extras['Cash Account Statement Text']) . "d the cash account statement for the VAT Registration.", $extras);
            break;  
          case "vatregmain-error-cash-account-statement":
            Log::info("Cannot " . strtolower($extras['Cash Account Statement Text']) . " the cash account statement for the VAT Registration.", $extras);
            break;  
          case "vatregmain-error-auth-cash-account-statement":
            Log::info("Don't have permission to " . strtolower($extras['Cash Account Statement Text']) . " the cash account statement for the VAT Registration.", $extras);
            break;  
          case "vatregmain-update-duty-deferment-account":
            Log::info($authUserName . " " . strtolower($extras['Duty Deferment Account Text']) . "d the duty deferment account for the VAT Registration.", $extras);
            break;  
          case "vatregmain-error-duty-deferment-account":
            Log::info("Cannot " . strtolower($extras['Duty Deferment Account Text']) . " the duty deferment account for the VAT Registration.", $extras);
            break;  
          case "vatregmain-error-auth-duty-deferment-account":
            Log::info("Don't have permission to " . strtolower($extras['Duty Deferment Account Text']) . " the duty deferment account for the VAT Registration.", $extras);
            break; 
          case "vatregmain-update-oss":
            Log::info($authUserName . " " . strtolower($extras['Oss Text']) . "d the oss for the VAT Registration.", $extras);
            break;  
          case "vatregmain-error-oss":
            Log::info("Cannot " . strtolower($extras['Oss Text']) . " the oss for the VAT Registration.", $extras);
            break;  
          case "vatregmain-error-auth-oss":
            Log::info("Don't have permission to " . strtolower($extras['Oss Text']) . " the oss for the VAT Registration.", $extras);
            break;
          case "vatregmain-update-excise-duty":
            Log::info($authUserName . " " . strtolower($extras['Excise Duty Text']) . "d the excise duty for the VAT Registration.", $extras);
            break;  
          case "vatregmain-error-excise-duty":
            Log::info("Cannot " . strtolower($extras['Excise Duty Text']) . " the excise duty for the VAT Registration.", $extras);
            break;  
          case "vatregmain-error-auth-excise-duty":
            Log::info("Don't have permission to " . strtolower($extras['Excise Duty Text']) . " the excise duty for the VAT Registration.", $extras);
            break;  
          /*end VAT REG.*/

          /*VAT REG.*/
          case "vatreg-add":
            Log::info($authUserName . " created the VAT Registration details for the client.", $extras);
            break;
          case "vatreg-update":
            Log::info($authUserName . " updated the VAT Registration details for the client.", $extras);
            break; 
          case "vatreg-edit":
            Log::info($authUserName . " edited the VAT Registration details for the client.", $extras);
            break;   
          case "vatreg-delete":
            Log::info($authUserName . " deleted the VAT Registration details for the client.", $extras);
            break; 
          case "vatreg-click":
            Log::info($authUserName . " clicked VAT Registration for the client.", $extras);
            break;     
          case "vatreg-exists":
            Log::warning("VAT Registration details already exists for the client.", $extras);
            break; 
          case "disregard-period":
            Log::info($authUserName . " disregarded period for " . $extras['period'] .".", $extras);
            break;  
          case "vatreg-excel-template-update":
            Log::info($authUserName . " updated the Excel Template for the VAT Registration details for the client.", $extras);
            break;       
          /*end VAT REG.*/

          /*RECEIPT*/
          case "receipt-upload":
            Log::info($authUserName . " uploaded receipt for the client.", $extras);
            break;
          case "receipt-delete":
            Log::info($authUserName . " deleted receipt for the client.", $extras);
            break;  
          /*end RECEIPT*/

          /*PIVS*/
          case "pivs-upload":
            Log::info($authUserName . " uploaded PIVS for the month of " . $extras['month'] . " for the client.", $extras);
            break;
          case "pivs-delete":
            Log::info($authUserName . " deleted PIVS for the client for the month of " . $extras['month'], $extras);
            break;  
          case "pivs-nodoc-email":
            Log::info($authUserName . " sent email to the client users without uploading PIVS.", $extras);
            break;  
          case "pivs-doc-email":
            Log::info($authUserName . " sent email to the client users with PIVS.", $extras);
            break;  
          /*end PIVS*/

          /*DOCUMENTS*/
          case "documents-upload":
            Log::info($authUserName . " uploaded documents of " . $extras['doc_type'] . " for the client.", $extras);
            break;
          case "documents-delete":
            Log::info($authUserName . " deleted documents for the client for the " . $extras['doc_type'], $extras);
            break;          
          case "documents-doc-email":
            Log::info($authUserName . " sent email to the client users with documents.", $extras);
            break;  
          /*end DOCUMENTS*/

          /*C79*/
          case "c79-upload":
            Log::info($authUserName . " uploaded C79 for the month of " . $extras['month'] . " for the client.", $extras);
            break;
          case "c79-delete":
            Log::info($authUserName . " deleted C79 for the client for the month of " . $extras['month'], $extras);
            break;  
          case "c79-nodoc-email":
            Log::info($authUserName . " sent email to the client users without uploading C79.", $extras);
            break;  
          case "c79-doc-email":
            Log::info($authUserName . " sent email to the client users with C79.", $extras);
            break;  
          /*end C79*/

          /*Cash Account Statement*/
          case "cash-account-statement-upload":
            Log::info($authUserName . " uploaded Cash Account Statement for the month of " . $extras['month'] . " for the client.", $extras);
            break;
          case "cash-account-statement-delete":
            Log::info($authUserName . " deleted Cash Account Statement for the client for the month of " . $extras['month'], $extras);
            break;  
          case "cash-account-statement-nodoc-email":
            Log::info($authUserName . " sent email to the client users without uploading Cash Account Statement.", $extras);
            break;  
          case "cash-account-statement-doc-email":
            Log::info($authUserName . " sent email to the client users with Cash Account Statement.", $extras);
            break;
          /*end Cash Account Statement*/

          /*Duty Deferment Account*/
          case "duty-deferment-account-upload":
            Log::info($authUserName . " uploaded Duty Deferment Account for the month of " . $extras['month'] . " for the client.", $extras);
            break;
          case "duty-deferment-account-delete":
            Log::info($authUserName . " deleted Duty Deferment Account for the client for the month of " . $extras['month'], $extras);
            break;  
          case "duty-deferment-account-nodoc-email":
            Log::info($authUserName . " sent email to the client users without uploading Duty Deferment Account.", $extras);
            break;  
          case "duty-deferment-account-doc-email":
            Log::info($authUserName . " sent email to the client users with Duty Deferment Account.", $extras);
            break;
          /*end Duty Deferment Account*/

          /*VAT RETURN*/
          case "vatreturn-upload":
            Log::info($authUserName . " uploaded VAT Return file for the month of " . $extras['VAT Reg'] . " for the client.", $extras);
            break;
          case "vatreturn-delete":
            Log::info($authUserName . " deleted VAT Return file for the month of " . $extras['VAT Reg'], $extras);
            break;  
          case "vatreturn-draft-email":
            Log::info($authUserName . " sent email to the client users for approval.", $extras);
            break;
          case "vatreturn-approve-numbers":          
            Log::info("Numbers approved.", $extras);
            break;  
          case "vatreturn-decline-numbers":
            Log::info("Numbers declined.", $extras);
            break;  
          case "vatreturn-lock-email":
            Log::info($authUserName . " sent email to the client users and locked.", $extras);
            break;
          case "vatreturn-cancel-pending-review":
            Log::info($authUserName . " cancelled the pending review.", $extras);
            break;
          case "vatreturn-reopen":
            Log::info($authUserName . " re-opened VAT Return folder", $extras);
            break;             
          /*end VAT RETURN*/

          /*VAT RETURN - SUBMITTING FIELDS*/
          case "vatreturn-submitting-fields-hmrc":
            Log::info($authUserName . " submitted vat returns to HMRC.", $extras);
            break;  
          case "vatreturn-submitting-fields-add":
            Log::info($authUserName . " saved the vat returns submitted fields and response from HMRC.", $extras);
            break; 
          case "vatreturn-submitting-fields-error":
            Log::error($authUserName . " submitted fields the vat returns. But error in saving the response from HMRC.", $extras);
            break;   
          /*end VAT RETURN - SUBMITTING FIELDS*/

          /*VAT RETURN - SUBMITTING FIELDS - NO*/        
          case "vatreturn-submitting-fields-NO-add":
            Log::info($authUserName . " saved the vat returns submitted fields.", $extras);
            break;         
          /*end VAT RETURN - SUBMITTING FIELDS - NO*/

          /*VAT RETURN - SUBMITTING FIELDS - CH*/        
          case "vatreturn-submitting-fields-CH-add":
            Log::info($authUserName . " saved the vat returns submitted fields.", $extras);
            break;         
          /*end VAT RETURN - SUBMITTING FIELDS - CH*/

          /*VAT RETURN - NOTES*/
          case "vatreturn-notes-add":
            Log::info($authUserName . " added ". $extras['type'] . " notes for vat return for the month of " . $extras['month'], $extras);
            break;
          case "vatreturn-notes-update":
            Log::info($authUserName . " updated ". $extras['type'] . " notes for vat return for the month of " . $extras['month'], $extras);
            break;    
          case "vatreturn-notes-delete":
            Log::info($authUserName . " deleted ". $extras['type'] . " notes for vat return for the month of " . $extras['month'], $extras);
            break;  
          /*end VAT RETURN - NOTES*/

          /*IMPORT VAT*/
          case "import-vat-numbers-update":
            Log::info($authUserName . " saved import vat file for the month of " . $extras['month'] . " for the client.", $extras);
            break;        
          /*end IMPORT VAT*/

          /*IMPORT VAT FILES*/
          case "import-vat-file-upload":
            Log::info($authUserName . " uploaded import vat file for the month of " . $extras['month'] . " for the client.", $extras);
            break;
          case "import-vat-file-delete":
            Log::info($authUserName . " deleted import vat file for the client for the month of " . $extras['month'], $extras);
            break;          
          case "import-vat-file-email":
            Log::info($authUserName . " sent email to the client users with import vat file.", $extras);
            break; 
          case "import-vat-file-comment":
            Log::info($authUserName . " added comment to the import vat file for the month of " . $extras['month'] . " for the client.", $extras);
            break;   
          case "import-vat-file-comment-delete":
            Log::info($authUserName . " deleted comment of the import vat file for the month of " . $extras['month'] . " for the client.", $extras);
            break;   
          case "import-vat-file-update-sendemail":
            Log::info($authUserName . " " . strtolower($extras['Status Text']) . "d the import vat send email.", $extras);
            break;   
          /*end IMPORT VAT FILES*/

          /*INVOICE*/
          case "invoice-load":          
            Log::info("Fetching the invoices from API/FTP/FILES for the client.", $extras);
            break;
          case "invoice-load-error":          
            Log::error("Error in fetching the invoices from API for the client.", $extras);
            break; 
          case "invoice-view":
            Log::info($authUserName . " viewed invoices for the client.", $extras);
            break;   
          case "invoice-currency-conversion":
            Log::info($authUserName . " converted the currencies for the invoices.", $extras);
            break;
          case "invoice-before-delete":
            Log::info($authUserName . " invoked delete for the invoices.", $extras);
            break;
          case "invoice-after-delete":
            Log::info($authUserName . " deleted the invoices to fetch invoices.", $extras);
            break;  
          case "invoice-insert-batch":
            Log::info($authUserName . " invoked the invoices batch for insertion.", $extras);
            break;
          case "invoice-group-percentage":
            Log::info($authUserName . " grouped the invoices based on VAT percentage.", $extras);
            break;  
          case "invoice-mapping":
            Log::info("Started mapping for the uploaded file.", $extras);
            break; 
          case "invoice-mapped":
            Log::info("Uploaded file was mapped into system file.", $extras);
            break;    
          case "invoice-reading-mapped-file":
            Log::info($authUserName . " started to read the mapped file.", $extras);
            break;
          case "invoice-read-mapped-file":
            Log::info($authUserName . " read the mapped file.", $extras);
            break;  
          case "invoice-disregard":
            Log::info($authUserName . " disregarded the invoices.", $extras);
            break; 
          case "invoice-column-settings":
            Log::info($authUserName . " updated the invoice column settings.", $extras);
            break;          
          /*end INVOICE*/

          /*IMPORT RECONCILIATION*/
          case "importreconciliation-load":          
            Log::info("Fetching the import reconciliation invoices from API/FTP/FILES for the client.", $extras);
            break;        
          case "importreconciliation-load-error":          
            Log::error("Error in fetching the import reconciliation invoices from API for the client.", $extras);
            break;
          case "importreconciliation-mapping":
            Log::info("Started mapping for the uploaded import reconciliation file.", $extras);
            break; 
          case "importreconciliation-mapped":
            Log::info("Uploaded import reconciliation file was mapped into system file.", $extras);
            break;    
          case "importreconciliation-reading-mapped-file":
            Log::info($authUserName . " started to read the import reconciliation mapped file.", $extras);
            break;
          case "importreconciliation-read-mapped-file":
            Log::info($authUserName . " read the import reconciliation mapped file.", $extras);
            break;   
          case "importreconciliation-before-delete":
            Log::info($authUserName . " invoked delete for the import reconciliation invoices.", $extras);
            break;
          case "importreconciliation-after-delete":
            Log::info($authUserName . " deleted the import reconciliation invoices to fetch invoices.", $extras);
            break;  
          case "importreconciliation-insert-batch":
            Log::info($authUserName . " invoked the import reconciliation invoices batch for insertion.", $extras);
            break;  
          case "importreconcilation-sales-invoice-disregard":
            Log::info($authUserName . " disregarded the import reconciliation sales invoices.", $extras);
            break;
          case "importreconcilation-sales-invoice-enable":
            Log::info($authUserName . " enabled the import reconciliation sales invoices.", $extras);
            break;  
          case "importreconcilation-sales-invoice-comment-add":
            Log::info($authUserName . " added comment to the import reconciliation sales invoices.", $extras);
            break;   
          case "importreconcilation-com-invoice-comment-add":
            Log::info($authUserName . " added comment to the import reconciliation com. invoices.", $extras);
            break;  
          case "importreconcilation-declaration-comment-add":
            Log::info($authUserName . " added comment to the import reconciliation declaration.", $extras);
            break; 
          case "importreconcilation-sales-invoice-comment-delete":
            Log::info($authUserName . " deleted comment to the import reconciliation sales invoices.", $extras);
            break;   
          case "importreconcilation-com-invoice-comment-delete":
            Log::info($authUserName . " deleted comment to the import reconciliation com. invoices.", $extras);
            break;  
          case "importreconcilation-declaration-comment-delete":
            Log::info($authUserName . " deleted comment to the import reconciliation declaration.", $extras);
            break;   
          case "importreconcilation-control-refresh":
            Log::info($authUserName . " refreshed import reconciliation controls.", $extras);
            break;  
          case "importreconcilation-global-search-refresh":
            Log::info($authUserName . " refreshed global search.", $extras);
            break;   
          case "importreconcilation-com-invoice-rematch":
            Log::info($authUserName . " rematched the import reconciliation com. invoice.", $extras);
            break;  
          case "importreconcilation-com-invoice-rematch-delete":
            Log::info($authUserName . " deleted the import reconciliation com. invoice. rematch", $extras);
            break;  
          case "importreconcilation-sales-invoice-relation-rematch":
            Log::info($authUserName . " rematched the import reconciliation sales invoice.", $extras);
            break; 
          case "importreconcilation-sales-invoice-relation-ftp-data-edit":
            Log::info($authUserName . " edited the import reconciliation sales invoice.", $extras);
            break;  
          case "importreconcilation-com-invoice-delete":
            Log::info($authUserName . " deleted import reconciliation com. invoices.", $extras);
            break;   
          case "importreconcilation-sales-invoice-delete":
            Log::info($authUserName . " deleted import reconciliation sales invoices.", $extras);
            break; 
          case "importreconcilation-xml-com-invoice-disregard":
            Log::info($authUserName . " disregarded the import reconciliation XML com. invoice.", $extras);
            break;  
          case "importreconcilation-com-invoice-disregard":
            Log::info($authUserName . " disregarded the import reconciliation com. invoice.", $extras);
            break;   
          case "importreconcilation-com-invoice-lopeno-disregard":
            Log::info($authUserName . " disregarded the import reconciliation lope no.", $extras);
            break;   
          case "importreconcilation-com-invoice-retain":
            Log::info($authUserName . " retained the import reconciliation com. invoice.", $extras);
            break; 
          case "importreconcilation-com-invoice-lopeno-retain":
            Log::info($authUserName . " retained the import reconciliation lope no.", $extras);
            break;
          case "importreconcilation-com-invoice-specific-invoice-global-search-refresh":
            Log::info($authUserName . " refreshed global search for specific com. invoices.", $extras);
            break;
          case "importreconcilation-sales-invoice-specific-invoice-global-search-refresh":
            Log::info($authUserName . " refreshed global search for specific sales invoices.", $extras);
            break;
          case "importreconcilation-invoice-currency-conversion":
            Log::info($authUserName . " converted the currencies for the declaration invoices.", $extras);
            break;   
          case "importreconcilation-com-invoice-unmatch":
            Log::info($authUserName . " unmatched the import reconciliation com. invoice.", $extras);
            break;    
          case "importreconcilation-com-invoice-specific-invoice-refresh":
            Log::info($authUserName . " refreshed the specific import reconciliation com. invoice.", $extras);
            break; 
          case "importreconcilation-sales-invoice-specific-invoice-refresh":
            Log::info($authUserName . " refreshed the specific import reconciliation sales invoice.", $extras);
            break;  
          case "importreconcilation-sales-invoice-ftp-data-edit":
            Log::info($authUserName . " updated the import reconciliation sales invoice datas.", $extras);
            break; 
          case "importreconcilation-sales-invoice-file-move":
            Log::info($authUserName . " moved the import reconciliation sales invoice files.", $extras);
            break;                    
          /*end IMPORT RECONCILIATION*/

          /*IMPORT RECONCILIATION - NOTES*/
          case "importreconciliation-notes-add":
            Log::info($authUserName . " added ". $extras['type'] . " notes for import reconciliation for the month of " . $extras['month'], $extras);
            break;
          case "importreconciliation-notes-update":
            Log::info($authUserName . " updated ". $extras['type'] . " notes for import reconciliation for the month of " . $extras['month'], $extras);
            break;    
          case "importreconciliation-notes-delete":
            Log::info($authUserName . " deleted ". $extras['type'] . " notes for import reconciliation for the month of " . $extras['month'], $extras);
            break;  
          /*end IMPORT RECONCILIATION - NOTES*/

          /*VAT CONTROL*/                
          case "vatcontrol-load-error":          
            Log::error("Error in fetching the VAT control missing invoices from excel for the client.", $extras);
            break;
          /*end VAT CONTROL*/

          /*IMPORT RECONCILIATION CONTROL*/                
          case "ircontrol-load-error":          
            Log::error("Error in fetching the Import Reconciliation control missing invoices from excel for the client.", $extras);
            break;
          /*end IMPORT RECONCILIATION CONTROL*/  

          /*GLOBAL SEARCH - AZURE*/
          case "globalsearch-view":
            Log::info($authUserName . " viewed global search page.");
            break; 
          case "globalsearch-refresh":
            Log::info($authUserName . " refreshed global search for " . $extras['Client Name']);
            break;          
          /*end GLOBAL SEARCH - AZURE*/

          /*PAYMENT INFO*/
          case "paymentinfo-list":
            Log::info($authUserName . " viewed payment info lists");
            break;
          case "paymentinfo-update":
            Log::info($authUserName . " updated the ". $extras['Country'] ." payment info.");
            break; 
          /*end PAYMENT INFO*/

          /*COMMENTS*/
          case "comment-add":
            Log::info($authUserName . " saved comment for the client.", $extras);
            break;
          case "comment-files-upload":
            Log::info($authUserName . " saved comment with files for the client.", $extras);
            break;        
          /*end COMMENTS*/

          /*STATS*/
          case "stats-list":
            Log::info($authUserName . " viewed stats.");
            break;            
          /*end STATS*/

          /*COMPLIANCE*/
          case "compliance-user-list":
            Log::info($authUserName . " viewed compliance users.");
            break;
          case "compliance-read-file":
            Log::info($authUserName . " uploaded compliance users.");
            break;               
          /*end COMPLIANCE*/

          /*REMINDER TASKS*/
          case "reminder-list":
            Log::info($authUserName . " viewed reminder tasks.");
            break;
          case "reminder-add":
            Log::info($authUserName . " added reminder task.", $extras);
            break; 
          case "reminder-edit":
            Log::info($authUserName . " edited reminder task.", $extras);
            break; 
          case "reminder-delete":
            Log::info($authUserName . " deleted reminder task.", $extras);
            break;
          case "reminder-email":
            Log::info("Reminder email sent to user.", $extras);
            break;   
          case "reminder-history":
            Log::info($authUserName . " viewed reminder histories.");
            break; 
          case "reminder-forwared-auto-reply":
            Log::info("Forwarded reminder auto-reply email to info@intravat.com.", $extras);
            break;                      
          /*end REMINDER TASKS*/

          /*FILE UPLOAD*/
          case "file-upload":
            Log::info($authUserName . " uploaded ". $extras['file_type_title'] ." for the month of " . $extras['month'] . " for the client.", $extras);
            break;
          case "file-delete":        
            if($extras['file_type_title'] == 'Documents')
              Log::info($authUserName . " deleted ". $extras['file_type_title'] ." for the client for the " . $extras['doc_type'], $extras);
            else
              Log::info($authUserName . " deleted ". $extras['file_type_title'] ." for the client for the month of " . $extras['month'], $extras);
            break;  
          case "file-nodoc-email":
            Log::info($authUserName . " sent email to the client users without uploading ". $extras['file_type_title'] .".", $extras);
            break;  
          case "file-doc-email":
            Log::info($authUserName . " sent email to the client users with ". $extras['file_type_title'] .".", $extras);
            break; 
          case "task-disregard":
            Log::info($authUserName . " disregarded " . $extras['file_type_title'] . " task for the month of " . $extras['month'] .".", $extras);
            break;   
          /*end FILE UPLOAD*/

          /*TASK DATE*/
          case "taskdate-list":
            Log::info($authUserName . " viewed task dates.");
            break;   
          case "taskdate-add":
            Log::info($authUserName . " added task date.", $extras);
            break;
          case "taskdate-edit":
            Log::info($authUserName . " edited task date.", $extras);
            break; 
          case "taskdate-delete":
            Log::info($authUserName . " deleted task date.", $extras);
            break;  
          /*end TASK DATE*/

          // /*EXCEL COLUMN TEMPLATES*/
          // case "excelcolumntemplate-list":
          //   Log::info($authUserName . " viewed excel column template.");
          //   break;   
          // case "excelcolumntemplate-add":
          //   Log::info($authUserName . " added excel column template.", $extras);
          //   break;
          // case "excelcolumntemplate-edit":
          //   Log::info($authUserName . " edited excel column template.", $extras);
          //   break; 
          // case "excelcolumntemplate-update":
          //   Log::info($authUserName . " updated excel column template.", $extras);
          //   break;  
          // case "excelcolumntemplate-delete":
          //   Log::info($authUserName . " deleted excel column template.", $extras);
          //   break;         
          // /*end EXCEL COLUMN TEMPLATES*/

          /*ANY EXCEL TEMPLATES*/
          case "anyexceltemplate-list":
            Log::info($authUserName . " viewed any excel template.");
            break;   
          case "anyexceltemplate-add":
            Log::info($authUserName . " added any excel template.", $extras);
            break;
          case "anyexceltemplate-edit":
            Log::info($authUserName . " edited any excel template.", $extras);
            break; 
          case "anyexceltemplate-update":
            Log::info($authUserName . " updated any excel template.", $extras);
            break;  
          case "anyexceltemplate-delete":
            Log::info($authUserName . " deleted any excel template.", $extras);
            break;   
          case "anyexceltemplate-file-upload":
            Log::info($authUserName . " uploaded any excel template file.", $extras);
            break;        
          /*end ANY EXCEL TEMPLATES*/

          /*ANALYZE PDF*/          
          case "analyzepdf-delete":
            Log::info($authUserName . " deleted analyse pdf.", $extras);
            break;
          case "analyzepdf-sync":            
            Log::info($authUserName . " synchronized OCR for " . $extras['Client Name']);
            break;               
          /*end ANALYZE PDF*/

          /*OCR PDF*/
          case "importreconcilation-ocr-search-refresh":
            Log::info($authUserName . " refreshed OCR search.", $extras);
            break;
          /*end OCR PDF*/

          /*CRM REMINDER*/
          case "crm-reminder-list":
            Log::info($authUserName . " viewed CRM reminders.");
            break;
          case "crm-reminder-add":
            Log::info($authUserName . " added CRM reminder.", $extras);
            break; 
          case "crm-reminder-edit":
            Log::info($authUserName . " edited CRM reminder.", $extras);
            break; 
          case "crm-reminder-delete":
            Log::info($authUserName . " deleted CRM reminder.", $extras);
            break;
          case "crm-reminder-email":
            Log::info("CRM Reminder email sent to recipient.", $extras);
            break; 
          case "crm-reminder-scheduled-email":
            Log::info("Scheduled CRM Reminder email.", $extras);
            break; 
          case "crm-reminder-no-schedule-email":
            Log::info("No email Scheduled for CRM Reminder.", $extras);
            break;
          // case "crm-reminder-history":
          //   Log::info($authUserName . " viewed reminder histories.");
          //   break; 
          // case "crm-reminder-forwared-auto-reply":
          //   Log::info("Forwarded reminder auto-reply email to info@intravat.com.", $extras);
          //   break;
          /*end CRM REMINDER*/
             
          /*ERROR CATCH*/
          case "error-log":
            Log::error("Error in functionality", $extras);
            break;
          /*end ERROR CATCH*/

          default:
            //Log::error("Error in log");
            throw new \InvalidArgumentException('Invalid action type');
        }    
      } catch (\InvalidArgumentException $e) {        
        Log::error("Error in log", $e->getMessage()); 
      } catch (\Exception $e) {
        // Handle any errors      
        Log::error("Error in log", $e->getMessage());
      } 
      return true;                
    }
    /* --/ LOG - ADD -- */

    /*For Testing Lazy loading*/
    //GET System Info
    public function getSystemInfoLazy($api_name = 'Microsoft Graph', $api_env = 'Sandbox')
    {       
      $system = System::with(['systemapi' => function ($query) use($api_name, $api_env) {                         
                      $query->where('api_name', $api_name); 
                      $query->where('api_env', $api_env); 
                      $query->where('status', 1);                     
                    } 
                  , 'systemfiles'])                  
                  ->first();
      return $system;                
    }

    //GET Main Table
    public function getMainTableLazy($mainTable)
    {   
      //Main Table      
      if($mainTable == 'cargodeclarationfiles')
        $query = new CargoDeclarationFiles();
      else if($mainTable == 'cas')
        $query = new CashAccountStatement();
      else if($mainTable == 'client')
        $query = new Client();
      else if($mainTable == 'clientapi')
        $query = new ClientApi();     
      else if($mainTable == 'clientcvr')
        $query = new ClientCvr();
      else if($mainTable == 'clientfiles')
        $query = new ClientFiles();
      else if($mainTable == 'clientqa')
        $query = new ClientQA();
      else if($mainTable == 'clientqafiles')
        $query = new ClientQAFiles();
      else if($mainTable == 'clientcomment')
        $query = new ClientComment();
      else if($mainTable == 'ci')
        $query = new CommercialInvoiceFiles();
      else if($mainTable == 'companyteamuser')
        $query = new CompanyTeamUser();
      else if($mainTable == 'documents')
        $query = new Documents();
      else if($mainTable == 'dda')
        $query = new DutyDefermentAccount();
      else if($mainTable == 'dvuser')
        $query = new DVUser();
      // else if($mainTable == 'excelcolumntemplates')
      //   $query = new ExcelColumnTemplates();
      else if($mainTable == 'anyexceltemplates')
        $query = new AnyExcelTemplates();
      else if($mainTable == 'exchangerate')
        $query = new ExchangeRates();
      else if($mainTable == 'filesemailnote')
        $query = new FilesEmailNote();
      else if($mainTable == 'invoices')
        $query = new Invoices();
      else if($mainTable == 'importreconciliationfiles')
        $query = new ImportReconciliationFiles();
      else if($mainTable == 'importreconciliationnotes')
        $query = new ImportReconciliationNotes();      
      else if($mainTable == 'importreconciliationswissfiles')
        $query = new ImportReconciliationSwissFiles();
      else if($mainTable == 'ivcomments')
        $query = new ImportVatComments();
      else if($mainTable == 'ivf')
        $query = new ImportVatFiles();
      else if($mainTable == 'mailboxfiles')
        $query = new MailBoxFiles();
      else if($mainTable == 'notificationsettings')
        $query = new NotificationSettings();
      else if($mainTable == 'paymentinfo')
        $query = new PaymentInfo();
      else if($mainTable == 'pivs')
        $query = new Pivs();
      else if($mainTable == 'receipt')
        $query = new Receipt();     
      else if($mainTable == 'reminder')
        $query = new Reminder(); 
      else if($mainTable == 'reminderactionoption')
        $query = new ReminderActionOption();
      else if($mainTable == 'submittingfields')
        $query = new SubmittingFields();
      else if($mainTable == 'submittingfieldsno')
        $query = new SubmittingFieldsNO();
      else if($mainTable == 'submittingfieldsch')
        $query = new SubmittingFieldsCH();
      else if($mainTable == 'system')
        $query = new System();     
      else if($mainTable == 'systemapis')
        $query = new SystemApis();
      else if($mainTable == 'systemtaskdate')
        $query = new SystemTaskDate();
      else if($mainTable == 'user')
        $query = new User();
      else if($mainTable == 'userclient')
        $query = new UserClient();
      else if($mainTable == 'uservatreg')
        $query = new UserVATRegistration();
      else if($mainTable == 'vatreg')
        $query = new VATRegistration();
      else if($mainTable == 'vatregmain')
        $query = new VATRegistrationMain();
      else if($mainTable == 'vatreturncommentfiles')
        $query = new VATReturnCommentFiles();
      else if($mainTable == 'vatreturncomments')
        $query = new VATReturnComments();
      else if($mainTable == 'vatreturnfiles')
        $query = new VATReturnFiles();
      else if($mainTable == 'vatreturnofiles')
        $query = new VATReturnOFiles();
      else if($mainTable == 'vatreturnnotes')
        $query = new VATReturnNotes();
      else if($mainTable == 'vatreturns')
        $query = new VATReturns(); 

      return $query;
    }

    //GET Table with
    public function getLazy($mainTable, $with = NULL, $where = NULL, $whereHas = NULL, $orderBy = NULL, $final = 'get', $partition = NULL)
    {  
      //Main Table
      $query = $this->getMainTableLazy($mainTable);
      
      //fetch with relation   
      if($with != null)          
        $query = $query->with($with);
      
      if($partition != null)      
        $query = $query->partitions($partition);   
      
      //where
      if($where != null)
      {                
        foreach($where as $key => $value)   
        {
          if(array_key_exists('operator', $value) && array_key_exists('value', $value))    
            $query = $query->where($key, $value['operator'], $value['value']);   
          else
          {
            foreach($value as $item)   
              $query = $query->where($key, $item['operator'], $item['value']);  
          }
        }
      }

      //wherehas
      if($whereHas != null)
      {
        foreach($whereHas as $key => $value)   
        {
          $query =  $query->whereHas($key, function ($subquery) use($value) {
                      $subquery->where($value['field'], $value['value']); 
                    });         
        }
      }

      //orderBy
      if($orderBy != null)
      {
        foreach($orderBy as $key => $value) 
          $query = $query->orderBy($key, $value);
      }  
    
      //fetch single/multiple rows
      if($final == 'get')
        $query = $query->get();
      else if($final == 'first')
        $query = $query->first();
      else if($final == 'delete')
        $query = $query->delete();
      else if($final == 'update')
        $query = $query->update();
      else if($final == 'save')
        $query = $query->save();
                                        
      return $query;             
    }  

    //GET Users
    public function getUsersLazy($user_id = NULL, $role = NULL, $with = NULL)
    { 
      $_with = ['dvuser', 'roles'];
      if($with != null)
        $_with = $with;
      $_where = [];
      if($user_id != null)
        $_where = [
          'id' => ['operator' => '=', 'value' => $user_id]
        ];
      $_whereHas = ['dvuser' => ['field' => 'is_deleted', 'value' => 0]];
      if($role != null)
        $_whereHas = [
          'roles' => ['field' => 'name', 'value' => $role],
          'dvuser' => ['field' => 'is_deleted', 'value' => 0]
        ];
      $_orderBy = [
        'id' => 'DESC'
      ];  
      $_final = 'get';
      if($user_id != null)
        $_final = 'first';
      $users = $this->getLazy('user', $_with, $_where, $_whereHas, $_orderBy, $_final); 

      return $users;     
    }    

    //GET Company/Client
    public function getCompanyLazy($client_id = NULL, $cvr_user = false)
    { 
      $_with = ['vatregmain'];
      $_where = [];
      if($client_id != null)
        $_where = [
          'id' => ['operator' => '=', 'value' => $client_id]
        ];
      else
      {
        if($cvr_user)
          $_where = [            
            'off_country' => ['operator' => '=', 'value' => 'DK']
          ];  
      }
      $_whereHas = [];      
      $_orderBy = [
        'id' => 'DESC'
      ];  
      $_final = 'get';
      if($client_id != null)
        $_final = 'first';
      $clients = $this->getLazy('client', $_with, $_where, $_whereHas, $_orderBy, $_final); 

      return $clients;        
    }

    //GET Company files
    public function getCompanyFilesLazy($file_id = NULL)
    {       
      $_with = ['client'];
      $_where = []; 
      if($file_id)    
      {         
        $_where = [
          'id' => ['operator' => '=', 'value' => $file_id]
        ]; 
      }  
      $_whereHas = [];          
      $_orderBy = []; 
      if($file_id != null)
        $_orderBy = [
          'id' => 'ASC'
        ];     
      $_final = 'get';
      if($file_id != null)
        $_final = 'first';    
      $companyfiles = $this->getLazy('clientfiles', $_with, $_where, $_whereHas, $_orderBy, $_final); 

      return $companyfiles;        
    }

    //GET VAT Reg. Main  
    public function getVatRegMainLazy($vat_reg_main_id = NULL, $_where = [])
    {  
      $_with = ['client'];
      //$_where = [];      
      if($vat_reg_main_id != null)
      {
        if($_where == null)
          $_where = [
            'id' => ['operator' => '=', 'value' => $vat_reg_main_id]
          ];
        else        
          $_where['id'] = ['operator' => '=', 'value' => $vat_reg_main_id];          
      }      
      $_whereHas = [];      
      $_orderBy = [
        'id' => 'DESC'
      ];  
      $_final = 'get';
      if($vat_reg_main_id != null)
        $_final = 'first';
      $vatregmains = $this->getLazy('vatregmain', $_with, $_where, $_whereHas, $_orderBy, $_final); 

      return $vatregmains;        
    }

    //GET VAT Reg.  
    public function getVatRegLazy($vat_reg_id = NULL, $_where = [], $_with = ['vatregmain', 'client', 'vatregmain.clientapi'])
    {             
      if($vat_reg_id != null)
      {
        if($_where == null)
          $_where = [
            'id' => ['operator' => '=', 'value' => $vat_reg_id]
          ];       
        else        
          $_where['id'] = ['operator' => '=', 'value' => $vat_reg_id];
      }
      $_whereHas = [];      
      $_orderBy = [
        'id' => 'DESC'
      ];  
      $_final = 'get';
      if($vat_reg_id != null)
        $_final = 'first';
      $vatreg = $this->getLazy('vatreg', $_with, $_where, $_whereHas, $_orderBy, $_final); 

      return $vatreg;        
    }

    //GET VAT Return Files  
    public function getVatReturnFileLazy($vat_return_file_id = NULL)
    { 
      $_with = ['vatreg', 'vatreg.client', 'anyexceltemplate', 'vatreturnofiles'];
      $_where = [];
      if($vat_return_file_id != null)
        $_where = [
          'id' => ['operator' => '=', 'value' => $vat_return_file_id]
        ];
      $_whereHas = [];      
      $_orderBy = [
        'id' => 'DESC'
      ];  
      $_final = 'get';
      if($vat_return_file_id != null)
        $_final = 'first';
      $vatreturnfile = $this->getLazy('vatreturnfiles', $_with, $_where, $_whereHas, $_orderBy, $_final); 

      return $vatreturnfile;        
    }
    
    //GET VAT Return Notes  
    public function getVatReturnNotes($type = 'general', $type_id = NULL, $vat_return_note_id = NULL)
    { 
      $_with = ['user', 'user.dvuser', 'user.roles'];
      $_where = [];
      if($vat_return_note_id)
        $_where = [
          'id' => ['operator' => '=', 'value' => $vat_return_note_id]
        ];
      else
      {
        if($type == 'general')
          $_where = [
            'type' => ['operator' => '=', 'value' => $type],
            'client_id' => ['operator' => '=', 'value' => $type_id]
          ];
        else if($type == 'specific')
          $_where = [
            'type' => ['operator' => '=', 'value' => $type],
            'vat_reg_id' => ['operator' => '=', 'value' => $type_id]
          ];
      }
      $_whereHas = [];      
      $_orderBy = [
        'id' => 'DESC'
      ];  
      $_final = 'get';
      if($vat_return_note_id != null)
        $_final = 'first';
      $vatreturnnote = $this->getLazy('vatreturnnotes', $_with, $_where, $_whereHas, $_orderBy, $_final); 

      return $vatreturnnote;        
    }

    //GET Import Reconciliation Notes  
    public function getImportReconciliationNotes($type = 'general', $type_id = NULL, $vat_return_note_id = NULL)
    { 
      $_with = ['user', 'user.dvuser', 'user.roles'];
      $_where = [];
      if($vat_return_note_id)
        $_where = [
          'id' => ['operator' => '=', 'value' => $vat_return_note_id]
        ];
      else
      {
        if($type == 'general')
          $_where = [
            'type' => ['operator' => '=', 'value' => $type],
            'client_id' => ['operator' => '=', 'value' => $type_id]
          ];
        else if($type == 'specific')
          $_where = [
            'type' => ['operator' => '=', 'value' => $type],
            'vat_reg_id' => ['operator' => '=', 'value' => $type_id]
          ];
      }
      $_whereHas = [];      
      $_orderBy = [
        'id' => 'DESC'
      ];  
      $_final = 'get';
      if($vat_return_note_id != null)
        $_final = 'first';
      $vatreturnnote = $this->getLazy('importreconciliationnotes', $_with, $_where, $_whereHas, $_orderBy, $_final); 

      return $vatreturnnote;        
    }

    //GET Invoices
    public function getInvoicesLazy($vat_reg_id = NULL, $search_by = NULL, $partitions = NULL)
    { 
      $_with = [];      
      $_where = [];
      if($vat_reg_id != null)
        $_where += [
          'vat_reg_id' => ['operator' => '=', 'value' => $vat_reg_id]
        ];
      if($search_by != null)
      {
        foreach($search_by as $key => $value) 
        {
          if($key == 'type')
            $field = 'invoice_type';
          else if($key == 'percentage')
            $field = 'vat_rate';
          else if($key == 'currency')
            $field = 'currency_code';

          $_where += [
            $field => ['operator' => '=', 'value' => $value]
          ];
        }       
      }
      $_whereHas = [];      
      $_orderBy = [
        'id' => 'ASC'
      ];
      $_final = 'get';      
      $invoices = $this->getLazy('invoices', $_with, $_where, $_whereHas, $_orderBy, $_final, $partitions); 

      return $invoices;        
    }

    //GET Vat reg. files
    public function getVatRegFilesLazy($file_type_name, $file_id = NULL)
    {       
      $_with = ['client', $file_type_name];
      $_where = []; 
      $_whereHas = [];
      if($file_id)    
      { 
        $_whereHas = [
          $file_type_name => ['field' => 'id', 'value' => $file_id]
        ]; 
      }      
      $_orderBy = []; 
      if($file_id != null)
        $_orderBy = [
          'id' => 'ASC'
        ];     
      $_final = 'get';
      if($file_id != null)
        $_final = 'first';    
      $vatregfiles = $this->getLazy('vatreg', $_with, $_where, $_whereHas, $_orderBy, $_final); 

      return $vatregfiles;        
    }

    //GET ImportReconciliation
    public function getImportReconciliationFilesLazy($file_id = NULL, $o_file_name = NULL)
    { 
      $_with = [];
      $_where = [];
      if($file_id != null)
        $_where = [
          'id' => ['operator' => '=', 'value' => $file_id]
        ];

      if($o_file_name != null)
        $_where = [
          'o_file_name' => ['operator' => '=', 'value' => $o_file_name]
        ];  
      $_whereHas = [];
     
      $_orderBy = [
        'id' => 'DESC'
      ];  
      $_final = 'get';
      if($file_id != null || $o_file_name != null)
        $_final = 'first';
      $users = $this->getLazy('importreconciliationfiles', $_with, $_where, $_whereHas, $_orderBy, $_final); 

      return $users;     
    }    

    //GET ImportReconciliation
    public function getImportReconciliationSwissFilesLazy($file_id = NULL, $o_file_name = NULL)
    { 
      $_with = [];
      $_where = [];
      if($file_id != null)
        $_where = [
          'id' => ['operator' => '=', 'value' => $file_id]
        ];

      if($o_file_name != null)
        $_where = [
          'o_file_name' => ['operator' => '=', 'value' => $o_file_name]
        ];  
      $_whereHas = [];
      
      $_orderBy = [
        'id' => 'DESC'
      ];  
      $_final = 'get';
      if($file_id != null || $o_file_name != null)
        $_final = 'first';
      $users = $this->getLazy('importreconciliationswissfiles', $_with, $_where, $_whereHas, $_orderBy, $_final); 

      return $users;     
    }    

    //GET ClientQAFiles
    public function getClientQAFiles($file_id = NULL, $o_file_name = NULL)
    { 
      $_with = [];
      $_where = [];
      if($file_id != null)
        $_where = [
          'id' => ['operator' => '=', 'value' => $file_id]
        ];

      if($o_file_name != null)
        $_where = [
          'o_file_name' => ['operator' => '=', 'value' => $o_file_name]
        ];  
      $_whereHas = [];
     
      $_orderBy = [
        'id' => 'DESC'
      ];  
      $_final = 'get';
      if($file_id != null || $o_file_name != null)
        $_final = 'first';
      $clientqafiles = $this->getLazy('clientqafiles', $_with, $_where, $_whereHas, $_orderBy, $_final); 

      return $clientqafiles;     
    }    

    //GET ExchangeRate
    public function getExchangeRateLazy($exchange_date = NULL, $currency_code = NULL, $partitions = NULL)
    {
      $_with = [];      
      $_where = [];
      if($exchange_date != null)
        $_where += [
          'exchange_date' => ['operator' => '=', 'value' => $exchange_date]
        ]; 
     
      if($currency_code != null)
        $_where += [
          'currency_code' => ['operator' => '=', 'value' => $currency_code]
        ];         
      $_whereHas = [];      
      $_orderBy = [
        'id' => 'ASC'
      ];
      $_final = 'first';      
      $exchangerate = $this->getLazy('exchangerate', $_with, $_where, $_whereHas, $_orderBy, $_final, $partitions); 

      if($exchangerate)
        return $exchangerate; 
      else
      {        
        $date = Carbon::parse($exchange_date);

        switch ($date->format('D')) {
          case 'Sat':
              $adjusted_date = $date->subDay();
              break;
          case 'Sun':
              $adjusted_date = $date->addDay();
              break;
          default:
              $adjusted_date = $date;
        }

        $final_date = $adjusted_date->format('Y-m-d');

        $_with = [];      
        $_where = [];
        if($final_date != null)
          $_where += [
            'exchange_date' => ['operator' => '=', 'value' => $final_date]
          ]; 
       
        if($currency_code != null)
          $_where += [
            'currency_code' => ['operator' => '=', 'value' => $currency_code]
          ];         
        $_whereHas = [];      
        $_orderBy = [
          'id' => 'ASC'
        ];
        $_final = 'first';      
        $exchangerate = $this->getLazy('exchangerate', $_with, $_where, $_whereHas, $_orderBy, $_final, $partitions); 

        return $exchangerate; 
      }//get before/after date exchange rate       
    }

    //GET Reminders
    public function getRemindersLazy($reminder_id = NULL, $_where = [], $_final = 'get')
    { 
      $_with = ['reminderhistory', 'reminderuser', 'reminderuser.user', 'reminderuser.user.dvuser', 'reminderuser.user.roles', 'reminderuser.reminderuserclient', 'reminderuser.reminderuserclient.client', 'vatregmain', 'vatregmain.vatreg', 'vatregmain.vatreg.vatreturns', 'vatregmain.vatreg.vatreturnfiles', 'vatregmain.vatreg.pivs', 'vatregmain.vatreg.cas', 'vatregmain.vatreg.dda', 'vatregmain.client', 'reminderactionoption'];
     
      if($reminder_id != null)
      {
        if($_where == null)
          $_where = [
            'id' => ['operator' => '=', 'value' => $reminder_id]
          ];       
        else        
          $_where['id'] = ['operator' => '=', 'value' => $reminder_id];
      }
      
      $_whereHas = [];      
      $_orderBy = [
        'id' => 'DESC'
      ];  
     
      if($reminder_id != null)        
        $_final = ($_final == null)?'first' : $_final;
      $reminders = $this->getLazy('reminder', $_with, $_where, $_whereHas, $_orderBy, $_final); 

      return $reminders;     
    }

    //GET Reminder Actions
    public function getReminderActionsLazy($action_id = NULL, $action_name = NULL)
    { 
      $_with = [];
      $_where = [];
      if($action_id != null && $action_name == null)
        $_where = [
          'id' => ['operator' => '=', 'value' => $action_id]
        ];

      if($action_name != null)      
        $_where = [
          'action_name' => ['operator' => '=', 'value' => $action_name]
        ];      
      else      
        $_where = [
          'action_name' => ['operator' => '!=', 'value' => 'General reminder']
        ];
        
      $_whereHas = [];      
      $_orderBy = [
        'id' => 'ASC'
      ];  
      $_final = 'get';
      if($action_id != null)
        $_final = 'first';
      $reminder_actions = $this->getLazy('reminderactionoption', $_with, $_where, $_whereHas, $_orderBy, $_final); 

      return $reminder_actions;     
    }

    //GET Task Dates
    public function getTaskDatesLazy($taskdate_id = NULL, $_where = [])
    { 
      $_with = [];      
      if($taskdate_id != null)
      {
        if($_where == null)
          $_where = [
            'id' => ['operator' => '=', 'value' => $taskdate_id]
          ];       
        else        
          $_where['id'] = ['operator' => '=', 'value' => $taskdate_id];
      }
      $_whereHas = [];      
      $_orderBy = [
        'id' => 'DESC'
      ];  
      $_final = 'get';
      if($taskdate_id != null)
        $_final = 'first';
      $taskdates = $this->getLazy('systemtaskdate', $_with, $_where, $_whereHas, $_orderBy, $_final); 

      return $taskdates;     
    }

    // //GET Excel Column Templates
    // public function getExcelColumnTemplatesLazy($template_id = NULL, $_where = [])
    // { 
    //   $_with = ['vatreg'];      
    //   if($template_id != null)
    //   {
    //     if($_where == null)
    //       $_where = [
    //         'id' => ['operator' => '=', 'value' => $template_id]
    //       ];       
    //     else        
    //       $_where['id'] = ['operator' => '=', 'value' => $template_id];
    //   }
    //   $_whereHas = [];      
    //   $_orderBy = [
    //     'id' => 'DESC'
    //   ];  
    //   $_final = 'get';
    //   if($template_id != null)
    //     $_final = 'first';
    //   $taskdates = $this->getLazy('excelcolumntemplates', $_with, $_where, $_whereHas, $_orderBy, $_final); 

    //   return $taskdates;     
    // }

    //GET Any Excel Templates
    public function getAnyExcelTemplates($template_id = NULL, $_where = [])
    { 
      $_with = ['vatreturnfiles', 'client'];  //vatreg
      if($template_id != null)
      {
        if($_where == null)
          $_where = [
            'id' => ['operator' => '=', 'value' => $template_id]
          ];       
        else        
          $_where['id'] = ['operator' => '=', 'value' => $template_id];
      }
      $_whereHas = [];      
      $_orderBy = [
        'id' => 'DESC'
      ];  
      $_final = 'get';
      if($template_id != null)
        $_final = 'first';
      $taskdates = $this->getLazy('anyexceltemplates', $_with, $_where, $_whereHas, $_orderBy, $_final); 

      return $taskdates;     
    }

    //GET Mailbox files
    public function getMailboxFilesLazy($file_id = NULL, $_where = [])
    {       
      $_with = ['vatregmain', 'vatregmain.client', 'vatregmain.vatreg', 'vatregmain.vatreg.anyexceltemplate'];
      $_where = []; 
      if($file_id != null)
      {
        if($_where == null)
          $_where = [
            'id' => ['operator' => '=', 'value' => $file_id]
          ];       
        else        
          $_where['id'] = ['operator' => '=', 'value' => $file_id];
      }
      $_whereHas = [];      
      $_orderBy = []; 
      if($file_id != null)
        $_orderBy = [
          'id' => 'ASC'
        ];     
      $_final = 'get';
      if($file_id != null)
        $_final = 'first';    
      $mailboxfiles = $this->getLazy('mailboxfiles', $_with, $_where, $_whereHas, $_orderBy, $_final); 

      return $mailboxfiles;        
    }  

    //GET Cargo Declaration files - WITH - Import VAT Files
    public function getCargoDeclarationFilesLazy($import_vat_id = NULL, $_where = [])
    {       
      $_with = ['vatreg', 'vatreg.vatregmain', 'vatreg.vatregmain.client', 'cargodeclarationfiles'];
      $_where = []; 
      if($import_vat_id != null)
      {
        if($_where == null)
          $_where = [
            'id' => ['operator' => '=', 'value' => $import_vat_id]
          ];       
        else        
          $_where['id'] = ['operator' => '=', 'value' => $import_vat_id];
      }
      $_whereHas = [];      
      $_orderBy = []; 
      if($import_vat_id != null)
        $_orderBy = [
          'id' => 'ASC'
        ];     
      $_final = 'get';
      if($import_vat_id != null)
        $_final = 'first';    
      $cargodeclarationfiles = $this->getLazy('ivf', $_with, $_where, $_whereHas, $_orderBy, $_final); 

      return $cargodeclarationfiles;        
    }

    //GET Cargo Declaration files - DIRECT
    public function getCargoDeclarationFileDirectLazy($file_id = NULL, $_where = [])
    {       
      $_with = [];
      $_where = []; 
      if($file_id != null)
      {
        if($_where == null)
          $_where = [
            'id' => ['operator' => '=', 'value' => $file_id]
          ];       
        else        
          $_where['id'] = ['operator' => '=', 'value' => $file_id];
      }
      $_whereHas = [];      
      $_orderBy = []; 
      if($file_id != null)
        $_orderBy = [
          'id' => 'ASC'
        ];     
      $_final = 'get';
      if($file_id != null)
        $_final = 'first';    
      $cargodeclarationfiles = $this->getLazy('cargodeclarationfiles', $_with, $_where, $_whereHas, $_orderBy, $_final); 

      return $cargodeclarationfiles;        
    }  
    
    //GET Team Users for the Company
    public function getTeamUsersLazy($client_id)
    {      
      $team_users = Client::leftJoin('dv_vat_registration', function($join) {
                            $join->on('dv_vat_registration.client_id', '=', 'dv_clients.id');
                          })   
                          ->leftJoin('dv_user_vat_registration', function($join) {
                            $join->on('dv_user_vat_registration.vat_reg_id', '=', 'dv_vat_registration.id');
                          }) 
                          ->leftJoin('users', function($join) {
                            $join->on('users.id', '=', 'dv_user_vat_registration.user_id');
                          }) 
                          ->rightJoin('dv_users', function($join) {
                            $join->on('users.id', '=', 'dv_users.user_id');
                          })                                                 
                          ->select(                            
                            DB::raw('coalesce(group_concat(distinct dv_users.firstname, " " , dv_users.lastname separator ", "),"-") AS team_user_name')
                          )  
                          ->distinct()                          
                          ->where('dv_clients.id', '=', $client_id)
                          ->first()
                          ;

      $team_users = $team_users->team_user_name;  
                          
      return $team_users;                
    }   

    public function getAllVatRegQuery($authUser, $client_id = NULL, $only_team_user = true, $pageSize = NULL, $page = 1)
    {      
        $query = VATRegistration::with([                   
                    'vatregmain' => function ($query) {
                        $query->select(['id',//foreign_key -DON'T REMOVE
                          'id AS vat_reg_main_id',//foreign_key -DON'T REMOVE
                          'status', 'is_deleted',
                          'vat_reg_type', 'product_type', 'cash_acc_stmt', 'duty_defer_acc', 'account_nos', 'org_no'
                          , 'uk_gateway_userid', 'uk_gateway_password', 'cds_gateway_userid', 'cds_gateway_password'
                        ]);                        
                    },
                    'client' => function ($query) use ($client_id) {
                        $query->select(['id',//foreign_key -DON'T REMOVE 
                          'id AS client_id',//foreign_key -DON'T REMOVE                                                     
                          'client_name', 'vatno', 'status'
                        ]);                                
                    },
                    'vatregmain.clientapi' => function ($query) {  
                        $query->select(['id',//foreign_key -DON'T REMOVE  
                          'vat_reg_main_id',//foreign_key -DON'T REMOVE
                          'api_name', 'api_env', 'api_base_url', 'api_tenant_id', 'api_client_id', 
                          'api_secret_key', 'api_company_id', 'api_token', 'api_token_expire',
                          'currency_code', 'status', 'use_base_currency_amount'
                        ]);            
                    },
                    'vatregmain.accnos',
                    'vatregmain.casddamonths',
                    'client.userclient' => function ($query) {
                        $query->select(['id',//foreign_key -DON'T REMOVE
                          'user_id',//foreign_key -DON'T REMOVE
                          'client_id',//foreign_key -DON'T REMOVE
                        ]);                                             
                    },
                    'client.userclient.user' => function ($query) {
                        $query->select(['id',//foreign_key -DON'T REMOVE
                          'id AS user_id',//foreign_key -DON'T REMOVE
                          'name', 'email'
                        ]);                                             
                    },                    
                    'client.userclient.user.dvuser' => function ($query) {
                        $query->select(['id',//foreign_key -DON'T REMOVE
                          'user_id',//foreign_key -DON'T REMOVE
                          'firstname', 'lastname', 'telephone', 'lang', 'status', 'is_deleted'
                        ]);                                             
                    },  
                    'client.userclient.user.notificationsettings',                          
                    'documents',
                    'c79',
                    'cas',
                    'dda',
                    'importvatfiles',
                    'commercialinvoicesfiles',
                    'pivs',
                    'receipt',
                    'filesemailnote',
                    'vatreturnfiles',
                    'vatreturnfiles.vatreturnofiles',
                    //'vatreturnfiles.excelcolumntemplate',
                    'vatreturnfiles.anyexceltemplate',

                    'vatcontrolfiles',
                    'vatcontrolfiles.vatcontrolofiles',
                    'vatcontrolfiles.anyexceltemplate',

                    'ircontrolfiles',
                    'ircontrolfiles.ircontrolofiles',
                    'ircontrolfiles.anyexceltemplate',

                    'vatreturns',                   
                    'invoices',
                    'submittingfields',
                    'submittingfieldsNO',
                    'submittingfieldsCH',
                    'uservatreg',
                    'uservatreg.user',
                    'uservatreg.user.dvuser',
                    'vatreturncomments',
                    'vatreturncommentfiles',
                    //'excelcolumntemplate',
                    'anyexceltemplate',
                    
                    'importreconciliationfiles',   
                    'importreconciliationanyexcelfiles',
                    
                    'importreconciliationcominvoices',
                    'importreconciliationsalesinvoices'
                  ])  
                  ->select(['id',
                    'vat_reg_main_id',//foreign_key -DON'T REMOVE 
                    'client_id',//foreign_key -DON'T REMOVE
                    'id AS vat_reg_id',//foreign_key -DON'T REMOVE 
                    
                    'anyexcel_template_id',
                    'country', 'service_start', 'turnover_date', 'general_periods', 
                    'status', 'status_import_re', 
                    'is_disregard', 'is_disregard_import_re',
                    'next_pivs_date', 'next_cas_date', 'next_dda_date',
                    'created_at', 'updated_at', 'email_by', 'email_at',
                    'approved_by', 'approved_at', 'declined_at', 'declined_reason',
                    'receipt_by', 'receipt_at', 'locked_by', 'locked_at',
                    DB::raw('(CASE 
                      WHEN status = 0 THEN "Inactive" 
                      WHEN status = 1 THEN "Draft Created" 
                      WHEN status = 2 THEN "Draft" 
                      WHEN status = 3 THEN
                        CASE
                          WHEN ISNULL(declined_at) THEN "Pending review"
                          ELSE "Declined"
                        END
                      WHEN status = 4 THEN "Ready to submit" 
                      WHEN status = 5 THEN "Submitted" 
                      WHEN status = 6 THEN "Locked" 
                      ELSE "" END) AS statustext'
                    ),
                    DB::raw('(CASE 
                      WHEN status = 0 THEN 0
                      WHEN status = 1 THEN 1 
                      WHEN status = 2 THEN 3 
                      WHEN status = 3 THEN 2
                      WHEN status = 4 THEN 4 
                      WHEN status = 5 THEN 5 
                      WHEN status = 6 THEN 6 
                      ELSE "" END) AS statusorder'
                    ),
                    DB::raw('(CASE 
                      WHEN status_import_re = 0 THEN "Inactive" 
                      WHEN status_import_re = 1 THEN "Draft Created" 
                      WHEN status_import_re = 2 THEN "Draft"                                                           
                      WHEN status_import_re = 3 THEN "Completed" 
                      ELSE "" END) AS statustext_importre'
                    ),
                    DB::raw('(CASE 
                      WHEN status_import_re = 0 THEN 0
                      WHEN status_import_re = 1 THEN 1 
                      WHEN status_import_re = 2 THEN 2 
                      WHEN status_import_re = 3 THEN 3                                    
                      ELSE "" END) AS statusorder_importre'
                    ),
                    DB::raw('(CASE                       
                      WHEN general_periods = "monthly" THEN 1 
                      WHEN general_periods = "bi-monthly" THEN 2
                      WHEN general_periods = "quarterly" THEN 3 
                      WHEN general_periods = "half-yearly" THEN 6 
                      WHEN general_periods = "yearly" THEN 12                      
                      ELSE "" END) AS frequency'
                    )
                  ]) 
                  // ->whereHas('vatregmain', function ($query) {
                  //     $query->where('status', 1);                      
                  // })
                  // ->whereHas('client', function ($query) {
                  //     $query->where('status', 1); 
                  // })                                   
        ;        
        
        if($client_id == null)
          $query = $query->where('status', '<>', 6);
        else
        {
          if($authUser->role == 'client-user') 
          {
            $query = $query->whereHas('client', function ($query) use ($client_id) {
                      if(is_string($client_id))
                        $query->where('id', $client_id);                         
                      else
                        $query->whereIn('id', $client_id); 
                    });

            if(!is_string($client_id))
              $query = $query->where('status', 3);//DON'T DELETE - need for Confirmation page
          }
          else            
            $query = $query->whereHas('client', function ($query) use ($client_id) {
                        $query->where('id', $client_id); 
                    });                      
        }

        $query = $query->orderBy('client_id', 'ASC')             
              ->orderBy('id', 'DESC')
              //->orderBy('statusorder', 'DESC')                           
              ;

      if($pageSize)      
        $query = $query->paginate($pageSize, ['*'], 'page', $page);
      else
        $query = $query->get();

      return $query;
     
    }

    public function getSpecificVatRegQuery($vat_reg_id, $_include = true)
    {
      if($_include)
        $_include = [                   
                  'vatregmain' => function ($query) {
                      $query->select(['id',//foreign_key -DON'T REMOVE
                        'id AS vat_reg_main_id',//foreign_key -DON'T REMOVE
                        'status', 'is_deleted', 'vat_reg_type', 'product_type', 'cash_acc_stmt', 'duty_defer_acc', 'account_nos'
                        , 'org_no', 'vat_no', 'country', 'uk_gateway_userid', 'uk_gateway_password', 'cds_gateway_userid', 'cds_gateway_password'
                      ]); 
                      //$query->where('status', '=', 1);                         
                  },
                  'client' => function ($query) {
                      $query->select(['id',//foreign_key -DON'T REMOVE 
                        'id AS client_id',//foreign_key -DON'T REMOVE                                                     
                        'client_name', 'vatno', 'status'
                      ]);

                      //$query->where('status', '=', 1);            
                  },
                  'vatregmain.clientapi' => function ($query) {  
                      $query->select(['id',//foreign_key -DON'T REMOVE  
                        'vat_reg_main_id',//foreign_key -DON'T REMOVE
                        'api_name', 'api_env', 'api_base_url', 'sales_invoice_url', 'purchase_invoice_url', 
                        'api_tenant_id', 'api_client_id', 
                        'api_secret_key', 'api_company_id', 'api_token', 'api_token_expire',
                        'currency_code', 'status', 'use_base_currency_amount'
                      ]);

                      $query->where('status', '=', 1);                     
                  },
                  'vatregmain.accnos',
                  'vatregmain.casddamonths',
                  'vatreturns',                 
                  'invoices',                 
                  'c79',                
                  'importvatfiles', 
                  'importvatfiles.cargodeclarationfiles',                
                  'pivs',                  
                  'submittingfields',
                  'submittingfieldsNO',
                  'submittingfieldsCH',
                  'vatreturnfiles',    
                  'vatreturnfiles.vatreturnofiles',  
                  //'vatreturnfiles.excelcolumntemplate',
                  'vatreturnfiles.anyexceltemplate',

                  'vatcontrolfiles',
                  'vatcontrolfiles.vatcontrolofiles',
                  'vatcontrolfiles.anyexceltemplate',

                  'ircontrolfiles',
                  'ircontrolfiles.ircontrolofiles',
                  'ircontrolfiles.anyexceltemplate',

                  //'excelcolumntemplate',
                  'anyexceltemplate',
                  'commercialinvoicesfiles',
                 
                  'importreconciliationfiles',
                  'importreconciliationanyexcelfiles',
                  'importreconciliationswissfiles',
                  'importreconciliationfiles.salesinvoicesdata',
                  'importreconciliationfiles.salesinvoicesdata.items',
                        
                  'importreconciliationcominvoices',
                  'importreconciliationsalesinvoices',                  
              ];
      else
        $_include = [                   
                  'vatregmain' => function ($query) {
                      $query->select(['id',//foreign_key -DON'T REMOVE
                        'id AS vat_reg_main_id',//foreign_key -DON'T REMOVE
                        'status', 'vat_reg_type', 'product_type', 'cash_acc_stmt', 'duty_defer_acc', 'account_nos', 'org_no'
                        , 'vat_no', 'country', 'uk_gateway_userid', 'uk_gateway_password', 'cds_gateway_userid', 'cds_gateway_password'
                      ]); 
                      //$query->where('status', '=', 1);                         
                  },
                  'client' => function ($query) {
                      $query->select(['id',//foreign_key -DON'T REMOVE 
                        'id AS client_id',//foreign_key -DON'T REMOVE                                                     
                        'client_name', 'vatno', 'status'
                      ]);

                      //$query->where('status', '=', 1);            
                  },
                  'vatregmain.clientapi' => function ($query) {  
                      $query->select(['id',//foreign_key -DON'T REMOVE  
                        'vat_reg_main_id',//foreign_key -DON'T REMOVE
                        'api_name', 'api_env', 'api_base_url', 'api_tenant_id', 'api_client_id', 
                        'api_secret_key', 'api_company_id', 'api_token', 'api_token_expire',
                        'currency_code', 'status', 'use_base_currency_amount'
                      ]);

                      $query->where('status', '=', 1);                     
                  },
                  //'vatregmain.clientapi',
                  'vatregmain.accnos',
                  'vatreturns'
              ];        

      $vatreg = VATRegistration::with($_include) 
              ->select(['id',
                  'vat_reg_main_id',//foreign_key -DON'T REMOVE 
                  'client_id',//foreign_key -DON'T REMOVE
                  'id AS vat_reg_id',//foreign_key -DON'T REMOVE              
                
                  'anyexcel_template_id',
                  'country', 'service_start', 'turnover_date', 'general_periods', 
                  'status', 'status_import_re', 
                  'is_disregard', 'is_disregard_import_re',
                  'next_pivs_date', 'next_cas_date', 'next_dda_date',
                  'created_at', 'updated_at', 'email_by', 'email_at',
                  'approved_by', 'approved_at', 'declined_at', 'declined_reason',
                  'receipt_by', 'receipt_at', 'locked_by', 'locked_at',
                  DB::raw('(CASE 
                    WHEN status = 0 THEN "Inactive" 
                    WHEN status = 1 THEN "Draft Created" 
                    WHEN status = 2 THEN "Draft" 
                    WHEN status = 3 THEN
                        CASE
                          WHEN ISNULL(declined_at) THEN "Pending review"
                          ELSE "Declined"
                        END
                    WHEN status = 4 THEN "Ready to submit" 
                    WHEN status = 5 THEN "Submitted" 
                    WHEN status = 6 THEN "Locked" 
                    ELSE "" END) AS statustext'
                  ),
                  DB::raw('(CASE 
                    WHEN status = 0 THEN 0
                    WHEN status = 1 THEN 1 
                    WHEN status = 2 THEN 3 
                    WHEN status = 3 THEN 2
                    WHEN status = 4 THEN 4 
                    WHEN status = 5 THEN 5 
                    WHEN status = 6 THEN 6 
                    ELSE "" END) AS statusorder'
                  ),
                  DB::raw('(CASE 
                    WHEN status_import_re = 0 THEN "Inactive" 
                    WHEN status_import_re = 1 THEN "Draft Created" 
                    WHEN status_import_re = 2 THEN "Draft"                                                           
                    WHEN status_import_re = 3 THEN "Completed" 
                    ELSE "" END) AS statustext_importre'
                  ),
                  DB::raw('(CASE 
                    WHEN status_import_re = 0 THEN 0
                    WHEN status_import_re = 1 THEN 1 
                    WHEN status_import_re = 2 THEN 2 
                    WHEN status_import_re = 3 THEN 3                                    
                    ELSE "" END) AS statusorder_importre'
                  ),
                  DB::raw('(CASE                       
                    WHEN general_periods = "monthly" THEN 1 
                    WHEN general_periods = "bi-monthly" THEN 2
                    WHEN general_periods = "quarterly" THEN 3 
                    WHEN general_periods = "half-yearly" THEN 6 
                    WHEN general_periods = "yearly" THEN 12                      
                    ELSE "" END) AS frequency'
                  )
              ])                  
              ->where('id', '=', $vat_reg_id)
              ->first();
              
      return $vatreg;              
    }

    /*ClientVATReturns */
    public function loadApiDatas($authUser, $vatreg, $system = null, $refresh = false, $from = 'default')
    {
      $apiClass = new ApiClass();   
      $anyExcelTemplateClass = new AnyExcelTemplateClass();   

      $vatregmain = $vatreg->vatregmain;
      $clientapi = ($vatregmain->clientapi) ? $vatregmain->clientapi : null;
      $api_name = ($clientapi) ? $clientapi->api_name: null;
      $client = $vatreg->client;
      $vat_reg_id = $vatreg->id;

      $this->addLog($authUser, 'invoice-load', 
        [
          'From' => $from,
          'Refresh' => ($refresh) ? 'Logged in user refreshed' : '',
          'Loggedin User' => ($from == 'cron') ? 'Cron Job' : ((isset($authUser->firstname) && isset($authUser->lastname)) ? ($authUser->firstname . ' ' . $authUser->lastname) : $authUser->name),
          'Client Name' => $client->client_name,
          'VAT Reg' => Carbon::parse($vatreg->service_start)->format('M Y') . ' ' . $vatreg->country . ' ' . $vatreg->general_periods
        ]
      );

      if($api_name == "Dynamics 365")
      {
        $specificClass = new DynamicsApiClass();

        $client_id = $vatreg->client_id;
        $client_name = $client->client_name;
        $vat_no = $vatreg->vatno;
        $api_base_url = ($clientapi) ? $clientapi->api_base_url : null;
        $tenant_id = ($clientapi) ? $clientapi->api_tenant_id : null;
        $api_environment = ($clientapi) ? $clientapi->api_env : null;
        $api_company_id = ($clientapi) ? (($clientapi->api_company_id) ? $clientapi->api_company_id : null) : null;

        if($api_company_id == null)    
        {  
          //API - Client
          $access_token = $specificClass->getAccessTokenLazy($vatreg);

          if(isset($access_token->error))
          {
            $result = [            
             'api_ledger' => [],
             'error' => "Please check the Dynamic 365 credentials"
            ];

            return response()->json($result);  
          }
          else
          {            
            $api_token = ($clientapi) ? (($clientapi->api_token != null) ? $clientapi->api_token : $clientapi->api_token) : null;

            $auth_bearer = ($access_token == "not expired") ? ('Bearer ' . $api_token) : ($access_token->token_type . ' ' . $access_token->access_token); 

            $api_client = $specificClass->getApiClientLazy($auth_bearer, $vatreg);
            if($api_client)
            {
              $api_company_id = $api_client->id;

              $api_details = ['vat_reg_main_id' => $vatreg->vat_reg_main_id, 'api_company_id' => $api_company_id];
      
              $updateApi = $this->updateApiDetails($client_id, $api_details);
         
              $api_company_id = $updateApi->api_company_id;
            }
          }//no error
        }
      } //Dynamics 365  
      else if($api_name == "Dynamics 365 via SmartApi")
      {
        $specificClass = new DynamicsSmartApiClass();        
      } //Dynamics 365 via SmartApi
      else if($api_name == "E-conomic" || $api_name == "Uniconta" || $api_name == "Shopify" || $api_name == "Billy")
      {
        $specificClass = new EconomicApiClass();

        $client_id = $vatreg->client_id;
        $api_base_url = ($clientapi) ? $clientapi->api_base_url : null;
        $api_secret_key = ($clientapi) ? $clientapi->api_secret_key : null;
        $api_client_id = ($clientapi) ? $clientapi->api_client_id : null;      
        $country = $vatreg->country;
      } //E-conomic, Uniconta, Shopify, Billy
      else if($api_name == "FTP")                           
      {        
        $specificClass = new FtpClass();

        $client_id = $vatreg->client_id;
        $client_name = $client->client_name;
        $vat_no = $vatreg->vatno;
      } //FTP
      
      if($api_name == "Shopify")
      {       
        $specificClass = new ShopifyApiClass();                          
        $api_version = ($clientapi) ? $clientapi->api_client_id : null;      
      } //Shopify

      if($api_name == "Uniconta")
        $specificClass = new UnicontaApiClass();

      if($api_name == "Billy")
        $specificClass = new BillyApiClass();
      
      $datas = [];
      
      if($vat_reg_id != null)   
      {   
        $client_name = $client->client_name;

        //Get VAT Returns from Table       
        $vatreturns = $vatreg->vatreturns;
        $invoices = $vatreg->invoices;
              
        if(($vatreg->status != 0) && count($vatreturns) == 0 || $refresh || count($invoices) == 0)
        {
          $account_data = [];
          $account_data_err_message = "";
          $err_vatreturn_file_id = null;
          
          if($api_name == null)
          {
            $service_start = $vatreg->service_start;
            $end_date = $apiClass->getEndDateLazy($vatreg);              
            
            //Get VAT Return File from One Drive                     
            $vatreturnfiles = $vatreg->vatreturnfiles;      
            if(count($vatreturnfiles) > 0) 
            {               
              $downloadurls = [];
              foreach ($vatreturnfiles as $vatreturnfile)
              {
                $vatreturnfileid = $vatreturnfile->id;
                if($vatreturnfile->file_id)
                {
                  $downloadurl = $apiClass->loadFromOneDriveLazy($vatreturnfile, $system);
                  $downloadurls[$vatreturnfileid][] = $downloadurl;

                  if(isset($downloadurl->error))
                  {
                    $account_data = "error";
                    $account_data_err_message = $downloadurl->error;
                    $this->addLog($authUser, 'invoice-load-error', 
                      [
                        'Loggedin User' => (isset($authUser->firstname) && isset($authUser->lastname)) ? ($authUser->firstname . ' ' . $authUser->lastname) : $authUser->name,
                        'Client Name' => $client_name,
                        'Error' => $account_data_err_message
                      ]
                    );
                  } /* --end if DOWNLOAD URL ERROR -- */
                  else if($downloadurl == null)
                  {
                    $account_data = "error";
                    $account_data_err_message = "No files exists";
                    $this->addLog($authUser, 'invoice-load-error', 
                      [
                        'Loggedin User' => (isset($authUser->firstname) && isset($authUser->lastname)) ? ($authUser->firstname . ' ' . $authUser->lastname) : $authUser->name,
                        'Client Name' => $client_name,
                        'Error' => $account_data_err_message//"No files exists"
                      ]
                    );
                  } /* --end else DOWNLOAD URL NULL -- */
                }
                else
                {                  
                  $vatreturnofiles = $vatreturnfile->vatreturnofiles;
                  foreach ($vatreturnofiles as $vatreturnofile)
                  {
                    $downloadurl = $apiClass->loadFromOneDriveLazy($vatreturnofile, $system, true);
                    $downloadurls[$vatreturnfileid][] = $downloadurl;

                    if(isset($downloadurl->error))
                    {
                      $account_data = "error";
                      $account_data_err_message = $downloadurl->error;
                      $this->addLog($authUser, 'invoice-load-error', 
                        [
                          'Loggedin User' => (isset($authUser->firstname) && isset($authUser->lastname)) ? ($authUser->firstname . ' ' . $authUser->lastname) : $authUser->name,
                          'Client Name' => $client_name,
                          'Error' => $account_data_err_message
                        ]
                      );
                    } /* --end if DOWNLOAD URL ERROR -- */
                    else if($downloadurl == null)
                    {
                      $account_data = "error";
                      $account_data_err_message = "No files exists";
                      $this->addLog($authUser, 'invoice-load-error', 
                        [
                          'Loggedin User' => (isset($authUser->firstname) && isset($authUser->lastname)) ? ($authUser->firstname . ' ' . $authUser->lastname) : $authUser->name,
                          'Client Name' => $client_name,
                          'Error' => $account_data_err_message//"No files exists"
                        ]
                      );
                    } /* --end else DOWNLOAD URL NULL -- */
                  } /* --end for O FILES -- */
                } /* --end if O FILES -- */                
              } /* --end for VAT RETURN FILES -- */
            
              $data_detail = [];
              $invoice_rows = [];
              foreach ($downloadurls as $key=>$downloadurlgroup) 
              {                
                if(count($downloadurlgroup) > 1)
                { 
                  //if($vatreturnfile->excel_column_template_id)
                  if($vatreturnfile->anyexcel_template_id)
                  {                                        
                    /* -- GENERATE SYSTEM DEFAULT EXCEL FROM TEMPLATE -- */                         
                    $generate_excel = $anyExcelTemplateClass->generateSystemDefaultExcel($vatreg, $authUser, $downloadurlgroup, true);
                    /* --end GENERATE SYSTEM DEFAULT EXCEL FROM TEMPLATE -- */

                    if($generate_excel['status'] == 'Success')
                    {
                      /* -- DOWNLOAD FROM ONE-DRIVE -- */ 
                      $vatreturnfile = $generate_excel['vatreturnfile'];
                      $downloadurl_mappedfile = $apiClass->loadFromOneDriveLazy($vatreturnfile, $system);
                      /* --end DOWNLOAD FROM ONE-DRIVE -- */ 

                      $this->addLog($authUser, 'invoice-reading-mapped-file', 
                        [          
                          'Client Name' => $client_name,
                          'VAT Reg' => Carbon::parse($vatreg->service_start)->format('M Y') . ' ' . $vatreg->country . ' ' . $vatreg->general_periods
                        ]
                      );

                      /* -- READ EXCEL FILE -- */
                      $read_data = $this->readVatReturnFile($downloadurl_mappedfile['download_url'], NULL, $downloadurl_mappedfile['file_extension'], $data_detail, $invoice_rows); 
                      /* --end READ EXCEL FILE -- */ 

                      $this->addLog($authUser, 'invoice-read-mapped-file', 
                        [          
                          'Client Name' => $client_name,
                          'VAT Reg' => Carbon::parse($vatreg->service_start)->format('M Y') . ' ' . $vatreg->country . ' ' . $vatreg->general_periods
                        ]
                      );                       
                    } /* --end if SUCCESS -- */
                    else
                    {
                      $account_data = "error";
                      $account_data_err_message = $generate_excel['message'];
                      $err_vatreturn_file_id = $key;
                      $this->addLog($authUser, 'invoice-load-error', 
                        [
                          'Loggedin User' => (isset($authUser->firstname) && isset($authUser->lastname)) ? ($authUser->firstname . ' ' . $authUser->lastname) : $authUser->name,
                          'Client Name' => $client_name,
                          'Error' => $account_data_err_message
                        ]
                      );
                    } /* --end else ERROR -- */
                  } /* --end if HAS TEMPLATE -- */
                } /* --end if MULTI VAT RETURN FILES -- */
                else
                {
                  foreach ($downloadurlgroup as $downloadurl) 
                  {                     
                    if($downloadurl['original_file'])                   
                    {
                      //if($vatreg->excelcolumntemplate)
                      if($vatreg->anyexceltemplate)
                      {                        
                        /* -- GENERATE SYSTEM DEFAULT EXCEL FROM TEMPLATE -- */
                        $generate_excel = $anyExcelTemplateClass->generateSystemDefaultExcel($vatreg, $authUser, $downloadurl);
                        /* --end GENERATE SYSTEM DEFAULT EXCEL FROM TEMPLATE -- */
                        
                        if($generate_excel['status'] == 'Success')
                        {
                          /* -- DOWNLOAD FROM ONE-DRIVE -- */ 
                          $vatreturnfile = $generate_excel['vatreturnfile'];
                          $downloadurl_mappedfile = $apiClass->loadFromOneDriveLazy($vatreturnfile, $system);
                          /* --end DOWNLOAD FROM ONE-DRIVE -- */ 

                          $this->addLog($authUser, 'invoice-reading-mapped-file', 
                            [          
                              'Client Name' => $client_name,
                              'VAT Reg' => Carbon::parse($vatreg->service_start)->format('M Y') . ' ' . $vatreg->country . ' ' . $vatreg->general_periods
                            ]
                          );

                          /* -- READ EXCEL FILE -- */
                          $read_data = $this->readVatReturnFile($downloadurl_mappedfile['download_url'], NULL, $downloadurl_mappedfile['file_extension'], $data_detail, $invoice_rows); 
                          /* --end READ EXCEL FILE -- */ 

                          $this->addLog($authUser, 'invoice-read-mapped-file', 
                            [          
                              'Client Name' => $client_name,
                              'VAT Reg' => Carbon::parse($vatreg->service_start)->format('M Y') . ' ' . $vatreg->country . ' ' . $vatreg->general_periods
                            ]
                          );                       
                        } /* --end if SUCCESS -- */
                        else
                        {
                          $account_data = "error";
                          $account_data_err_message = $generate_excel['message'];
                          $err_vatreturn_file_id = $key;
                          $this->addLog($authUser, 'invoice-load-error', 
                            [
                              'Loggedin User' => (isset($authUser->firstname) && isset($authUser->lastname)) ? ($authUser->firstname . ' ' . $authUser->lastname) : $authUser->name,
                              'Client Name' => $client_name,
                              'Error' => $account_data_err_message
                            ]
                          );
                        } /* --end else ERROR -- */
                      } /* --end if HAS TEMPLATE -- */
                      else
                      {
                        //DO AUTO PROCESS  
                      } /* --end if not HAS TEMPLATE -- */
                    } /* --end if ORGINAL FILE -- */
                    else
                    {  
                      $this->addLog($authUser, 'invoice-reading-mapped-file', 
                        [          
                          'Client Name' => $client_name,
                          'VAT Reg' => Carbon::parse($vatreg->service_start)->format('M Y') . ' ' . $vatreg->country . ' ' . $vatreg->general_periods
                        ]
                      );
                      
                      /* -- READ EXCEL FILE -- */
                      $read_data = $this->readVatReturnFile($downloadurl['download_url'], NULL, $downloadurl['file_extension'], $data_detail, $invoice_rows);
                      /* --end READ EXCEL FILE -- */ 

                      $this->addLog($authUser, 'invoice-read-mapped-file', 
                        [          
                          'Client Name' => $client_name,
                          'VAT Reg' => Carbon::parse($vatreg->service_start)->format('M Y') . ' ' . $vatreg->country . ' ' . $vatreg->general_periods
                        ]
                      );                     
                    } /* --end else not ORGINAL FILE -- */
                    
                    if(!empty($read_data))
                    {
                      $data_detail = $read_data['data_detail'];
                      $invoice_rows = $read_data['invoice_rows'];     
                    }          
                  } /* --end for DOWNLOAD URL GROUP -- */  
                } /* --end else MULTI VAT RETURN FILES -- */  
              } /* --end for DOWNLOAD URLS -- */

              if($account_data != "error")    
              {
                if(!empty($invoice_rows))
                {
                  /* -- INSERT INTO INVOICES -- */
                  $insert_invoices = $this->insertInvoices($invoice_rows, $vat_reg_id, $authUser, $api_name);             
                  /* --end INSERT INTO INVOICES -- */
                 
                  $account_data = $data_detail;
                }      
              }                          
            } /* --end if VAT RETURN FILES -- */
          } /* --end if API NULL -- */
          else
          {   
            if($api_name == "FTP")
            {
              $service_start = $vatreg->service_start;
              $end_date = $apiClass->getEndDateLazy($vatreg);              
              
              $frequency_index = Carbon::parse($end_date)->format('m')/$vatreg->frequency;

              $filenameeixsts = $vatreg->country . '-' . $frequency_index . '-' . Carbon::parse($end_date)->format('y');
                        
              /* -- READ EXCEL FILE FROM FTP -- */
              $read_data = $specificClass->getVatReturnFilesFromFtpLazy($vatreg, $authUser, $filenameeixsts); 
              /* --end READ EXCEL FILE FROM FTP -- */

              if(!empty($read_data))              
              {
                /* -- INSERT INTO INVOICES -- */
                $insert_invoices = $this->insertInvoices($read_data['invoice_rows'], $vat_reg_id, $authUser, $api_name);
                /* --end INSERT INTO INVOICES -- */

                $account_data = $read_data['data_detail'];
              }
            } /* --end if FTP -- */
            else
            {
              // $_get_invoices = true;
              // if($api_name == "E-conomic")
              // {
              //   $api_call_results = DB::table('api_call_results')                                    
              //                         ->where('vat_reg_id', $vat_reg_id)
              //                         //->where('status', 'completed')                                       
              //                         ->get();
              
              //   $grouped = $api_call_results->groupBy('total_job');

              //   foreach ($grouped as $item_key => $items) 
              //   {     
              //     if(count($api_call_results->where('total_job', $item_key)) > 0)
              //     { 
              //       $_completed_jobs = $api_call_results->where('total_job', $item_key)->where('status', 'completed');
              //       if($item_key == count($_completed_jobs))
              //       {
              //         foreach ($_completed_jobs as $api_call_result) 
              //         {                             
              //           $api_call_result_account_no_datas = json_decode($api_call_result->account_no_datas);

              //           if(count($api_call_result_account_no_datas) > 0)                     
              //             $account_data = array_merge($account_data, $api_call_result_account_no_datas);
              //         }
              //       }
              //       else
              //         $_get_invoices = false; //still jobs running

              //       // if($api_call_results->batch_id)
              //       // {                    
              //       //   $_get_invoices = false;
                   
              //       //   $api_call_result_account_no_datas = json_decode($api_call_results->account_no_datas);

              //       //   if(count($api_call_result_account_no_datas) > 0)
              //       //   {                            
              //       //     $account_data = $api_call_result_account_no_datas;   

              //       //     // $delete_api_call_results = DB::table('api_call_results')                                  
              //       //     //                             ->where('vat_reg_id', $vat_reg_id)
              //       //     //                             ->delete();                      
              //       //   } /* --end if HAS ACCOUNT INVOICES -- */                     
              //       // } //has batch id   
              //       // else
              //       // {
              //       //   if($api_call_results->total_job >= $api_call_results->completed_job)
              //       //   {
              //       //     $_get_invoices = false;

              //       //     $api_call_result_account_no_datas = json_decode($api_call_results->account_no_datas);

              //       //     if(count($api_call_result_account_no_datas) > 0)                     
              //       //       $account_data = $api_call_result_account_no_datas;   
              //       //   } //still jobs running
              //       // } //batch id NULL                            
              //     } //has api_call_results
              //   } //for grouped  
              // }//E-conomic
             
              // if($_get_invoices)
                $account_data = $specificClass->getAllInvoicesLazy(null, $vatreg, $authUser);              
            } /* --end else NOT FTP -- */
          } /* --end else API NOT NULL -- */                   

          $sales = [];
          $purchase = [];

          if($account_data == "error")    
          {            
            $updateVatReturnsValue = $this->updateVatReturnsValue($vatreg, [], []);

            $result = [            
             'api_ledger' => [],
             'error' => ($account_data_err_message) ? $account_data_err_message : "Error in reading the excel/xml file",
             'vatreturn_file_id' => ($err_vatreturn_file_id) ? $err_vatreturn_file_id : null
            ];

            return response()->json($result);  
          } /* --end if ACCOUNT DATA ERROR -- */
          else if(isset($account_data->error))
          {
            $this->addLog($authUser, 'invoice-load-error', 
              [
                'Loggedin User' => (isset($authUser->firstname) && isset($authUser->lastname)) ? ($authUser->firstname . ' ' . $authUser->lastname) : $authUser->name,
                'Client Name' => $client_name,
                'Error' => ($account_data_err_message) ? $account_data_err_message : $account_data->error
              ]
            );

            $updateVatReturnsValue = $this->updateVatReturnsValue($vatreg, [], []);
            
            $account_data_err_message = isset($account_data->error->message) ? $account_data->error->message : '';

            $result = [            
             'api_ledger' => [],
             'error' => ($account_data_err_message) ? $account_data_err_message : $account_data->error
            ];

            return response()->json($result);  
          } /* --end else ACCOUNT DATA ERROR -- */
          else
          {   
            $_organize_data = true;  
            // //if ($api_name == "E-conomic" && is_string($account_data))
            // if ($api_name == "E-conomic")
            // {
            //   $api_call_results = DB::table('api_call_results')                                  
            //                           ->where('vat_reg_id', $vat_reg_id)
            //                           ->where('status', 'completed')  
            //                           //->groupBy('total_job')         
            //                           ->get();

            //   $grouped = $api_call_results->groupBy('total_job');

            //   foreach ($grouped as $item_key => $items) 
            //   {     
            //     if(count($api_call_results->where('total_job', $item_key)) > 0)
            //     { 
            //       $_completed_jobs = $api_call_results->where('total_job', $item_key)->where('status', 'completed');
            //       if($item_key == count($_completed_jobs))
            //       {
            //         foreach ($_completed_jobs as $api_call_result) 
            //         {                             
            //           $api_call_result_account_no_datas = json_decode($api_call_result->account_no_datas);

            //           if(count($api_call_result_account_no_datas) > 0)                     
            //             $account_data = array_merge($account_data, $api_call_result_account_no_datas);
            //         }
            //       }
            //       else
            //         $_organize_data = false; //still jobs running                                  
            //     } //has api_call_results
            //     else
            //       $_organize_data = false; //still jobs running   
            //   } //for grouped

            //   // if(count($api_call_results) > 0)
            //   // {
            //   //   if($api_call_results->first()->total_job == count($api_call_results))
            //   //   {   
            //   //     foreach ($api_call_results as $api_call_result) 
            //   //     {                             
            //   //       $api_call_result_account_no_datas = json_decode($api_call_result->account_no_datas);

            //   //       if(count($api_call_result_account_no_datas) > 0)                     
            //   //         $account_data = $datas = array_merge($account_data, $api_call_result_account_no_datas);
            //   //     }                
            //   //   }
            //   //   else              
            //   //     $_organize_data = false;
            //   // } //has api_call_results
            //   // else              
            //   //   $_organize_data = false;
            // } //E-conomic
          
            if (is_array($account_data) && $_organize_data) 
            {           
              if(count($account_data) > 0) 
              {    
                if($api_name != null)              
                {
                  if($api_name != "FTP")           
                    $insert_invoices = $this->insertInvoices($account_data, $vat_reg_id, $authUser, $api_name);
                }
                
                $organize_account_datas = $this->organizeAccountDatas($api_name, $account_data, $vatreg);
              
                $this->addLog($authUser, 'invoice-group-percentage', 
                  [
                    'Client Name' => $vatreg->client->client_name,
                    'VAT Reg' => Carbon::parse($vatreg->service_start)->format('M Y') . ' ' . $vatreg->country . ' ' . $vatreg->general_periods
                  ]
                );

                $sales = $organize_account_datas['sales'];       
                $purchase = $organize_account_datas['purchase'];
              }
            }//account data is array
          } /* --end else ACCOUNT DATA -- */

          $datas = ['vatid' => $vat_reg_id, 'status' => $vatreg->status, "title" => (Carbon::parse($vatreg->service_start)->format('M Y') . " " . $vatreg->country . " " . $vatreg->general_periods), 'sales' => $sales, 'purchase' => $purchase
          ];

          /* -- UPDATE VAT RETURN -- */
          /* -- DON'T DELETE NOW -- */
          $updateVatReturnsValue = $this->updateVatReturnsValue($vatreg, $sales, $purchase);
          /* --end UPDATE VAT RETURN -- */
          
        } /* --end if REFRESH TRUE -- */
        else
        {   
          $vatreturnfromtbl = $this->VATReturnsFromTable($vatreturns);  
          $sales = $vatreturnfromtbl['sales'];
          $purchase = $vatreturnfromtbl['purchase'];
                     
          $datas = ['vatid' => $vat_reg_id, 'status' => $vatreg->status, "title" => (Carbon::parse($vatreg->service_start)->format('M Y') . " " . $vatreg->country . " " . $vatreg->general_periods), 'sales' => $sales, 'purchase' => $purchase
          ]; 
        } /* --end else REFRESH FALSE -- */        
      } /* --end if VAT REG NOT NULL -- */
      
      $result = [            
       'api_ledger' => $datas
      ];

      /* -- RETURN JSON -- */
      return response()->json($result); 
      /* --end RETURN JSON -- */
    }
      
    public function insertInvoices($invoice_data, $vat_reg_id, $authUser, $api_name)
    {          
      $taxcodelist = [
          //NO
          'DPGS', 'DPGS_CN','IMPTS', 'IMPTS_CN','IMPTGD', 
          'IMPTGD_CN','IMPTGND', 'IMPTGND_CN','DSGS', 
          'DSGS_CN','EXG', 'EXG_CN','EXS', 'EXS_CN',
          //UK
          'DPGRC', 'DPGRC_CN', 'DPSRC', 'DPSRC_CN', 
          'DSSRC', 'DSSRC_CN', 'DSGRC', 'DSGRC_CN'
      ];

      $vatreg_before = VATRegistration::with('client')->where('id', $vat_reg_id)->first(); 

      $this->addLog($authUser, 'invoice-before-delete', 
        [          
          'Client Name' => $vatreg_before->client->client_name,
          'VAT Reg' => Carbon::parse($vatreg_before->service_start)->format('M Y') . ' ' . $vatreg_before->country . ' ' . $vatreg_before->general_periods
        ]
      ); 
     
      DB::transaction(function () use ($vat_reg_id) {
        Invoices::where('vat_reg_id', $vat_reg_id)->chunkById(100, function ($rows) {
          foreach ($rows as $row) {
            $row->delete();
          }
        });
      });

      $this->addLog($authUser, 'invoice-after-delete', 
        [
          'Client Name' => $vatreg_before->client->client_name,
          'VAT Reg' => Carbon::parse($vatreg_before->service_start)->format('M Y') . ' ' . $vatreg_before->country . ' ' . $vatreg_before->general_periods
        ]
      ); 

      $vatreg = VATRegistration::with(['vatregmain', 'vatregmain.clientapi'])->where('id', $vat_reg_id)->first();   

      try
      {                           
        $chunks = array_chunk($invoice_data, 25); // Divide your data array into chunks

        // Create a batch and add jobs to it.
        $jobs = [];
        foreach ($chunks as $chunk) {
          $jobs[] = new InsertInvoices($vat_reg_id, $chunk, $vatreg, $authUser, $api_name, $taxcodelist);
        }

        // Dispatch all jobs in the batch.
        Bus::batch($jobs)->onQueue('invoice')->dispatch();

        $this->addLog($authUser, 'invoice-insert-batch', 
          [
            'Client Name' => $vatreg_before->client->client_name,
            'VAT Reg' => Carbon::parse($vatreg_before->service_start)->format('M Y') . ' ' . $vatreg_before->country . ' ' . $vatreg_before->general_periods
          ]
        );              
      }//try
      catch (\Exception $e) 
      {dd($e);
        return  $e->getMessage();
      }//catch     
    }  

    public function loadInvoicesFromPartition($vatreg)
    {
      try
      { 
        $apiClass = new ApiClass(); 

        $vat_reg_id = $vatreg->id;

        //Get start year
        $start_year = Carbon::parse($vatreg->service_start)->format('Y');

        //Get end year
        $end_date = $apiClass->getEndDateLazy($vatreg); 
        $end_year = Carbon::parse($end_date)->format('Y');

        //Get partition      
        $partitions = ($start_year == $end_year) ? ['invoice_'.$start_year] : ['invoice_'.$start_year, 'invoice_'.$end_year];

        //Get invoices  
        $search_by = null;
        $invoices = $this->getInvoicesLazy($vat_reg_id, $search_by, $partitions);

        return $invoices;
      }
      catch (Exception $e) 
      {
        return  $e->getMessage();
      }
    } 

    /*Load VAT/Import Reconciliation Control Files */
    public function loadControlFiles($authUser, $vatreg, $system = null, $type = 'vatcontrol')
    {
      $apiClass = new ApiClass();   
      $anyExcelTemplateClass = new AnyExcelTemplateClass();   

      $vatregmain = $vatreg->vatregmain;
      $clientapi = ($vatregmain->clientapi) ? $vatregmain->clientapi : null;
      $api_name = ($clientapi) ? $clientapi->api_name: null;
      $client = $vatreg->client;
      $client_name = $client->client_name;
      $vat_reg_id = $vatreg->id;

      $read_data = [];

      /*E-conomic - VAT check*/
      if($clientapi)
      {
        if($clientapi->api_name == 'E-conomic' && $vatreg->status <= 2)   
        {          
          $check_accountnos = [];
          $accountnos = $vatregmain->accnos;
          if(count($accountnos) > 0)
          {         
            foreach ($accountnos as $accountno) 
            {
              if($accountno->is_auto_vat_check == 1 || $accountno->is_auto_vat_check == 2)
              { 
                $check_accountnos[] = $accountno;
                // $filtered_invoices = $invoices->filter(function ($invoice, $key) use ($accountno) {         
                //   return $invoice->acc_no == $accountno->acc_no; 
                // });

                // $invoice_total_vat_amount = $filtered_invoices->sum('total_vat');
                // dd($invoice_total_vat_amount);
              }
            }
          }

          if(count($check_accountnos) > 0)
          {
            $economicApiClass = new EconomicApiClass();
            $account_data = $economicApiClass->getAllInvoicesLazy(null, $vatreg, $authUser, $check_accountnos);

            $invoiceCollection = $this->loadInvoicesFromPartition($vatreg);

            // Index both collections by 'invoice_no' for fast lookup
            $dbInvoices = $invoiceCollection->keyBy('invoice_no');   

            //E-conomic Data  
            $economicInvoices = collect($account_data)->keyBy('voucherNumber');

            //// Find missing in E-conomic
            //$missingInEconomic = $dbInvoices->keys()->diff($economicInvoices->keys());

            //// Find missing in DB (i.e., extra in E-conomic)
            //$missingInDB = $economicInvoices->keys()->diff($dbInvoices->keys());

            // Find amount mismatches
            $amountMismatch = $dbInvoices->filter(function ($item, $invoiceNo) use ($economicInvoices, $check_accountnos) {
              $acc_reverse = 1;
              $accountno = isset($economicInvoices[$invoiceNo]) ? $economicInvoices[$invoiceNo]->account->accountNumber : null;
              $filtered_check_accountno = collect($check_accountnos)->filter(function ($check_accountno, $key) use ($accountno) {
                return ($accountno) ? ($check_accountno->acc_no == $accountno) : false;
              })->first(); 

              if(isset($filtered_check_accountno) && $filtered_check_accountno->is_reverse)  
                $acc_reverse = -1;
              
              return isset($economicInvoices[$invoiceNo]) && $item['total_vat'] != ($acc_reverse * $economicInvoices[$invoiceNo]->amount);       
            });

            // Optional: Collect mismatches nicely
            $mismatchedDetails = $amountMismatch->map(function ($item, $invoiceNo) use ($economicInvoices, $check_accountnos) { 

              $acc_reverse = 1;
              $accountno = isset($economicInvoices[$invoiceNo]) ? $economicInvoices[$invoiceNo]->account->accountNumber : null;
              $filtered_check_accountno = collect($check_accountnos)->filter(function ($check_accountno, $key) use ($accountno) {
                return ($accountno) ? ($check_accountno->acc_no == $accountno) : false;
              })->first(); 

              if(isset($filtered_check_accountno) && $filtered_check_accountno->is_reverse)  
                $acc_reverse = -1;

              $difference_vat_amount = $item['total_vat'] - ($acc_reverse * $economicInvoices[$invoiceNo]->amount);
              $difference_percent = (($acc_reverse * $economicInvoices[$invoiceNo]->amount) != 0) ? ($difference_vat_amount / ($acc_reverse * $economicInvoices[$invoiceNo]->amount)) * 100 : 0;

                return [
                    //'file_id' => $economicInvoices[$invoiceNo]['file_id'],

                    'invoice_type' => ucfirst($item['invoice_type']),
                    'invoice_no' => $invoiceNo,
                    'invoice_date' => $item['invoice_date'],             
                    'net_amount' => $item['total_net'],
                    'vat_amount' => $item['total_vat'],
                    'currency_code' => $item['currency_code'],

                    'control_invoice_date' => $economicInvoices[$invoiceNo]->date,             
                    //'control_net_amount' => $economicInvoices[$invoiceNo]->amount,
                    'control_vat_amount' => ($acc_reverse * $economicInvoices[$invoiceNo]->amount),                 
                    
                    'difference_vat_amount' => $difference_vat_amount,
                    'difference_percent' => $difference_percent
                ];
            });      
          
            /* -- MISSING INVOICE ECONOMIC -- */
            if(count($mismatchedDetails) > 0)
            {                       
              /* -- FINALLY SAVE -- */       
              try 
              { 
                /* -- CREATE MISSING EXCEL -- */
                // Create new Spreadsheet object
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();

                $sheet->getColumnDimension('A')->setWidth(10);
                $sheet->getColumnDimension('B')->setWidth(15);
                $sheet->getColumnDimension('C')->setWidth(15);
                $sheet->getColumnDimension('D')->setWidth(15);
                $sheet->getColumnDimension('E')->setWidth(15);
                $sheet->getColumnDimension('F')->setWidth(2);
                $sheet->getColumnDimension('G')->setWidth(15);
                $sheet->getColumnDimension('H')->setWidth(15);

                // Define header style
                $headerStyle = [
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => Color::COLOR_WHITE],
                        'size' => 12,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF4F81BD'], // blue background
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ];

                // Apply style to header row A1 to H1
                $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

                // Optional: Set row height or column width if needed
                //$sheet->getRowDimension(1)->setRowHeight(25);

                // Set headers
                $sheet->setCellValue('A1', 'Invoice Type');
                $sheet->setCellValue('B1', 'Invoice Number');
                $sheet->setCellValue('C1', 'Invoice Date');
                $sheet->setCellValue('D1', 'NET Amount');
                $sheet->setCellValue('E1', 'VAT Amount');
                $sheet->setCellValue('F1', '.');
                $sheet->setCellValue('G1', 'Currency');
                $sheet->setCellValue('H1', 'Difference');
                
                // Start from row 2
                $row = 2;
                $file_ids = [];
                foreach ($mismatchedDetails as $item) {
                  $sheet->setCellValue("A{$row}", $item['invoice_type']);
                  $sheet->setCellValue("B{$row}", $item['invoice_no']);
                  $sheet->setCellValue("C{$row}", $item['invoice_date']);
                  $sheet->setCellValue("D{$row}", '');
                  $sheet->setCellValue("E{$row}", $item['vat_amount']);
                  $sheet->setCellValue("F{$row}", '');
                  $sheet->setCellValue("G{$row}", $item['currency_code']);
                  $sheet->setCellValue("H{$row}", $item['difference_vat_amount']);
                  
                  // Check for >5% change
                  if (abs($item['difference_percent']) > 5)                    
                    $sheet->getStyle("H{$row}")->getFont()->getColor()->setARGB(Color::COLOR_RED);
                  
                  // if(!in_array($item['file_id'], $file_ids, true))              
                  //   array_push($file_ids, $item['file_id']);
                  
                  $row++;
                }
                  
                $storage_path = storage_path('app/public/');
                $newFileName = $vat_reg_id . '.xlsx';    
                $newFilePath = $storage_path . $newFileName;

                $writer = new WriterXlsx($spreadsheet);
                $writer->save($newFilePath); 
                /* --end CREATE MISSING EXCEL -- */       

                /* -- STORE MAPPED FILE IN VAT CONTROL FILES -- */
                $apiClass =  new ApiClass();

                $system = $this->getSystemInfoLazy();
                $systemapi = $system->systemapi->first();
               
                $filecontent = file_get_contents($newFilePath);
                //$file[0] = $filecontent;
               
                $file = [
                    'file_ids' => $file_ids,
                    'file' => $filecontent,
                    'file_type' => $type, //'vatcontrol',
                    'file_type_title' => ($type == 'ircontrol') ? 'Import Reconciliation Control' : 'VAT Control'
                ];

                $fileDetails = $apiClass->uploadFileToOneDriveLazy($file, $vatreg, $authUser, $systemapi);                
                /* --end STORE MAPPED FILE IN VAT CONTROL FILES -- */

                /* -- DELETE FROM PUBLIC FOLDER -- */              
                Storage::disk('public')->delete($newFileName);  
                /* --end DELETE FROM PUBLIC FOLDER -- */   

                /* -- RETURN JSON -- */
                return [   
                    'status' => 'Success',                 
                    'fileDetails' => $fileDetails[0]
                ];
                /* --end RETURN JSON -- */ 
              } 
              catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) 
              {
                /* -- RETURN JSON -- */
                return response()->json([   
                    'status' => 'Error',                 
                    'message' => $e->getMessage()
                ]);
                /* --end RETURN JSON -- */ 
              }        
              /* -- FINALLY SAVE -- */
            } /* --end if HAS MISSING ROWS -- */
            else
            {
              /* -- RETURN JSON -- */
              return [   
                  'status' => 'Success',                 
                  'message' => 'No missing invoices'
              ];
                  /* --end RETURN JSON -- */
            } /* --end if NO MISSING ROWS -- */
          }
        }
      }
      /*E-conomic*/
      
      //Get Control File from One Drive                     
      $controlfiles = ($type == 'ircontrol') ? $vatreg->ircontrolfiles : $vatreg->vatcontrolfiles;
      if(count($controlfiles) > 0) 
      {               
        $downloadurls = [];
        foreach ($controlfiles as $controlfile)
        {
          $controlfileid = $controlfile->id;
          if(!$controlfile->file_id)
          {
            $controlofiles = ($type == 'ircontrol') ? $vatreg->ircontrolofiles : $vatreg->vatcontrolofiles;      
            $file_id = $controlfile->id;
            $filtered_controlofile = $controlofiles->filter(function($controlofile, $key) use ($file_id, $type) {
              return ($type == 'ircontrol') ? ($controlofile->ircontrol_file_id == $file_id) : ($controlofile->vatcontrol_file_id == $file_id);
            })->first();

            $downloadurl = $apiClass->loadFromOneDriveLazy($filtered_controlofile, $system);

            if(isset($downloadurl->error))
            {
              $account_data = "error";
              $account_data_err_message = $downloadurl->error;
              $this->addLog($authUser, $type .'-load-error', 
                [
                  'Loggedin User' => (isset($authUser->firstname) && isset($authUser->lastname)) ? ($authUser->firstname . ' ' . $authUser->lastname) : $authUser->name,
                  'Client Name' => $client_name,
                  'Error' => $account_data_err_message
                ]
              );
            } /* --end if DOWNLOAD URL ERROR -- */
            else if($downloadurl == null)
            {
              $account_data = "error";
              $account_data_err_message = "No files exists";
              $this->addLog($authUser, $type.'-load-error', 
                [
                  'Loggedin User' => (isset($authUser->firstname) && isset($authUser->lastname)) ? ($authUser->firstname . ' ' . $authUser->lastname) : $authUser->name,
                  'Client Name' => $client_name,
                  'Error' => $account_data_err_message//"No files exists"
                ]
              );
            } /* --end else DOWNLOAD URL NULL -- */
            else
            {
              $downloadurl['file_id'] = $file_id;
              $downloadurl['anyexcel_template_id'] = $controlfile->anyexcel_template_id;
              $downloadurls[$controlfileid][] = $downloadurl;
            } /* --end else DOWNLOAD URL NO ERROR -- */
          }          
        } /* --end for VAT/Import Reconciliation CONTROL FILES -- */
      
        if($downloadurls)
        {
          /* -- READ EXCEL FILE -- */
          $read_data = $anyExcelTemplateClass->CompareControlExcel($vatreg, $authUser, $downloadurls, true, $type); 
          /* --end READ EXCEL FILE -- */
        } 
       
      } /* --end if VAT RETURN/Import Reconciliation FILES -- */

      return $read_data;
    }

    public function is_associative_array($array) {
        // Check if the array is actually an array and not empty
        if (!is_array($array) || empty($array)) {
            return false;
        }
        // Check if the array has a string key or non-sequential keys
        return array_keys($array) !== range(0, count($array) - 1);
    }
    
    public function readImportReconciliationFile($url, $vat_reg_main_id, $o_filename, $extension = '.xml', $invoice_rows = [])
    {     
      $contents = (strpos($url, "https://") !== false) ? file_get_contents($url) : $url;        
      $storage_path = storage_path('app/public/');
      $filename = Str::random(10). $extension;
      
      Storage::disk('public')->put($filename, $contents);     

      if(strtolower($extension) == 'xml')
      {
        $xmlString = $contents;
     
        $xmlNamespaces = simplexml_load_string($xmlString)->getDocNamespaces(true);
        $namespaces = array_values(array_filter(array_keys($xmlNamespaces), function ($k) {
            return !empty($k);
        })); 
        $namespaces = array_map(function ($ns) {
            return "$ns:";
        }, $namespaces);    
          
        $xmlObject = simplexml_load_string(str_replace($namespaces, '', $xmlString)); 
        $rootName = $xmlObject->getName();
            
        $json = json_encode($xmlObject);
        $phpArray = json_decode($json, true); 
       
        $invoice_no = ''; 
        $invoice_date = ''; 
        $currency_code = ''; 
        $note = '';
        $vat_percentage = 0;

        $org_no = '';

        $country_orgin = '';
        $country_destination = '';
        $referenced_invoice = '';
        $referenced_invoice_date = '';

        $account_name = '';
        $vat_no = '';
       
        $client_street = '';
        $client_houseno = '';
        $client_city = '';
        $client_postcode = '';
        $client_countrycode = '';


        $creditline = false;
        $vat_percentage = 0;
        $vat_amount = 0;
        $net_amount = 0;
        //$invoices = [];

        foreach($phpArray as $key => $tagvalue)
        {    
          if(strtolower($key) == 'id') 
          {  
            if($invoice_no == '')
              $invoice_no = $tagvalue;
          } 
          else if(strtolower($key) == 'issuedate') 
          {  
            if($invoice_date == '')
              $invoice_date = $tagvalue;
          }    
          else if(strtolower($key) == 'note') 
          {  
            $note = $tagvalue;
            if(is_array($tagvalue))
              $note = $tagvalue[0];
            
            if(preg_match('/Org\. Nr\. (.*?) MVA/', $note, $matches))
              $org_no = str_replace(' ', '', $matches[1]);         
            else if(preg_match('/Org\. Nr\. (.*?) MVA\s*/', $note, $matches))  
              $org_no = str_replace(' ', '', $matches[1]); 
            else if(preg_match('/Org\.Nr\. (.*?) MVA/', $note, $matches))
                $org_no = str_replace(' ', '', $matches[1]);  
            else if(preg_match('/Org\.Nr\. (.*?) MVA\s*/', $note, $matches))   
                $org_no = str_replace(' ', '', $matches[1]);              
          }                  
          else if(strpos(strtolower($key), 'orderreference') !== false || strpos(strtolower($key), 'referencedorder') !== false) 
          {
            if($invoice_date == '')
            {
              if(isset($tagvalue['IssueDate']))
                $invoice_date = $tagvalue['IssueDate'];
            }
          }         
          else if(strpos(strtolower($key), 'currencycode') !== false) 
            $currency_code = $tagvalue;         
          else if(strpos(strtolower($key), 'taxtotal') !== false)  
          { 
            if(isset($tagvalue['CategoryTotal']))
            {
              $vat_percentage = $tagvalue['CategoryTotal']['RatePercentNumeric'];    

              if(isset($tagvalue['CategoryTotal']['TaxAmounts']))
              {                
                $vat_amount = $tagvalue['CategoryTotal']['TaxAmounts']['TaxAmount'];
                $net_amount = $tagvalue['CategoryTotal']['TaxAmounts']['TaxableAmount'];
              }             
            }//AUBO format
            else
            {
              if(isset($tagvalue['TaxSubtotal']))
              {
                if (isset($tagvalue['TaxSubtotal'])) {
                  if (is_array($tagvalue['TaxSubtotal'])) {
                    // Check if it's an associative array
                    if ($this->is_associative_array($tagvalue['TaxSubtotal'])) {
                      // It's a single associative array (single <item>)
                      $tagvalue['TaxSubtotal'] = [$tagvalue['TaxSubtotal']]; // Wrap it in an array
                    }
                  }
                }
                
                foreach($tagvalue['TaxSubtotal'] as $taxsubtotaltag)
                { 
                  if($vat_amount == 0)
                    $vat_amount = isset($taxsubtotaltag['TaxAmount']) ? $taxsubtotaltag['TaxAmount'] : 0;

                  if($net_amount == 0)
                    $net_amount = isset($taxsubtotaltag['TaxableAmount']) ? $taxsubtotaltag['TaxableAmount'] : 0;

                  if(isset($taxsubtotaltag['TaxCategory']))
                  {
                    if(isset($taxsubtotaltag['TaxCategory']['Percent']))
                    {
                      if($taxsubtotaltag['TaxCategory']['Percent'] != 0)
                      {
                        $vat_percentage = $taxsubtotaltag['TaxCategory']['Percent'];  

                      }
                    }
                  }
                }//loop tax                            
              }
            }//BECK format
          }          
          else if(strpos(strtolower($key), 'buyerparty') !== false || strpos(strtolower($key), 'customerparty') !== false) 
          {       
            $customer = $tagvalue; 
            if(strpos(strtolower($key), 'customer') !== false) 
            { 
              if(isset($tagvalue['Party']))
                $customer = $tagvalue['Party'];
            }

            foreach($customer as $customer_key => $customer)
            {
              if(strpos(strtolower($customer_key), 'name') !== false) 
                $account_name = $customer['Name'];   
              else if(strpos(strtolower($customer_key), 'address') !== false)
              { 
                if(isset($customer['Street']))
                  $client_street = $customer['Street'];
                else if(isset($customer['StreetName']))
                  $client_street = $customer['StreetName'];
             
                if(isset($customer['Street']))
                  $client_houseno = $customer['Street'];
                else if(isset($customer['AdditionalStreetName']))
                {
                  $client_houseno = $customer['AdditionalStreetName'];
                  if(empty($client_houseno))
                    $client_houseno = '';
                }
             
                if(isset($customer['CityName']))
                  $client_city = $customer['CityName'];
                
                if(isset($customer['PostalZone']))
                  $client_city = $customer['PostalZone'];
                
                if(isset($customer['Country']))
                {
                  if(isset($customer['Country']['Code']))
                    $client_countrycode = $customer['Country']['Code'];                
                  else if(isset($customer['Country']['IdentificationCode']))
                    $client_countrycode = $customer['Country']['IdentificationCode'];  
                }                
              }                          
            }
          }         
          else if(strpos(strtolower($key), 'creditnoteline') !== false)
          { 
            $creditline = true;           
          }
        } //for

        $invoice_rows[] = [
          'o_filename' => $o_filename,

          'note' => $note,
          'org_no' => $org_no,

          'invoice_no' => $invoice_no,
          'invoice_date' => $invoice_date,
          'invoice_document_status' => 'validated',
          'invoice_swiss_declaration_sub_type' => NULL,
          'invoice_country' => ($client_countrycode == 'NO') ? 'Norway' : $client_countrycode,
          'invoice_currency' => $currency_code,
          'invoice_net_amount' => $net_amount,
          'invoice_vat_amount' => $vat_amount,
          'invoice_total_amount' => $net_amount + $vat_amount,
          'invoice_shipping' => 0,
          'invoice_credit_note' => (strtolower($rootName) == 'creditnote') ? true : $creditline,
          'invoice_saved_at' => NULL,

          'account_name' => $account_name,
          'vat_no' => $vat_no,
          'client_street' => is_array($client_street) ? NULL : $client_street,
          'client_houseno' => is_array($client_houseno) ? NULL : $client_houseno,
          'client_city' => is_array($client_city) ? NULL : $client_city,
          'client_postcode' => is_array($client_postcode) ? NULL : $client_postcode,
          'client_countrycode' => is_array($client_countrycode) ? NULL : $client_countrycode
        ];        

        //match invoice no. under Import Reconciliation Sales invoice Table
        $client_id = VATRegistrationMain::where('id', $vat_reg_main_id)->value('client_id');
     
        if(strtolower($rootName) == 'creditnote')
        {
          $carbonDate = Carbon::createFromFormat('Y-m-d', $invoice_date);
          $matched_month_year = $carbonDate->format('m-Y');
          
          $client_vatregs = VATRegistration::with(['vatregmain'])->where('client_id', $client_id)->get();  

          $filtered_vatregs = $client_vatregs->filter(function($vatreg, $key) use ($invoice_date, $org_no, $carbonDate) {
            $frequency = $this->getFrequency($vatreg->general_periods);    
          
            if($org_no == '')
            {              
              return  (
                ($carbonDate->format('Ymd') >= Carbon::parse($vatreg->service_start)->format('Ymd')) && 
                ($carbonDate->format('Ymd') <= Carbon::parse($vatreg->service_start)->addMonth($frequency-1)->endOfMonth()->format('Ymd'))
                )
              ;
            }
            else
            {              
              return ($vatreg->vatregmain->org_no == $org_no) && (
                ($carbonDate->format('Ymd') >= Carbon::parse($vatreg->service_start)->format('Ymd')) && 
                ($carbonDate->format('Ymd') <= Carbon::parse($vatreg->service_start)->addMonth($frequency-1)->endOfMonth()->format('Ymd'))
                )
              ;
            }
          }); 

          if($filtered_vatregs)
            $matched_invoice_no = $filtered_vatregs->first();          
        }
        else
        {
          $matched_invoice_no = ImportReconciliationSalesInvoices::with(['vatreg', 'vatreg.client'])                  
                    ->whereHas('vatreg.client', function ($subquery) use($client_id) {                                        
                        $subquery->where('id', $client_id);
                    })
                    ->where('invoice_no', $invoice_no)
                    ->first();

          if(!$matched_invoice_no)
          {                        
            $carbonDate = Carbon::createFromFormat('Y-m-d', $invoice_date);
            $matched_month_year = $carbonDate->format('m-Y');
          
            $client_vatregs = VATRegistration::with(['vatregmain'])->where('client_id', $client_id)->get();  

            $filtered_vatregs = $client_vatregs->filter(function($vatreg, $key) use ($invoice_date, $org_no, $carbonDate) {
              $frequency = $this->getFrequency($vatreg->general_periods);    
            
              if($org_no == '')
              {                
                return (
                  ($carbonDate->format('Ymd') >= Carbon::parse($vatreg->service_start)->format('Ymd')) && 
                  ($carbonDate->format('Ymd') <= Carbon::parse($vatreg->service_start)->addMonth($frequency-1)->endOfMonth()->format('Ymd'))
                  )
                ;
              }
              else
              {                
                return ($vatreg->vatregmain->org_no == $org_no) && (
                  ($carbonDate->format('Ymd') >= Carbon::parse($vatreg->service_start)->format('Ymd')) && 
                  ($carbonDate->format('Ymd') <= Carbon::parse($vatreg->service_start)->addMonth($frequency-1)->endOfMonth()->format('Ymd'))
                  )
                ;
              }
            }); 

            if($filtered_vatregs)
            {
              $matched_invoice_no = $filtered_vatregs->first();

              if($matched_invoice_no)
                $rootName = 'noref';
            }
          }
        }

        if($matched_invoice_no)
        {
          $matched_vatregid = (strtolower($rootName) == 'creditnote' || strtolower($rootName) == 'noref') ? $matched_invoice_no->id : $matched_invoice_no->vat_reg_id;
          
          if($matched_invoice_no->invoice_date)
            $carbonDate = Carbon::createFromFormat('Y-m-d', $matched_invoice_no->invoice_date);
          else if($matched_invoice_no->service_start)
            $carbonDate = Carbon::createFromFormat('Y-m-d', $matched_invoice_no->service_start);
          $matched_month_year = (strtolower($rootName) == 'creditnote' || strtolower($rootName) == 'noref') ? $matched_month_year : $carbonDate->format('m-Y');

          $matched_vatreg = $this->getSpecificVatRegQuery($matched_vatregid);          
        }
        //end match invoice no. under Import Reconciliation Sales invoice Table

        Storage::disk('public')->delete($filename);        

        return [                   
          'invoice_rows' => $invoice_rows,
          'invoice_no' => $invoice_no,
          
          'month_year' => isset($matched_month_year) ? $matched_month_year : null,
          'matched_vatreg' => isset($matched_vatreg) ? $matched_vatreg : []
        ];
      }//xml
    }
    
    public function generateSalesInvoicePdfFromXml($downloadfile, $test = false)
    { 
      try 
      {
        //Generate PDF from XML
        if($test)
        {         
          $url = base_path('public/3096541.xml');   
          $contents = file_get_contents($url);
        }
        else
        {
          $url = $downloadfile['download_url'];
          $contents = (strpos($url, "https://") !== false) ? file_get_contents($url) : $downloadfile['file'];     
        }        

        if(strtolower($downloadfile['file_extension']) == 'xml')
        {
          $xmlString = $contents;

          $xmlNamespaces = simplexml_load_string($xmlString)->getDocNamespaces(true);
          $namespaces = array_values(array_filter(array_keys($xmlNamespaces), function ($k) {
            return !empty($k);
          })); 
          $namespaces = array_map(function ($ns) {
            return "$ns:";
          }, $namespaces);    

          $xmlObject = simplexml_load_string(str_replace($namespaces, '', $xmlString)); 
         
          $credit_note = false; 

          $invoice_no = ''; 
          $footer_note = '';
          $currency_code = ''; 
          $invoice_date = ''; 
          $order_no = '';
          
          $sender_website = '';
          $sender_endpoint = '';
          $sender_name = '';
          $sender_vat_no = '';         
          $sender_street = '';
          $sender_houseno = '';
          $sender_city = '';
          $sender_postcode = '';
          $sender_countrycode = '';
          $sender_email = '';
          $sender_contact_id = '';
          $sender_contact_name = '';
          $sender_contact_telephone = '';
          $sender_contact_email = '';

          $buyer_website = '';
          $buyer_endpoint = '';
          $buyer_name = '';
          $buyer_vat_no = '';         
          $buyer_street = '';
          $buyer_houseno = '';
          $buyer_city = '';
          $buyer_postcode = '';
          $buyer_countrycode = '';
          $buyer_email = '';
          $buyer_contact_id = '';
          $buyer_contact_name = '';
          $buyer_contact_telephone = '';
          $buyer_contact_email = '';

          $delivery_date = '';
          $delivery_street = '';
          $delivery_houseno = '';
          $delivery_city = '';
          $delivery_postcode = '';
          $delivery_countrycode = '';
          
          $payment_id = '';
          $payment_branch_id = '';
          $payment_due_date = '';
          $payment_channel_code = '';          
          $payment_institute_name = '';
         
          $payment_type_id = '';
          $payment_note = '';
          $payment_discount_percent = '';
          $payment_amount = '';
          $payment_currencycode = '';
          $payment_settlement_date = '';
          $payment_penalty_date = '';

          $allowance_charge = '';
          $allowance_charge_currencycode = '';
         
          $tax_amount = '';
          $tax_currencycode = ''; 
          $net_amount = '';
          $net_currencycode = '';
          $tax_percent = '';
          $tax_name = '';
        
          $line_amount = '';
          $line_currencycode = '';
          $tax_excl_amount = '';
          $tax_excl_currencycode = '';
          $tax_incl_amount = '';
          $tax_incl_currencycode = '';
          $payable_amount = '';
          $payable_currencycode = '';
          
          $invoices = [];          
          foreach($xmlObject as $xmlTag => $xmlTagValue)
          {            
            if(strtolower($xmlTag) == 'id') 
            {
              if($invoice_no == '')
                $invoice_no = (string)$xmlTagValue;
            }//INVOICE NO.
            else if(strpos(strtolower($xmlTag), 'note') !== false && (strtolower($xmlTag) != 'creditnoteline')) 
            {              
              if($footer_note == '')
                $footer_note = (string)$xmlTagValue;
            }//FOOTER NOTE
            else if(strpos(strtolower($xmlTag), 'currencycode') !== false) 
            {
              if($currency_code == '')
                $currency_code = (string)$xmlTagValue;
            }//CURRENCY CODE
            else if(strpos(strtolower($xmlTag), 'orderreference') !== false || strpos(strtolower($xmlTag), 'referencedorder') !== false) 
            {
              if(isset($xmlTagValue->IssueDate))
                $invoice_date = (string)$xmlTagValue->IssueDate;

              if(isset($xmlTagValue->SalesOrderID))
                $order_no = (string)$xmlTagValue->SalesOrderID;
              else if(isset($xmlTagValue->SellersOrderID))
                $order_no = (string)$xmlTagValue->SellersOrderID;
            }//INVOICE DATE & SALES ORDER NO.
            else if(strpos(strtolower($xmlTag), 'supplierparty') !== false || strpos(strtolower($xmlTag), 'sellerparty') !== false) 
            {
              $sender = $xmlTagValue;
              if(isset($sender->Party))
                $sender = $sender->Party;
            
              if(isset($sender->WebsiteURI))
                $sender_website = (string)$sender->WebsiteURI;
              
              if(isset($sender->PartyIdentification))
                $sender_endpoint = (string)$sender->PartyIdentification->ID . ' (' . (string)$sender->PartyIdentification->ID['schemeID'] . ', ' .  __('EndpointID', [], 'dk') . ')';              
              else if(isset($sender->ID))
                  $sender_endpoint = (string)$sender->ID . ' (' . (string)$sender->ID['schemeID'] . ', ' .  __('EndpointID', [], 'dk') . ')';
              
              if(isset($sender->PartyName))
                $sender_name = (string)$sender->PartyName->Name;

              if(isset($sender->PostalAddress))
              {
                $sender_street = (string)$sender->PostalAddress->StreetName;
                if(isset($sender->PostalAddress->AdditionalStreetName))
                  $sender_houseno = (string)$sender->PostalAddress->AdditionalStreetName;
                $sender_city = (string)$sender->PostalAddress->CityName;
                $sender_postcode = (string)$sender->PostalAddress->PostalZone;
                $sender_email = (string)$sender->PostalAddress->InhouseMail;
                if(isset($sender->PostalAddress->Country))
                  $sender_countrycode = (string)$sender->PostalAddress->Country->IdentificationCode;
              }
              else if(isset($sender->Address))
              {
                $sender_street = (string)$sender->Address->Street;
                if(isset($sender->Address->AdditionalStreet))
                  $sender_houseno = (string)$sender->Address->AdditionalStreet;
                $sender_city = (string)$sender->Address->CityName;
                $sender_postcode = (string)$sender->Address->PostalZone;
                $sender_email = (string)$sender->Address->InhouseMail;
                if(isset($sender->Address->Country))
                  $sender_countrycode = (string)$sender->Address->Country->Code;
              }

              if(isset($sender->PartyTaxScheme))
              {
                if(isset($sender->PartyTaxScheme->CompanyID))                
                  $sender_vat_no = (string)$sender->PartyTaxScheme->CompanyID . ' (' . (string)$sender->PartyTaxScheme->CompanyID['schemeID'] . ', ' .  (string)$sender->PartyTaxScheme->TaxScheme->Name . ')';                
                else if(isset($sender->PartyTaxScheme->CompanyTaxID))                 
                  $sender_vat_no = (string)$sender->PartyTaxScheme->CompanyTaxID  . ' (' . (string)$sender->PartyTaxScheme->CompanyTaxID ['schemeID'] . ')';               
              }

              if(isset($sender->Contact))
              {
                $sender_contact_id = (string)$sender->Contact->ID;
                $sender_contact_name = (string)$sender->Contact->Name;
                $sender_contact_telephone = (string)$sender->Contact->Telephone;
                $sender_contact_email = (string)$sender->Contact->ElectronicMail;
              }
              else if(isset($sender->SellerContact))
              {
                $sender_contact_id = (string)$sender->SellerContact->ID;
                $sender_contact_name = (string)$sender->SellerContact->Name;
                $sender_contact_telephone = (string)$sender->SellerContact->Telephone;
                $sender_contact_email = (string)$sender->SellerContact->ElectronicMail;
              }             
            }//SENDER
            else if(strpos(strtolower($xmlTag), 'buyerparty') !== false || strpos(strtolower($xmlTag), 'customerparty') !== false) 
            {
              $buyer = $xmlTagValue;
              if(isset($buyer->Party))
                $buyer = $buyer->Party;
            
              if(isset($buyer->WebsiteURI))
                $buyer_website = (string)$buyer->WebsiteURI;
              
              if(isset($buyer->PartyIdentification))
                $buyer_endpoint = (string)$buyer->PartyIdentification->ID . ' (' . (string)$buyer->PartyIdentification->ID['schemeID'] . ', ' .  __('EndpointID', [], 'dk') . ')';              
              else if(isset($buyer->ID))
                  $buyer_endpoint = (string)$buyer->ID . ' (' . (string)$buyer->ID['schemeID'] . ', ' .  __('EndpointID', [], 'dk') . ')';

              if(isset($buyer->PartyName))
                $buyer_name = (string)$buyer->PartyName->Name;

              if(isset($buyer->PostalAddress))
              {
                $buyer_street = (string)$buyer->PostalAddress->StreetName;
                if(isset($buyer->PostalAddress->AdditionalStreetName))
                  $buyer_houseno = (string)$buyer->PostalAddress->AdditionalStreetName;
                $buyer_city = (string)$buyer->PostalAddress->CityName;
                $buyer_postcode = (string)$buyer->PostalAddress->PostalZone;
                $buyer_email = (string)$buyer->PostalAddress->InhouseMail;
                if(isset($buyer->PostalAddress->Country))
                  $buyer_countrycode = (string)$buyer->PostalAddress->Country->IdentificationCode;
              }
              else if(isset($buyer->Address))
              {
                $buyer_street = (string)$buyer->Address->Street;
                if(isset($buyer->Address->AdditionalStreet))
                  $buyer_houseno = (string)$buyer->Address->AdditionalStreet;
                $buyer_city = (string)$buyer->Address->CityName;
                $buyer_postcode = (string)$buyer->Address->PostalZone;
                $buyer_email = (string)$buyer->Address->InhouseMail;
                if(isset($buyer->Address->Country))
                  $buyer_countrycode = (string)$buyer->Address->Country->Code;
              }

              if(isset($buyer->PartyTaxScheme))              
                $buyer_vat_no = (string)$buyer->PartyTaxScheme->CompanyID . ' (' . (string)$buyer->PartyTaxScheme->CompanyID['schemeID'] . ', ' .  (string)$buyer->PartyTaxScheme->TaxScheme->Name . ')';
              
              if(isset($buyer->Contact))
              {
                $buyer_contact_id = (string)$buyer->Contact->ID;
                $buyer_contact_name = (string)$buyer->Contact->Name;
                $buyer_contact_telephone = (string)$buyer->Contact->Telephone;
                $buyer_contact_email = (string)$buyer->Contact->ElectronicMail;
              }
              else if(isset($buyer->BuyerContact))
              {
                $buyer_contact_id = (string)$buyer->BuyerContact->ID;
                $buyer_contact_name = (string)$buyer->BuyerContact->Name;
                $buyer_contact_telephone = (string)$buyer->BuyerContact->Telephone;
                $buyer_contact_email = (string)$buyer->BuyerContact->ElectronicMail;
              }
            }//BUYER
            else if(strpos(strtolower($xmlTag), 'delivery') !== false) 
            {
              if(isset($xmlTagValue->ActualDeliveryDate))
                $delivery_date = (string)$xmlTagValue->ActualDeliveryDate;

              if(isset($xmlTagValue->DeliveryLocation))
              {
                if(isset($xmlTagValue->DeliveryLocation->Address))
                {
                  $delivery_street = (string)$xmlTagValue->DeliveryLocation->Address->StreetName;
                  if(isset($xmlTagValue->DeliveryLocation->Address->AdditionalStreetName))
                    $delivery_houseno = (string)$xmlTagValue->DeliveryLocation->Address->AdditionalStreetName;
                  $delivery_city = (string)$xmlTagValue->DeliveryLocation->Address->CityName;
                  $delivery_postcode = (string)$xmlTagValue->DeliveryLocation->Address->PostalZone;
                  if(isset($xmlTagValue->DeliveryLocation->Address->Country))
                    $delivery_countrycode = (string)$xmlTagValue->DeliveryLocation->Address->Country->IdentificationCode;
                }
              }
            } //DELIVERY
            else if(strpos(strtolower($xmlTag), 'paymentmeans') !== false) 
            {
              if(isset($xmlTagValue->PaymentDueDate))
                $payment_due_date = (string)$xmlTagValue->PaymentDueDate;

              if(isset($xmlTagValue->PaymentChannelCode))
                $payment_channel_code = (string)$xmlTagValue->PaymentChannelCode;

              if(isset($xmlTagValue->PayeeFinancialAccount))
              {
                $payment_id = (string)$xmlTagValue->PayeeFinancialAccount->ID;

                if(isset($xmlTagValue->PayeeFinancialAccount->FinancialInstitutionBranch))
                  $payment_branch_id = (string)$xmlTagValue->PayeeFinancialAccount->FinancialInstitutionBranch->ID;
                else if(isset($xmlTagValue->PayeeFinancialAccount->FiBranch))
                  $payment_branch_id = (string)$xmlTagValue->PayeeFinancialAccount->FiBranch->ID;
              }

              if(isset($xmlTagValue->PayeeFinancialAccount->FinancialInstitutionBranch->FinancialInstitution))
                $payment_institute_name = (string)$xmlTagValue->PayeeFinancialAccount->FinancialInstitutionBranch->FinancialInstitution->Name;
              else if(isset($xmlTagValue->PayeeFinancialAccount->FiBranch->FinancialInstitution))
                $payment_institute_name = (string)$xmlTagValue->PayeeFinancialAccount->FiBranch->FinancialInstitution->Name;
            } //PAYMENT MEANS
            else if(strpos(strtolower($xmlTag), 'paymentterms') !== false) 
            {
              if(isset($xmlTagValue->PaymentMeansID))
                $payment_type_id = (string)$xmlTagValue->PaymentMeansID;

              if(isset($xmlTagValue->Note))
                $payment_note = (string)$xmlTagValue->Note;

              if(isset($xmlTagValue->SettlementDiscountPercent))
                $payment_discount_percent = (string)$xmlTagValue->SettlementDiscountPercent;
              else if(isset($xmlTagValue->SettlementDiscountRateNumeric))
                $payment_discount_percent = (string)$xmlTagValue->SettlementDiscountRateNumeric;

              if(isset($xmlTagValue->Amount))
              {
                $payment_amount = (string)$xmlTagValue->Amount;
                $payment_currencycode = (string)$xmlTagValue->Amount['currencyID'];
              }
              else if(isset($xmlTagValue->RateAmount))
              {
                $payment_amount = (string)$xmlTagValue->RateAmount;
                $payment_currencycode = (string)$xmlTagValue->RateAmount['currencyID'];
              }

              if(isset($xmlTagValue->SettlementPeriod))
              {
                if(isset($xmlTagValue->SettlementPeriod->EndDate))
                  $payment_settlement_date = (string)$xmlTagValue->SettlementPeriod->EndDate;
                else if(isset($xmlTagValue->SettlementPeriod->EndDateTimeDate))
                  $payment_settlement_date = (string)$xmlTagValue->SettlementPeriod->EndDateTimeDate;
              }

              if(isset($xmlTagValue->PenaltyPeriod))
              {
                if(isset($xmlTagValue->PenaltyPeriod->StartDate))
                  $payment_penalty_date = (string)$xmlTagValue->PenaltyPeriod->StartDate;              
                else if(isset($xmlTagValue->PenaltyPeriod->StartDateTime))
                  $payment_penalty_date = (string)$xmlTagValue->PenaltyPeriod->StartDateTime;              
              }
            } //PAYMENT TERMS
            else if(strpos(strtolower($xmlTag), 'allowancecharge') !== false) 
            {              
              if(isset($xmlTagValue->AllowanceChargeReason))
              {
                if(strpos(strtolower($xmlTagValue->AllowanceChargeReason), 'rabat') !== false) 
                {                  
                  $allowance_charge = (string)$xmlTagValue->Amount;
                  $allowance_charge_currencycode = (string)$xmlTagValue->Amount['currencyID'];
                }
              }
            } // ALLOWANCE CHARGES
            else if(strpos(strtolower($xmlTag), 'taxtotal') !== false) 
            {
              if(isset($xmlTagValue->TaxAmount))
              {
                $tax_amount = (string)$xmlTagValue->TaxAmount;
                $tax_currencycode = (string)$xmlTagValue->TaxAmount['currencyID'];
              }
              else if(isset($xmlTagValue->TaxAmounts))
              {
                $tax_amount = (string)$xmlTagValue->TaxAmounts->TaxAmount;
                $tax_currencycode = (string)$xmlTagValue->TaxAmounts->TaxAmount['currencyID'];
              }

              if(isset($xmlTagValue->TaxSubtotal))
              {
                $net_amount = (string)$xmlTagValue->TaxSubtotal->TaxableAmount;
                $net_currencycode = (string)$xmlTagValue->TaxAmount->TaxableAmount['currencyID'];

                if(isset($xmlTagValue->TaxSubtotal->TaxCategory))
                {
                  $tax_percent = (string)$xmlTagValue->TaxSubtotal->TaxCategory->Percent;
                  $tax_name = (string)$xmlTagValue->TaxSubtotal->TaxCategory->TaxScheme->Name;
                }
              }    
              else if(isset($xmlTagValue->CategoryTotal))
              {
                if(isset($xmlTagValue->CategoryTotal->TaxAmounts))
                {
                  $net_amount = (string)$xmlTagValue->CategoryTotal->TaxAmounts->TaxableAmount;
                  $net_currencycode = (string)$xmlTagValue->CategoryTotal->TaxAmounts->TaxableAmount['currencyID'];
                }  
                
                $tax_percent = (string)$xmlTagValue->CategoryTotal->RatePercentNumeric;
                $tax_name = (string)$xmlTagValue->CategoryTotal->RateCategoryCodeID;
                
              }             
            } //TAX TOTAL
            else if(strpos(strtolower($xmlTag), 'monetarytotal') !== false || strpos(strtolower($xmlTag), 'legaltotal') !== false) 
            {
              if(isset($xmlTagValue->LineExtensionAmount))
              {
                $line_amount = (string)$xmlTagValue->LineExtensionAmount;
                $line_currencycode = (string)$xmlTagValue->LineExtensionAmount['currencyID'];
              }
              else if(isset($xmlTagValue->LineExtensionTotalAmount))
              {
                $line_amount = (string)$xmlTagValue->LineExtensionTotalAmount;
                $line_currencycode = (string)$xmlTagValue->LineExtensionTotalAmount['currencyID'];
              }

              if(isset($xmlTagValue->TaxExclusiveAmount))
              {
                $tax_excl_amount = (string)$xmlTagValue->TaxExclusiveAmount;
                $tax_excl_currencycode = (string)$xmlTagValue->TaxExclusiveAmount['currencyID'];
              } 
              else
              {
                $tax_excl_amount = (string)$xmlTagValue->LineExtensionTotalAmount;
                $tax_excl_currencycode = (string)$xmlTagValue->LineExtensionTotalAmount['currencyID'];
              }

              if(isset($xmlTagValue->TaxInclusiveAmount))
              {
                $tax_incl_amount = (string)$xmlTagValue->TaxInclusiveAmount;
                $tax_incl_currencycode = (string)$xmlTagValue->TaxInclusiveAmount['currencyID'];
              } 
              else
              {
                $tax_incl_amount = (string)$xmlTagValue->ToBePaidTotalAmount;
                $tax_incl_currencycode = (string)$xmlTagValue->ToBePaidTotalAmount['currencyID'];
              }

              if(isset($xmlTagValue->PayableAmount))
              {
                $payable_amount = (string)$xmlTagValue->PayableAmount;
                $payable_currencycode = (string)$xmlTagValue->PayableAmount['currencyID'];
              }  
              else if(isset($xmlTagValue->ToBePaidTotalAmount))
              {
                $payable_amount = (string)$xmlTagValue->ToBePaidTotalAmount;
                $payable_currencycode = (string)$xmlTagValue->ToBePaidTotalAmount['currencyID'];
              }           
            } //MONETARY TOTAL
            else if(strpos(strtolower($xmlTag), 'invoiceline') !== false || strpos(strtolower($xmlTag), 'creditnoteline') !== false) 
            { 
              if(strpos(strtolower($xmlTag), 'creditnoteline') !== false) 
                $credit_note = true; 

              $item_name = '';
              if(isset($xmlTagValue->Item->Name))
                $item_name = (string)$xmlTagValue->Item->Name;
              else if(isset($xmlTagValue->Item->ID))
                $item_name = (string)$xmlTagValue->Item->ID;

              $price = '';
              $base_qty = '';
              if(isset($xmlTagValue->Price))
              {
                if(isset($xmlTagValue->Price->PriceAmount))
                  $price = (string)$xmlTagValue->Price->PriceAmount;

                if(isset($xmlTagValue->Price->BaseQuantity))
                  $base_qty = (string)$xmlTagValue->Price->BaseQuantity;
              }
              else if(isset($xmlTagValue->BasePrice))
              {
                if(isset($xmlTagValue->BasePrice->PriceAmount))
                  $price = (string)$xmlTagValue->BasePrice->PriceAmount;

                if(isset($xmlTagValue->BasePrice->BaseQuantity))
                  $base_qty = (string)$xmlTagValue->BasePrice->BaseQuantity;    
              }

              $invoices[] = [
                'no' => isset($xmlTagValue->ID) ? ((string)$xmlTagValue->ID) : '',
                'qty' => isset($xmlTagValue->InvoicedQuantity) ? ((string)$xmlTagValue->InvoicedQuantity) : '',
                'unit_code' => isset($xmlTagValue->InvoicedQuantity['unitCode']) ? ((string)$xmlTagValue->InvoicedQuantity['unitCode']) : '',
                'line_amount' => isset($xmlTagValue->LineExtensionAmount) ? ((string)$xmlTagValue->LineExtensionAmount) : '',
                'accounting_cost' => isset($xmlTagValue->AccountingCost) ? ((string)$xmlTagValue->AccountingCost) : '',
                'order_no' => isset($xmlTagValue->OrderLineReference->OrderReference->ID) ? ((string)$xmlTagValue->OrderLineReference->OrderReference->ID) : '',

                'tax_amount' => isset($xmlTagValue->TaxTotal->TaxSubtotal->TaxAmount) ? ((string)$xmlTagValue->TaxTotal->TaxSubtotal->TaxAmount) : '',
                'net_amount' => isset($xmlTagValue->TaxTotal->TaxSubtotal->TaxableAmount) ? ((string)$xmlTagValue->TaxTotal->TaxSubtotal->TaxableAmount) : '',
                'tax_percent' => isset($xmlTagValue->TaxTotal->TaxSubtotal->TaxCategory->Percent) ? ((string)$xmlTagValue->TaxTotal->TaxSubtotal->TaxCategory->Percent) : '',
                'tax_name' => isset($xmlTagValue->TaxTotal->TaxSubtotal->TaxCategory->TaxScheme->Name) ? ((string)$xmlTagValue->TaxTotal->TaxSubtotal->TaxCategory->TaxScheme->Name) : '',

                'item_name' => $item_name,
                'item_desc' => isset($xmlTagValue->Item->Description) ? ((string)$xmlTagValue->Item->Description) : '',
                'seller_item_id' => isset($xmlTagValue->Item->SellersItemIdentification->ID) ? ((string)$xmlTagValue->Item->SellersItemIdentification->ID) : '',
                'seller_item_schema' => isset($xmlTagValue->Item->SellersItemIdentification->ID) ? ((string)$xmlTagValue->Item->SellersItemIdentification->ID['schemeID']) : '',
                'std_item_id' => isset($xmlTagValue->Item->StandardItemIdentification->ID) ? ((string)$xmlTagValue->Item->StandardItemIdentification->ID) : '',
                'std_item_schema' => isset($xmlTagValue->Item->StandardItemIdentification->ID) ? ((string)$xmlTagValue->Item->StandardItemIdentification->ID['schemeID']) : '',

                'price' => $price,
                'base_qty' => ($base_qty) ? $base_qty : '1'
              ];
            } //INVOICE LINE - CREDIT NOTE LINE          
          } //for
          
          $sales_invoice_xml_content = [
            'credit_note' => $credit_note,
           
            'invoice_no' => $invoice_no,
            'footer_note' => $footer_note,
            'currency_code' => $currency_code,

            'invoice_date' => $invoice_date, 
            'order_no' => $order_no,

            'sender' => [
                'website' => $sender_website,
                'endpoint' => $sender_endpoint,
                'name' => $sender_name,
                'street' => $sender_street,
                'houseno' => $sender_houseno,
                'city' => $sender_city,
                'postcode' => $sender_postcode,
                'email' => $sender_email,
                'countrycode' => $sender_countrycode,
                'vat_no' => $sender_vat_no,
                'contact' => [
                  'id' => $sender_contact_id,
                  'name' => $sender_contact_name,
                  'telephone' => $sender_contact_telephone,
                  'email' => $sender_contact_email
                ],
              ],

              'buyer' => [
                'website' => $buyer_website,
                'endpoint' => $buyer_endpoint,
                'name' => $buyer_name,
                'street' => $buyer_street,
                'houseno' => $buyer_houseno,
                'city' => $buyer_city,
                'postcode' => $buyer_postcode,
                'email' => $buyer_email,
                'countrycode' => $buyer_countrycode,
                'vat_no' => $buyer_vat_no,
                'contact' => [
                  'id' => $buyer_contact_id,
                  'name' => $buyer_contact_name,
                  'telephone' => $buyer_contact_telephone,
                  'email' => $buyer_contact_email
                ]
              ],

              'delivery' => [
                'date' => $delivery_date,               
                'street' => $delivery_street,
                'houseno' => $delivery_houseno,
                'city' => $delivery_city,
                'postcode' => $delivery_postcode,                
                'countrycode' => $delivery_countrycode                
              ],

              'payment_means' => [
                'id' => $payment_id,
                'branch_id' => $payment_branch_id,
                'due_date' => $payment_due_date,               
                'channel_code' => $payment_channel_code,                
                'institute_name' => $payment_institute_name,
                
                'type_id' => $payment_type_id,
                'note' => $payment_note,
                'discount_percent' => $payment_discount_percent,
                'amount' => $payment_amount,               
                'currencycode' => $payment_currencycode,                
                'settlement_date' => $payment_settlement_date,
                'penalty_date' => $payment_penalty_date
              ],

              'allowance_charge' => $allowance_charge, 
              'allowance_charge_currencycode' => $allowance_charge_currencycode, 

              'tax_total' => [
                'amount' => $tax_amount,
                'tax_currencycode' => $tax_currencycode,
                'net_amount' => $net_amount,               
                'net_currencycode' => $net_currencycode,                
                'percent' => $tax_percent,
                'name' => $tax_name
              ],          

              'monetary_total' => [
                'line_amount' => $line_amount,
                'line_currencycode' => $line_currencycode,
                'tax_excl_amount' => $tax_excl_amount,               
                'tax_excl_currencycode' => $tax_excl_currencycode,                
                'tax_incl_amount' => $tax_incl_amount,
                'tax_incl_currencycode' => $tax_incl_currencycode,
                'payable_amount' => $payable_amount,
                'payable_currencycode' => $payable_currencycode
              ],
          
              'invoices' => $invoices
          ];

          return $sales_invoice_xml_content;         
        }//xml            
      }//try
      catch (Exception $e) {
        return  $e->getMessage();
      }//catch
    }

    public function insertImportReconciliationInvoices($result, $vatregs, $authUser, $from, $vat_reg_id = NULL)
    {   
      $chunks = array_chunk($result, 100); // Divide your data array into chunks     

      // Create a batch and add jobs to it.
      $jobs = [];
      foreach ($chunks as $chunk) {
        $jobs[] = new InsertComSalesInvoices($chunk, $vatregs, $authUser, $from);
      }

      // // Dispatch all jobs in the batch.    
      $batch = Bus::batch($jobs)
        ->then(function ($batch) {
            // All jobs were successful
          //\Log::info('All jobs completed successfully.');
        })
        ->catch(function ($batch, $e) {
            // Some jobs failed
          //\Log::error('Some jobs failed: ' . $e->getMessage());
        })
        ->finally(function ($batch) use($jobs) {
          //\Log::info('Batch finished. Finally callback triggered.', ['batch' => $batch]);         
            // $totalJobs = count($jobs);
            // $batchId = $batch->id;
            // // The batch is complete
            // foreach ($jobs as $key => $job) {
            //   $progress = (($key + 1) / $totalJobs) * 100;
            //     // You can update the status of each job here if needed
            //   event(new ImportReconciliationComSalesInvoicesJobProgressEvent($batchId, $progress));
            // }
        })
        ->dispatch();

      // Get the batch ID
      $batchId = $batch->id;    

      return $batchId;      
    }

    public function OrgNoForOcr()
    {      
      $org_no = [
        '928729605', //Adag
        '932337274', //Aid
        '831160462', //Almuegaarden
        '988440965', //Aubo
        '377642755', //Beck - GB
        '928996212', //Beck - NO
        '983799620', //Berend        
        '292640361', //Berg - CH
        '379603560', //Berg - GB
        '934286723', //Berg - NO
        '981353986', //Bessie
        '916483988', //Bianco
        '977545455', //Calida
        '922905886', //Committee
        '391411117', //Committee - GB
        '933137740', //Dan
        '887858152', //DFI
        '923791957', //Einhell
        '986211195', //Engel

        '913538366', //Guardian
        '994268341', //Halo
        '917406138', //Hjort
        '992659823', //Horn
        '136731107', //Kite - CH
        '917413452', //Kite - NO
        '369530275', //Kite - UK
        '932155141', //Lost
        '913077679', //Lyng Rainwear
        '925000353', //Millarco
        '923791957', //Nordic
        '915704573', //Noscomed
        '158341364', //Our Units - CH
        '819662452', //Our Units - NO
        '913873572', //Qnuz
        '995167352', //Rexholm
        '980827682', //Rieker
        '332375380', //Samsøe
        '914821924', //Sebra
        '454158271', //Second female - CH
        '914733057', //Second female - NO
        '997015606', //SGI
        '91644842', //Sindico
        '912676331', //Sports - NO
        '980188744', //Stof
        '814079112', //Tannermedico
        '915527167', //Vernon
        '913597877', //Villy
        '235759090' //Woden
      ];

      return $org_no;
    }

    public function loadImportReconciliationDatasFromAzureDb($authUser, $vatreg, $from = 'azure', $full_refresh = false, $invoice_name = null, $invoice_no = null)
    {
      try
      {              
        $apiClass = new ApiClass();     

        $client_id = $vatreg->client_id;
        $vat_reg_id = $vatreg->id;

        $vatregmain = $vatreg->vatregmain; 
        $vat_reg_main_id = $vatreg->vat_reg_main_id;

        if($vatregmain->country == 'NO')
          $org_no = $vatregmain->org_no;        
        else
          $org_no = str_replace(['.', '-'], '', $vatregmain->vat_no);
                
        $check_org_no = $org_no ? preg_replace('/\D/', '', $org_no) : '';

        
        $omit_org_no = $this->OrgNoForOcr();
        if ($check_org_no && in_array($check_org_no, $omit_org_no))
        {
          //dd($org_no, $check_org_no, "omit");
          //return "ocr";

          //sync from OCR extraction
          $from = str_replace('global', 'ocr', $from);

          $insert_invoices = 0;
          //$insert_invoices = $this->loadImportReconciliationDatasFromOcr($authUser, $vatreg, $from, $full_refresh);
          $insert_invoices = $this->loadImportReconciliationDatasFromOcr($authUser, $vatreg, $from, $full_refresh, $invoice_name, $invoice_no);

          return $insert_invoices;
          // if($full_refresh && $from == 'ocr-search-refresh')
          // {
          //   if(count($insert_invoices['result']) > 0)
          //     return $insert_invoices;
          //   else  
          //     return $insert_invoices['insert_invoices'];  
          // }
          // else  
          //   return $insert_invoices;    

          // return 0;      
        } //OCR
        else
        {
          $service_start = $vatreg->service_start;
          $end_date = $apiClass->getEndDateLazy($vatreg);          

          $_fetch_new_data = "";
          $_specific_invoice_data = "";
          if(!$full_refresh)
          {               
            if($invoice_name)
            {
              if($invoice_name == 'com')            
                $_specific_invoice_data = " AND (S.Field17 = '". $invoice_no ."') ";
              else if($invoice_name == 'sales')
                $_specific_invoice_data = " AND (S1.Field5 = '". $invoice_no ."' OR M.Field15 = '". $invoice_no ."') ";
            }
            else
            {
              $importreconciliationcominvoices = $vatreg->importreconciliationcominvoices; 
              if($importreconciliationcominvoices)
              {
                if(count($importreconciliationcominvoices) > 0)
                {                           
                  $com_invoice_last_modified_at = Carbon::parse($importreconciliationcominvoices->first()->last_modified_at)->format('Y-m-d'); 

                  if($com_invoice_last_modified_at)
                    $_fetch_new_data = " AND (S.Field30 > '". $com_invoice_last_modified_at ."' OR S1.Field30 > '". $com_invoice_last_modified_at ."') "; 
                  else
                    $_fetch_new_data = " AND (S.Field30 IS NOT NULL OR S1.Field30 IS NOT NULL) ";        
                }
              }
            } 
          }

          if($_specific_invoice_data)        
            $vatregs = $vatreg;       
          else
          {
            //GET All VATreg. based on vat_reg_main_id
            $_with = ['client'];
            $_where = [
              'vat_reg_main_id' => ['operator' => '=', 'value' => $vat_reg_main_id]
            ];
            $_whereHas = [];      
            $_orderBy = [
              'id' => 'DESC'
            ];  
            $_final = 'get';        
            $vatregs = $this->getLazy('vatreg', $_with, $_where, $_whereHas, $_orderBy, $_final); 
            //GET All VATreg. based on vat_reg_main_id  
          }  

          $query = "SELECT " .
                    
                    "S.Field20 AS client_name, " . 
                    "S.Field27 AS client_number, " .
                  
                    "S.Field17 AS commercial_invoice_no, " . 
                    "S.Field18 AS commercial_invoice_date, " .                  

                    "S.Field8 AS document_status, " . 
                    "S.Field10 AS swiss_declaration_sub_type, " . 
                    "S.Field13 AS country, " . 

                    "S.Field7 AS currency, " .
                    "S.Field25 AS net_amount, " . 
                    "S.Field12 AS vat_amount, " . 
                    "S.Field29 AS total_amount, " .                
                    "S.Field3 AS shipping, " . 
                    "S.Field4 AS saved_at, " .
                    
                    "M.DocID AS doc_id," .
                    "M.Field15 AS relation_match_no, " .
                    "M.Field1 AS relation_declaration_match_no, " .
                   
                    "S1.Field5 AS invoice_no, " .
                    "S1.Field18 AS invoice_date, " .
                    "S1.Field8 AS invoice_document_status, " . 
                    "S1.Field10 AS invoice_swiss_declaration_sub_type, " . 
                    "S1.Field13 AS invoice_country, " . 

                    "S1.Field7 AS invoice_currency, " . 
                    "S1.Field25 AS invoice_net_amount, " . 
                    "S1.Field12 AS invoice_vat_amount, " . 
                    "S1.Field29 AS invoice_total_amount, " .
                    "S1.Field26 AS invoice_credit_note, " .                                     
                    "S1.Field3 AS invoice_shipping, " .
                    "S1.Field14 AS invoice_variance, " .
                    "S1.Field6 AS invoice_saved_at, " .

                    "S.Field30 AS last_modified_at " .

                    "FROM ssFields S " .
                    "LEFT JOIN ssMVFields M ON S.DocID = M.DocID " . 
                    "LEFT JOIN ssFields S1 ON (S1.Field5 = M.Field15 AND S1.Field27 = '". $org_no ."') " .  

                    "WHERE S.Field27 IS NOT NULL " .                  
                    "AND S.Field27 = '". $org_no ."' " .                 
                                    
                    (($_fetch_new_data) ? $_fetch_new_data :

                    (($_specific_invoice_data) ? $_specific_invoice_data :
                    "AND (TRY_CAST(S.Field18 AS date) BETWEEN '". $service_start ."' AND '". $end_date ."') " )).                    

                    //$_fetch_new_data .                   

                    "AND S.Field17 IS NOT NULL " .  
                    
                    "ORDER BY S.Field17"
                    ;
// ASC, S1.Field6 DESC

          try
          {
            $result = DB::connection('azure_sql')->select($query);               
          }
          catch (\Exception $e) 
          {        dd($e);
            $errorMessage = $e->getMessage(); 

            return $errorMessage;  
          } 
         
          $insert_invoices = 0;
          if($result)  
            $insert_invoices = $this->insertImportReconciliationInvoices($result, $vatregs, $authUser, $from);
          
          if($full_refresh && $from == 'global-search-refresh')
            return [
              'insert_invoices' => $insert_invoices,
              'result' => $result,
            ];
          else  
            return $insert_invoices;
        } //azure
      }
      catch (\Exception $e) 
      {        
        $errorMessage = $e->getMessage(); 

        return $errorMessage;  
      } 
    }

    public function parseRelatedInvoices($relatedRaw): \Illuminate\Support\Collection {
      $invoiceValues = collect();

      if (!$relatedRaw) {
          return $invoiceValues;
      }

      // Ensure $relatedRaw is an array
      if (!is_array($relatedRaw)) {
          $relatedRaw = preg_split('/[\r\n,]+/', (string) $relatedRaw);
      }

      foreach ($relatedRaw as $val) {
          $val = trim($val);
          if (!$val) continue;

          // Split by commas first (already split sometimes)
          $parts = preg_split('/,/', $val);

          foreach ($parts as $part) {
              $part = trim($part);
              if (!$part) continue;

              // Match ranges like INV100-INV105
              if (preg_match('/^([A-Za-z]*)(\d+)\s*-\s*([A-Za-z]*)(\d+)$/', $part, $matches)) {
                  [$full, $prefixStart, $startNum, $prefixEnd, $endNum] = $matches;
                  $startNum = (int)$startNum;
                  $endNum = (int)$endNum;

                  if ($prefixStart === $prefixEnd && $startNum <= $endNum) {
                      $len = strlen((string)$matches[2]);
                      for ($i = $startNum; $i <= $endNum; $i++) {
                          $invoiceValues->push($prefixStart . str_pad($i, $len, '0', STR_PAD_LEFT));
                      }
                  }
              } else {
                  // Not a range: split by spaces (e.g., "123 124 125" or "NO123 NO124")
                  foreach (preg_split('/\s+/', $part) as $p) {
                      $p = trim($p, ".,;"); // remove trailing punctuation
                      if ($p) $invoiceValues->push($p);
                  }
              }
          }
      }

      // Remove duplicates and sort
      return $invoiceValues->unique()->sort()->values();
  }

  public function loadImportReconciliationDatasFromOcr($authUser, $vatreg, $from = 'ocr', $full_refresh = false, $invoice_name = null, $invoice_no = null)
    {
        try {

            $client_id = $vatreg->client_id;
            $client_name = $vatreg->client->client_name;
            $vat_reg_main_id = $vatreg->vat_reg_main_id;

            $vatregmain = $vatreg->vatregmain;

            $org_no = ($vatregmain->country == 'NO')
                ? $vatregmain->org_no
                : str_replace(['.', '-'], '', $vatregmain->vat_no);

            $org_no = $org_no ? preg_replace('/\D/', '', $org_no) : '';

            $totalProcessed = 0;

            // Fetch VAT regs once (lightweight)
            $vatregs = $this->getLazy(
                'vatreg',
                ['client'],
                ['vat_reg_main_id' => ['operator' => '=', 'value' => $vat_reg_main_id]],
                [],
                ['id' => 'DESC'],
                'get'
            );
// Log::info("BEFORE dooooooooooooooooooooo");                
// Log::info($org_no);

            //$hasDispatchedAnyJobs = false;

            InvoiceOcrPdf::where('sync_status', 0)
              ->where('is_locked', 1)
              ->update(['is_locked' => 0]);

            InvoiceOcrPdf::where('invoice_type', 'com')              
              ->where('is_locked', 1)
              ->update(['is_locked' => 0]);  

            do {
//Log::info("start dooooooooooooooooooooo1111111");
                /**
                 * STEP 1: FETCH + LOCK 100 COM INVOICES
                 */
                //$com_ids = DB::transaction(function () use ($org_no, $client_id, $invoice_no) {
                $com_ids = DB::transaction(function () use ($org_no, $invoice_no) {
                  if($invoice_no)
                  {
                    $rows = DB::select("
                        SELECT id
                        FROM dv_invoice_ocr_pdfs
                        WHERE invoice_type = 'com'
                          AND is_locked = 0
                          AND is_deleted = 0
                          AND status = 'completed'
                          AND (
                            REGEXP_REPLACE(JSON_UNQUOTE(JSON_EXTRACT(extracted_data, '$.supplier.org_number')), '[^0-9]', '') = ?
                            OR
                            REGEXP_REPLACE(JSON_UNQUOTE(JSON_EXTRACT(extracted_data, '$.supplier.cvr_number')), '[^0-9]', '') = ?
                            OR
                            REGEXP_REPLACE(JSON_UNQUOTE(JSON_EXTRACT(extracted_data, '$.recipient.org_number')), '[^0-9]', '') = ?
                          )
                          AND (                            
                            JSON_UNQUOTE(JSON_EXTRACT(extracted_data, '$.invoice_number')) = ?
                          )                        
                        FOR UPDATE SKIP LOCKED
                    ", [$org_no, $org_no, $org_no, $invoice_no]);                    
                  }
                  else
                  {
                    $rows = DB::select("
                        SELECT id
                        FROM dv_invoice_ocr_pdfs
                        WHERE invoice_type = 'com'
                          AND is_locked = 0
                          AND is_deleted = 0
                          AND status = 'completed'
                          AND (
                            REGEXP_REPLACE(JSON_UNQUOTE(JSON_EXTRACT(extracted_data, '$.supplier.org_number')), '[^0-9]', '') = ?
                            OR
                            REGEXP_REPLACE(JSON_UNQUOTE(JSON_EXTRACT(extracted_data, '$.supplier.cvr_number')), '[^0-9]', '') = ?
                            OR
                            REGEXP_REPLACE(JSON_UNQUOTE(JSON_EXTRACT(extracted_data, '$.recipient.org_number')), '[^0-9]', '') = ?
                          )
                        LIMIT 100
                        FOR UPDATE SKIP LOCKED
                    ", [$org_no, $org_no, $org_no]);
                  }
                    
                    $ids = collect($rows)->pluck('id');

                    if ($ids->isNotEmpty()) {

                        // Lock COM invoices
                        DB::table('dv_invoice_ocr_pdfs')
                            ->whereIn('id', $ids)
                            ->update([
                                'is_locked' => 1,
                                //'client_id' => $client_id,
                                'updated_at' => now()
                            ]);
                    }

                    return $ids;
                });
// Log::info("Com IDs: ");                
// Log::info($com_ids);
                if ($com_ids->isEmpty()) {
                    break;
                }

                /**
                 * STEP 2: LOAD COM (ONLY REQUIRED FIELDS)
                 */
                $comInvoices = InvoiceOcrPdf::whereIn('id', $com_ids)
                    ->select('id', 'extracted_data')
                    ->get();

                /**
                 * STEP 3: PARSE RELATED NUMBERS
                 */
                $comParsedMap = [];
                $allRelatedNumbers = collect();

                foreach ($comInvoices as $com) {

                    $data = is_string($com->extracted_data)
                        ? json_decode($com->extracted_data, true)
                        : $com->extracted_data;

                    $related = $this->parseRelatedInvoices($data['related_sales_invoices'] ?? '');

                    $related = collect($related)
                        ->filter()
                        ->map(fn($v) => ltrim($v, '#'));

                    $comParsedMap[$com->id] = $related;

                    $allRelatedNumbers = $allRelatedNumbers->merge($related);
                }

                $allRelatedNumbers = $allRelatedNumbers->unique()->values();
// Log::info("Related sales invoices: ");                
// Log::info($allRelatedNumbers);s
                // if ($allRelatedNumbers->isEmpty()) {
                //     // nothing to match → continue next batch
                //     continue;
                // }

                /**
                 * STEP 4: FETCH MATCHING SALES (UNLOCKED ONLY)
                 */
                $salesInvoices = collect();

                if ($allRelatedNumbers->isNotEmpty()) {

                  if ($client_name && (
                      stripos(strtolower($client_name), "aubo") !== false || stripos(strtolower($client_name), "beck") !== false ||
                      stripos(strtolower($client_name), "geisler") !== false || stripos(strtolower($client_name), "noscomed") !== false ||
                      stripos(strtolower($client_name), "rexholm") !== false || stripos(strtolower($client_name), "villy") !== false
                    )
                  ) 
                  {
                    $salesInvoices = ImportReconciliationSalesInvoicesData::select('id', 'invoice_no')
                                        ->whereIn('invoice_no', $allRelatedNumbers)
                                        ->get();                    
                  } //in sales invoice data table
                  else
                  {
                    if($invoice_no)
                    {
                      $salesInvoices = InvoiceOcrPdf::whereIn('invoice_type', ['sales', 'multi-invoices'])                           
                          ->where('sync_status', 1)                         
                          ->where('is_deleted', 0)
                          ->where('status', 'completed')
                          ->select('id', 'extracted_data')
                          ->where(function ($q) use ($allRelatedNumbers, $client_name) {

                              foreach ($allRelatedNumbers->chunk(500) as $chunk) {

                                  $placeholders = implode(',', array_fill(0, count($chunk), '?'));

                                  if(str_contains(strtolower($client_name), 'stof'))
                                    $q->orWhereRaw("
                                        REPLACE(REPLACE(JSON_UNQUOTE(JSON_EXTRACT(extracted_data, '$.invoice_number')), '#', ''), '-', '')
                                        IN ($placeholders)
                                    ", $chunk->toArray());
                                  else if(str_contains(strtolower($client_name), 'horn bord'))
                                    $q->orWhereRaw("
                                        REPLACE(REPLACE(JSON_UNQUOTE(JSON_EXTRACT(extracted_data, '$.order_number')), '#', ''), '-', '')
                                        IN ($placeholders)
                                    ", $chunk->toArray());
                                  else  
                                    $q->orWhereRaw("
                                        REPLACE(JSON_UNQUOTE(JSON_EXTRACT(extracted_data, '$.invoice_number')), '#', '') IN ($placeholders)
                                    ", $chunk->toArray());

                                  $q->orWhereRaw("
                                      REPLACE(JSON_UNQUOTE(JSON_EXTRACT(extracted_data, '$.no_invoice_number')), '#', '') IN ($placeholders)
                                  ", $chunk->toArray());
                              }
                          })
                          ->get();
                    }
                    else
                    {
                      $salesInvoices = InvoiceOcrPdf::whereIn('invoice_type', ['sales', 'multi-invoices'])
                          ->where('is_locked', 0)
                          ->where('sync_status', 0)
                          ->where('is_deleted', 0)
                          ->where('status', 'completed')
                          ->select('id', 'extracted_data')
                          ->where(function ($q) use ($allRelatedNumbers, $client_name) {

                              foreach ($allRelatedNumbers->chunk(500) as $chunk) {

                                  $placeholders = implode(',', array_fill(0, count($chunk), '?'));

                                  if(str_contains(strtolower($client_name), 'stof'))
                                    $q->orWhereRaw("
                                        REPLACE(REPLACE(JSON_UNQUOTE(JSON_EXTRACT(extracted_data, '$.invoice_number')), '#', ''), '-', '')
                                        IN ($placeholders)
                                    ", $chunk->toArray());
                                  else if(str_contains(strtolower($client_name), 'horn bord'))
                                    $q->orWhereRaw("
                                        REPLACE(REPLACE(JSON_UNQUOTE(JSON_EXTRACT(extracted_data, '$.order_number')), '#', ''), '-', '')
                                        IN ($placeholders)
                                    ", $chunk->toArray());
                                  else  
                                    $q->orWhereRaw("
                                        REPLACE(JSON_UNQUOTE(JSON_EXTRACT(extracted_data, '$.invoice_number')), '#', '') IN ($placeholders)
                                    ", $chunk->toArray());

                                  $q->orWhereRaw("
                                      REPLACE(JSON_UNQUOTE(JSON_EXTRACT(extracted_data, '$.no_invoice_number')), '#', '') IN ($placeholders)
                                  ", $chunk->toArray());
                              }
                          })
                          ->get();
                      }
                  }//else in OCR table
                }

                /**
                 * STEP 5: INDEX SALES
                 */
                $salesMap = [];

                foreach ($salesInvoices as $sale) {

                  if ($client_name && (
                    stripos(strtolower($client_name), "aubo") !== false || stripos(strtolower($client_name), "beck") !== false ||
                    stripos(strtolower($client_name), "geisler") !== false || stripos(strtolower($client_name), "noscomed") !== false ||
                    stripos(strtolower($client_name), "rexholm") !== false || stripos(strtolower($client_name), "villy") !== false
                    )
                  ) 
                  {
                    $inv = ltrim($sale->invoice_no ?? '', '#');

                    $noInv = null;

                    if ($inv) $salesMap[$inv][] = $sale->id;
                    if ($noInv) $salesMap[$noInv][] = $sale->id;
                  } //in sales invoice data table
                  else
                  {
                    $data = is_string($sale->extracted_data)
                        ? json_decode($sale->extracted_data, true)
                        : $sale->extracted_data;

                    if(str_contains(strtolower($client_name), 'stof'))
                      $inv = preg_replace('/-/', '', $data['invoice_number']);
                    else if(str_contains(strtolower($client_name), 'horn bord'))
                      $inv = preg_replace('/-/', '', $data['order_number']);
                    else
                      $inv = ltrim($data['invoice_number'] ?? '', '#');
                    
                    $noInv = ltrim($data['no_invoice_number'] ?? '', '#');

                    if ($inv) $salesMap[$inv][] = $sale->id;
                    if ($noInv) $salesMap[$noInv][] = $sale->id;
                  }//else from OCR table extracted_data
                }

                /**
                 * STEP 6: BUILD FINAL (LIGHTWEIGHT)
                 */
                $final = [];

                foreach ($comParsedMap as $comId => $numbers) {

                    $matchedSales = [];

                    foreach ($numbers as $num) {
                        if (isset($salesMap[$num])) {
                            $matchedSales = array_merge($matchedSales, $salesMap[$num]);
                        }
                    }

                    // $matchedSales = array_unique($matchedSales);

                    // if (!empty($matchedSales)) {
                    //     $final[] = [
                    //         'com_id' => $comId,
                    //         'sales_ids' => $matchedSales
                    //     ];
                    // }

                    $final[] = [
                        'com_id' => $comId,
                        'sales_ids' => array_values(array_unique($matchedSales))
                    ];
                }
// Log::info("BEFORE INSERT");                
// Log::info($final);
                
                if (!empty($final)) {
                  //$hasDispatchedAnyJobs = true;

                    /**
                     * STEP 7: LOCK SALES (PREVENT DUPLICATES)
                     */
                    $allSalesIds = collect($final)->pluck('sales_ids')->flatten()->unique();

                    if ($client_name && (
                      stripos(strtolower($client_name), "aubo") !== false || stripos(strtolower($client_name), "beck") !== false ||
                    stripos(strtolower($client_name), "geisler") !== false || stripos(strtolower($client_name), "noscomed") !== false ||
                    stripos(strtolower($client_name), "rexholm") !== false || stripos(strtolower($client_name), "villy") !== false
                      )
                    ) 
                    {
                      
                    } //in sales invoice data table
                    else
                    {
                      DB::table('dv_invoice_ocr_pdfs')
                          ->whereIn('id', $allSalesIds)
                          ->update([
                            'is_locked' => 1,
                            //'client_id' => $client_id,
                            'updated_at' => now()
                          ]);
                    }//else from OCR table extracted_data
// Log::info($vatregs);                        
// Log::info("FINALLLLLLLLLLLLLLLLLLLL");
// Log::info($final);
                   
                    /**
                     * STEP 8: DISPATCH JOBS IMMEDIATELY
                     */
                    foreach (array_chunk($final, 10) as $chunk) {
                        Bus::dispatch(
                            (new InsertComSalesInvoicesFromOcr($chunk, $vatregs, $authUser, $from))
                                ->onQueue('ocrpdfsyncinvoices')
                        );
                    }

                    $totalProcessed += count($final);
                }                

                /**
                 * STEP 9: FREE MEMORY
                 */
                unset(
                    $comInvoices,
                    $salesInvoices,
                    $salesMap,
                    $comParsedMap,
                    $allRelatedNumbers,
                    $final
                );

                gc_collect_cycles();

            } while (true);

            // if ($hasDispatchedAnyJobs) {

            //     $logType = match($from) {
            //         'ocr-search-refresh', 'specific-ocr-search-refresh'
            //             => 'importreconcilation-ocr-search-refresh',
            //         default
            //             => 'importreconcilation-control-refresh',
            //     };

            //     $this->addLog($authUser, $logType, [
            //         'status' => 'OCR sync completed',
            //         'client' => $client_name,
            //         'processed' => $totalProcessed
            //     ]);

            //     event(new OcrInvoicesSyncEvent(
            //         $client_id,
            //         'Synced the OCR invoices'
            //     ));
            // }

            return [
                'processed' => $totalProcessed
            ];

        } catch (\Exception $e) {
            Log::error('OCR Load Failed: ' . $e->getMessage());
            return $e->getMessage();
        }
    }

//   public function loadImportReconciliationDatasFromOcr($authUser, $vatreg, $from = 'ocr', $full_refresh = false, $invoice_name = null, $invoice_no = null)
//     {
//         try 
//         {
//           $client_id = $vatreg->client_id;
//           $client_name = $vatreg->client->client_name;
//           $vat_reg_main_id = $vatreg->vat_reg_main_id;

//           $vatregmain = $vatreg->vatregmain;

//           $org_no = ($vatregmain->country == 'NO')
//               ? $vatregmain->org_no
//               : str_replace(['.', '-'], '', $vatregmain->vat_no);

//           $org_no = $org_no ? preg_replace('/\D/', '', $org_no) : '';

//           /**
//            * STEP 1: FETCH ONLY COM INVOICES (LIMIT 100)
//            */
//           $com_ids = DB::transaction(function () use ($org_no, $client_id) {
//             $rows = DB::select("
//                 SELECT id
//                 FROM dv_invoice_ocr_pdfs
//                 WHERE invoice_type = 'com'
//                   AND is_locked = 0
//                   AND (
//                     REGEXP_REPLACE(JSON_UNQUOTE(JSON_EXTRACT(extracted_data, '$.supplier.org_number')), '[^0-9]', '') = ?
//                     OR
//                     REGEXP_REPLACE(JSON_UNQUOTE(JSON_EXTRACT(extracted_data, '$.supplier.cvr_number')), '[^0-9]', '') = ?
//                     OR
//                     REGEXP_REPLACE(JSON_UNQUOTE(JSON_EXTRACT(extracted_data, '$.recipient.org_number')), '[^0-9]', '') = ?
//                   )
//                 LIMIT 100
//                 FOR UPDATE SKIP LOCKED
//             ", [$org_no, $org_no, $org_no]);

//             $ids = collect($rows)->pluck('id');

//             if ($ids->isNotEmpty()) {
//                 DB::table('dv_invoice_ocr_pdfs')
//                     ->whereIn('id', $ids)
//                     ->update(['is_locked' => 1]);

//                 // update client_id correctly
//                 InvoiceOcrPdf::whereIn('id', $ids)
//                     ->whereNull('client_id')
//                     ->update([
//                         'client_id' => $client_id,
//                         'updated_at' => now()
//                     ]);   
//             }

//             return $ids;
//           });

//           if ($com_ids->isEmpty()) {
//               return 0;
//           }

//           /**
//            * STEP 2: FETCH COM INVOICES
//            */
//           $comInvoices = InvoiceOcrPdf::whereIn('id', $com_ids)
//               ->orderBy('id', 'ASC')
//               ->get();

//           /**
//            * STEP 3: COLLECT RELATED SALES NUMBERS
//            */
//           $allRelatedNumbers = collect();

//           foreach ($comInvoices as $com) {
//               $data = is_string($com->extracted_data)
//                   ? json_decode($com->extracted_data, true)
//                   : $com->extracted_data;

//               $relatedRaw = $data['related_sales_invoices'] ?? '';
//               $related = $this->parseRelatedInvoices($relatedRaw);

//               $allRelatedNumbers = $allRelatedNumbers->merge($related);
//           }

//           $allRelatedNumbers = $allRelatedNumbers->unique()->values();

//           /**
//            * STEP 4: FETCH MATCHING SALES INVOICES
//            */
//           $salesInvoices = collect();

//           if ($allRelatedNumbers->isNotEmpty()) {
//               $salesInvoices = InvoiceOcrPdf::whereIn('invoice_type', ['sales', 'multi-invoices'])
//                   ->where(function ($q) use ($allRelatedNumbers, $client_name) {

//                       foreach ($allRelatedNumbers as $num) {

//                           if (str_contains(strtolower($client_name), 'rainwear')) {
//                               $q->orWhereRaw("
//                                   JSON_UNQUOTE(JSON_EXTRACT(extracted_data, '$.no_invoice_number')) = ?
//                               ", [$num]);
//                           } else {
//                               $q->orWhereRaw("
//                                   JSON_UNQUOTE(JSON_EXTRACT(extracted_data, '$.invoice_number')) = ?
//                               ", [$num]);
//                           }
//                       }
//                   })
//                   ->get();
//           }

//           /**
//            * STEP 5: GROUP SALES BY INVOICE NUMBER (FAST LOOKUP)
//            */
//           $salesMap = [];

//           foreach ($salesInvoices as $sale) {
//               $data = is_string($sale->extracted_data)
//                   ? json_decode($sale->extracted_data, true)
//                   : $sale->extracted_data;

//               $key = str_contains(strtolower($client_name), 'rainwear')
//                   ? ltrim($data['no_invoice_number'] ?? '', '#')
//                   : ltrim($data['invoice_number'] ?? '', '#');

//               if ($key) {
//                   $salesMap[$key][] = $sale;
//               }
//           }

//           /**
//            * STEP 6: BUILD FINAL STRUCTURE
//            */
//           $final = $comInvoices->map(function ($com) use ($salesMap) {

//               $data = is_string($com->extracted_data)
//                   ? json_decode($com->extracted_data, true)
//                   : $com->extracted_data;

//               $relatedNumbers = $this->parseRelatedInvoices($data['related_sales_invoices'] ?? '');

//               $matchedSales = collect();

//               foreach ($relatedNumbers as $num) {
//                   if (isset($salesMap[$num])) {
//                       $matchedSales = $matchedSales->merge($salesMap[$num]);
//                   }
//               }

//               return [
//                   'com_invoice' => $com,
//                   'sales_invoices' => $matchedSales->values()
//               ];
//           })
//           ->filter(fn($item) => $item['sales_invoices']->isNotEmpty())
//           ->values();
// dd($final);
//           if ($final->isEmpty()) {
//               return 0;
//           }

//           /**
//            * STEP 7: FETCH VAT REGS
//            */
//           $vatregs = $this->getLazy(
//               'vatreg',
//               ['client'],
//               ['vat_reg_main_id' => ['operator' => '=', 'value' => $vat_reg_main_id]],
//               [],
//               ['id' => 'DESC'],
//               'get'
//           );

//           /**
//            * STEP 8: CHUNK + DISPATCH
//            */
//           $chunks = $final->chunk(100);

//           $jobs = [];
//           foreach ($chunks as $chunk) {
//               $jobs[] = new InsertComSalesInvoicesFromOcr($chunk, $vatregs, $authUser, $from);
//           }

//           $batch = Bus::batch($jobs)
//               ->onQueue('ocrpdfsyncinvoices')
//               ->catch(function ($batch, $e) {
//                   Log::error('OCR SYNC batch failed: ' . $e->getMessage());
//               })
//               ->dispatch();

//           return [
//               'insert_invoices' => $batch->id,
//               'result' => $final,
//           ];

//         } catch (\Exception $e) {
//             Log::error('OCR Load Failed: ' . $e->getMessage());
//             return $e->getMessage();
//         }
//     }

  /*
    public function loadImportReconciliationDatasFromOcr($authUser, $vatreg, $from = 'ocr', $full_refresh = false, $invoice_name = null, $invoice_no = null)
    {
      try
      {
        $client_id = $vatreg->client_id;
        $vat_reg_id = $vatreg->id;

        $vatregmain = $vatreg->vatregmain; 
        $vat_reg_main_id = $vatreg->vat_reg_main_id;

        if($vatregmain->country == 'NO')
          $org_no = $vatregmain->org_no;        
        else
          $org_no = str_replace(['.', '-'], '', $vatregmain->vat_no);

        $org_no = $org_no ? preg_replace('/\D/', '', $org_no) : '';
        
        $result_ids = DB::transaction(function () use ($org_no) {
            $rows = DB::select("
                SELECT id
                FROM dv_invoice_ocr_pdfs
                WHERE ((invoice_type = 'com') OR (invoice_type != 'com' AND sync_status = 0 AND is_locked = 0))
                  AND (
                    REGEXP_REPLACE(JSON_UNQUOTE(JSON_EXTRACT(extracted_data, '$.supplier.org_number')), '[^0-9]', '') = ?
                    OR
                    REGEXP_REPLACE(JSON_UNQUOTE(JSON_EXTRACT(extracted_data, '$.supplier.cvr_number')), '[^0-9]', '') = ?
                    OR
                    REGEXP_REPLACE(JSON_UNQUOTE(JSON_EXTRACT(extracted_data, '$.recipient.org_number')), '[^0-9]', '') = ?
                  )" .
                //LIMIT 100
               " FOR UPDATE SKIP LOCKED
            ", [$org_no, $org_no, $org_no]);

            $ids = collect($rows)->pluck('id');

            if ($ids->isNotEmpty()) {
                DB::table('dv_invoice_ocr_pdfs')
                    ->whereIn('id', $ids)
                    ->update(['is_locked' => 1]);
            }

            return $ids;
        });

        // Update client_id for the results
        $updateClientId = InvoiceOcrPdf::whereIn('id', $result_ids)
                            ->whereNull('client_id')
                            ->update([
                              'client_id' => $client_id,
                              'updated_at' => now()
                            ]);

        // Now fetch full models with relations
        $result = InvoiceOcrPdf::with('client')
                    ->whereIn('id', $result_ids)
                    ->orderBy('id', 'ASC')
                    ->get();

        $grouped = $result->groupBy('invoice_type');

        $comInvoices   = $grouped->get('com', collect());
        $salesInvoices = $grouped->get('sales', collect())
            ->merge($grouped->get('multi-invoices', collect()));

        $final = $comInvoices->map(function ($com) use ($salesInvoices, $client_name) {

            $extracted = $com->extracted_data;

            // Convert JSON string → array if needed
            if (is_string($extracted)) {
                $extracted = json_decode($extracted, true);
            }
            
            $relatedRaw = $extracted['related_sales_invoices'] ?? '';           

            $relatedNumbers = $this->parseRelatedInvoices($relatedRaw);

            // Match with sales invoices
            $matchedSales = $salesInvoices->filter(function ($sale) use ($relatedNumbers, $client_name) {

                $saleData = is_string($sale->extracted_data)
                    ? json_decode($sale->extracted_data, true)
                    : $sale->extracted_data;

                if(str_contains(strtolower($client_name), 'rainwear'))
                  return in_array(ltrim($saleData['no_invoice_number'] ?? '', '#'), $relatedNumbers->toArray());
                else
                  return in_array(ltrim($saleData['invoice_number'] ?? '', '#'), $relatedNumbers->toArray());
            })->values();

            return [
                'com_invoice'   => $com,
                'sales_invoices'=> $matchedSales
            ];
        })
        ->filter(function ($item) {
            return $item['sales_invoices']->isNotEmpty();
        })
        ->values(); // optional: reindex

        if(count($final) > 0)
        {
          //GET All VATreg. based on vat_reg_main_id
          $_with = ['client'];
          $_where = [
            'vat_reg_main_id' => ['operator' => '=', 'value' => $vat_reg_main_id]
          ];
          $_whereHas = [];      
          $_orderBy = [
            'id' => 'DESC'
          ];  
          $_final = 'get';        
          $vatregs = $this->getLazy('vatreg', $_with, $_where, $_whereHas, $_orderBy, $_final); 
          //GET All VATreg. based on vat_reg_main_id  

          //$chunks = array_chunk($final, 100); // Divide your data array into chunks     
          $chunks = $final->chunk(100);

          // Create a batch and add jobs to it.
          $jobs = [];
          foreach ($chunks as $chunk) {
            $jobs[] = new InsertComSalesInvoicesFromOcr($chunk, $vatregs, $authUser, $from);
          }

          // // Dispatch all jobs in the batch.    
          $batch = Bus::batch($jobs)
            ->onQueue('ocrpdfsyncinvoices')
            ->then(function ($batch) {            
              //\Log::info('All jobs completed successfully.');
            })
            ->catch(function ($batch, $e) {            
              //\Log::error('Some jobs failed: ' . $e->getMessage());
            })
            ->finally(function ($batch) use($jobs) {
              //\Log::info('Batch finished. Finally callback triggered.', ['batch' => $batch]);
            })
            ->dispatch();

          // Get the batch ID
          $batchId = $batch->id;    

          //return $batchId;
          return [
            'insert_invoices' => $batchId,
            'result' => $final,
          ];
        }
        else
          return 0;  
      }
      catch (\Exception $e) 
      {    dd($e);    
        $errorMessage = $e->getMessage(); 

        return $errorMessage;  
      }
    }
    */

    /*  DON'T USE THIS METHOD */
    public function loadImportReconciliationDatasFromFtp($authUser, $vatreg, $system = null, $refresh = false, $from = 'ftp', $which_folder = 'main')
    {
      $apiClass = new ApiClass();      

      $vatregmain = $vatreg->vatregmain;     
      $api_name = "FTP";
      $client = $vatreg->client;
      $vat_reg_id = $vatreg->id;

      $this->addLog($authUser, 'importreconciliation-load', 
        [
          'From' => $from,
          'Refresh' => ($refresh) ? 'Logged in user refreshed' : '',
          'Loggedin User' => ($from == 'cron') ? 'Cron Job - FTP' : ((isset($authUser->firstname) && isset($authUser->lastname)) ? ($authUser->firstname . ' ' . $authUser->lastname) : $authUser->name),
          'Client Name' => $client->client_name,
          'VAT Reg' => Carbon::parse($vatreg->service_start)->format('M Y') . ' ' . $vatreg->country . ' ' . $vatreg->general_periods
        ]
      );
        
      if($api_name == "FTP")                           
      {           
        $specificClass = new FtpClass();

        $client_id = $vatreg->client_id;
        $client_name = $client->client_name;
        $vat_no = $vatreg->vatno;
      }
     
      $datas = [];
      
      if($vat_reg_id != null)   
      {   
        $client_name = $client->client_name;

        //Get VAT Returns from Table              
        $importreconciliationfiles = $vatreg->importreconciliationfiles;

        $importreconciliationcominvoices = $vatreg->importreconciliationcominvoices;
        $importreconciliationsalesinvoices = $vatreg->importreconciliationsalesinvoices;
               
        if(($vatreg->status_import_re != 0) && (count($importreconciliationcominvoices) == 0 || count($importreconciliationsalesinvoices) == 0) || $refresh)
        {
          $account_data = [];
          $account_data_err_message = "";
         
          if($api_name == "FTP")
          {
            $service_start = $vatreg->service_start;
            $end_date = $apiClass->getEndDateLazy($vatreg);              
                        
            /* -- READ XML FILE FROM FTP -- */
            $read_data = $specificClass->getImportReconciliationFilesFromFtp($vatreg, $authUser, $which_folder); 
            /* --end READ XML FILE FROM FTP -- */
            
            /* -- READ XML FILE FROM E-FACTO -- */
            if (stripos(strtolower($client_name), "noscomed") !== false ||
                stripos(strtolower($client_name), "rexholm") !== false)
            { 
              $efacto_read_data = $specificClass->getImportReconciliationFilesFromFtp($vatreg, $authUser, $which_folder, true);
              $read_data = array_merge($read_data, $efacto_read_data);             
            }
            /* --end READ XML FILE FROM E-FACTO -- */

            if(empty($read_data))
            {
              if(count($importreconciliationfiles) > 0) 
              {                 
                $invoice_rows = [];              
                foreach ($importreconciliationfiles as $importreconciliationfile)
                {
                  $importreconciliationfileid = $importreconciliationfile->id;
                  if($importreconciliationfile->file_id)
                  {
                    $downloadurl = $apiClass->loadFromOneDriveLazy($importreconciliationfile, $system);
                    
                    if(isset($downloadurl->error))
                    {
                      $account_data = "error";
                      $account_data_err_message = $downloadurl->error;
                      $this->addLog($authUser, 'importreconciliation-load-error', 
                        [
                          'Loggedin User' => (isset($authUser->firstname) && isset($authUser->lastname)) ? ($authUser->firstname . ' ' . $authUser->lastname) : $authUser->name,
                          'Client Name' => $client_name,
                          'Error' => $account_data_err_message
                        ]
                      );
                    } /* --end if DOWNLOAD URL ERROR -- */
                    else if($downloadurl == null)
                    {
                      $account_data = "error";
                      $account_data_err_message = "No files exists";
                      $this->addLog($authUser, 'importreconciliation-load-error', 
                        [
                          'Loggedin User' => (isset($authUser->firstname) && isset($authUser->lastname)) ? ($authUser->firstname . ' ' . $authUser->lastname) : $authUser->name,
                          'Client Name' => $client_name,
                          'Error' => $account_data_err_message//"No files exists"
                        ]
                      );
                    } /* --end else DOWNLOAD URL NULL -- */
                    else
                    {
                      $o_filename = $importreconciliationfile->o_file_name;
                      /* -- READ EXCEL FILE -- */                    
                      $read_ftp_data = $this->readImportReconciliationFile($downloadurl['download_url'], $vatreg->vat_reg_main_id, $o_filename, $downloadurl['file_extension'], $invoice_rows);                       
                      $invoice_rows = $read_ftp_data['invoice_rows'];
                      /* --end READ EXCEL FILE -- */ 
                    } /* --end else DOWNLOAD URL NOT NULL -- */
                  }
                } //for

                $account_data = $invoice_rows;                 
              }//FROM files
            }
            else                  
              $account_data = $read_data['invoice_rows'];                      
          } /* --end if FTP -- */
                        
          //$sales = [];
          //$purchase = [];

          if($account_data == "error")    
          {                        
            return ($account_data_err_message) ? $account_data_err_message : "Error in reading the import reconciliation xml file";
          } /* --end if ACCOUNT DATA ERROR -- */
          else if(isset($account_data->error))
          {
            $this->addLog($authUser, 'importreconciliation-load-error', 
              [
                'Loggedin User' => (isset($authUser->firstname) && isset($authUser->lastname)) ? ($authUser->firstname . ' ' . $authUser->lastname) : $authUser->name,
                'Client Name' => $client_name,
                'Error' => ($account_data_err_message) ? $account_data_err_message : $account_data->error
              ]
            );
           
            $account_data_err_message = isset($account_data->error->message) ? $account_data->error->message : '';          

            return ($account_data_err_message) ? $account_data_err_message : $account_data->error;
          } /* --end else ACCOUNT DATA ERROR -- */
          else
          { 
            if(count($account_data) > 0) 
            {                      
              return (count($account_data) > 0) ? true : false;                    
            }
          } /* --end else ACCOUNT DATA -- */                    
        } /* --end if REFRESH TRUE -- */            
      } /* --end if VAT REG NOT NULL -- */
            
      return (count($account_data) > 0) ? true : false;
    }

    public function insertExchangeRates($file = NULL)
    {            
      if($file == 'xml')  
      {
        try 
        {
          $xmlString = file_get_contents('https://www.nationalbanken.dk/api/currencyratesxml?lang=en');
          $xmlObject = simplexml_load_string($xmlString);
          $json = json_encode($xmlObject);
          $phpArray = json_decode($json, true); 
   
          $exchange_date = $phpArray['dailyrates']['@attributes']['id'];
          
          foreach($phpArray['dailyrates']['currency'] as $key=>$item)
          {         
            $currency_code = $item['@attributes']['code'];
            $exchange_rate = $item['@attributes']['rate'];

            $exchangeRates = ExchangeRates::updateOrCreate(
              [
                'main_currency_code' => 'DKK', 
                'currency_code' => $currency_code,
                'exchange_date' => $exchange_date                 
              ],
              [
                  'main_currency_code' => 'DKK', 
                  'per_unit' => 100,                    
                  'currency_code' => $currency_code,
                  'exchange_date' => $exchange_date,
                  'exchange_rate' => $exchange_rate    
              ]
            );         
          }
          return 'success';
        }
        catch (\Exception $e)        
        {
            return $e->getMessage();
        }        
      } 
      else if($file == 'xmlspecific')  
      {
        $xmlString = file_get_contents('https://www.nationalbanken.dk/api/currencyratesxmlhistory?lang=en');
        $xmlObject = simplexml_load_string($xmlString);
        $json = json_encode($xmlObject);
        $phpArray = json_decode($json, true); 

        foreach($phpArray['Cube'] as $cubes)
        {         
          foreach($cubes as $cube)
          {
            if(strpos('2024-03-22', $cube['@attributes']['time']) !== false)
            {
              foreach($cube['Cube'] as $key=>$item)
              {
                $currency_code = $item['@attributes']['currency'];
                $exchange_date = $cube['@attributes']['time'];
                $exchange_rate = $item['@attributes']['rate'];

                $exchangeRates = ExchangeRates::updateOrCreate(
                  [
                    'main_currency_code' => 'DKK', 
                    'currency_code' => $currency_code,
                    'exchange_date' => $exchange_date                 
                  ],
                  [
                      'main_currency_code' => 'DKK', 
                      'per_unit' => 100,                    
                      'currency_code' => $currency_code,
                      'exchange_date' => $exchange_date,
                      'exchange_rate' => $exchange_rate    
                  ]
                );
              }
            }
          }         
        }
        return 'success';
      }  
      else if($file == 'excel')  
      {        
        $inputFileName = public_path('exchange-rate-22.04.2024-to-07.05.2024.xlsx');

        $spreadsheet = new Spreadsheet();

        $inputFileType = 'Xlsx';    
             
        $reader = IOFactory::createReader($inputFileType);     
        $reader->setReadDataOnly(true);

        $worksheetData = $reader->listWorksheetInfo($inputFileName);    

        $data_detail = [];        
        foreach ($worksheetData as $worksheet) 
        {
          $sheetName = $worksheet['worksheetName'];

          $reader->setLoadSheetsOnly($sheetName);
          $spreadsheet = $reader->load($inputFileName);

          $worksheet = $spreadsheet->getActiveSheet();
          
          $highestRow = $worksheet->getHighestRow(); 
          $highestColumn = $worksheet->getHighestColumn();
          
          $highestColumn++;       
          for ($col = 'B'; $col != $highestColumn; $col++)
          {             
            for ($row = 2; $row <= $highestRow; $row++)
            {             
              $currency_code = $worksheet->getCell("A$row")->getCalculatedValue();              
             
              $exchange_date = str_replace('D', '-', str_replace('M', '-', $worksheet->getCell($col."1")->getValue()));
              $exchange_rate = $worksheet->getCell($col.$row)->getValue();

              if($exchange_date != "")             
                $exchangeRates = ExchangeRates::updateOrCreate(
                  [
                    'main_currency_code' => 'DKK', 
                    'currency_code' => $currency_code,
                    'exchange_date' => $exchange_date                 
                  ],
                  [
                      'main_currency_code' => 'DKK', 
                      'per_unit' => 100,                    
                      'currency_code' => $currency_code,
                      'exchange_date' => $exchange_date,
                      'exchange_rate' => $exchange_rate    
                  ]
                );            
            }
          }
        }
        return "success";
      } //excel       
    }

    public function readComplianceFile($url, $type = NULL)
    { 
      if($type == 'split')
        $extension = 'csv';
      else
        $extension = (strpos($url, "https://") !== false) ? '' : $url->getClientOriginalExtension();
     
      if($extension == 'xlsx')
      {       
        $spreadsheet = new Spreadsheet();

        $inputFileType = 'Xlsx';           
        $inputFileName = $url;
       
        $reader = IOFactory::createReader($inputFileType);
        $reader->setReadDataOnly(true);

        try 
        {
          $worksheetData = $reader->listWorksheetInfo($inputFileName);    
          
          $matched_users = [];   
          $matched_user_ids = [];      

          $matched_cvr_user_ids = [];     
          $matched_cvr_users = [];  
          foreach ($worksheetData as $worksheet) 
          {
            $sheetName = $worksheet['worksheetName'];
            
            $reader->setLoadSheetsOnly($sheetName);
            $spreadsheet = $reader->load($inputFileName);

            $worksheet = $spreadsheet->getActiveSheet();
            
            $highestRow = $worksheet->getHighestRow(); 
            $highestColumn = $worksheet->getHighestColumn();
           
            $chunkSize = 1000; // Adjust as needed

            $startRow = 2;

            $_firstname_col = 3;
            $_lastname_col = 4;
            $_middlename_col = 5;
            $_designation_col = '';  
            if($sheetName == "Nuværende PEP'ere")
            {      
              $startRow = 4;
        
              $_firstname_col = 1;
              $_lastname_col = 2;
              $_middlename_col = '';
              $_designation_col = 3;    
            }
            else if($sheetName == "Tidligere PEP'ere")
            {             
              $_firstname_col = 0;
              $_lastname_col = 1;
              $_middlename_col = '';
              $_designation_col = 2;    
            }

            do {
              $endRow = min($startRow + $chunkSize - 1, $highestRow);            
              
              // Process chunk of rows
              for ($row = $startRow; $row <= $endRow; $row++)           
              {
                $rowData = $worksheet->rangeToArray('A' . $row . ':' . $highestColumn . $row);

                $firstname = trim($rowData[0][$_firstname_col]);  
                $lastname = trim($rowData[0][$_lastname_col]);  
                $middlename = ($_middlename_col == '') ? '' : trim($rowData[0][$_middlename_col]);  
                $designation = ($_designation_col == '') ? '' : trim($rowData[0][$_designation_col]);

                if($firstname || $lastname || $middlename)
                {                  
                  /* SYSTEM USER MATCH CHECK*/
                  if($middlename == '')
                    $musers = User::leftJoin('dv_users', function($join) {
                                  $join->on('users.id', '=', 'dv_users.user_id');                      
                                })  
                                ->select('dv_users.*')                     
                                ->where('dv_users.firstname', $firstname)
                                ->where('dv_users.lastname', $lastname)
                                ->get();
                  else                                
                    $musers = User::leftJoin('dv_users', function($join) {
                                  $join->on('users.id', '=', 'dv_users.user_id');                      
                                })  
                                ->select('dv_users.*')                    
                                ->where('dv_users.firstname', $firstname)
                                ->where(
                                  function($query) use ($lastname, $middlename) {
                                    return $query
                                        ->where('dv_users.lastname', $lastname)
                                        ->orWhere('dv_users.lastname', $middlename);
                                })
                                ->get();

                  if(count($musers) > 0)
                  {     
                    foreach ($musers as $muser) 
                    {                  
                      if(!in_array($muser->user_id, $matched_user_ids, true))
                      {
                        array_push($matched_user_ids, $muser->user_id);
                         
                        $matched_users[] = [                    
                          'excel_user' => [
                            'firstname' => $firstname,
                            'lastname' => $lastname,
                            'designation' => $designation
                          ],
                          'user' => $muser,
                        ];  

                        $dvUsers = DVUser::updateOrCreate(
                          ['user_id' => $muser->user_id],
                          [                
                              'compliance_firstname' => $firstname,
                              'compliance_lastname' => $lastname,                              
                              'compliance_designation' => $designation,                     
                              'is_compliance' => 1,
                              'compliance_status' => 1,// 1-PEP, 2-EU, 3-UNSC
                              'is_deleted' => 0
                          ]
                        );
                      }
                    }
                  } 
                  /* end SYSTEM USER MATCH CHECK*/

                  /* CVR USER MATCH CHECK*/   
                  if($middlename == '')
                    $mcvrusers = ClientCvr::leftJoin('dv_clients', function($join) {
                                  $join->on('dv_client_cvr.client_id', '=', 'dv_clients.id');                      
                                })       
                                ->select('dv_clients.client_name', 'dv_client_cvr.*')              
                                ->where('dv_client_cvr.person_name', $firstname)
                                ->orWhere('dv_client_cvr.person_name', $lastname)
                                ->orWhere('dv_client_cvr.person_name',$firstname.' '. $lastname) 
                                ->get(); 
                  else
                    $mcvrusers = ClientCvr::leftJoin('dv_clients', function($join) {
                                  $join->on('dv_client_cvr.client_id', '=', 'dv_clients.id');                      
                                })       
                                ->select('dv_clients.client_name', 'dv_client_cvr.*')              
                                ->where('dv_client_cvr.person_name', $firstname)   
                                ->orWhere('dv_client_cvr.person_name', $lastname)    
                                ->orWhere('dv_client_cvr.person_name', $middlename) 
                                ->orWhere('dv_client_cvr.person_name',$firstname.' '. $lastname .' '.  $middlename)
                                ->get(); 


                  if(count($mcvrusers) > 0)
                  {
                    foreach ($mcvrusers as $mcvruser)
                    {
                      if(!in_array($mcvruser->id, $matched_cvr_user_ids, true))
                      {
                        array_push($matched_cvr_user_ids, $mcvruser->id);
                         
                        $matched_cvr_users[] = [                    
                          'excel_user' => [
                            'firstname' => $firstname,
                            'lastname' => $lastname,
                            'designation' => $designation
                          ],
                          'cvruser' => $mcvruser,
                        ];  

                        $dvCvrUsers = ClientCvr::updateOrCreate(
                          ['id' => $mcvruser->id],
                          [                
                          'compliance_firstname' => $mcvruser->person_name,
                          'compliance_lastname' => null,                              
                          'compliance_designation' => $mcvruser->person_designation,                     
                          'is_compliance' => 1,
                          'compliance_status' => 1// 1-PEP, 2-EU, 3-UNSC
                          ]
                        );
                      }
                    }     
                  }
                  /*END CVR USER MATCH CHECK*/   
                }  //any names  
              }//chunk for          
              $startRow = $endRow + 1;                              
            } while ($startRow <= $highestRow);            
          }
                            
          return [
            'matched_users' => $matched_users,
            'matched_cvr_users' => $matched_cvr_users
           ];
        }  //try
        catch (\Exception $e) 
        {   dd($e);
          return "error";
        }
      } //excel
      else if($extension == 'xml')
      {        
        $xmlString = file_get_contents($url);
        $xmlObject = simplexml_load_string($xmlString);
        $json = json_encode($xmlObject);
        $phpArray = json_decode($json, true);
               
        $matched_users = [];  
        $matched_cvr_users = []; 

        $matched_user_ids = [];   
        $matched_cvr_user_ids = [];     
        foreach($phpArray['INDIVIDUALS'] as $individuals)
        {
          foreach($individuals as $key=>$individual)
          {
            $firstname = isset($individual['FIRST_NAME']) ? $individual['FIRST_NAME'] : '';
            $secondname = isset($individual['SECOND_NAME']) ? $individual['SECOND_NAME'] : '';
            $thirdname = isset($individual['THIRD_NAME']) ? $individual['THIRD_NAME'] : '';
            $designation = isset($individual['DESIGNATION']) ? $individual['DESIGNATION'] : '';

            /* -- CHECK USERS FOR COMPLIANCE -- */           
            $musers = User::leftJoin('dv_users', function($join) {
                              $join->on('users.id', '=', 'dv_users.user_id');                      
                            })  
                            ->select('dv_users.*')                    
                            ->where('dv_users.firstname', $firstname)
                            ->orWhere('dv_users.lastname', $secondname)
                            ->orWhere('dv_users.lastname', $thirdname)                           
                            ->get();
                
            if(count($musers) > 0)
            {    
              foreach ($musers as $muser)                
              {
                if(!in_array($muser->user_id, $matched_user_ids, true))
                {
                  array_push($matched_user_ids, $muser->user_id);
                    $lastname = $secondname . ' ' . $thirdname;
                    $matched_users[] = [                    
                      'excel_user' => [
                        'firstname' => $firstname,
                        'lastname' => $lastname,
                        'designation' => $designation
                      ],
                      'user' => $muser,
                    ];  

                    $dvUsers = DVUser::updateOrCreate(
                      ['user_id' => $muser->user_id],
                      [                
                          'compliance_firstname' => $firstname,
                          'compliance_lastname' => $lastname,                              
                          'compliance_designation' => $designation,                     
                          'is_compliance' => 1,
                          'compliance_status' => 3,// 1-PEP, 2-EU, 3-UNSC
                          'is_deleted' => 0
                      ]
                    );
                }
              }
            }           
            /* --end CHECK USERS FOR COMPLIANCE -- */ 

            /* CVR USER MATCH CHECK*/           
            $mcvrusers = ClientCvr::leftJoin('dv_clients', function($join) {
                              $join->on('dv_client_cvr.client_id', '=', 'dv_clients.id');                      
                            })   
                            ->select('dv_clients.client_name', 'dv_client_cvr.*')                  
                            ->where('dv_client_cvr.person_name', $firstname)
                            ->orwhere('dv_client_cvr.person_name', $secondname)
                            ->orWhere('dv_client_cvr.person_name', $thirdname)
                            ->orWhere('dv_client_cvr.person_name',$firstname .' '. $secondname .' '. $thirdname)
                            ->get(); 
           
            if(count($mcvrusers) > 0)
            {
              foreach ($mcvrusers as $mcvruser)
              {
                if(!in_array($mcvruser->id, $matched_cvr_user_ids, true))
                {
                  array_push($matched_cvr_user_ids, $mcvruser->id);
                  $lastname = $secondname . ' ' . $thirdname;
                  $matched_cvr_users[] = [                    
                      'excel_user' => [
                      'firstname' => $firstname,
                      'lastname' => $lastname,
                      'designation' => $designation
                    ],
                    'cvruser' => $mcvruser,
                  ];  

                  $dvCvrUsers = ClientCvr::updateOrCreate(
                    ['id' => $mcvruser->id],
                    [                
                    'compliance_firstname' => $mcvruser->person_name,
                    'compliance_lastname' => null,                              
                    'compliance_designation' => $mcvruser->person_designation,                     
                    'is_compliance' => 1,
                    'compliance_status' => 3// 1-PEP, 2-EU, 3-UNSC
                    ]
                  );
                }     
              }
            }           
            /*END CVR USER MATCH CHECK*/  
          } /* --end for INDIVIDUAL -- */  
        } /* --end for INDIVIDUALS -- */  
       
        return [
          'matched_users' => $matched_users,
          'matched_cvr_users' => $matched_cvr_users
         ];
      } /* --end if XML -- */  
      else if($extension == 'csv')
      {            
        try 
        {          
              $matched_users = [];   
              $matched_cvr_users = [];  

              $matched_user_ids = [];  
              $matched_cvr_user_ids = [];  
                           
              $inputFileType = 'Csv';                 
              if($type == 'split')
              {               
                $directory = storage_path('app/public/splits/');
                $inputFileName = $directory . $url;
              }
              else
                $inputFileName = $url->getPathName();
              
              $reader = new Csv(); 
              $encoding = \PhpOffice\PhpSpreadsheet\Reader\Csv::guessEncoding($inputFileName);        
              $reader->setInputEncoding($encoding);

              $spreadsheet = $reader->load($inputFileName);

              $worksheetData = $spreadsheet->getActiveSheet()->toArray();

              foreach($worksheetData as $key=>$individual)
              {
                if($key > 0)
                {
                  $firstname = $individual[17];
                  $middlename = $individual[18];
                  $lastname = $individual[16];
                  $wholename = $individual[19];
                  $designation = $individual[5];
                  
                  /* -- CHECK USERS FOR COMPLIANCE -- */                  
                  $musers = User::leftJoin('dv_users', function($join) {
                              $join->on('users.id', '=', 'dv_users.user_id');                      
                            })   
                            ->select('dv_users.*')                   
                            ->where('dv_users.firstname', $firstname)                            
                            ->where(
                              function($query) use ($middlename, $lastname) {
                                return $query
                                    ->where('dv_users.lastname', $middlename)
                                    ->orWhere('dv_users.lastname', $lastname);
                            })                          
                            ->get();
                 
                  if(count($musers) > 0)
                  {    
                    foreach ($musers as $muser) 
                    {                
                      if(!in_array($muser->user_id, $matched_user_ids, true))
                      {
                        array_push($matched_user_ids, $muser->user_id);

                          $lastname = ($lastname == '') ? $wholename : $lastname;
                          $matched_users[] = [                    
                            'excel_user' => [
                              'firstname' => $firstname,
                              'lastname' => $lastname,
                              'designation' => $designation
                            ],
                            'user' => $muser,
                          ];  

                          $dvUsers = DVUser::updateOrCreate(
                            ['user_id' => $muser->user_id],
                            [                
                                'compliance_firstname' => $firstname,
                                'compliance_lastname' => $lastname,                              
                                'compliance_designation' => $designation,                     
                                'is_compliance' => 1,
                                'compliance_status' => 2,// 1-PEP, 2-EU, 3-UNSC
                                'is_deleted' => 0
                            ]
                          );
                      }
                    }
                  }                                                
                  /* --end CHECK USERS FOR COMPLIANCE -- */

                  /* CVR USER MATCH CHECK*/                  
                  $mcvrusers = ClientCvr::leftJoin('dv_clients', function($join) {
                              $join->on('dv_client_cvr.client_id', '=', 'dv_clients.id');                      
                            })      
                            ->select('dv_clients.client_name', 'dv_client_cvr.*')               
                            ->where('dv_client_cvr.person_name', $firstname)
                            ->orWhere('dv_client_cvr.person_name', $lastname)    
                            ->orWhere('dv_client_cvr.person_name', $middlename) 
                            ->orWhere('dv_client_cvr.person_name',$firstname.' '. $lastname .' '.  $middlename)
                            ->get(); 

                  if(count($mcvrusers) > 0)
                  {
                    foreach ($mcvrusers as $mcvruser)
                    {
                      if(!in_array($mcvruser->id, $matched_cvr_user_ids, true))
                      {
                        array_push($matched_cvr_user_ids, $mcvruser->id);

                        $lastname = ($lastname == '') ? $wholename : $lastname;
                        $matched_cvr_users[] = [                    
                          'excel_user' => [
                          'firstname' => $firstname,
                          'lastname' => $lastname,
                          'designation' => $designation
                          ],
                          'cvruser' => $mcvruser,
                        ];  

                        $dvCvrUsers = ClientCvr::updateOrCreate(
                          ['id' => $mcvruser->id],
                          [                
                          'compliance_firstname' => $mcvruser->person_name,
                          'compliance_lastname' => null,                              
                          'compliance_designation' => $mcvruser->person_designation,                     
                          'is_compliance' => 1,
                          'compliance_status' => 2// 1-PEP, 2-EU, 3-UNSC
                          ]
                        );
                      }     
                    }
                  }                  
                  /*END CVR USER MATCH CHECK*/
                } /* --end if NOT HEADER -- */  
              } /* --end for INDIVIDUAL -- */
              
               return [
                'matched_users' => $matched_users,
                'matched_cvr_users' => $matched_cvr_users
               ];           
        } 
        catch (\Exception $e) 
        {
          dd($e);
            // Handle any exceptions
            return ['error' => $e->getMessage()];
        }
   
      } /* --end if CSV -- */  
    }

    public function split_file($file)
    {      
      $split_dir = storage_path('app/public/splits/');
      $ufile_target = "";
      
      $file_header = "";
      $file_content = "";
      $max_rows = 1000; // 1 header row + 4999 data rows
          
        $file_src = $file;
        $file_name = str_replace(".csv","",$file->getClientOriginalName());
        $file_counter = 1; // append to end of file name

        $i = 0; // source file row counter
        $col = 0; // source file row counter
        $row = 1; // destination file counter (keep under $max_rows)
        
        if(($handle = fopen($file_src, "r")) !== FALSE) {

          while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {            
            $col = count($data);
           
            if($i==0){
              
              // store the file header
              for($n=0;$n<$col;$n++){
                if($n>0){
                  $file_header.= ",";
                }
                
                $file_header.= $data[$n];
              }
              
              $file_header.= "\n";
            }
            else{
              if($row<$max_rows){
                for($n=0;$n<$col;$n++){
                  if($n>0){
                    $file_content.= ",";
                  }
                
                  $file_content.= '"'.$data[$n].'"';
                }
                
                $file_content.= "\n";
              }
              else{
                $this->make_file($file_name,$file_counter,$split_dir,$file_header,$file_content);
                
                // increment
                $file_counter++;
                
                // reset
                $file_content = "";
                
                // record this row
                for($n=0;$n<$col;$n++){
                  if($n>0){
                    $file_content.= ",";
                  }
                
                  $file_content.= '"'.$data[$n].'"';
                }
                
                $file_content.= "\n";
                
                
                $row = 1;
              }
              $row++;
            }
            $i++;
          }
          
          $this->make_file($file_name,$file_counter,$split_dir,$file_header,$file_content);
          
          fclose($handle);
        }
            
        return $file_counter;            
    }

    public function make_file($file_name,$file_counter,$split_dir,$file_header,$file_content)
    {    
      // name file
      $name = $file_name."_".$file_counter.".csv";
      
      // set path
      $path = $split_dir.$name;
      
      // set content
      $content = $file_header.$file_content;
     
      // save file
      if(($fp = fopen($path, "w+")) !== FALSE) {
        fwrite($fp, $content);
        fclose($fp);
      }
      
    } // make_file()

    /* -- scheduleReminderEmail -- */
    public function scheduleReminderEmail($authUser, $reminder_request = NULL, $reminder_id = NULL)
    {                  
      try 
      {
        $result = [];

        /* -- GET REMINDERS -- */
        $_where = [
          'status' => ['operator' => '=', 'value' => 1],        
          'close_status' => ['operator' => '=', 'value' => 0]  
        ];
        if($reminder_id)
          $reminders = $this->getRemindersLazy($reminder_id, $_where, 'get');
        else if(isset($reminder_request->save_status))
        {   
          if($reminder_request->save_status == 'nosave')
          {
            $reminder_request->merge(
              [
                "start_at"=>$reminder_request->datetime_value,
                "schedule"=>$reminder_request->schedule_value,
                "close_status"=>'0',
                'updated_by' => null
              ]); 
            $reminders[0] = $reminder_request; 
          }         
        }
        else
          $reminders = $this->getRemindersLazy(null, $_where);
        /* --end GET REMINDERS -- */
               
        /* -- GET ADMIN USER -- */
        $sender = $this->getUsersLazy(NULL, 'super-admin')->first();        
        /* --end GET ADMIN USER -- */

        foreach($reminders as $key=>$reminder)
        {
          $reminder->send_to_client = $reminder_request->send_to_client;

          $_start_at = Carbon::parse($reminder->start_at);
          $_schedule = $reminder->schedule;  

          $send_test_text_yes = ($reminder_request->send_test_reminder)? $reminder_request->send_test_reminder: '';
          $send_test_text_no = ($reminder_request->save_status) ? $reminder_request->save_status : '';

          if(!empty($reminder->reminderhistory))
          {           
            $_reminder_last_history = $reminder->reminderhistory->first();  
            $_sent_at = ($_reminder_last_history) ? $_reminder_last_history->sent_at : '';
          }

          if($_start_at <= Carbon::now())
          {                        
            if($_schedule == 'Does not repeat')
            {
              if($_start_at->format('Ymd') <= Carbon::now()->format('Ymd'))
              {
                $_sent = $this->sendReminderEmail($reminder, $sender, $authUser, $send_test_text_yes, $send_test_text_no);
               
                if($_sent == 'success')
                {    
                  if($send_test_text_yes != 'send_test_reminder' || $send_test_text_no != 'nosave')  
                  {
                    /* -- UPDATE REMINDER is_closed -- */
                    $getreminder = Reminder::where('id', $reminder->id)->first();

                    $getreminder->close_status = 1;
                    $getreminder->updated_by = $authUser->user_id;
                    $getreminder->save();
                    /* --end UPDATE REMINDER is_closed -- */                    
                  }

                  $result[$key][$_start_at->format('Ymd')] = $reminder->title . ': email sent successfully';                  
                } /* --end if EMAIL SENT -- */                         
              } /* --end if START DATE MATCH -- */
            } /* --end if SCHEDULE-Does not repeat -- */
            else
            {
              if($_schedule == 'Every second week')
                $_day = $_start_at->format('l');

              $_startdate = $_start_at->format('d');

              $_startmonth = ($_sent_at == '') ? $_start_at->format('m') : Carbon::parse($_sent_at)->format('m');
              $_startyear = ($_sent_at == '') ? $_start_at->format('Y') : Carbon::parse($_sent_at)->format('Y');

              $_currentmonth = Carbon::now()->format('m');
              $_currentyear = Carbon::now()->format('Y');

              for($y = $_startyear; $y <= $_currentyear; $y++)
              {
                for($m = $_startmonth; $m <= $_currentmonth; $m++)
                {
                  $_date = $y . '/' . $m . '/' . (($_schedule == 'Every second week') ? '01' : $_startdate);  
                  $_format_date = Carbon::parse($_date)->format('Ymd');
                  
                  if($_schedule == 'Every second week')
                    $_next_date = date('Ymd',strtotime($_date .' second ' .$_day));
                  else if($_schedule == 'Every second month')
                    $_next_date = date('Ymd',strtotime('2 months', strtotime($_date)));
                  else if($_schedule == 'Every quarterly')
                    $_next_date = date('Ymd',strtotime('3 months', strtotime($_date)));
                  else
                    $_next_date = $_format_date;
                
                  if($_next_date <= Carbon::now()->format('Ymd')) 
                  {                                        
                    $_is_send_email = false;
                    if($_sent_at == '')
                    {
                      $_sent_at = $_next_date;
                      $_is_send_email = true;
                    } /* --end if SENT_AT null -- */   
                    else
                    {
                      if($_next_date > Carbon::parse($_sent_at)->format('Ymd'))
                      {
                        $_sent_at = $_next_date;
                        $_is_send_email = true;
                      } /* --end if SENT_AT MATCH -- */
                    } /* --end if SENT_AT not null -- */  

                    if($_is_send_email)
                    {                     
                      $_sent = $this->sendReminderEmail($reminder, $sender, $authUser, $send_test_text_yes, $send_test_text_no, $_sent_at);
                    
                      if($_sent == 'success')
                        $result[$key][$_sent_at] = $reminder->title . ': email sent successfully'; 
                    } /* --end if SEND EMAIL -- */                                                 
                  } /* --end if DATE MATCH -- */
                } /* --end for MONTH -- */
              } /* --end for YEAR -- */
            } /* --end else SCHEDULE-Does not repeat -- */
          } /* --end if START DATE -- */          
        } /* --end for REMINDERS -- */

        if($result)
          /* -- LOG -- */
          $this->addLog($authUser, 'reminder-email', 
            [
              'Reminder' => $result
            ]
          );
          /* --end LOG -- */

        return $result;
      }
      catch (\Exception $e)        
      {dd($e);
          return $e->getMessage();
      }            
    }
    /* --end scheduleReminderEmail -- */

    /* -- sendReminderEmail -- */
    public function sendReminderEmail($reminder, $sender, $authUser, $send_test_text_yes, $send_test_text_no, $_sent_at = '')
    {
        try {
            ProcessReminderEmailJob::dispatch(
                $reminder,
                $sender,
                $authUser,
                $send_test_text_yes,
                $send_test_text_no,
                $_sent_at
            )->onQueue('reminderemails');

            return 'success'; // keep same behavior
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    /* --end sendReminderEmail -- */

    /* -- processReminderEmail -- */
    //public function sendReminderEmail($reminder, $sender, $authUser, $send_test_text_yes, $send_test_text_no, $_sent_at = '')
    public function processReminderEmail($reminder, $sender, $authUser, $send_test_text_yes, $send_test_text_no, $send_to_client, $_sent_at = '')
    {                  
      try 
      {
        /* -- GET ACTION -- */
        if(!empty($reminder->reminderactionoption))
        {
          $actionoption = $reminder->reminderactionoption;
          $_action_id = $actionoption->id;
          $_action_name = $actionoption->action_name;
        }
        else if($send_test_text_no == 'nosave')
        {
           $_action_id = $reminder->reminder_action;
           $_action_name = $reminder->sel_action_name;
        }
        /* --end GET ACTION -- */

        $_title = $reminder->title;
        $_dk_title = $reminder->dk_title;

        // if($send_test_text_no == 'nosave')
        //   $_content = $reminder->content;
        // else       
        //   $_content = $reminder->content;

        $_content = $reminder->content;
        $_dk_content = $reminder->dk_content;

        $_year = $reminder->year;
        $_period = $reminder->period;
        
        /* -- GET VAT REG. MAIN -- */
        $vatregmain = "";
        $client = "";
        $vatregs = [];
        if($reminder->vatregmain != null)
        {
          $vatregmain = $reminder->vatregmain;
          $client = $vatregmain->client;
          $vatregs = $vatregmain->vatreg;
        }        
        /* --end VAT REG. MAIN -- */        

        $return_msg = 0;

        $_exists = false;
        $_vatreg_folder = "";
        $_break_line = "";
        $vat_reg_ids = [];

        $data = [];
        $send_to;
        $dvuser = "";

        if(!empty($vatregs)) 
        {
          foreach($vatregs as $vatreg)
          {                  
            /* -- if ACTION -- */
            if(strtolower($_action_name) == 'no data in folder')
            {
              if(count($vatreg->vatreturns) == 0)
                $_exists = true;
            }
            else if(strtolower($_action_name) == 'upload missed') 
            {
              if(count($vatreg->vatreturnfiles) == 0)
                $_exists = true;
            }
            else if(strtolower($_action_name) == 'pivs not uploaded') 
            {
              if(count($vatreg->pivs) == 0)
                $_exists = true;
            }
            else if(strtolower($_action_name) == 'cash account statement not uploaded') 
            {
              if(count($vatreg->cas) == 0)
                $_exists = true;
            }
            else if(strtolower($_action_name) == 'duty deferment account not uploaded') 
            {
              if(count($vatreg->dda) == 0)
                $_exists = true;
            }
            else if(strtolower($_action_name) == 'general reminder') 
            {
              $_vatreg_folder = $_action_name;
              $_exists = true;
            }
            /* --end if ACTION -- */

            if($_exists)
            {
              $vat_reg_ids[] = $vatreg->id;

              $frequency = $this->getFrequency($vatreg->general_periods);
              if($_vatreg_folder != "")                
                $_break_line = "<br>";
              
              $_vatreg_folder .= $_break_line . Carbon::parse($vatreg->service_start)->format('M y') . '-' . Carbon::parse($vatreg->service_start)->addMonth(($frequency-1))->format('M y');
            }
          } /* --end for VAT REG. -- */
        } /* --end if VAT REG. -- */
        else
        {
          if(strtolower($_action_name) == 'general reminder' || $send_test_text_no == 'nosave')
            $_vatreg_folder = strtolower($_action_name);
        } /* --end else VAT REG. -- */

        if($_vatreg_folder != "")
        {          
          /* -- GET ADMIN USER -- */        
          $sender_dvuser = $sender->dvuser;

          $sender_firstname = $sender_dvuser->firstname;
          $sender_designation = $sender_dvuser->designation;
          /* --end GET ADMIN USER -- */

          /* Send reminder email based on condition */          
          if($send_test_text_yes == 'send_test_reminder' || $send_test_text_no == 'nosave')
          {            
            $send_to = $authUser->email;
            
            $emaillang = $authUser->lang;
           
            $period_process = $this->periodProcessForm($_period, $_year, $emaillang);
            $period_text = $period_process['period_label'];

            // $period_text = str_replace(
            //   array(
            //     "no_1",
            //     "no_2",
            //     "no_3",
            //     "no_4",
            //     "no_5",
            //     "no_6",
            //     "uk_1",
            //     "uk_2",
            //     "uk_3",
            //     "uk_4",
            //     "uk_5",
            //     "uk_6",
            //     "uk_7",
            //     "uk_8",
            //     "uk_9",
            //     "uk_10",
            //     "uk_11",
            //     "uk_12"
            //   ),
            //   array(
            //     __('january-february', [], $emaillang),
            //     __('march-april', [], $emaillang),
            //     __('may-june', [], $emaillang),
            //     __('july-august', [], $emaillang),
            //     __('september-october', [], $emaillang),
            //     __('november-december', [], $emaillang),
            //     __('january-february-march', [], $emaillang),
            //     __('february-march-april', [], $emaillang),
            //     __('march-april-may', [], $emaillang),
            //     __('april-may-june', [], $emaillang),
            //     __('may-june-july', [], $emaillang),
            //     __('june-july-august', [], $emaillang),
            //     __('july-august-september', [], $emaillang),
            //     __('august-september-october', [], $emaillang),
            //     __('september-october-november', [], $emaillang),
            //     __('october-november-december', [], $emaillang),
            //     __('november-december-january', [], $emaillang),
            //     __('december-january-february', [], $emaillang)
            //   ),           
            // $_period);
                    
            $final_title = str_replace(
              array(
                "[period]",
                "[username]"
              ),
              array(
                $period_text,
                $authUser->firstname . " " . $authUser->lastname
              ),           
            (($emaillang == 'dk') ? $_dk_title : $_title));

            $final_content = str_replace(
              array(
                "[period]",
                "[username]"
              ),
              array(
                $period_text,
                $authUser->firstname . " " . $authUser->lastname
              ),  
            (($emaillang == 'dk') ? $_dk_content : $_content));           
            
            $data = [
              'subject' => $final_title,
              'lang' => $authUser->lang,
              'app_name' => config('app.name'),                      
              'user_firstname' => $authUser->firstname,
              'user_lastname' => $authUser->lastname,
              'sender_firstname' => $sender_firstname,
              'sender_designation' => $sender_designation,
              'message' => (($_vatreg_folder == "general reminder"  || $send_test_text_no == 'nosave') ? '' : ($_vatreg_folder . "<br>")) . $final_content,
              'attachment' => [], 
              'align' => 'left'
            ];
           
            $mailsent = $this->SendEmail($data,$send_to,$_action_name);
          }
          else
          {
            $reminderusers = $reminder->reminderuser;
  
            foreach($reminderusers as $reminderuser)
            {
              //dd($reminder->send_to_client[$reminderuser->user_id], $reminderuser);
              $user = $reminderuser->user;
              $dvuser = $user->dvuser;
              $roles = $user->roles;
              $_user_lang = $dvuser->lang;

              $send_to = $user->email;
              
              $period_process = $this->periodProcessForm($_period, $_year, $_user_lang);
              $period_text = $period_process['period_label'];

              // $period_text = str_replace(
              //   array(
              //     "no_1",
              //     "no_2",
              //     "no_3",
              //     "no_4",
              //     "no_5",
              //     "no_6",
              //     "uk_1",
              //     "uk_2",
              //     "uk_3",
              //     "uk_4",
              //     "uk_5",
              //     "uk_6",
              //     "uk_7",
              //     "uk_8",
              //     "uk_9",
              //     "uk_10",
              //     "uk_11",
              //     "uk_12"
              //   ),
              //   array(
              //     __('january-february', [], $_user_lang),
              //     __('march-april', [], $_user_lang),
              //     __('may-june', [], $_user_lang),
              //     __('july-august', [], $_user_lang),
              //     __('september-october', [], $_user_lang),
              //     __('november-december', [], $_user_lang),
              //     __('january-february-march', [], $_user_lang),
              //     __('february-march-april', [], $_user_lang),
              //     __('march-april-may', [], $_user_lang),
              //     __('april-may-june', [], $_user_lang),
              //     __('may-june-july', [], $_user_lang),
              //     __('june-july-august', [], $_user_lang),
              //     __('july-august-september', [], $_user_lang),
              //     __('august-september-october', [], $_user_lang),
              //     __('september-october-november', [], $_user_lang),
              //     __('october-november-december', [], $_user_lang),
              //     __('november-december-january', [], $_user_lang),
              //     __('december-january-february', [], $_user_lang)
              //   ),           
              // $_period);
               
              $final_title = str_replace(
                array(
                  "[period]",
                  "[username]"
                ),
                array(
                  $period_text,
                  $dvuser->firstname . " " . $dvuser->lastname
                ),           
              (($_user_lang == 'dk') ? $_dk_title : $_title));

              $final_content = str_replace(
                array(
                  "[period]",
                  "[username]"
                ),
                array(
                  $period_text,
                  $dvuser->firstname . " " . $dvuser->lastname
                ),  
              (($_user_lang == 'dk') ? $_dk_content : $_content));   

              $data = [
                'subject' => $final_title,
                'lang' => $_user_lang,
                'app_name' => config('app.name'),                      
                'user_firstname' => $dvuser->firstname,
                'user_lastname' => $dvuser->lastname,
                'sender_firstname' => $sender_firstname,
                'sender_designation' => $sender_designation,
                'message' => (($_vatreg_folder == "general reminder") ? '' : ($_vatreg_folder . "<br>")) . $final_content,
                'attachment' => [], 
                'align' => 'left'
              ];
                            
              $reminderuser_client_users = UserClient::with(['client'])->where('user_id', $reminderuser->user_id)->get();
              
              foreach($reminderuser_client_users as $reminderuser_client_user)
              {   
                if (array_key_exists($reminderuser->user_id, $send_to_client)) 
                {             
                  //if(in_array($reminderuser_client_user->client_id, $reminder->send_to_client[$reminderuser->user_id]))
                  if(in_array($reminderuser_client_user->client_id, $send_to_client[$reminderuser->user_id]))
                  {                  
                    $clientname_final_title = str_replace(
                      array(
                        "[client_name]"
                      ),
                      array(
                        $reminderuser_client_user->client->client_name
                      ),           
                    "[client_name] - " . $final_title);
                    $data['subject'] = $clientname_final_title;

                    $clientname_final_content = str_replace(
                      array(
                        "[client_name]"
                      ),
                      array(
                        $reminderuser_client_user->client->client_name
                      ),  
                    $final_content);
                    $data['message'] = (($_vatreg_folder == "general reminder") ? '' : ($_vatreg_folder . "<br>")) . $clientname_final_content;

                    $mailsent = $this->SendEmail($data,$send_to,$_action_name);

                    // if($mailsent == 'premtest')            
                    // {
                    //   \Log::info("Reminder email sent to : " . $send_to);          
                    //   $return_msg++;
                    // }
                    // else 
                    if($mailsent)            
                    {
                      $return_msg++;

                      $email_headers = $mailsent->getOriginalMessage()->getHeaders();
                      $message_id = $email_headers->getHeaderBody('X-SES-Message-ID');                     
                      if ($message_id)
                      {
                        $email_sent_to = $email_headers->getHeaderBody('To');
                        $email_sent_subject = $email_headers->getHeaderBody('Subject');

                        if($dvuser != "")             
                          $uname = $dvuser->firstname . ' ' . $dvuser->lastname;             
                        else             
                          $uname = $authUser->firstname . ' ' . $authUser->lastname;
                       
                        if(empty($vat_reg_ids))
                        {
                          if($send_test_text_yes != 'send_test_reminder' || $send_test_text_no != 'nosave')
                          {
                            $period_process = $this->periodProcessForm($_period, $_year, $_user_lang);
                            
                            // $period_month = str_replace(
                            //   array(
                            //     "no_1",
                            //     "no_2",
                            //     "no_3",
                            //     "no_4",
                            //     "no_5",
                            //     "no_6",
                            //     "uk_1",
                            //     "uk_2",
                            //     "uk_3",
                            //     "uk_4",
                            //     "uk_5",
                            //     "uk_6",
                            //     "uk_7",
                            //     "uk_8",
                            //     "uk_9",
                            //     "uk_10",
                            //     "uk_11",
                            //     "uk_12"
                            //   ),
                            //   array(
                            //     '01',
                            //     '03',
                            //     '05',
                            //     '07',
                            //     '09',
                            //     '11',
                            //     '01',
                            //     '02',
                            //     '03',
                            //     '04',
                            //     '05',
                            //     '06',
                            //     '07',
                            //     '08',
                            //     '09',
                            //     '10',
                            //     '11',
                            //     '12'
                            //   ),           
                            // $_period);

                            $vatreg = VATRegistration::with(['client'])
                                        ->where('client_id', $reminderuser_client_user->client->id)
                                        //->where('service_start', $_year . '-' . $period_month . '-01')
                                        ->where('service_start', $period_process['service_start'])
                                        ->first();

                            $emailNotification = new EmailNotification;
                            $emailNotification->vat_reg_id = ($vatreg) ? $vatreg->id : NULL; 
                            $emailNotification->message_id = $message_id;   
                            $emailNotification->subject = $email_sent_subject;                   
                            $emailNotification->name = $uname;            
                            $emailNotification->email = ($email_sent_to) ? $email_sent_to[0]->getAddress() : '';     
                            $emailNotification->sent_by = $authUser->user_id;
                            $emailNotification->reminder_action_id = $_action_id;
                            
                            $emailNotification->save();
                          }
                        }
                      }              
                    }                  
                  } //only selected CLIENT
                } //if user id exists  as key  
                // else
                // {
                //   \Log::info("user id not exists in sent_to_client: " . $reminderuser->user_id);
                // }  
              } //for user clients
            } //for loop
          }
         
          // if($mailsent == 'premtest')            
          // {
          //   $return_msg++;
          // }
          // else 
          if($mailsent)            
          {
            $return_msg++;

            $email_headers = $mailsent->getOriginalMessage()->getHeaders();
            $message_id = $email_headers->getHeaderBody('X-SES-Message-ID');                     
            if ($message_id)
            {
              $email_sent_to = $email_headers->getHeaderBody('To');
              $email_sent_subject = $email_headers->getHeaderBody('Subject');

              if($dvuser != "")             
                $uname = $dvuser->firstname . ' ' . $dvuser->lastname;             
              else             
                $uname = $authUser->firstname . ' ' . $authUser->lastname;
             
              if(!empty($vat_reg_ids))
              {
                foreach($vat_reg_ids as $vat_reg_id)
                {
                  $emailNotification = new EmailNotification;
                  $emailNotification->vat_reg_id = $vat_reg_id; 
                  $emailNotification->message_id = $message_id;   
                  $emailNotification->subject = $email_sent_subject;                     
                  $emailNotification->name = $uname;             
                  $emailNotification->email = ($email_sent_to) ? $email_sent_to[0]->getAddress() : '';     
                  $emailNotification->sent_by = $authUser->user_id;
                  $emailNotification->reminder_action_id = $_action_id;
                  
                  $emailNotification->save(); 
                }
              }  
              /*           
              else if($send_test_text_yes != 'send_test_reminder' || $send_test_text_no != 'nosave')
              {
                $emailNotification = new EmailNotification;
                $emailNotification->vat_reg_id = NULL; 
                $emailNotification->message_id = $message_id;   
                $emailNotification->subject = $email_sent_subject;                   
                $emailNotification->name = $uname;            
                $emailNotification->email = ($email_sent_to) ? $email_sent_to[0]->getAddress() : '';     
                $emailNotification->sent_by = $authUser->user_id;
                $emailNotification->reminder_action_id = $_action_id;
                
                $emailNotification->save();
              }
              */
            }              
          }          

          /* -- CREATE REMINDER HISTORY -- */
          if($send_test_text_yes != 'send_test_reminder' || $send_test_text_no != 'nosave')
          {
            $reminderHistory = ReminderHistory::updateOrCreate(                     
              [
                'reminder_id' => $reminder->id,                   
                'sent_at' => ($_sent_at == '') ? Carbon::now()->format('Y-m-d') : Carbon::parse($_sent_at)->format('Y-m-d'),
                'status' => 1,
                'created_by' => $authUser->user_id
              ]
            );
          }
          /* --end CREATE REMINDER HISTORY -- */
                    
        } /* --end if VAT REG. FOLDER not null -- */ 
        
        if($return_msg > 0)
          return 'success';  
      }
      catch (\Exception $e)        
      {
          return $e->getMessage();
      }            
    }
    /* --end processReminderEmail -- */

    // Example controller method
    public function periodProcessForm($_period, $_year, $emaillang)
    {
        // $_period = $request->input('period'); // e.g., 'uk_3'
        // $_year = $request->input('year');     // e.g., '2026'
        // $emaillang = app()->getLocale();      // current language

        // 1️⃣ Define period keys and their readable periods
        $periodLabels = [
            // single months
            'jan' => __('January', [], $emaillang),
            'feb' => __('February', [], $emaillang),
            'mar' => __('March', [], $emaillang),
            'apr' => __('April', [], $emaillang),
            'may' => __('May', [], $emaillang),
            'jun' => __('June', [], $emaillang),
            'jul' => __('July', [], $emaillang),
            'aug' => __('August', [], $emaillang),
            'sep' => __('September', [], $emaillang),
            'oct' => __('October', [], $emaillang),
            'nov' => __('November', [], $emaillang),
            'dec' => __('December', [], $emaillang),

            // combined periods → use first month as start
            'jan-feb' => __('January - February', [], $emaillang),
            'mar-apr' => __('March - April', [], $emaillang),
            'may-jun' => __('May - June', [], $emaillang),
            'jul-aug' => __('July - August', [], $emaillang),
            'sep-oct' => __('September - October', [], $emaillang),
            'nov-dec' => __('November - December', [], $emaillang),
            'jan-mar' => __('January - March', [], $emaillang),
            'apr-jun' => __('April - June', [], $emaillang),
            'jul-sep' => __('July - September', [], $emaillang),
            'oct-dec' => __('October - December', [], $emaillang),
            'jan-jun' => __('January - June', [], $emaillang),
            'jul-dec' => __('July - December', [], $emaillang),
            'jan-dec' => __('January - December', [], $emaillang),
            'feb-apr' => __('February - April', [], $emaillang),
            'mar-may' => __('March - May', [], $emaillang),
            'may-jul' => __('May - July', [], $emaillang),
            'jun-aug' => __('June - August', [], $emaillang),
            'aug-oct' => __('August - October', [], $emaillang),
            'sep-nov' => __('September - November', [], $emaillang),
            'nov-jan' => __('November - January', [], $emaillang),
            'dec-feb' => __('December - February', [], $emaillang),
        ];

        // 2️⃣ Map country-period keys to period labels
        $countryPeriods = [
            'at' => ['1'=>'jan','2'=>'feb','3'=>'mar','4'=>'apr','5'=>'may','6'=>'jun','7'=>'jul','8'=>'aug','9'=>'sep','10'=>'oct','11'=>'nov','12'=>'dec'],
            'be' => ['1'=>'jan-mar','2'=>'apr-jun','3'=>'jul-sep','4'=>'oct-dec'],
            'cz' => ['1'=>'jan','2'=>'feb','3'=>'mar','4'=>'apr','5'=>'may','6'=>'jun','7'=>'jul','8'=>'aug','9'=>'sep','10'=>'oct','11'=>'nov','12'=>'dec'],
            'fi' => ['1'=>'jan','2'=>'feb','3'=>'mar','4'=>'apr','5'=>'may','6'=>'jun','7'=>'jul','8'=>'aug','9'=>'sep','10'=>'oct','11'=>'nov','12'=>'dec'],
            'de' => ['1'=>'jan-mar','2'=>'apr-jun','3'=>'jul-sep','4'=>'oct-dec','5'=>'jan-jun','6'=>'jul-dec','7'=>'jan-dec','8'=>'jan','9'=>'feb','10'=>'mar','11'=>'apr','12'=>'may','13'=>'jun','14'=>'jul','15'=>'aug','16'=>'sep','17'=>'oct','18'=>'nov','19'=>'dec'],
            'dk' => ['1'=>'jan-mar','2'=>'apr-jun','3'=>'jul-sep','4'=>'oct-dec','5'=>'jan-jun','6'=>'jul-dec'],
            'fr' => ['1'=>'jan','2'=>'feb','3'=>'mar','4'=>'apr','5'=>'may','6'=>'jun','7'=>'jul','8'=>'aug','9'=>'sep','10'=>'oct','11'=>'nov','12'=>'dec'],
            'ie' => ['1'=>'jan-feb','2'=>'mar-apr','3'=>'may-jun','4'=>'jul-aug','5'=>'sep-oct','6'=>'nov-dec'],
            'it' => ['1'=>'jan-mar','2'=>'apr-jun','3'=>'jul-sep','4'=>'oct-dec'],
            'lu' => ['1'=>'jan','2'=>'feb','3'=>'mar','4'=>'apr','5'=>'may','6'=>'jun','7'=>'jul','8'=>'aug','9'=>'sep','10'=>'oct','11'=>'nov','12'=>'dec'],
            'nl' => ['1'=>'jan-mar','2'=>'apr-jun','3'=>'jul-sep','4'=>'oct-dec'],
            'no' => ['1'=>'jan-feb','2'=>'mar-apr','3'=>'may-jun','4'=>'jul-aug','5'=>'sep-oct','6'=>'nov-dec'],
            'pl' => ['1'=>'jan','2'=>'feb','3'=>'mar','4'=>'apr','5'=>'may','6'=>'jun','7'=>'jul','8'=>'aug','9'=>'sep','10'=>'oct','11'=>'nov','12'=>'dec'],
            'pt' => ['1'=>'jan','2'=>'feb','3'=>'mar','4'=>'apr','5'=>'may','6'=>'jun','7'=>'jul','8'=>'aug','9'=>'sep','10'=>'oct','11'=>'nov','12'=>'dec'],
            'es' => ['1'=>'jan-mar','2'=>'apr-jun','3'=>'jul-sep','4'=>'oct-dec'],
            'se' => ['1'=>'jan-mar','2'=>'apr-jun','3'=>'jul-sep','4'=>'oct-dec'],
            'ch' => ['1'=>'jan-mar','2'=>'apr-jun','3'=>'jul-sep','4'=>'oct-dec'],
            'uk' => ['1'=>'jan-mar','2'=>'feb-apr','3'=>'mar-may','4'=>'apr-jun','5'=>'may-jul','6'=>'jun-aug','7'=>'jul-sep','8'=>'aug-oct','9'=>'sep-nov','10'=>'oct-dec','11'=>'nov-jan','12'=>'dec-feb'],
            'us' => ['1'=>'jan-mar','2'=>'apr-jun','3'=>'jul-sep','4'=>'oct-dec'],
        ];

        // 3️⃣ Map period key to start month for DB
        $periodStartMonth = [
            'jan' => '01', 'feb' => '02', 'mar' => '03', 'apr' => '04', 'may' => '05', 'jun' => '06',
            'jul' => '07', 'aug' => '08', 'sep' => '09', 'oct' => '10', 'nov' => '11', 'dec' => '12',
            'jan-feb' => '01','mar-apr' => '03','may-jun' => '05','jul-aug' => '07','sep-oct' => '09','nov-dec' => '11',
            'jan-mar' => '01','apr-jun' => '04','jul-sep' => '07','oct-dec' => '10','jan-jun' => '01','jul-dec' => '07',
            'jan-dec' => '01','feb-apr' => '02','mar-may' => '03','may-jul' => '05','jun-aug' => '06','aug-oct' => '08',
            'sep-nov' => '09','nov-jan' => '11','dec-feb' => '12',
        ];        

        // 5️⃣ Resolve the submitted period
        $resolved = $this->resolvePeriod($_period, $countryPeriods, $periodLabels, $periodStartMonth);

        if (!$resolved['month']) {
            throw new \Exception("Invalid period key: $_period");
        }

        $serviceStart = $_year . '-' . $resolved['month'] . '-01';
        
        // 7️⃣ Return translated period for frontend + DB results
        return //response()->json(
          [
            'period_label' => $resolved['label'],
            'service_start' => $serviceStart            
          ];
        //);
    }

    // 4️⃣ Function to resolve period key
    public function resolvePeriod($_period, $countryPeriods, $periodLabels, $periodStartMonth) 
    {
        if (preg_match('/^([a-z]{2})_(\d+)$/', $_period, $matches)) {
            $country = $matches[1];
            $num = $matches[2];

            if (isset($countryPeriods[$country][$num])) {
                $labelKey = $countryPeriods[$country][$num];
                return [
                    'label' => $periodLabels[$labelKey] ?? null,
                    'month' => $periodStartMonth[$labelKey] ?? null,
                ];
            }
        }
        return ['label'=>null, 'month'=>null];
    }

    public function SendEmail($data, $send_to, $_action_name)
    {
      // if(strtolower($_action_name) == 'general reminder') 
      // {                            
      //   return "premtest";
      // }

      if(strtolower($_action_name) == 'no data in folder')
        $email_data = new ReminderNoDataInFolderEmail($data);
      else if(strtolower($_action_name) == 'upload missed') 
        $email_data = new ReminderUploadMissedEmail($data);
      else if(strtolower($_action_name) == 'pivs not uploaded') 
        $email_data = new ReminderPivsNotUploadedEmail($data);
      else if(strtolower($_action_name) == 'cash account statement not uploaded') 
        $email_data = new ReminderCasNotUploadedEmail($data);
      else if(strtolower($_action_name) == 'duty deferment account not uploaded') 
        $email_data = new ReminderDdaNotUploadedEmail($data);
      else if(strtolower($_action_name) == 'general reminder') 
        $email_data = new ReminderGeneral($data);
      /* --end if ACTION -- */ 

      $mail_sent = Mail::to($send_to)                        
        ->send($email_data);

      return $mail_sent;  
    }

    /* -- List Excel Columns -- */
    public function listExcelColumns()
    {
      /* -- EXCEL COLUMN LIST -- */
      $excelColumns = [
        "A:tax_code" => "Tax code",
        "B:invoice_date" => "Invoice date", 
        "C:invoice_number" => "Invoice number", 
        "D:currency_code" => "Currency code", 
        "E:total_net_invoice_currency" => "Total NET (invoice currency)", 
        "F:vat_rate" => "VAT rate", 
        "G:total_vat_invoice_currency" => "Total VAT (invoice currency)", 
        "H:total_gross_invoice_currency" => "Total GROSS (invoice currency)", 
        "I:local_currency_code" => "Local currency code", 
        "J:exchange_rate" => "Exchange rate", 
        "K:total_net_local_currency" => "Total NET (local currency)",         
        "L:total_vat_local_currency" => "Total VAT (local currency)", 
        "M:total_gross_local_currency" => "Total GROSS (local currency)", 
        "N:n" => "N", 
        "O:o" => "O", 
        "P:p" => "P", 
        "Q:q" => "Q", 
        "R:name" => "Name", 
        "S:vat_number_if_applicable" => "VAT number (if applicable)",
        "T:client_street" => "Client/Customer street", 
        "U:client_house_and_office_no" => "Client/Customer house and office no.", 
        "V:client_city" => "Client/Customer city", 
        "W:postal_code" => "Postal code", 
        "X:country_code" => "Country code"
      ];
      /* --end EXCEL COLUMN LIST -- */

      /* -- RETURN EXCEL COLUMN LIST -- */
      return $excelColumns; 
      /* --end RETURN EXCEL COLUMN LIST -- */
    } 
    /* --end List Excel Columns -- */

    // /* -- List VAT Control Excel Columns -- */
    // public function listVATControlExcelColumns($type = 'vatcontrol')
    // {
    //   /* -- VAT CONTROL EXCEL COLUMN LIST -- */
    //   $excelColumns = [       
    //     "A:invoice_number" => "Invoice number",        
    //     "B:total_vat_invoice_currency" => "Total VAT (invoice currency)"        
    //   ];
    //   /* --end VAT CONTROL EXCEL COLUMN LIST -- */

    //   /* -- RETURN VAT CONTROL EXCEL COLUMN LIST -- */
    //   return $excelColumns; 
    //   /* --end RETURN VAT CONTROL EXCEL COLUMN LIST -- */
    // } 
    // /* --end List VAT Control Excel Columns -- */

    public function specialRowArithmeticCalculation($value1, $value2, $operator)
    {
      switch ($operator) { 
          case "+": 
              return $value1 + $value2; 
              break; 
          case "-": 
              return $value1 - $value2; 
              break; 
          case "*": 
              return $value1 * $value2; 
              break; 
          case "/": 
              return $value1 / $value2; 
              break;                                        
          default: 
              return 0; 
              break; 
      }
    }
   
    /* -- EXTRACT TEXT VIA OPENAI -- */   
    public function extractTextViaOpenAi($file_type, $file = NULL)
    {      
      try
      {  
        $template = 'invoice-full';      

        if($file)     
        {
          if(is_array($file))
            $file_content = $file['file'];
          else
            $file_content = $file->get();
          
          $textPdf = Text::pdf($file_content);
          //dd($textPdf);
          //Default - gpt-3.5-turbo-instruct  (TURBO_INSTRUCT)     
          //$result = ReceiptScanner::scan(text: $textPdf->toString(), model: Model::GPT35_TURBO_0125, template: $template, asArray: true);   
          //$template = 'coinvoice';
          //$result = ReceiptScanner::scan(text: $textPdf->toString(), model: Model::GPT4_O, template: $template, asArray: true);   
          $result = ReceiptScanner::scan(text: $textPdf->toString(), model: Model::TURBO_INSTRUCT, template: $template, asArray: true);   
          //dd($result);
        }
        else
        {
          $sampleOutputClass =  new SampleOutputClass();
          $result = $sampleOutputClass->pdftextoutput('ci-20');
        }

        if($file_type == 'ci')
        {  
          if(is_array($result))
            $ci_details = $this->arrayRecursive($file_type, $result);     
          else
          {
            dd("dfdsfd");
            $ci_details = [];
//             $storage_path = storage_path('app/public/invoices/01 commercial invoices/');
//             if(is_array($file))
//               $file_name = $file['downloadurl'];
//             else
//               $file_name = $storage_path.'AID STUDIO - commercial_invoice_129.pdf';
   
//             $pdf = new \Spatie\PdfToImage\Pdf($file_name);
//         dd($pdf);
        
//         $noofpages = $pdf->saveAllPagesAsImages($storage_path);
// dd($noofpages);
          }

          $commercial_invoice_nos = '';
          foreach ($ci_details as $key => $ci_detail) 
          {
            if(is_array($ci_detail))
            {

            }
            else
            {              
              $ci_array = explode(', ', $ci_detail);
             
              foreach ($ci_array as $value) 
              {
                if ((stripos($value, "-") !== false))
                {
                  if((stripos($key, "references") !== false))
                    $commercial_invoice_nos .= $value . ', ';
                  else
                  {
                    $start_end_no = explode('-', $value);               
                    for($i = trim($start_end_no[0]); $i <= trim($start_end_no[1]); $i++)
                      $commercial_invoice_nos .= $i . ', ';    
                  }                        
                }
                else                
                  $commercial_invoice_nos .= $value . ', ';
              }              
            }            
          }

          $sale_invoice_nos = rtrim($commercial_invoice_nos, ', ');
         
          $invoice_count = 0;
          if($sale_invoice_nos != '')            
            $invoice_count = count(explode(', ', $sale_invoice_nos));
          
          return [
            'sale_invoice_nos' => $sale_invoice_nos,
            'invoice_count' => $invoice_count
          ];         
        }
        else if($file_type == 'pivs' || $file_type == 'c79')
        {                 
          $month_total = $this->arrayRecursive($file_type, $result);

          return $month_total;
        }

        return null;           
      }
      catch (\Exception $e)        
      {        
dd($e);
        return $e->getMessage();
      } 
    }

    function arrayRecursive($file_type, $array, $prefix = '') 
    {
      $result = [];

      foreach ($array as $key => $value) 
      {  
        $newKey = $prefix ? $prefix . '.' . $key : $key;

        if (is_array($value))         
          $result = array_merge($result, $this->arrayRecursive($file_type, $value, $newKey));        
        else 
        {
          if($file_type == 'ci')
          {        

            if( ((stripos($key, "commercial") !== false) || (stripos($key, "sale") !== false && stripos($key, "invoice") !== false)) ||
              (stripos($prefix, "commercial") !== false && stripos($prefix, "sale") !== false && stripos($prefix, "invoice") !== false) ||
              (stripos($prefix, "consolidated") !== false && stripos($prefix, "invoice") !== false && stripos($prefix, "number") !== false) || 
              (stripos($key, "samlefaktura") !== false) ||
              (stripos($prefix, "Salgsfaktura") !== false) || 
              (stripos($prefix, "order") !== false && stripos($prefix, "number") !== false) ||
              (stripos($prefix, "references") !== false) ||
              (stripos($prefix, "related") !== false && stripos($prefix, "invoice") !== false) ||
              (stripos($prefix, "reference") !== false && stripos($prefix, "number") !== false) ||
              (stripos($prefix, "samlefakturaen") !== false && stripos($prefix, "dækker") !== false)
            )
            {
              if ((stripos($value, ":") !== false))
              {
                //$receiptscanner = ReceiptScanner::scan(text: $value, template: 'invoice-full', asArray: true);
                //$receiptscanner = ReceiptScanner::scan(text: $value, model: Model::GPT35_TURBO_0125, template: 'invoice-full', asArray: true); 
               
                $sampleOutputClass =  new SampleOutputClass();
                $receiptscanner = $sampleOutputClass->pdftextoutput('ci-14-1');
              
                if(is_array($receiptscanner))              
                  $result = array_merge($result, $this->arrayRecursive($file_type, $receiptscanner, $newKey));
              } /* --end else HAS : -- */
              else  
              {  
                if(stripos($prefix, "references") !== false)        
                {     
                  $last_no = substr($value, (strlen($value) - 2), strlen($value));
                 
                  if($last_no == "-1")                 
                    $result[$newKey] = substr($value, 0, (strlen($value) - 2));                    
                  else
                    $result[$newKey] = $value;
                } 
                else if ((stripos($value, "og") !== false))
                {       
                  preg_match_all("/[^0-9]/", '', $value, $matches);
dd($matches);
                }
                else
                  $result[$newKey] = $value;
              }
            } /* --end else HAS COMMERCIAL, SALE, INVOICE -- */
          } /* --end else COMMERCIAL INVOICES -- */
          else if($file_type == 'pivs' || $file_type == 'c79')
          {
            if( ((stripos($key, 'month') !== false) && (stripos($key, 'total') !== false)) ||
                 ((stripos($key, 'total') !== false) && (stripos($key, 'VAT') !== false) && (stripos($key, 'postponed') !== false)))              
            {              
              $number = preg_replace('/[\'^£$%&*()}{@#~?><>,|=_+¬]/', '', $value);
              
              $result['month_total'] = $number;
              break;
            } /* --end if MONTH TOTAL -- */
          } /* --end else PIVS, C79 -- */
        } /* --end else NOT ARRAY -- */
      } /* --end for ARRAY -- */

      return $result;
    }    
    /* --end EXTRACT TEXT VIA OPENAI -- */

    /* -- READ VAT CHECK -- */
    public function readVATCheckFile($url, $type = NULL)
    {                 
      $spreadsheet = new Spreadsheet();

      $inputFileType = 'Xlsx';           
      $inputFileName = $url;
     
      $reader = IOFactory::createReader($inputFileType);
      $reader->setReadDataOnly(true);

      try 
      {
        $worksheetData = $reader->listWorksheetInfo($inputFileName);    
        
        $unmatched_invoices = [];   
        $matched_user_ids = [];      
        
        foreach ($worksheetData as $worksheet) 
        {
          $sheetName = $worksheet['worksheetName'];
          
          $reader->setLoadSheetsOnly($sheetName);
          $spreadsheet = $reader->load($inputFileName);

          $worksheet = $spreadsheet->getActiveSheet();
          
          $highestRow = $worksheet->getHighestRow(); 
          $highestColumn = $worksheet->getHighestColumn();
         
          $chunkSize = 1000; // Adjust as needed

          $startRow = 2;

          $_firstname_col = 3;
          $_lastname_col = 4;
          $_middlename_col = 5;
          $_designation_col = '';  
          if($sheetName == "Nuværende PEP'ere")
          {      
            $startRow = 4;
      
            $_firstname_col = 1;
            $_lastname_col = 2;
            $_middlename_col = '';
            $_designation_col = 3;    
          }
          else if($sheetName == "Tidligere PEP'ere")
          {             
            $_firstname_col = 0;
            $_lastname_col = 1;
            $_middlename_col = '';
            $_designation_col = 2;    
          }

          do {
            $endRow = min($startRow + $chunkSize - 1, $highestRow);            
            
            // Process chunk of rows
            for ($row = $startRow; $row <= $endRow; $row++)           
            {
              $rowData = $worksheet->rangeToArray('A' . $row . ':' . $highestColumn . $row);

              $firstname = trim($rowData[0][$_firstname_col]);  
              $lastname = trim($rowData[0][$_lastname_col]);  
              $middlename = ($_middlename_col == '') ? '' : trim($rowData[0][$_middlename_col]);  
              $designation = ($_designation_col == '') ? '' : trim($rowData[0][$_designation_col]);

              if($firstname || $lastname || $middlename)
              {                  
                /* SYSTEM INVOICE MATCH CHECK*/
                if($middlename == '')
                  $musers = User::leftJoin('dv_users', function($join) {
                                $join->on('users.id', '=', 'dv_users.user_id');                      
                              })  
                              ->select('dv_users.*')                     
                              ->where('dv_users.firstname', $firstname)
                              ->where('dv_users.lastname', $lastname)
                              ->get();
                else                                
                  $musers = User::leftJoin('dv_users', function($join) {
                                $join->on('users.id', '=', 'dv_users.user_id');                      
                              })  
                              ->select('dv_users.*')                    
                              ->where('dv_users.firstname', $firstname)
                              ->where(
                                function($query) use ($lastname, $middlename) {
                                  return $query
                                      ->where('dv_users.lastname', $lastname)
                                      ->orWhere('dv_users.lastname', $middlename);
                              })
                              ->get();

                if(count($musers) > 0)
                {     
                  foreach ($musers as $muser) 
                  {                  
                    if(!in_array($muser->user_id, $matched_user_ids, true))
                    {
                      array_push($matched_user_ids, $muser->user_id);
                       
                      $matched_users[] = [                    
                        'excel_user' => [
                          'firstname' => $firstname,
                          'lastname' => $lastname,
                          'designation' => $designation
                        ],
                        'user' => $muser,
                      ];  

                      $dvUsers = DVUser::updateOrCreate(
                        ['user_id' => $muser->user_id],
                        [                
                            'compliance_firstname' => $firstname,
                            'compliance_lastname' => $lastname,                              
                            'compliance_designation' => $designation,                     
                            'is_compliance' => 1,
                            'compliance_status' => 1,// 1-PEP, 2-EU, 3-UNSC
                            'is_deleted' => 0
                        ]
                      );
                    }
                  }
                } 
                /* end SYSTEM USER MATCH CHECK*/  
              }  //any names  
            }//chunk for          
            $startRow = $endRow + 1;                              
          } while ($startRow <= $highestRow);            
        }
                          
        return [
          'unmatched_invoices' => $unmatched_invoices
         ];
      }  //try
      catch (\Exception $e) 
      {   dd($e);
        return "error";
      }     
    }
    /* --end READ VAT CHECK -- */

    /* -- executeScript -- */
    public function executeScript()
    {
      try
      {
        // Path to the shell script
        $scriptPath = storage_path('app/public/autocreateftpuser.sh');
        
        // $user1 = "user123";
        // $user2 = "user234";
        // $user3 = "user345";
        //$rootpassword = "dinV@13at";

        $usernames = "user321 test45454 okhujj";

        // Execute the script
        //$output = shell_exec("bash $scriptPath $user1 $user2 $user3 2>&1");
        $output = shell_exec("bash $scriptPath '$usernames' 2>&1");
        //$output = shell_exec("bash $scriptPath $username 2>&1");
 dd($output);
        // Log the output or do something with it
        //\Log::info($output);
 
        // Return a response to the user
        return response("Script executed successfully!");
      }
      catch (\Exception $e)        
      {
        return $e->getMessage();
      }  
    }
    /* --end executeScript -- */

    /* -- Re-match Com. Invoices -- */
    public function rematchComInvoices($client_id, $sample = false)
    {
      try
      {
        $importreconciliationcominvoices = ImportReconciliationComInvoices::with(['vatreg', 'vatreg.client'])
                        ->whereHas('vatreg.client', function ($subquery) use($client_id) {                                        
                            $subquery->where('id', $client_id); //SPORTS
                        })                           
                        ->whereNull('rematch_com_invoice_id')                                                            
                        ->where('invoice_no', 'NOT LIKE', 'SPG-%-NO')                                                
                        ->whereNot('data_from', 'ivf')
                        ->whereNot('data_from', 'swiss')                                             
                        ->get();
      
        foreach($importreconciliationcominvoices as $importreconciliationcominvoice)       
        {           
          //GET MATCH COM. INVOICE ROW
          $query_match_cominvoice = ImportReconciliationComInvoices::with(['vatreg', 'vatreg.client'])
                    ->whereHas('vatreg.client', function ($subquery) use($client_id) {                                        
                        $subquery->where('id', $client_id);
                    })
                    ->whereNull('rematch_com_invoice_id');                    
                   
          if(strtoupper($importreconciliationcominvoice->vatreg->client->client_name) == 'SECOND FEMALE NORGE AS')
          {
            if(Str::startsWith(Str::lower($importreconciliationcominvoice->invoice_no), ['ic']))
            {
              $specific_query = $query_match_cominvoice->where(function ($query) use($importreconciliationcominvoice) {
                                    $query->where('invoice_no', $importreconciliationcominvoice->invoice_no) ;
                                });
            }
            else
            {
              $specific_query = $query_match_cominvoice->where(function ($query) use($importreconciliationcominvoice) {
                                    $query->where('invoice_no', str_replace('IC', '', $importreconciliationcominvoice->invoice_no)) ;
                                });
            }
          }
          else if(strtoupper($importreconciliationcominvoice->vatreg->client->client_name) == 'REXHOLM A/S')
          {            
            $specific_query = $query_match_cominvoice->where(function ($query) use($importreconciliationcominvoice) {

                                $changed_invoice_no = str_replace('PROF', '', $importreconciliationcominvoice->invoice_no);
                                $query->where('invoice_no', $changed_invoice_no)
                                        ->orWhere('invoice_no', $importreconciliationcominvoice->invoice_no);

                                if(str_starts_with($changed_invoice_no, '0'))
                                {
                                  $remove_leading_zero = preg_replace('/^0/', '', $changed_invoice_no);
                                  $query->orWhere('invoice_no', $remove_leading_zero);
                                }
                              });
          }        
          else if(stripos(strtoupper($importreconciliationcominvoice->vatreg->client->client_name), "BECKS") !== false) 
          {          
            $specific_query = $query_match_cominvoice->where(function ($query) use($importreconciliationcominvoice) {

                                $changed_invoice_no = str_replace('NIC', '', $importreconciliationcominvoice->invoice_no);
                                $query->where('invoice_no', $changed_invoice_no)
                                        ->orWhere('invoice_no', $importreconciliationcominvoice->invoice_no);

                                if(str_starts_with($changed_invoice_no, '0'))
                                {
                                  $remove_leading_zero = preg_replace('/^0/', '', $changed_invoice_no);
                                  $query->orWhere('invoice_no', $remove_leading_zero)
                                        ->orWhere('invoice_no', 'NIC' . $remove_leading_zero);
                                }
                              });
          }
          else if(strtoupper($importreconciliationcominvoice->vatreg->client->client_name) == 'DAN-FORM A/S')
          {
            $specific_query = $query_match_cominvoice->where(function ($query) use($importreconciliationcominvoice) {

                                $changed_invoice_no = str_replace('S-NO-', '', $importreconciliationcominvoice->invoice_no);
                                $query->where('invoice_no', $changed_invoice_no)
                                        ->orWhere('invoice_no', $importreconciliationcominvoice->invoice_no)
                                        ->orWhere('invoice_no', str_replace('-', '', $importreconciliationcominvoice->invoice_no));
                              });
          }
          else
          {
             $specific_query = $query_match_cominvoice->where(function ($query) use($importreconciliationcominvoice) {
                                  $query->where('invoice_no', 'LIKE', 'SPG-'. $importreconciliationcominvoice->invoice_no .'-NO%')
                                        ->orWhere('invoice_no', $importreconciliationcominvoice->invoice_no) ;
                              });
          }

          $match_cominvoice = $specific_query->where('id', '!=', $importreconciliationcominvoice->id) 
                                ->whereNotNull('lope_no')
                                ->where('unmatch', 0) 
                                ->first();  
          
            if ($match_cominvoice) 
            {      
              if($match_cominvoice->rematch_com_invoice_id == $importreconciliationcominvoice->id)  
              {
                if($sample)
                  echo $importreconciliationcominvoice->invoice_no . " matched with same ID. So Don't match." . "<br>";
              }
              else
              {
                if(!$match_cominvoice->no_of_split)
                {
                  $match_cominvoice->rematch_com_invoice_id = $importreconciliationcominvoice->id;
                  $match_cominvoice->save();
                }
              }

                $_changed_com_period = false;
                $_changed_sales_period = false;
                if($importreconciliationcominvoice->month_year != $match_cominvoice->month_year)
                {
                    $importreconciliationcominvoice->month_year = $match_cominvoice->month_year;
                    $importreconciliationcominvoice->vat_reg_id = $match_cominvoice->vat_reg_id;
                    $importreconciliationcominvoice->save();

                    $_changed_com_period = true;

                    $salesinvoices = ImportReconciliationSalesInvoices::where('com_invoice_id', $importreconciliationcominvoice->id);
                    if($salesinvoices)
                    {                                
                        $update_sales = ImportReconciliationSalesInvoices::where('com_invoice_id', $importreconciliationcominvoice->id)
                                            ->update(['vat_reg_id' => $match_cominvoice->vat_reg_id]);

                        $_changed_sales_period = true;
                    }
                }

                if($sample)
                  echo $importreconciliationcominvoice->invoice_no . " matched with " . $match_cominvoice->invoice_no .
                      (($_changed_com_period) ? ' ---- changed Com invoice period ' : '') . 
                      (($_changed_sales_period) ? ' ---- changed Sales invoice period ' : '') .
                  "<br>";
            }                            
        }
      }
      catch (\Exception $e)        
      {
        return $e->getMessage();
      }  
    }
    /* --end Re-match Com. Invoices -- */

    /* -- Get Excel Column Letter -- */
    public function getExcelColumnLetter($index) {
      $letter = '';
      while ($index >= 0) {
          $letter = chr($index % 26 + 65) . $letter;
          $index = floor($index / 26) - 1;
      }
      return $letter;
    }
    /* --end Get Excel Column Letter -- */

    /* -- Get Email Size -- */
    public function getEmailSizeEstimate($body, $attachments = [])
    {
        $htmlSize = strlen($body); // Message body size

        $encodedAttachmentSize = 0;
        foreach ($attachments as $attachment) {
          // Get attachment file size from local file (or original file)
          $rawAttachmentSize = strlen($attachment['url']['file']); // in bytes

          // Estimate size with Base64 encoding (~33% increase)
          $encodedAttachmentSize += ceil($rawAttachmentSize * 1.37);
        }
        // Total email size (body + attachments)
        $totalSize = $htmlSize + $encodedAttachmentSize;
        return $totalSize;
    }
    /* -- Get Email Size -- */

    /* -- GET Reminders schedule -- */
    public function scheduleCRMReminder($authUser)
    {
        try 
        {      
            $now = now();
            $check_date = $now->toDateString();
            $check_time = $now->format('H:i:s');

            $reminders = CRMReminder::whereDate('reminder_date', $check_date)
                            ->whereTime('reminder_time','<=', $check_time)
                            ->where('email_sent',0)                            
                            ->get();
            
            // $sent_to = (strtolower(env('APP_URL')) === "http://localhost:8000" || strtolower(config('app.url')) === "http://localhost:8000") ? 'mail2oxygeninfotech@gmail.com' : 'info@intravat.com';
            //$sent_to = env('MAIL_FROM_ADDRESS');            

            $sent_email = 0;
            foreach($reminders as $reminder)
            {
                $sent_to = $reminder->sent_to;

                if($reminder->module_type == 'lead')
                {
                    $lead = CRMLead::with(['contact'])->where('id', $reminder->module_id)->first();
                }
                else if($reminder->module_type == 'quote')
                {
                    $quote = CRMQuote::with(['lead', 'lead.contact'])->where('id', $reminder->module_id)->first();                    
                    $lead = $quote->lead;
                }

                $data = [                 
                  'lang' => $lead->contact->lang,
                  'app_name' => config('app.name'),                      
                  'company_name' => $lead->company_name,
                  'company_website' => $lead->company_website,
                  'first_name' => $lead->contact->first_name,
                  'last_name' => $lead->contact->last_name,
                  'email' => $lead->contact->email,
                  'phone' => $lead->contact->phone,
                  'designation' => $lead->contact->designation,                 
                  'message' => $reminder->notes,
                  'attachment' => [], 
                  'align' => 'left'
                ];
                $email_data = new CRMNoQuoteReminder($data);

                $mailsent = Mail::to($sent_to)->send($email_data);                

                if($mailsent)
                {
                  $reminderupdate = CRMReminder::where('id', $reminder->id)->first();
                  if($reminderupdate)
                  {
                    $reminderupdate->email_sent = 1;
                    $reminderupdate->save();
                  }

                    $email_headers = $mailsent->getOriginalMessage()->getHeaders();
                    $message_id = $email_headers->getHeaderBody('X-SES-Message-ID');                     
                    if ($message_id)
                    {
                        $email_sent_to = $email_headers->getHeaderBody('To');
                        $email_sent_subject = $email_headers->getHeaderBody('Subject');
                        
                        $uname = 'Cron User';

                        $emailNotification = new EmailNotification;
                        $emailNotification->vat_reg_id = NULL; 
                        $emailNotification->message_id = $message_id;   
                        $emailNotification->subject = $email_sent_subject;                   
                        $emailNotification->name = $uname;            
                        $emailNotification->email = ($email_sent_to) ? $email_sent_to[0]->getAddress() : '';     
                        $emailNotification->sent_by = $authUser->user_id;
                        $emailNotification->send_type = "to";
                        $emailNotification->reminder_action_id = NULL;

                        $emailNotification->save();
                    } 

                    /* -- LOG -- */
                    $this->addLog($authUser, 'crm-reminder-email',
                        [
                            'Company Name' =>  $data['company_name'],
                            'Contact Person' =>  $data['first_name'] . ' ' . $data['last_name'],
                            'Recipient' =>  $sent_to
                        ]
                    );
                    /* --end LOG -- */

                    $sent_email++;
                }
            } //for

            if($sent_email > 0)
            {
                /* -- LOG -- */
                $this->addLog($authUser, 'crm-reminder-scheduled-email',
                    [
                        'Date' =>  $check_date,
                        'Time' =>  $check_time,
                        'Total' =>  $sent_email                        
                    ]
                );
                /* --end LOG -- */
            }
            else
            {
                /* -- LOG -- */
                $this->addLog($authUser, 'crm-reminder-no-schedule-email',
                    [
                        'Date' =>  $check_date,
                        'Time' =>  $check_time
                    ]
                );
                /* --end LOG -- */
            }

            return $sent_email;
        }
        catch (\Exception $e) 
        {
            /* -- LOG -- */
            $this->addLog($authUser, 'error-log',
                [
                    'status' => 'Error',
                    'controller' => 'CRM Reminder Controller',
                    'method' => 'scheduleCRMReminder',
                    'message' => $e->getMessage()
                ]
            );
            /* --end LOG -- */

            return  $e->getMessage();
        }  
    }
    /* --end Reminders schedule -- */
}
