<?php

namespace App\Http\Controllers\reminder;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

use App\Models\Reminder;
use App\Models\ReminderUser;
use App\Models\Client;
use App\Models\UserClient;
use App\Models\VATRegistration;
use App\Models\VATRegistrationMain;

use \App\Classes\CommonClass;

use Mail;

class ReminderController extends Controller
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
   
    /* -- GET /reminders -- */
    public function loadReminders()
    {   
      try
      {    
        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser);      
        /* --end PAGE CONFIG -- */
               
        /* -- GET VAT REG. MAIN -- */
        $vat_reg_mains = $this->commonClass->getVatRegMainLazy();
        /* --end GET VAT REG. MAIN -- */
      
        /* -- GET REMINDERS -- */
        $reminders = $this->commonClass->getRemindersLazy();
        /* --end GET REMINDERS -- */
      
        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'reminder-list');
        /* --end LOG -- */

        /* -- RETURN VIEW -- */
        return view('content.reminder.index', [
          'pageConfigs' => $pageConfigs, 
          'authUser' => $this->authUser, 
         
          'vat_reg_mains' => $vat_reg_mains,
      
          'reminders' => $reminders
        ]);
        /* --end RETURN VIEW -- */
      }      
      catch (\Exception $e) 
      {           
        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'error-log', 
          [
            'status' => 'Error',
            'controller' => 'Reminder Controller',
            'method' => 'loadReminders',
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
    /* --end GET /reminders -- */    
   
    /* --LOAD REMINDER ACTIONS GET /reminders/{user_role}/reminderactions -- */    
   public function loadReminderActions(Request $request, $user_role)
    {
      try
      {       
        $action_name = "";
         /* -- GET REMINDER ACTION OPTIONS -- */   
        if($user_role == "reminder")
          $action_name = "General reminder";

        $reminder_actions = $this->commonClass->getReminderActionsLazy(null,$action_name);     
        /* --end GET REMINDER ACTION OPTIONS -- */       
        /* -- RETURN JSON -- */
        return response()->json(
          [
            'status' => 200,             
            'reminder_actions' => $reminder_actions            
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
            'controller' => 'Reminder Controller',
            'method' => 'loadReminderActions',
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
    /* --end LOAD REMINDER ACTIONS GET /reminders/{user_role}/reminderactions  -- */  

    /* -- GET /reminder/{country}/companies -- */
    public function loadReminderCompanies(Request $request, $country)
    {
      try
      {                
        /* -- GET VAT REG. MAINS -- */        
        $vat_reg_mains_active = VATRegistrationMain::with(['client'])         
                          ->where('country', $country)   
                          ->where('status', 1)   
                          ->orderBy(
                              Client::select('client_name')
                                  ->whereColumn('dv_clients.id', 'dv_vat_registration_main.client_id')
                          )                                       
                          ->get();
        /* --end GET VAT REG. MAINS -- */

        /* -- GET VAT REG. MAINS -- */        
        $vat_reg_mains_inactive = VATRegistrationMain::with(['client'])         
                          ->where('country', $country)   
                          ->where('status', 0)  
                          ->orderBy(
                              Client::select('client_name')
                                  ->whereColumn('dv_clients.id', 'dv_vat_registration_main.client_id')
                          )                                         
                          ->get();
        /* --end GET VAT REG. MAINS -- */ 

        /* -- RETURN JSON -- */
        return response()->json(
          [
            'status' => 200,             
        
            'vat_reg_mains_active' => $vat_reg_mains_active,
            'vat_reg_mains_inactive' => $vat_reg_mains_inactive
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
            'controller' => 'Reminder Controller',
            'method' => 'loadReminderCompanies',
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
    /* --end GET /reminder/{country}/companies -- */

    /* -- GET /reminder/{country}/companies -- */
    public function loadAllReminderCompanies(Request $request, $country)
    {
      try
      {        
        /* -- GET VAT REG. MAINS -- */        
        $vat_reg_mains = VATRegistrationMain::with(['client'])         
                          ->where('country', $country)   
                          ->orderBy(
                              Client::select('client_name')
                                  ->whereColumn('dv_clients.id', 'dv_vat_registration_main.client_id')
                          )                                                              
                          ->get();
        /* --end GET VAT REG. MAINS -- */
             
        /* -- RETURN JSON -- */
        return response()->json(
          [
            'status' => 200,             
            'vat_reg_mains' => $vat_reg_mains           
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
            'controller' => 'Reminder Controller',
            'method' => 'loadReminderAllCompanies',
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
    /* --end GET /reminder/{country}/companies -- */

    /* -- GET /reminder/{vat_reg_main_id}/users -- */  
    public function loadReminderUsers(Request $request)
    {
      try
      {
        $country = $request->country;
        $user_role = $request->user_role;
        $vat_reg_main_id = $request->vat_reg_main_id;

        $team_users = [];
        $client_users = [];
        $reminder_users =[];

        $reminderusers = [];
        $file_type = 'reminders';
       
        if($vat_reg_main_id != "" || $vat_reg_main_id == 0)
        {
          if($user_role == 'team-user')
            /* -- GET TEAM USERS -- */
            $team_users = VATRegistration::with(['uservatreg', 'uservatreg.user', 'uservatreg.user.dvuser'])         
                          ->where('vat_reg_main_id', $vat_reg_main_id)                                                  
                          ->get();
            /* --end GET TEAM USERS -- */

          if($user_role == 'client-user')  
          {
            /* -- GET CLIENT USERS -- */
            $client_id = $request->client_id;
            $client_users = UserClient::with(['user', 'user.dvuser'])         
                          ->where('client_id', $client_id)                                                  
                          ->get();
            /* --end GET CLIENT USERS -- */
          }  

          if($user_role == 'reminder')  
          {          
            $client_id = $request->client_id;           
            if($client_id == "0")
            {           
              /* -- GET REMINDER USERS FOR ALL COMPANY -- */            
              $reminder_users = UserClient::with(['client', 'client.vatregmain', 'user', 'user.dvuser','user.notificationsettings'])
                                  ->whereHas('user.notificationsettings', function ($query) use($file_type) {
                                    $query->where('file_type', $file_type);                      
                                  })
                                  ->whereHas('client.vatregmain', function ($query) use($country) {
                                    $query->where('country', $country);                      
                                  }) 
                                  ->orderBy(
                                      Client::select('client_name')
                                          ->whereColumn('dv_clients.id', 'dv_user_client.client_id')
                                  )                                                   
                                  ->get();                                     
             /* --end GET REMINDER USERS FOR ALL COMPANY-- */             
            }
            else
            {
              /* -- GET REMINDER USERS FOR PARTICULAR COMPANY -- */
              $client_id = $request->client_id;//dd($client_id);
              $reminder_users = UserClient::with(['client', 'client.vatregmain', 'user', 'user.dvuser','user.notificationsettings']) 
                                  ->whereHas('user.notificationsettings', function ($query) use($file_type) {
                                    $query->where('file_type', $file_type);                      
                                  })                                           
                                  ->where('client_id', $client_id)           
                                  ->get();
              /* --end GET REMINDER USERS FOR PARTICULAR COMPANY-- */
            }            
          }

          $reminderusers = $request->reminderusers;
        }       
        
        $view = view('_partials._modals._email-to-reminder', 
          compact(
          'team_users',
          'client_users',
          'reminder_users',
          'client_id',
          'reminderusers'
          )
        )->render();

        /* -- RETURN JSON -- */
        return response()->json(
          [
            'status' => 200,             
            'view' => $view
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
            'controller' => 'Reminder Controller',
            'method' => 'loadReminderUsers',
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
    /* --end GET /reminder/{vat_reg_main_id}/users -- */

    public function sendreminderemail(Request $request)
    { 
      try 
      {              
        $result = $this->commonClass->scheduleReminderEmail($this->authUser, $request);    
       
        return $result;            
      }
      catch (\Exception $e) 
      {
        return  $e->getMessage();
      }
    } 

    /* -- POST /reminder -- */
    public function postReminder(Request $request)
    {
      try
      {
        if($request->send_test_reminder == 'send_test_reminder')
        {
          $schedule_value = $request->schedule_value;
          $datetime_value = $request->datetime_value;
        }
        else
        {
          $schedule_value = $request->schedule;
          $datetime_value = $request->reminder_datetime;
        }

        $reminderID = $request->reminder_id;

        $vat_reg_main_id = $request->company;
        if($vat_reg_main_id == 0)
            $vat_reg_main_id = null;

        $_where = [];
        $_fields = [];
        /* -- if HAS REMINDER EDIT -- */
        if ($reminderID) 
        {    
          $_where = ['id' => $reminderID];
          $_fields = [    
            'reminder_role' => $request->user_role, 
            'reminder_country' => $request->country,            
          
            'vat_reg_main_id' =>  $vat_reg_main_id,
            'action_id' => $request->reminder_action,           
           
            'schedule' => $schedule_value, 
            'start_at' => $datetime_value,
            'title' => $request->title,             
            'content' => $request->reminder_content_quill,
            'dk_title' => $request->dk_title,             
            'dk_content' => $request->dk_reminder_content_quill,
            'year' => $request->year, 
            'period' => $request->period,             
            'status' => 1,
            'close_status' => 0,
            'updated_by' => $this->authUser->user_id
          ];          
          /* --end UPDATE REMINDER -- */
        } /* -- else HAS REMINDER EDIT -- */  
        else 
        { 
          $_where = [];
          
          $_fields = [        
            'reminder_role' => $request->user_role, 
            'reminder_country' => $request->country,        
           
            'vat_reg_main_id' =>  $vat_reg_main_id,
            'action_id' => $request->reminder_action,           
           
            'schedule' => $schedule_value, 
            'start_at' => $datetime_value,
            'title' => $request->title,             
            'content' => $request->reminder_content_quill,
            'dk_title' => $request->dk_title,             
            'dk_content' => $request->dk_reminder_content_quill,
            'year' => $request->year, 
            'period' => $request->period,             
            'status' => 1,
            'created_by' => $this->authUser->user_id
          ];
        } /* --end if HAS REMINDER EDIT -- */

        /* -- CREATE/UPDATE REMINDER -- */
        if($_where)
          $reminder = Reminder::updateOrCreate(            
            $_where,
            $_fields
          );
        else
          $reminder = Reminder::updateOrCreate(                      
            $_fields
          );
        /* --end CREATE/UPDATE REMINDER -- */
        $reminder_id = $reminder->id;

        /* -- DELETE REMINDER USER -- */
        $reminder = ReminderUser::where('reminder_id', $reminder_id)->delete();
        /* --end DELETE REMINDER USER -- */

        if($request->send_to != null)
        {
          foreach($request->send_to as $email)
          {
            /* -- GET USERS -- */
            $with_user = ['dvuser'];           
            $where_user = [
              'email' => ['operator' => '=', 'value' => $email],            
            ];
            $whereHas_user = [
              'dvuser' => ['field' => 'is_deleted', 'value' => 0]
            ];
            $orderBy_user = [];           
            $user = $this->commonClass->getLazy('user', $with_user, $where_user, $whereHas_user, $orderBy_user, 'first');  
            /* --end GET USERS -- */

            /* -- CREATE REMINDER USER -- */
            $reminder = ReminderUser::updateOrCreate(                     
              [
                'reminder_id' => $reminder_id,    
                'user_id' => $user->id                
              ]
            );
            /* --end CREATE REMINDER USER -- */
          } /* --end for SEND_TO -- */
        } /* --end if SEND_TO -- */
         else
        {          
          if($request->edit_sent_to != null)
          {
            $edit_send_to = explode(',', $request->edit_sent_to);
            foreach($edit_send_to as $send_email) 
            {              
              /* -- GET USERS -- */
              $with_user = ['dvuser'];           
              $where_user = [
                'email' => ['operator' => '=', 'value' => $send_email],             
              ];
              $whereHas_user = [
                'dvuser' => ['field' => 'is_deleted', 'value' => 0]
              ];
              $orderBy_user = [];           
              $user = $this->commonClass->getLazy('user', $with_user, $where_user, $whereHas_user, $orderBy_user, 'first');  
              /* --end GET USERS -- */

              /* -- CREATE REMINDER USER -- */
              $reminder = ReminderUser::updateOrCreate(                     
                [
                  'reminder_id' => $reminder_id,    
                  'user_id' => $user->id                
                ]
              );
              /* --end CREATE REMINDER USER -- */
            }
          }
        } /* --end else SEND_TO -- */

        /* Send email after saving the reminder */        
        $result = $this->commonClass->scheduleReminderEmail($this->authUser, $request, $reminder_id);    
        /*End Send email after saving the reminder  */

        /* -- GET REMINDERS -- */
        $reminders = $this->commonClass->getRemindersLazy();
        /* --end GET REMINDERS -- */
        
        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'reminder-add',
          [
            'Reminder' => $request->title
          ]
        );
        /* --end LOG -- */

        /* -- RETURN JSON -- */
        return response()->json([
          'status' => 200,             
          'reminders' => $reminders,
          'authUser' => $this->authUser,
          'message' => 'Created'
        ]);
        /* --end RETURN JSON -- */
      }      
      catch (\Exception $e) 
      {           
        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'error-log', 
          [
            'status' => 'Error',
            'controller' => 'Reminder Controller',
            'method' => 'postReminder',
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
    /* --end POST /reminder -- */

    /* -- GET /reminder/{reminder_id}/edit -- */
    public function editReminder(Request $request, $reminder_id)
    {
      try
      {    
        $reminder = [];    
        if($reminder_id != 0)
        {
          /* -- GET REMINDERS -- */
          $reminder = $this->commonClass->getRemindersLazy($reminder_id, null, 'first');
          /* --end GET REMINDERS -- */  

          /* -- LOG -- */
          $this->commonClass->addLog($this->authUser, 'reminder-edit',
            [
              'Reminder' => $reminder->title
            ]
          );
          /* --end LOG -- */ 
        }        

        /* -- RETURN JSON -- */
        return response()->json(
          [
            'status' => 200,             
            'reminder' => $reminder,
            'authUser' => $this->authUser
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
            'controller' => 'Reminder Controller',
            'method' => 'editReminder',
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
    /* --end GET /reminder/{reminder_id}/edit -- */ 

    /* -- DELETE /reminder/{reminder_id} -- */
    public function deleteReminder(Request $request, $reminder_id)
    {
      try
      {        
        if($reminder_id != 0)
        {
          /* -- DELETE REMINDERS -- */
          $reminder = $this->commonClass->getRemindersLazy($reminder_id, null, 'first');
          $reminder_title = $reminder->title;
          $reminder->delete();
          /* --end DELETE REMINDERS -- */ 

          /* -- LOG -- */
          $this->commonClass->addLog($this->authUser, 'reminder-delete',
            [
              'Reminder' => $reminder_title
            ]
          );
          /* --end LOG -- */  
        } 

        /* -- GET REMINDERS -- */
        $reminders = $this->commonClass->getRemindersLazy();
        /* --end GET REMINDERS -- */       

        /* -- RETURN JSON -- */
        return response()->json(
          [
            'status' => 200,             
            'reminders' => $reminders,
            'message' => 'Deleted',
            'authUser' => $this->authUser
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
            'controller' => 'Reminder Controller',
            'method' => 'deleteReminder',
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
    /* --end DELETE /reminder/{reminder_id} -- */   

    /* -- GET /reminder-history -- */
    public function historyReminder(Request $request)
    {
      try
      {  
        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser);      
        /* --end PAGE CONFIG -- */

        /* -- GET REMINDERS -- */
        $reminders = $this->commonClass->getRemindersLazy();
        /* --end GET REMINDERS -- */  

        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'reminder-history');
        /* --end LOG -- */ 
        
        /* -- RETURN VIEW -- */
        return view('content.reminder.history', [
          'pageConfigs' => $pageConfigs, 
          'authUser' => $this->authUser,           
          'reminders' => $reminders
        ]);
        /* --end RETURN VIEW -- */
      }      
      catch (\Exception $e) 
      {           
        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'error-log', 
          [
            'status' => 'Error',
            'controller' => 'Reminder Controller',
            'method' => 'historyReminder',
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
    /* --end GET /reminder/history -- */  
}
