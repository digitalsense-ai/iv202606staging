<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

use \App\Classes\CommonClass;
use \App\Classes\ApiClass;

use App\Models\User;
use App\Models\DVUser;
use App\Models\Client;
use App\Models\UserClient;
use App\Models\ClientLegalRep;

class RegisterController extends Controller
{
	public $commonClass;	
	public $apiClass;

	public function __construct()
    {        
        $this->middleware(function ($request, $next) {                      
            $this->commonClass = new CommonClass();
            $this->apiClass = new ApiClass(); 

            return $next($request);
        });                   
    }

    /* -- GET /register -- */
	public function index()
	{
		try
      	{    
	        /* -- PAGE CONFIG -- */
	        $pageConfigs = ['myLayout' => 'blank'];    
	        /* --end PAGE CONFIG -- */

	        /* -- RETURN VIEW -- */
	        return view('auth.register', ['pageConfigs' => $pageConfigs]);
	        /* --end RETURN VIEW -- */
    	}
    	catch (\Exception $e) 
		{   			
			/* -- LOG -- */
			$this->commonClass->addLog($this->authUser, 'error-log', 
				[
					'status' => 'Error',
					'controller' => 'Register Controller',
					'method' => 'index',
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
	/* --end GET /register -- */

	/* -- POST /register -- */
	public function store(Request $request)
    {
		try
		{       
			/* -- GET USER COUNT -- */
			$userexists = User::where('email',$request->email)->count();			
			/* --end GET USER COUNT -- */

			/* -- if USER COUNT 0 -- */
			if($userexists == 0)
			{          	
				/* -- CREATE USER AND ASSIGN ROLE -- */
				$user = User::create([
					'name' => $request->multiStepsFirstname,
					'email' => $request->multiStepsUserEmail,
					'password' => Hash::make($request->multiStepsPass),
				])->assignRole(['client-user']);
				/* --end CREATE USER AND ASSIGN ROLE -- */

				/* -- if email is unique CREATE USER DETAILS -- */        
				if (!empty($user)) 
				{   
					/* -- CREATE USER DETAILS -- */
					$dvUsers = DVUser::updateOrCreate(			
						['user_id' => $user->id],
						[
							'user_id' => $user->id,    
							'firstname' =>  $request->multiStepsFirstname,
							'lastname' => $request->multiStepsLastname,           
							'telephone' => isset($request->multiStepsUserTelephone) ? $request->multiStepsUserTelephone : NULL, 
							'designation' =>  NULL,						
							'status' => 1,
							'is_deleted' => 0      
						]
					);			
					/* --end CREATE USER DETAILS -- */
				
					/* -- CREATE COMPANY AND LEGAL REP DETAILS  -- */
					$clients = Client::updateOrCreate(              
						[                
							'client_name' => $request->multiStepsCompanyName, 
							'lrep_fname' => $request->multiStepsRepFirstName,
							'lrep_sname' => $request->multiStepsSurname,
							'lrep_email' => $request->multiStepsCompEmail,
							'lrep_position' =>  null,
							'off_houseno' =>  null,
							'off_street' =>  null,
							'off_officeno' =>  null,
							'off_city' => $request->multiStepsCity,
							'off_postcode' => $request->multiStepsZipcode,
							'off_country' => $request->multiStepsState,
							'telephone' => ($request->multiStepsTelephone) ? $request->multiStepsTelephone : null,
							'vatno' => $request->multiStepsVatNo,                
							'email' => ($request->multiStepsCompEmail) ? $request->multiStepsCompEmail : null,
							'short_desc' => ($request->multiStepsCompDesc) ? $request->multiStepsCompDesc : null,
							'status' => 0,

							'off_address' => $request->multiStepsAddress,              
							'employees' => $request->multiStepsEmployees,  
							'start_date' => Carbon::parse($request->multiStepsStartDate)->format('Y-m-d'),
							'end_date' => ($request->multiStepsEndDate) ? Carbon::parse($request->multiStepsEndDate)->format('Y-m-d') : null,

							'lrep_address' => $request->multiStepsRepAddress,   
							'lrep_city' => $request->multiStepsRepCity,
							'lrep_postcode' => $request->multiStepsRepZipcode,

							'risk_assessment' => $request->multiStepsRiskAssessment,
							'use_trademark' => $request->multiStepsUseTrademark,
							'trading_name' => $request->multiStepsTradingName,

							'economics_id' => ($request->multiStepsEconomicsId) ? $request->multiStepsEconomicsId : null,

							'adm_fee' => ($request->multiStepsAdmFee) ? $request->multiStepsAdmFee : null,
							'consultancy_low' => ($request->multiStepsConsultancyLow) ? $request->multiStepsConsultancyLow : null,
							'consultancy_high' => ($request->multiStepsConsultancyHigh) ? $request->multiStepsConsultancyHigh : null
						]
					);
					/* --end CREATE COMPANY AND LEGAL REP DETAILS  -- */

					if($request->multiStepsRepFirstName)
		            {		              
		                $client_legalrep = ClientLegalRep::updateOrCreate(              
		                  [                
		                    'client_id' => $clients->id, 
		                    'lrep_role' => "legal-owner",
		                    'lrep_fname' => $request->multiStepsRepFirstName,       
		                    'lrep_sname' => $request->multiStepsSurname,
		                    'lrep_address' => $request->multiStepsRepAddress,       
		                    'lrep_postcode' => $request->multiStepsRepZipcode,
		                    'lrep_city' => $request->multiStepsRepCity,                
		                    'lrep_country' => $request->multiStepsState,
		                    'created_by' => $user->id
		                  ]
		                );		             
		            }

					/* --  ASSIGN USER COMPANY  -- */  
                    $assignclients = UserClient::Create(               
	                    [                
	                        'user_id' => $user->id,
	                        'client_id' => $clients->id
	                    ]
	                );  
                 	/* --end  ASSIGN USER COMPANY -- */ 

					/* -- LOG -- */
					$this->commonClass->addLog(null , 'new-register', 
						[
							'User Name' => $request->multiStepsFirstname,
							'Client Name' => $request->multiStepsCompanyName
						]
					);
					/* --end LOG -- */
				
					/* -- RETURN JSON -- */
					return response()->json([
						'status' => 200,             			
						'message' => 'Created',
						'client_id' => $clients->id
					]);
					/* --end RETURN JSON -- */ 
				} /* --end if email is unique CREATE USER DETAILS  -- */
			} /* -- else USER COUNT 0 -- */
			else
			{			
				/* -- RETURN JSON -- */
				return response()->json([
					'status' => 400,             			
					'message' => 'Already exists'		
				]); 
				/* --end RETURN JSON -- */
			} /* --end if USER COUNT 0 -- */
		}      
		catch (\Exception $e) 
		{  		
			/* -- LOG -- */
			$this->commonClass->addLog(null, 'error-log', 
				[
					'status' => 'Error',
					'controller' => 'Register Controller',
					'method' => 'store',
					'message' => $e->getMessage()
				]
			);
			/* --end LOG -- */

			/* -- RETURN JSON -- */
			return response()->json([   
				'status' => 400,        			
				'message' => $e->getMessage()
			]);
			/* --end RETURN JSON -- */      
		}  
    }
    /* --end POST /register -- */
    
    /* -- GET /register/files/{client_id} -- */   
    public function loadCompanyFilesFromOneDrive(Request $request, $client_id)
    {     	
		try 
		{    
			/* -- GET CLIENT/COMPANY -- */
			$with_client_files = ['clientfiles'];
	        $where_client_files = [
	            'id' => ['operator' => '=', 'value' => $client_id]
	        ]; 
	        $whereHas_client_files = [];   
	        $orderBy_client_files = [];     
	        $client_files = $this->commonClass->getLazy('client', $with_client_files, $where_client_files, $whereHas_client_files, $orderBy_client_files); 
			/* --end GET CLIENT/COMPANY -- */

			return $client_files;
		}
		catch (Exception $e) 
		{
			/* -- LOG -- */
			$this->commonClass->addLog(null, 'error-log', 
				[
					'status' => 'Error',
					'controller' => 'Register Controller',
					'method' => 'loadCompanyFilesFromOneDrive',
					'message' => $e->getMessage()
				]
			);
			/* --end LOG -- */

			/* -- RETURN MESSAGE -- */
			return  $e->getMessage();
			/* --end RETURN MESSAGE -- */     			
		}
    }
    /* --end GET /register/files/{client_id} -- */ 

    /* -- POST /register/files/{client_id} -- */   
    public function uploadCompanyFilesToOneDrive(Request $request, $client_id)
    {     	
		try 
		{    
			/* -- GET CLIENT/COMPANY -- */
			$with_client = [];
			$where_client = [
				'id' => ['operator' => '=', 'value' => $client_id]
			]; 
			$whereHas_client = [];  
			$orderBy_client = [];          
			$client = $this->commonClass->getLazy('client', $with_client, $where_client, $whereHas_client, $orderBy_client, 'first');
			/* --end GET CLIENT/COMPANY -- */

			/* -- GET SYSTEM -- */
			$system = $this->commonClass->getSystemInfoLazy();   
			$systemapi = $system->systemapi->first();     
			/* --end GET SYSTEM -- */

			if(isset($client))
				return $this->apiClass->uploadCompanyFilesToOneDriveLazy($request, $client, null, $systemapi); 
			else           
				return false;
		}
		catch (Exception $e) 
		{
			/* -- LOG -- */
			$this->commonClass->addLog(null, 'error-log', 
				[
					'status' => 'Error',
					'controller' => 'Register Controller',
					'method' => 'uploadCompanyFilesToOneDrive',
					'message' => $e->getMessage()
				]
			);
			/* --end LOG -- */

			/* -- RETURN MESSAGE -- */
			return  $e->getMessage();
			/* --end RETURN MESSAGE -- */     			
		}
    }
    /* --end POST /register/files/{client_id} -- */   
}
