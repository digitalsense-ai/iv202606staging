<?php

namespace App\Http\Controllers\dvuser;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

use App\Models\DVUser;
use App\Models\User;
use App\Models\Client;
use App\Models\VATRegistration;
use App\Models\VATRegistrationMain;
use App\Models\UserVATRegistration;
use App\Models\UserVATRegistrationMain;
use App\Models\CompanyTeamUser;
use App\Models\UserClient;
use App\Models\NotificationSettings;
use App\Models\InvoiceColumnSettings;

use \App\Classes\CommonClass;

use Mail;
use App\Mail\NewUser as NewUserEmail;

class DVUserController extends Controller
{
    public $authUser;
    
    public $commonClass;
   
    public $fileTypes;
    public $invoiceColumnNames;

    public $with;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {                     
            $this->commonClass = new CommonClass();
            $this->authUser = $this->commonClass->getAuthUser();     

            $this->fileTypes = $this->commonClass->fileTypesData();   
            $this->invoiceColumnNames = $this->commonClass->invoiceColumnNamesData();   

            $this->with = ['dvuser', 'roles', 'userclient', 'userclient.client'];

            return $next($request);
        });
    }      

    /*Lazy*/        
    /* -- GET /users -- */
    public function loadUsers()
    {   
      try
      {    
        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser);      
        /* --end PAGE CONFIG -- */
        
        /* -- GET USERS -- */        
        $users = $this->commonClass->getUsersLazy(NULL, NULL, $this->with);
        /* --end GET USERS -- */

        /* -- GET COMPANY/CLIENT -- */
        $clients = $this->commonClass->getCompanyLazy();
        /* --end GET COMPANY/CLIENT -- */

        /* -- GET VAT REG. MAIN -- */
        $vatregmains = $this->commonClass->getVatRegMainLazy();
        /* --end GET VAT REG. MAIN -- */
            
        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'user-list');
        /* --end LOG -- */

        /* -- RETURN VIEW -- */
        return view('content.dvuser.index-lazy', [
          'pageConfigs' => $pageConfigs, 
          'authUser' => $this->authUser, 
          'users' => $users,
          'vatregmains' => $vatregmains,          
          'clients' => $clients,           
          'fileTypes' => $this->fileTypes
        ]);
        /* --end RETURN VIEW -- */
      }      
      catch (\Exception $e) 
      {   
        /* -- GET USERS -- */
        $users = $this->commonClass->getUsersLazy(NULL, NULL, $this->with);
        /* --end GET USERS -- */

        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'error-log', 
          [
            'status' => 'Error',
            'controller' => 'DVUser Controller',
            'method' => 'loadUsers',
            'message' => $e->getMessage()
          ]
        );
        /* --end LOG -- */

        /* -- RETURN JSON -- */
        return response()->json([   
          'status' => 'Error',        
          'users' => $users,
          'message' => $e->getMessage()
        ]);
        /* --end RETURN JSON -- */ 
      }  
    }  
    /* --end GET /users -- */ 

    /* -- GET /dv-user/notification/{user_id} -- */ 
    public function loadNotification(Request $request, $user_id)
    {
      try
      {
        /* -- GET NOTIFICATION SETTINGS -- */
        $notification_settings = NotificationSettings::where('user_id', $user_id)
                    ->get();
        /* --end GET NOTIFICATION SETTINGS -- */
        
        /* -- RETURN NOTIFICATION SETTINGS -- */
        return  $notification_settings;  
        /* --end RETURN NOTIFICATION SETTINGS -- */
      }
      catch (\Exception $e) 
      {   
        /* -- GET USERS -- */
        $users = $this->commonClass->getUsersLazy(NULL, NULL, $this->with);
        /* --end GET USERS -- */

        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'error-log', 
          [
            'status' => 'Error',
            'controller' => 'DVUser Controller',
            'method' => 'loadNotification',
            'message' => $e->getMessage()
          ]
        );
        /* --end LOG -- */

        /* -- RETURN JSON -- */
        return response()->json([   
          'status' => 'Error',        
          'users' => $users,
          'message' => $e->getMessage()
        ]);
        /* --end RETURN JSON -- */ 
      }
    }
    /* --end GET /dv-user/notification/{user_id} -- */ 

    /* -- POST /dv-user/notification/{user_id} -- */ 
    public function postNotification(Request $request)
    {
      try
      {
        $user_id = $request->user_id;

        /* -- if USER ID -- */
        if ($user_id) 
        {
          /* -- UPDATE USER NOTIFICATION SETTINGS -- */
          $fileTypes = $this->fileTypes;

          foreach($fileTypes as $key => $file_type)
          {
            $notification = NotificationSettings::updateOrCreate(
              [
                'user_id' => $user_id,
                'file_type' => $key
              ],
              [                
                  'email_notification' => ($request->input('chk_email_notification_'.$key)) ? 1 : 0
              ]
            );
          }
          /* --end UPDATE USER NOTIFICATION SETTINGS -- */
         
          /* -- GET USER -- */
          $user = $this->commonClass->getUsersLazy($user_id, NULL, $this->with);
          $dvuser = $user->dvuser;
          /* --end GET USER -- */

          /* -- LOG -- */
          $this->commonClass->addLog($this->authUser, 'user-notification', 
            [
              'User Name' => ($dvuser) ? ($dvuser->firstname . ' ' . $dvuser->lastname) : $user->name
            ]
          );
          /* --end LOG -- */
          
          /* -- GET USERS -- */
          $users = $this->commonClass->getUsersLazy(NULL, NULL, $this->with);
          /* --end GET USERS -- */
          
          /* -- RETURN JSON -- */
          return response()->json([
            'status' => 200,             
            'users' => $users,
            'message' => 'Updated'
          ]);
          /* --end RETURN JSON -- */  
        } /* -- else USER ID -- */
        else
        {          
          /* -- GET USERS -- */
          $users = $this->commonClass->getUsersLazy(NULL, NULL, $this->with);
          /* --end GET USERS -- */

          /* -- RETURN JSON -- */
          return response()->json([            
            'users' => $users,
            'message' => 'Error'
          ]); 
          /* --end RETURN JSON -- */ 
        } /* --end if USER ID -- */
      }
      catch (\Exception $e) 
      {   
        /* -- GET USERS -- */
        $users = $this->commonClass->getUsersLazy(NULL, NULL, $this->with);
        /* --end GET USERS -- */

        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'error-log', 
          [
            'status' => 'Error',
            'controller' => 'DVUser Controller',
            'method' => 'postNotification',
            'message' => $e->getMessage()
          ]
        );
        /* --end LOG -- */

        /* -- RETURN JSON -- */
        return response()->json([   
          'status' => 'Error',        
          'users' => $users,
          'message' => $e->getMessage()
        ]);
        /* --end RETURN JSON -- */ 
      }
    }
    /* --end POST /dv-user/notification/{user_id} -- */ 

    /* -- POST /dv-user/invoice-column-settings/{user_id} -- */   
    public function postInvoiceColumnSettings(Request $request)
    {
      try
      {
        $user_id = $this->authUser->user_id; 

        /* -- if USER ID -- */
        if ($user_id) 
        {
          /* -- UPDATE INVOICE COLUMN SETTINGS -- */
          $invoiceColumnNames = $this->invoiceColumnNames;

          foreach($invoiceColumnNames as $key => $columnNames)
          {            
            $notification = InvoiceColumnSettings::updateOrCreate(
              [
                'user_id' => $user_id,
                'column_name' => $key
              ],
              [                                  
                'status' => ($request->has('chk_invoice_column_'.$key)) ? 1 : 0
              ]
            );
          }
          /* --end UPDATE INVOICE COLUMN SETTINGS -- */
         
          /* -- LOG -- */
          $this->commonClass->addLog($this->authUser, 'user-invoice-column-settings');
          /* --end LOG -- */

          /* -- GET USERS -- */
          $users = $this->commonClass->getUsersLazy(NULL, NULL, $this->with);
          /* --end GET USERS -- */
          
          /* -- RETURN JSON -- */
          return response()->json([
            'status' => 200,             
            'users' => $users,
            'message' => 'Updated'
          ]);
          /* --end RETURN JSON -- */
        }  /* -- else USER ID -- */
        else
        {          
          /* -- GET USERS -- */
          $users = $this->commonClass->getUsersLazy(NULL, NULL, $this->with);
          /* --end GET USERS -- */

          /* -- RETURN JSON -- */
          return response()->json([           
            'users' => $users,
            'message' => 'Error'
          ]); 
          /* --end RETURN JSON -- */ 
        }  /* --end if USER ID -- */
      }
      catch (\Exception $e) 
      {  
        /* -- GET USERS -- */
        $users = $this->commonClass->getUsersLazy(NULL, NULL, $this->with);
        /* --end GET USERS -- */

        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'error-log', 
          [
            'status' => 'Error',
            'controller' => 'DVUser Controller',
            'method' => 'postInvoiceColumnSettings',
            'message' => $e->getMessage()
          ]
        );
        /* --end LOG -- */
        
        /* -- RETURN JSON -- */
        return response()->json([   
          'status' => 'Error',        
          'users' => $users,
          'message' => $e->getMessage()
        ]);
        /* --end RETURN JSON -- */      
      } 
    }
    /* --end POST /dv-user/invoice-column-settings/{user_id} -- */

    /* -- GET /dv-user/{user_id} -- */
    public function edit(DVUser $dvuser, $id)
    {
      try
      {        
        /* -- GET USER -- */
        $user = $this->commonClass->getUsersLazy($id, NULL, $this->with);
        $dvuser = $user->dvuser;
        /* --end GET USER -- */
        
        /* -- LOG -- */  
        $this->commonClass->addLog($this->authUser, 'user-edit', 
          [
            'User Name' => ($dvuser) ? ($dvuser->firstname . ' ' . $dvuser->lastname) : $user->name
          ]
        );
        /* --end LOG -- */
             
        /* -- RETURN JSON -- */       
        return response()->json($user);  
        /* --end RETURN JSON -- */
      }   
      catch (\Exception $e) 
      {  
        /* -- GET USER -- */
        $user = $this->commonClass->getUsersLazy($id, NULL, $this->with);
        /* --end GET USER -- */

        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'error-log', 
          [
            'status' => 'Error',
            'controller' => 'DVUser Controller',
            'method' => 'edit',
            'message' => $e->getMessage()
          ]
        );
        /* --end LOG -- */
        
        /* -- RETURN JSON -- */
        return response()->json([   
          'status' => 400,        
          'users' => $user,
          'message' => $e->getMessage()
        ]);
        /* --end RETURN JSON -- */      
      }       
    }
    /* --end GET /dv-user/{user_id} -- */

    /* -- POST /dv-user/{user_id} -- */
    public function store(Request $request)
    {
      try
      {
        $userID = $request->user_id;

        /* -- if HAS USER EDIT -- */
        if ($userID) 
        { 
          /* -- UPDATE USER NAME -- */
          $users = User::updateOrCreate(
            ['id' => $userID],
            [                
                'name' => $request->firstname                
            ]
          );
          /* --end UPDATE USER NAME -- */

          /* -- UPDATE USER DETAILS -- */
          $dvUsers = DVUser::updateOrCreate(
            ['user_id' => $userID],
            [                
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,      
                'telephone' => isset($request->telephone) ? $request->telephone : NULL,   
                'designation' => ($request->designation == '') ? NULL : $request->designation,
                'lang' => $request->lang,            
                'status' => $request->status,
                'is_deleted' => 0
            ]
          );
          /* --end UPDATE USER DETAILS -- */
          
          /* -- LOG -- */
          $this->commonClass->addLog($this->authUser, 'user-update', 
            [
              'User Name' => $request->firstname . ' ' . $request->lastname
            ]
          );
          /* --end LOG -- */

          if($request->user_contact_tab == 0)
          {
            /* -- GET USERS -- */
            $users = $this->commonClass->getUsersLazy(NULL, NULL, $this->with);
            /* --end GET USERS -- */

            /* -- RETURN JSON -- */
            return response()->json([
              'status' => 200,             
              'users' => $users,
              'message' => 'Updated'
            ]); 
            /* --end RETURN JSON -- */
          }
          else
          {
            /* -- CONTACTS TAB -- */ 
            $client_id = $request->user_contact_tab_client_id;            
            $with_client = [
              'vatregmain',
              'vatregmain.clientapi',
             
              'clientcomment',
              'clientcomment.user',                                  
              'clientcomment.user.roles',
              'clientcomment.user.dvuser',
              'userclient',
              'userclient.user',
              'userclient.user.dvuser'
            ];  
            $where_client = [
              'id' => ['operator' => '=', 'value' => $client_id]
            ]; 
            $whereHas_client = [];    
            $orderBy_client = [];            
            $client = $this->commonClass->getLazy('client', $with_client, $where_client, $whereHas_client, $orderBy_client, 'first');                                               
            /* --/ CONTACTS TAB -- */

            /* -- RETURN JSON -- */
            return response()->json([
              'status' => 200,             
              'client' => $client,
              'message' => 'Updated'
            ]); 
            /* --end RETURN JSON -- */
          }          
        } /* -- else HAS USER EDIT -- */  
        else 
        {     
          /* -- CREATE PASSWORD -- */
          if($request->role == 'client-user')
            $newpassword = config('app.dv_user_password');
          else
            $newpassword = Str::random(8);
          /* --end CREATE PASSWORD -- */
          
          /* -- GET USER COUNT -- */
          $userexists = User::where('email',$request->email)->count();
          /* --end GET USER COUNT -- */

          /* -- if USER COUNT 0 -- */
          if($userexists == 0)
          {
            /* -- CREATE USER AND ASSIGN ROLE -- */
            $user = User::create([
                'name' => $request->firstname,
                'email' => $request->email,
                'password' => Hash::make($newpassword),
            ])->assignRole([$request->role]);
            /* --end CREATE USER AND ASSIGN ROLE -- */

            /* -- if email is unique CREATE USER DETAILS -- */        
            if (!empty($user)) 
            {   
              /* -- CREATE USER DETAILS -- */
              $dvUsers = DVUser::updateOrCreate(
                //['id' => $userID],
                ['user_id' => $user->id],
                [
                  'user_id' => $user->id,    
                  'firstname' => $request->firstname,
                  'lastname' => $request->lastname,           
                  'telephone' => isset($request->telephone) ? $request->telephone : NULL, 
                  'designation' => ($request->designation == '') ? NULL : $request->designation,
                  'lang' => $request->lang,             
                  'status' => $request->status,
                  'is_deleted' => 0      
                ]
              );
              /* --end CREATE USER DETAILS -- */
             
              /* -- LOG -- */
              $this->commonClass->addLog($this->authUser, 'user-add', 
                [
                  'User Name' => $request->firstname . ' ' . $request->lastname
                ]
              );
              /* --end LOG -- */

              /* -- if ROLE not client-user -- */
              if($request->role != 'client-user')
              {
                /* -- EMAIL DATAS -- */
                $emaildata = [             
                  'subject' => trans('User created'),
                  'lang' => $request->lang,
                  'app_name' => config('app.name'),
                  'user' => [
                    'firstname' => $request->firstname,      
                    'lastname' => $request->lastname,            
                    'password' => $newpassword,
                    'email' => $request->email,    
                    'role' => $request->role       
                  ]                     
                ];
                /* --end EMAIL DATAS -- */

                /* -- if SEND EMAIL -- */
                if(Mail::to($request->email)              
                    ->send(new NewUserEmail($emaildata)))
                {
                  /* -- GET USERS -- */
                  $users = $this->commonClass->getUsersLazy(NULL, NULL, $this->with);
                  /* --end GET USERS -- */
                  
                  /* -- RETURN JSON -- */
                  return response()->json([
                    'status' => 200,             
                    'users' => $users,
                    'message' => 'Created'
                  ]);
                  /* --end RETURN JSON -- */
                } /* -- else SEND EMAIL -- */
                else
                {                  
                  /* -- GET USERS -- */
                  $users = $this->commonClass->getUsersLazy(NULL, NULL, $this->with);
                  /* --end GET USERS -- */
                  
                  /* -- RETURN JSON -- */
                  return response()->json([
                    'status' => 200,             
                    'users' => $users,
                    'message' => 'Cannot send email'
                  ]);
                  /* --end RETURN JSON -- */
                }/* --end if SEND EMAIL -- */
              } /* --end if ROLE not client-user -- */
              
              /* -- GET USERS -- */
              $users = $this->commonClass->getUsersLazy(NULL, NULL, $this->with);
              /* --end GET USERS -- */
              
              /* -- RETURN JSON -- */
              return response()->json([
                'status' => 200,             
                'users' => $users,
                'message' => 'Created'
              ]);
              /* --end RETURN JSON -- */ 
            } /* --end if email is unique CREATE USER DETAILS  -- */
          } /* -- else USER COUNT 0 -- */
          else
          {
            /* -- GET USERS -- */
            $users = $this->commonClass->getUsersLazy(NULL, NULL, $this->with);
            /* --end GET USERS -- */

            /* -- IS USER DELETED -- */
            $userexist = User::with('dvuser')->where('email',$request->email)->first();
            if($userexist->dvuser->is_deleted)
            {
              /* -- RETURN JSON -- */
              return response()->json([
                'status' => 400,             
                'users' => $users,
                'message' => 'Deleted user'
              ]); 
              /* --end RETURN JSON -- */
            }
            /* --end IS USER DELETED -- */

            /* -- RETURN JSON -- */
            return response()->json([
              'status' => 400,             
              'users' => $users,
              'message' => 'Already exists'
            ]); 
            /* --end RETURN JSON -- */
          } /* --end if USER COUNT 0 -- */
        } /* --end if HAS USER EDIT -- */
      }
      catch (\Exception $e) 
      {  
        /* -- GET USERS -- */
        $users = $this->commonClass->getUsersLazy(NULL, NULL, $this->with);
        /* --end GET USERS -- */

        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'error-log', 
          [
            'status' => 'Error',
            'controller' => 'DVUser Controller',
            'method' => 'store',
            'message' => $e->getMessage()
          ]
        );
        /* --end LOG -- */
        
        /* -- RETURN JSON -- */
        return response()->json([   
          'status' => 400,        
          'users' => $users,
          'message' => $e->getMessage()
        ]);
        /* --end RETURN JSON -- */      
      }  
    }
    /* --end POST /dv-user/{user_id} -- */

    /* -- DELETE /dv-user/{user_id} -- */
    public function destroy(DVUser $dvuser, $id)
    {
      try
      {        
        /* -- GET USER -- */
        $user = $this->commonClass->getUsersLazy($id, NULL, $this->with);
        $dvuser = $user->dvuser;
        $dvUserName = ($dvuser) ? ($dvuser->firstname . ' ' . $dvuser->lastname) : $user->name;
        /* --end GET USER -- */

        /* -- DELETE USER -- */ 
        /*    
        $model_has_roles = DB::table('model_has_roles')
                ->where('model_id', $id)
                ->delete();
        $model_has_permissions = DB::table('model_has_permissions')
                ->where('model_id', $id)
                ->delete();   

        $userDelete = User::where('id', $id)->delete(); 
        $dvUserDelete = DVUser::where('user_id', $id)->delete();
        */

        $dvuser->is_deleted = 1;
        $dvuser->save();
        /* --end DELETE USER -- */

        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'user-delete', 
          [
            'User Name' => $dvUserName
          ]
        );
        /* --end LOG -- */

        /* -- GET USERS -- */
        $users = $this->commonClass->getUsersLazy(NULL, NULL, $this->with);
        /* --end GET USERS -- */

        /* -- RETURN JSON -- */
        return response()->json([
          'status' => 200,             
          'users' => $users,
          'message' => 'Deleted'
        ]); 
        /* --end RETURN JSON -- */ 
      }
      catch (\Exception $e) 
      {  
        /* -- GET USERS -- */
        $users = $this->commonClass->getUsersLazy(NULL, NULL, $this->with);
        /* --end GET USERS -- */

        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'error-log', 
          [
            'status' => 'Error',
            'controller' => 'DVUser Controller',
            'method' => 'destroy',
            'message' => $e->getMessage()
          ]
        );
        /* --end LOG -- */
        
        /* -- RETURN JSON -- */
        return response()->json([   
          'status' => 400,        
          'users' => $users,
          'message' => $e->getMessage()
        ]);
        /* --end RETURN JSON -- */      
      }    
    }
    /* --end DELETE /dv-user/{user_id} -- */
       
    /* -- POST /dv-user/assign -- */
    public function assign(Request $request)
    {
      try
      {
        $userID = $request->team_user_id;
        $vatRegIDs = $request->chk_vatreg;        

        /* --if USER ID EXISTS -- */
        if ($userID) 
        { 
          /* -- ASSIGN TEAM USER WITH VAT REG. -- */
          $alreadyAssignedVatRegIds = UserVATRegistration::where('user_id', $userID)->get();   
          if(!empty($alreadyAssignedVatRegIds))       
          {
            foreach($alreadyAssignedVatRegIds as $alreadyAssignedVatRegId)
            {
              $VATRegMainId = VATRegistration::where('id', $alreadyAssignedVatRegId->vat_reg_id)
                                ->first();

              $VATRegMain = VATRegistrationMain::where('id', $VATRegMainId->vat_reg_main_id)
                                          ->first(); 

              $product_type = $VATRegMain->product_type;
                                      
              if($product_type == 1 || $product_type == 4)
                $updateVATRegStatus = VATRegistration::where('id', $alreadyAssignedVatRegId->vat_reg_id)
                                            ->where('status', 2)             
                                            ->update(['status' => 1]);//From 'Draft' to 'Draft Created' (removing assigned vat reg. to team user)
              else if($product_type == 2)
                $updateVATRegStatus = VATRegistration::where('id', $alreadyAssignedVatRegId->vat_reg_id)
                                            ->where('status_import_re', 2)             
                                            ->update(['status_import_re' => 1]);//From 'Draft' to 'Draft Created' (removing assigned vat reg. to team user)  
              else if($product_type == 3 || $product_type == 5)  
              {
                $updateVATRegStatus = VATRegistration::where('id', $alreadyAssignedVatRegId->vat_reg_id)
                                            ->where('status', 2)             
                                            ->update(['status' => 1]);//From 'Draft' to 'Draft Created' (removing assigned vat reg. to team user)

                $updateVATRegStatusImportRe = VATRegistration::where('id', $alreadyAssignedVatRegId->vat_reg_id)
                                            ->where('status_import_re', 2)             
                                            ->update(['status_import_re' => 1]);//From 'Draft' to 'Draft Created' (removing assigned vat reg. to team user)                              
              }                                                        

                $team_user_client = UserVATRegistration::where('user_id', $userID)
                                      ->where('vat_reg_id', $alreadyAssignedVatRegId->vat_reg_id)
                                      ->delete();                          
            }
          }   

          /* -- ASSIGN TEAM USER WITH VAT REG. MAIN -- */
          $alreadyAssignedVatRegIdsMain = UserVATRegistrationMain::where('user_id', $userID)->get(); 
          if(!empty($alreadyAssignedVatRegIdsMain))       
          {
            foreach($alreadyAssignedVatRegIdsMain as $alreadyAssignedVatRegIdMain)
            {
              $team_user_client_main = UserVATRegistrationMain::where('user_id', $userID)
                                          ->where('vat_reg_main_id', $alreadyAssignedVatRegIdMain->vat_reg_main_id)
                                          ->delete();    
            }
          }     
                  
          if(!empty($vatRegIDs))       
          {
            foreach($vatRegIDs as $vatRegID)
            {      
              $VATRegMain = VATRegistrationMain::where('id', $vatRegID)
                              ->first(); 

              $product_type = $VATRegMain->product_type;              

              if($product_type == 1 || $product_type == 4)          
                $vatRegRows = VATRegistration::where('vat_reg_main_id', $vatRegID)
                                          ->where('status', '<=', 2)             
                                          ->get();
              else if($product_type == 2) 
                $vatRegRows = VATRegistration::where('vat_reg_main_id', $vatRegID)
                                        ->where('status_import_re', '<=', 2)             
                                        ->get();
              else if($product_type == 3 || $product_type == 5)
                $vatRegRows = VATRegistration::where('vat_reg_main_id', $vatRegID)
                                        ->where('status', '<=', 2)                                        
                                        ->get();

              if(count($vatRegRows) > 0)
              {
                foreach($vatRegRows as $vatRegRow)
                {                                            
                  $teamUsers = UserVATRegistration::updateOrCreate(  
                    [                
                        'user_id' => $userID,
                        'vat_reg_id' => $vatRegRow->id
                    ],             
                    [                
                        'user_id' => $userID,
                        'vat_reg_id' => $vatRegRow->id
                    ]
                  );

                  if($product_type == 1 || $product_type == 4)
                    $updateVATRegStatus = VATRegistration::where('id', $vatRegRow->id)
                                            ->where('status', 1)             
                                            ->update(['status' => 2]);//From 'Draft Created' to 'Draft' (assigned vat reg. to team user)
                  else if($product_type == 2)
                    $updateVATRegStatus = VATRegistration::where('id', $vatRegRow->id)
                                            ->where('status_import_re', 1)             
                                            ->update(['status_import_re' => 2]);//From 'Draft Created' to 'Draft' (assigned vat reg. to team user)    
                  else if($product_type == 3 || $product_type == 5)
                  {
                    $updateVATRegStatus = VATRegistration::where('id', $vatRegRow->id)
                                            ->where('status', 1)             
                                            ->update(['status' => 2]);//From 'Draft Created' to 'Draft' (assigned vat reg. to team user)

                    $updateVATRegStatusImportRe = VATRegistration::where('id', $vatRegRow->id)
                                            ->where('status_import_re', 1)             
                                            ->update(['status_import_re' => 2]);//From 'Draft Created' to 'Draft' (assigned vat reg. to team user)                                                
                  }
                }
              }
              else
              {
                $teamUsers = UserVATRegistrationMain::updateOrCreate(  
                  [                
                      'user_id' => $userID,
                      'vat_reg_main_id' => $vatRegID
                  ],             
                  [                
                      'user_id' => $userID,
                      'vat_reg_main_id' => $vatRegID
                  ]
                );
              }
            } 
            /* --end ASSIGN TEAM USER WITH VAT REG. -- */
           
            /* -- GET USERS -- */
            $users = $this->commonClass->getUsersLazy(NULL, NULL, $this->with);
            /* --end GET USERS -- */
            
            /* -- RETURN JSON -- */
            return response()->json([
              'status' => 200,             
              'users' => $users,
              'message' => 'Assigned'
            ]);  
            /* --end RETURN JSON -- */
          }
          // user updated
         
          /* -- GET USERS -- */
          $users = $this->commonClass->getUsersLazy(NULL, NULL, $this->with);
          /* --end GET USERS -- */
          
          /* -- RETURN JSON -- */
          return response()->json([
            //'status' => 200,             
            'users' => $users,
            'message' => 'Not Selected'
          ]); 
          /* --end RETURN JSON -- */ 
        } /* --end if USER ID EXISTS -- */
      }
      catch (\Exception $e) 
      {  
        /* -- GET USERS -- */
        $users = $this->commonClass->getUsersLazy(NULL, NULL, $this->with);
        /* --end GET USERS -- */

        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'error-log', 
          [
            'status' => 'Error',
            'controller' => 'DVUser Controller',
            'method' => 'assign',
            'message' => $e->getMessage()
          ]
        );
        /* --end LOG -- */
        
        /* -- RETURN JSON -- */
        return response()->json([   
          'status' => 400,        
          'users' => $users,
          'message' => $e->getMessage()
        ]);
        /* --end RETURN JSON -- */      
      }   
    }
    /* --end POST /dv-user/assign -- */

    /* -- GET /dv-user/assigned/{user_id} -- */
    public function assigned($team_user_id)
    {
      try
      {
        $vat_regs = VATRegistration::leftJoin('dv_user_vat_registration', function($join) {
                      $join->on('dv_user_vat_registration.vat_reg_id', '=', 'dv_vat_registration.id');
                    })                                                            
                    ->where('dv_user_vat_registration.user_id', $team_user_id)
                    ->get();

        $vat_regs_main = VATRegistrationMain::leftJoin('dv_user_vat_registration_main', function($join) {
                      $join->on('dv_user_vat_registration_main.vat_reg_main_id', '=', 'dv_vat_registration_main.id');
                    })                                                            
                    ->where('dv_user_vat_registration_main.user_id', $team_user_id)
                    ->get();
      
        /* -- RETURN JSON -- */
        return response()->json([
          'vat_regs' => $vat_regs,
          'vat_regs_main' => $vat_regs_main
        ]);
        /* --end RETURN JSON -- */   
      }
      catch (\Exception $e) 
      {  
        /* -- GET USERS -- */
        $users = $this->commonClass->getUsersLazy(NULL, NULL, $this->with);
        /* --end GET USERS -- */

        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'error-log', 
          [
            'status' => 'Error',
            'controller' => 'DVUser Controller',
            'method' => 'assigned',
            'message' => $e->getMessage()
          ]
        );
        /* --end LOG -- */
        
        /* -- RETURN JSON -- */
        return response()->json([   
          'status' => 400,        
          'users' => $users,
          'message' => $e->getMessage()
        ]);
        /* --end RETURN JSON -- */      
      }                       
    }

    /* -- GET /team-user -- */
    public function loadTeamUsers()
    {     
        $team_users = User::leftJoin('dv_users', function($join) {
                $join->on('dv_users.user_id', '=', 'users.id');
              })                
              ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
              ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id') 
              ->select('users.id AS id', 'users.name AS name', 'users.email AS email',             
               'roles.name as role',  'dv_users.status AS status', 'dv_users.telephone AS telephone',
               'dv_users.firstname', 'dv_users.lastname', 'dv_users.designation'
              )             
              ->where('roles.name', '=', 'team-user')
              ->where('dv_users.status', '=', 1)
              ->get()
              ;

        return response()->json($team_users);
    }
    /* --end GET /team-user -- */
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function assignCompany(Request $request)
    {
        $companyID = $request->company_id;
        $teamUserIDs = $request->chk_team_user;

        if ($companyID) { 
          $team_user_company = CompanyTeamUser::where('company_id', $companyID)->delete();     

          if(!empty($teamUserIDs))       
          {
            foreach($teamUserIDs as $teamUserID)
            {
                $teamUsers = CompanyTeamUser::Create(               
                  [                
                      'company_id' => $companyID,
                      'team_user_id' => $teamUserID
                  ]
                );
            } 
            // user updated
           
            //Get Users       
            $users = $this->commonClass->getUsersLazy(NULL, NULL, $this->with);
            
            return response()->json([
              'status' => 200,             
              'users' => $users,
              'message' => 'Assigned'
            ]);  
          }
          // user updated
          
          //Get Users       
          $users = $this->commonClass->getUsersLazy(NULL, NULL, $this->with);
          
          return response()->json([
            //'status' => 200,             
            'users' => $users,
            'message' => 'Not Selected'
          ]);  
        } 
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DVUser  $dvuser
     * @return \Illuminate\Http\Response
     */
    public function assignedCompany($company_id)
    {
        return CompanyTeamUser::where('company_id', $company_id)->get();     
    }

    /**
     * Show the form for the specified resource.
     *    
     * @return \Illuminate\Http\Response
     */
    public function loadCompanyAdmin()
    {     
        $company_admin = User::leftJoin('dv_users', function($join) {
                $join->on('dv_users.user_id', '=', 'users.id');
              })                
              ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
              ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id') 
              ->select('users.id AS id', 'users.name AS name', 'users.email AS email',             
               'roles.name as role',  'dv_users.status AS status', 'dv_users.telephone AS telephone',
               'dv_users.firstname', 'dv_users.lastname', 'dv_users.designation'
              )             
              ->where('roles.name', '=', 'company-admin')
              ->where('dv_users.status', '=', 1)
              ->get()
              ;

        return response()->json($company_admin);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function assignClient(Request $request)
    {
        $userID = $request->client_user_id;
        $clientIDs = $request->chk_client;

        if ($userID) { 
          $client_user_client = UserClient::where('user_id', $userID)->delete();     

          if(!empty($clientIDs))       
          {
            foreach($clientIDs as $clientID)
            {
                $clientUsers = UserClient::Create(               
                  [                
                      'user_id' => $userID,
                      'client_id' => $clientID
                  ]
                );                
            } 
            // user updated
           
            //Get Users       
            $users = $this->commonClass->getUsersLazy(NULL, NULL, $this->with);
            
            return response()->json([
              'status' => 200,             
              'users' => $users,
              'message' => 'Assigned'
            ]);  
          }
          // user updated
          
          //Get Users       
          $users = $this->commonClass->getUsersLazy(NULL, NULL, $this->with);
          
          return response()->json([              
            'users' => $users,
            'message' => 'Not Selected'
          ]);  

        }         
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DVUser  $dvuser
     * @return \Illuminate\Http\Response
     */
    public function assignedClient($client_user_id)
    {
      $clients = Client::leftJoin('dv_user_client', function($join) {
                      $join->on('dv_user_client.client_id', '=', 'dv_clients.id');
                    })                                                          
                    ->where('dv_user_client.user_id', $client_user_id)
                    ->get();

        return  $clients;                  
    }

    /**
     * Show the form for the specified resource.
     *    
     * @return \Illuminate\Http\Response
     */
    public function loadClientUsers()
    {     
        $client_users = User::leftJoin('dv_users', function($join) {
                $join->on('dv_users.user_id', '=', 'users.id');
              })                
              ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
              ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id') 
              ->select('users.id AS id', 'users.name AS name', 'users.email AS email',             
               'roles.name as role',  'dv_users.status AS status', 'dv_users.telephone AS telephone',
               'dv_users.firstname', 'dv_users.lastname', 'dv_users.designation'
              )             
              ->where('roles.name', '=', 'client-user')
              ->where('dv_users.status', '=', 1)
              ->get()
              ;

        return response()->json($client_users);        
    }
}
