<?php

namespace App\Http\Controllers\company;

use App\Http\Controllers\Controller;

use App\Models\Client;
use App\Models\ClientApi;
use App\Models\ClientApiVatAccNo;
use App\Models\ClientComment;
use App\Models\ClientExtraField;
use App\Models\ClientFiles;
use App\Models\ClientLegalRep;
use App\Models\ClientQA;
use App\Models\ClientQAFiles;
use App\Models\VATRegistration;
use App\Models\VATRegistrationMain;
use App\Models\User;
use App\Models\UserClient;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

use App\Classes\CommonClass;
use App\Classes\ApiClass;
use App\Classes\CVRApiClass;

class CompanyController extends Controller
{    
    public $authUser;
    public $clientIds;

    public $commonClass;
    public $apiClass;
    public $cvrApiClass;
   
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {                      
            $this->commonClass = new CommonClass();
            $this->authUser = $this->commonClass->getAuthUser();
           
            //GET CLIENT IDs based on VAT Reg. for Team User           
            if($this->authUser->role == 'team-user')            
              $this->clientIds = $this->commonClass->getClientIdsFromVatReg($this->authUser);    
            else if($this->authUser->role == 'client-user')            
              $this->clientIds = $this->commonClass->getClientIdsForClientUser($this->authUser);            
            else            
              $this->clientIds = $this->commonClass->getClientIdsFromClient();
           
            $this->apiClass = new ApiClass(); 
            $this->cvrApiClass = new CVRApiClass();            

            return $next($request);
        });                   
    }

    /*Lazy*/    
    public function loadCompanies()
    {       
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser); 

        $other_companies = [];
        if($this->authUser->role == 'team-user');        
          $other_companies = Client::with([
                          'vatregmain' => function ($query) {
                              $query->select(['id',//foreign_key -DON'T REMOVE
                                'client_id',//foreign_key -DON'T REMOVE
                                'country', 'status'
                              ]);                                 
                          }
                        ])                       
                        //->whereNotIn('id', $this->clientIds)
                        ->get();

        $companies = Client::with([
                          'vatregmain' => function ($query) {
                              $query->select(['id',//foreign_key -DON'T REMOVE
                                'client_id',//foreign_key -DON'T REMOVE
                                'country', 'status'
                              ]);                                 
                          }
                        ])
                        //->where('status', 1)
                        ->whereIn('id', $this->clientIds)
                        ->get();                       
            
        $this->commonClass->addLog($this->authUser, 'client-list');

        return view('content.company.index', ['pageConfigs' => $pageConfigs, 'authUser' => $this->authUser, 'companies' => $companies, 'other_companies' => $other_companies]);
    }  

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createCompany()
    {        
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser); 
       
        $this->commonClass->addLog($this->authUser, 'client-create');

        return view('content.company.create', ['pageConfigs' => $pageConfigs, 'authUser' => $this->authUser, 'client' => null, 'title' => 'Creation']);
    }

    //GET cvr-details/{vat_no}
    public function getCVRDetails($vat_no)
    {        
        $client_cvr_view = $this->cvrApiClass->getCVRCompany($vat_no); 
       
        return response()->json([   
            'status' => 200,        
            'client_cvr_view' => ($client_cvr_view) ? $client_cvr_view : null                
        ]);     
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeCompany(Request $request)
    {   
        $clientID = $request->id;

        if ($clientID) {
          // update the value
          $clients = Client::updateOrCreate(
            ['id' => $clientID],
            [
                'client_name' => $request->client_name, 
                //'lrep_fname' => $request->lrep_fname,
                //'lrep_sname' => $request->lrep_sname,
                'lrep_email' => $request->lrep_email,
                'lrep_position' => ($request->lrep_position) ? $request->lrep_position : null,
                'off_houseno' => ($request->off_houseno) ? $request->off_houseno : null,
                'off_street' => ($request->off_street) ? $request->off_street : null,
                'off_officeno' => ($request->off_officeno) ? $request->off_officeno : null,
                'off_city' => $request->off_city,
                'off_postcode' => $request->off_postcode,
                'off_country' => $request->off_country,
                'telephone' => ($request->telephone) ? $request->telephone : null,
                'vatno' => $request->vatno,                
                'email' => ($request->lrep_email) ? $request->lrep_email : null,
                'short_desc' => ($request->short_desc) ? $request->short_desc : null,
                'status' => 0,

                'off_address' => $request->off_address,              
                'employees' => $request->employees,  
                'start_date' => Carbon::parse($request->start_date)->format('Y-m-d'),
                'end_date' => ($request->end_date) ? Carbon::parse($request->end_date)->format('Y-m-d') : null,

                //'lrep_address' => $request->lrep_address,   
                //'lrep_city' => $request->lrep_city,
                //'lrep_postcode' => $request->lrep_postcode,

                'risk_assessment' => $request->risk_assessment,
                'use_trademark' => $request->use_trademark,
                'trading_name' => $request->trading_name,

                'economics_id' => ($request->economics_id) ? $request->economics_id : null,

                'adm_fee' => ($request->adm_fee) ? $request->adm_fee : null,
                'consultancy_low' => ($request->consultancy_low) ? $request->consultancy_low : null,
                'consultancy_high' => ($request->consultancy_high) ? $request->consultancy_high : null
            ]
          );

          $this->commonClass->addLog($this->authUser, 'client-update', 
            [
              'Client Name' => $request->client_name
            ]
          );
          
          return response()->json(
            [
              'status' => 200,
              'message' => 'Updated',
              'client_id' => $clientID
            ]
          ); 
        } else {
         
            //DON'T DELETE FOR NOW
            /*
            //Create New Client User
            $user = User::create([
                'name' => $request->client_name,
                'email' => $request->email,
                'password' => Hash::make('12345678'),
            ])->assignRole(['client-user']);
            */

            $clients = Client::updateOrCreate(              
              [                
                'client_name' => $request->client_name, 
                //'lrep_fname' => $request->lrep_fname,
                //'lrep_sname' => $request->lrep_sname,
                'lrep_email' => $request->lrep_email,
                'lrep_position' => ($request->lrep_position) ? $request->lrep_position : null,
                'off_houseno' => ($request->off_houseno) ? $request->off_houseno : null,
                'off_street' => ($request->off_street) ? $request->off_street : null,
                'off_officeno' => ($request->off_officeno) ? $request->off_officeno : null,
                'off_city' => $request->off_city,
                'off_postcode' => $request->off_postcode,
                'off_country' => $request->off_country,
                'telephone' => ($request->telephone) ? $request->telephone : null,
                'vatno' => $request->vatno,                
                'email' => ($request->lrep_email) ? $request->lrep_email : null,
                'short_desc' => ($request->short_desc) ? $request->short_desc : null,
                'status' => 0,

                'off_address' => $request->off_address,              
                'employees' => $request->employees,  
                'start_date' => Carbon::parse($request->start_date)->format('Y-m-d'),
                'end_date' => ($request->end_date) ? Carbon::parse($request->end_date)->format('Y-m-d') : null,

                //'lrep_address' => $request->lrep_address,   
                //'lrep_city' => $request->lrep_city,
                //'lrep_postcode' => $request->lrep_postcode,

                'risk_assessment' => $request->risk_assessment,
                'use_trademark' => $request->use_trademark,
                'trading_name' => $request->trading_name,

                'economics_id' => ($request->economics_id) ? $request->economics_id : null,

                'adm_fee' => ($request->adm_fee) ? $request->adm_fee : null,
                'consultancy_low' => ($request->consultancy_low) ? $request->consultancy_low : null,
                'consultancy_high' => ($request->consultancy_high) ? $request->consultancy_high : null
              ]
            );
               
            foreach($request->about as $about)
            {
              $client_qa = ClientQA::updateOrCreate(              
                [                
                  'client_id' => $clients->id, 
                  'country' => $about['about_country'],

                  'est_date' => ($about['est_date']) ? Carbon::parse($about['est_date'])->format('Y-m-d') : null,
                  'est_name' => ($about['est_name']) ? $about['est_name'] : null,
                  'est_warehouse_address' => ($about['est_warehouse_address']) ? $about['est_warehouse_address'] : null,
                  'est_warehouse' => ($about['est_warehouse']) ? $about['est_warehouse'] : null,
                  'est_new_warehouse' => ($about['est_new_warehouse']) ? $about['est_new_warehouse'] : null,
                  'est_showroom' => ($about['est_showroom']) ? $about['est_showroom'] : null,
                  'est_branch' => ($about['est_branch']) ? $about['est_branch'] : null,
                  'est_office' => ($about['est_office']) ? $about['est_office'] : null,
                  'est_office_employee' => ($about['est_office_employee']) ? $about['est_office_employee'] : null,
                  'est_emp_authority' => ($about['est_emp_authority']) ? $about['est_emp_authority'] : null,
                  'est_emp_role' => ($about['est_emp_role']) ? $about['est_emp_role'] : null,     
                  'est_emp_type' => ($about['est_emp_type']) ? $about['est_emp_type'] : null,
                  'est_emp_stay' => ($about['est_emp_stay']) ? $about['est_emp_stay'] : null,
                  'est_agent' => ($about['est_agent']) ? $about['est_agent'] : null,
                  'est_invoice' => ($about['est_invoice']) ? $about['est_invoice'] : null,   
                  'est_subcontractor' => ($about['est_subcontractor']) ? $about['est_subcontractor'] : null,
                  'est_goods_value' => ($about['est_goods_value']) ? $about['est_goods_value'] : null,
                  'est_services_value' => ($about['est_services_value']) ? $about['est_services_value'] : null,
                  'est_industry_regulation' => ($about['est_industry_regulation']) ? $about['est_industry_regulation'] : null,
                  'est_cost_element' => ($about['est_cost_element']) ? $about['est_cost_element'] : null,

                  'gs_desc' => ($about['gs_desc']) ? $about['gs_desc'] : null,
                  'gs_value' => ($about['gs_value']) ? $about['gs_value'] : null,
                  'gs_annual_turnover' => ($about['gs_annual_turnover']) ? $about['gs_annual_turnover'] : null,
                  'gs_internal_consumption' => ($about['gs_internal_consumption']) ? $about['gs_internal_consumption'] : null,
                  'gs_sell' => ($about['gs_sell']) ? $about['gs_sell'] : null,
                  'gs_sell_value' => ($about['gs_sell_value']) ? $about['gs_sell_value'] : null,
                  'gs_free_sample' => ($about['gs_free_sample']) ? $about['gs_free_sample'] : null,
                  'gs_influencer' => ($about['gs_influencer']) ? $about['gs_influencer'] : null,
                  'gs_vat_exempt' => ($about['gs_vat_exempt']) ? $about['gs_vat_exempt'] : null,
                  'gs_vat_exempt_turnover' => ($about['gs_vat_exempt_turnover']) ? $about['gs_vat_exempt_turnover'] : null,
                  'gs_service' => ($about['gs_service']) ? $about['gs_service'] : null,
                  'gs_service_value' => ($about['gs_service_value']) ? $about['gs_service_value'] : null,
                  'gs_event' => ($about['gs_event']) ? $about['gs_event'] : null,
                  'gs_market' => ($about['gs_market']) ? $about['gs_market'] : null,
                  'gs_real_estate' => ($about['gs_real_estate']) ? $about['gs_real_estate'] : null,

                  'eu_acquisition_turnover' => ($about['eu_acquisition_turnover']) ? $about['eu_acquisition_turnover'] : null,
                  'eu_reg_export_turnover' => ($about['eu_reg_export_turnover']) ? $about['eu_reg_export_turnover'] : null,
                  'eu_import_turnover' => ($about['eu_import_turnover']) ? $about['eu_import_turnover'] : null,
                  'eu_export_turnover' => ($about['eu_export_turnover']) ? $about['eu_export_turnover'] : null,
                  'eu_export_owner' => ($about['eu_export_owner']) ? $about['eu_export_owner'] : null,

                  'ie_import_turnover' => ($about['ie_import_turnover']) ? $about['ie_import_turnover'] : null,
                  'ie_export_turnover' => ($about['ie_export_turnover']) ? $about['ie_export_turnover'] : null,
                  'ie_export_owner' => ($about['ie_export_owner']) ? $about['ie_export_owner'] : null,

                  'about_vat_countries' => ($about['about_vat_countries']) ? $about['about_vat_countries'] : null,
                  'about_warehouse_countries' => ($about['about_warehouse_countries']) ? $about['about_warehouse_countries'] : null,
                  'about_sell_countries' => ($about['about_sell_countries']) ? $about['about_sell_countries'] : null,
                  'about_originate_countries' => ($about['about_originate_countries']) ? $about['about_originate_countries'] : null,
                  'about_suppliers' => ($about['about_suppliers']) ? $about['about_suppliers'] : null,
                  'about_freight' => ($about['about_freight']) ? $about['about_freight'] : null,
                  'about_bank_details' => ($about['about_bank_details']) ? $about['about_bank_details'] : null,
                  'about_erp' => ($about['about_erp']) ? $about['about_erp'] : null,
                  'about_erp_contact' => ($about['about_erp_contact']) ? $about['about_erp_contact'] : null,
                  'about_main_contact' => ($about['about_main_contact']) ? $about['about_main_contact'] : null,
                  'about_cvr_contact' => ($about['about_cvr_contact']) ? $about['about_cvr_contact'] : null,
                  'about_invoice_email' => ($about['about_invoice_email']) ? $about['about_invoice_email'] : null,
                  'about_invoice_contact' => ($about['about_invoice_contact']) ? $about['about_invoice_contact'] : null,
                  'about_scan_contact' => ($about['about_scan_contact']) ? $about['about_scan_contact'] : null,

                  'created_by' => $this->authUser->user_id
                ]
              );
            } //qa for

            if($request->extra)
            {
              foreach($request->extra as $extra)
              {
                $client_extra = ClientExtraField::updateOrCreate(              
                  [                
                    'client_id' => $clients->id, 
                    'subject' => $extra['extra_subject'],
                    'value' => $extra['extra_value'],                 
                    'created_by' => $this->authUser->user_id
                  ]
                );
              }//extra fields for
            }

            if($request->legalrep)
            {
              foreach($request->legalrep as $legalrep)
              {
                $client_legalrep = ClientLegalRep::updateOrCreate(              
                  [                
                    'client_id' => $clients->id, 
                    'lrep_role' => $legalrep['lrep_role'],
                    'lrep_fname' => $legalrep['lrep_fname'],       
                    'lrep_sname' => $legalrep['lrep_sname'],
                    'lrep_address' => $legalrep['lrep_address'],       
                    'lrep_postcode' => $legalrep['lrep_postcode'],
                    'lrep_city' => $legalrep['lrep_city'],                
                    'lrep_country' => $legalrep['lrep_country'],
                    'created_by' => $this->authUser->user_id
                  ]
                );
              }//legal rep. for
            }
                 
            //$cvr_user_details = $this->cvrApiClass->getCVRCompany($clients->id, $clients->vatno); 
            $cvr_user_details = $this->cvrApiClass->getCVRCompany($clients->vatno, $clients->id); 
                         
            $this->commonClass->addLog($this->authUser, 'client-add', 
              [
                'Client Name' => $request->client_name
              ]
            );
           
            return response()->json(
              [
                'status' => 200,
                'message' => 'Created',
                'client_id' => $clients->id
              ]
            );          
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function editCompany($client_id)
    {        
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser);   
            
        $this->commonClass->addLog($this->authUser, 'client-edit', 
          [
            'Client Name' => $client->client_name
          ]
        );

        return view('content.company.create', ['pageConfigs' => $pageConfigs, 'authUser' => $this->authUser, 'client' => $client, 'title' => 'Edit']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function updateCompany(Request $request, $client_id)
    {              
        if(isset($request->frmClient_client_id))        
          $clientID = $request->frmClient_client_id;        
        else
        {
          if(isset($request->frmLegalRep_client_id))        
            $clientID = $request->frmLegalRep_client_id;  
          else
          {
            if(isset($request->frmAdditional_client_id))        
              $clientID = $request->frmAdditional_client_id;  
            else
            {
              if(isset($request->frmBilling_client_id))              
                $clientID = $request->frmBilling_client_id;             
              else
              {
                if(isset($request->frmAbout_client_id))
                  $clientID = $request->frmAbout_client_id;
                else
                {
                  if(isset($request->frmExtraField_client_id))
                    $clientID = $request->frmExtraField_client_id;
                }
              }
            }
          }
        }

        if ($clientID) 
        {
          $with_client = [];
          $where_client = [
              'id' => ['operator' => '=', 'value' => $client_id]
          ]; 
          $whereHas_client = [];    
          $orderBy_client = [];       
          $client = $this->commonClass->getLazy('client', $with_client, $where_client, $whereHas_client, $orderBy_client, 'first');

          if(isset($request->frmClient_client_id))
          {
            $update = Client::where('id', $clientID)->update(
                [                
                  'client_name' => $request->clientname,                                        
                  'off_city' => $request->off_city,
                  'off_postcode' => $request->off_postcode,
                  'off_country' => $request->off_country,                  
                  'vatno' => $request->vatno,

                  'off_address' => $request->off_address,    

                  'lrep_email' => $request->lrep_email, 
                  'email' => $request->lrep_email,                                
                  'telephone' => $request->telephone,    

                  'short_desc' => $request->short_desc,     
                  'employees' => $request->employees,  
                  'start_date' => Carbon::parse($request->start_date)->format('Y-m-d'),
                  'end_date' => ($request->end_date) ? Carbon::parse($request->end_date)->format('Y-m-d') : null                
                ]
            );
          
            $this->commonClass->addLog($this->authUser, 'client-update', 
              [
                'Client Name' => $request->clientname
              ]
            );
          }
          else if(isset($request->frmLegalRep_client_id))
          {
            // $update = Client::where('id', $clientID)->update(
            //     [                                  
            //       'lrep_fname' => $request->lrep_fname,
            //       'lrep_sname' => $request->lrep_sname,
            //       'lrep_address' => $request->lrep_address,                                                                
            //       'lrep_postcode' => $request->lrep_postcode,  
            //       'lrep_city' => $request->lrep_city
            //     ]
            // );

            if($request->legalrep)
            {
              foreach($request->legalrep as $legalrep)
              {
                $_fields = [                
                  'client_id' => $clientID, 
                  'lrep_role' => $legalrep['lrep_role'],
                  'lrep_fname' => $legalrep['lrep_fname'],       
                  'lrep_sname' => $legalrep['lrep_sname'],
                  'lrep_address' => $legalrep['lrep_address'],       
                  'lrep_postcode' => $legalrep['lrep_postcode'],
                  'lrep_city' => $legalrep['lrep_city'],                
                  'lrep_country' => $legalrep['lrep_country'],
                  'updated_by' => $this->authUser->user_id
                ];

                if(!$legalrep['lrep_id'])
                  $_fields['created_by'] = $this->authUser->user_id;

                $client_legalrep = ClientLegalRep::updateOrCreate( 
                  [
                    'id' => $legalrep['lrep_id']
                  ],             
                  $_fields
                );
              }//legal rep. for    
            }        
            
            $this->commonClass->addLog($this->authUser, 'client-update-legal', 
              [
                'Client Name' => $client->client_name
              ]
            );
          }
          else if(isset($request->frmAdditional_client_id))
          {
            $update = Client::where('id', $clientID)->update(
                [                                  
                  'risk_assessment' => $request->risk_assessment,
                  'use_trademark' => $request->use_trademark,
                  'trading_name' => $request->trading_name
                ]
            );
            
            $this->commonClass->addLog($this->authUser, 'client-update-additional', 
              [
                'Client Name' => $client->client_name
              ]
            );
          }
          else if(isset($request->frmBilling_client_id))
          {           
            $client->economics_id = $request->economics_id;  

            $client->adm_fee = $request->adm_fee;  
            $client->consultancy_low = $request->consultancy_low;  
            $client->consultancy_high = $request->consultancy_high;  
                      
            $client->save(); 
            
            $this->commonClass->addLog($this->authUser, 'client-update-billing', 
              [
                'Client Name' => $client->client_name
              ]
            );
          }
          else if(isset($request->frmAbout_client_id))
          {
            foreach($request->about as $about)
            {
              $client_qa = ClientQA::updateOrCreate(   
                [
                  'id' => $about['qa_id']
                ],           
                [                
                  'client_id' => $clientID, 
                  'country' => $about['about_country'],

                  'est_date' => ($about['est_date']) ? Carbon::parse($about['est_date'])->format('Y-m-d') : null,
                  'est_name' => ($about['est_name']) ? $about['est_name'] : null,
                  'est_warehouse_address' => ($about['est_warehouse_address']) ? $about['est_warehouse_address'] : null,
                  'est_warehouse' => ($about['est_warehouse']) ? $about['est_warehouse'] : null,
                  'est_new_warehouse' => ($about['est_new_warehouse']) ? $about['est_new_warehouse'] : null,
                  'est_showroom' => ($about['est_showroom']) ? $about['est_showroom'] : null,
                  'est_branch' => ($about['est_branch']) ? $about['est_branch'] : null,
                  'est_office' => ($about['est_office']) ? $about['est_office'] : null,
                  'est_office_employee' => ($about['est_office_employee']) ? $about['est_office_employee'] : null,
                  'est_emp_authority' => ($about['est_emp_authority']) ? $about['est_emp_authority'] : null,
                  'est_emp_role' => ($about['est_emp_role']) ? $about['est_emp_role'] : null,     
                  'est_emp_type' => ($about['est_emp_type']) ? $about['est_emp_type'] : null,
                  'est_emp_stay' => ($about['est_emp_stay']) ? $about['est_emp_stay'] : null,
                  'est_agent' => ($about['est_agent']) ? $about['est_agent'] : null,
                  'est_invoice' => ($about['est_invoice']) ? $about['est_invoice'] : null,   
                  'est_subcontractor' => ($about['est_subcontractor']) ? $about['est_subcontractor'] : null,
                  'est_goods_value' => ($about['est_goods_value']) ? $about['est_goods_value'] : null,
                  'est_services_value' => ($about['est_services_value']) ? $about['est_services_value'] : null,
                  'est_industry_regulation' => ($about['est_industry_regulation']) ? $about['est_industry_regulation'] : null,
                  'est_cost_element' => ($about['est_cost_element']) ? $about['est_cost_element'] : null,

                  'gs_desc' => ($about['gs_desc']) ? $about['gs_desc'] : null,
                  'gs_value' => ($about['gs_value']) ? $about['gs_value'] : null,
                  'gs_annual_turnover' => ($about['gs_annual_turnover']) ? $about['gs_annual_turnover'] : null,
                  'gs_internal_consumption' => ($about['gs_internal_consumption']) ? $about['gs_internal_consumption'] : null,
                  'gs_sell' => ($about['gs_sell']) ? $about['gs_sell'] : null,
                  'gs_sell_value' => ($about['gs_sell_value']) ? $about['gs_sell_value'] : null,
                  'gs_free_sample' => ($about['gs_free_sample']) ? $about['gs_free_sample'] : null,
                  'gs_influencer' => ($about['gs_influencer']) ? $about['gs_influencer'] : null,
                  'gs_vat_exempt' => ($about['gs_vat_exempt']) ? $about['gs_vat_exempt'] : null,
                  'gs_vat_exempt_turnover' => ($about['gs_vat_exempt_turnover']) ? $about['gs_vat_exempt_turnover'] : null,
                  'gs_service' => ($about['gs_service']) ? $about['gs_service'] : null,
                  'gs_service_value' => ($about['gs_service_value']) ? $about['gs_service_value'] : null,
                  'gs_event' => ($about['gs_event']) ? $about['gs_event'] : null,
                  'gs_market' => ($about['gs_market']) ? $about['gs_market'] : null,
                  'gs_real_estate' => ($about['gs_real_estate']) ? $about['gs_real_estate'] : null,

                  'eu_acquisition_turnover' => ($about['eu_acquisition_turnover']) ? $about['eu_acquisition_turnover'] : null,
                  'eu_reg_export_turnover' => ($about['eu_reg_export_turnover']) ? $about['eu_reg_export_turnover'] : null,
                  'eu_import_turnover' => ($about['eu_import_turnover']) ? $about['eu_import_turnover'] : null,
                  'eu_export_turnover' => ($about['eu_export_turnover']) ? $about['eu_export_turnover'] : null,
                  'eu_export_owner' => ($about['eu_export_owner']) ? $about['eu_export_owner'] : null,

                  'ie_import_turnover' => ($about['ie_import_turnover']) ? $about['ie_import_turnover'] : null,
                  'ie_export_turnover' => ($about['ie_export_turnover']) ? $about['ie_export_turnover'] : null,
                  'ie_export_owner' => ($about['ie_export_owner']) ? $about['ie_export_owner'] : null,

                  'about_vat_countries' => ($about['about_vat_countries']) ? $about['about_vat_countries'] : null,
                  'about_warehouse_countries' => ($about['about_warehouse_countries']) ? $about['about_warehouse_countries'] : null,
                  'about_sell_countries' => ($about['about_sell_countries']) ? $about['about_sell_countries'] : null,
                  'about_originate_countries' => ($about['about_originate_countries']) ? $about['about_originate_countries'] : null,
                  'about_suppliers' => ($about['about_suppliers']) ? $about['about_suppliers'] : null,
                  'about_freight' => ($about['about_freight']) ? $about['about_freight'] : null,
                  'about_bank_details' => ($about['about_bank_details']) ? $about['about_bank_details'] : null,
                  'about_erp' => ($about['about_erp']) ? $about['about_erp'] : null,
                  'about_erp_contact' => ($about['about_erp_contact']) ? $about['about_erp_contact'] : null,
                  'about_main_contact' => ($about['about_main_contact']) ? $about['about_main_contact'] : null,
                  'about_cvr_contact' => ($about['about_cvr_contact']) ? $about['about_cvr_contact'] : null,
                  'about_invoice_email' => ($about['about_invoice_email']) ? $about['about_invoice_email'] : null,
                  'about_invoice_contact' => ($about['about_invoice_contact']) ? $about['about_invoice_contact'] : null,
                  'about_scan_contact' => ($about['about_scan_contact']) ? $about['about_scan_contact'] : null,

                  'updated_by' => $this->authUser->user_id
                ]
              );
              
              $system = $this->commonClass->getSystemInfoLazy();   
              $systemapi = $system->systemapi->first();     

              if(!$about['qa_id'])
                $about['qa_id'] = $client_qa->id;
              
              if($about['director_file_name'])
                $qa_director_file_names = $this->apiClass->uploadCompanyFilesToOneDriveLazy($about, $client, $this->authUser, $systemapi, 'name');

              if($about['director_file_address'])
                $qa_director_file_addresses = $this->apiClass->uploadCompanyFilesToOneDriveLazy($about, $client, $this->authUser, $systemapi, 'address');              
            } //qa for
          }
          else if(isset($request->frmExtraField_client_id))
          {
            if($request->extra)
            {
              foreach($request->extra as $extra)
              {
                $_fields = [                
                  'client_id' => $clientID, 
                  'subject' => $extra['extra_subject'],
                  'value' => $extra['extra_value'],
                  'updated_by' => $this->authUser->user_id
                ];

                if(!$extra['extra_id'])
                  $_fields['created_by'] = $this->authUser->user_id;

                $client_extra = ClientExtraField::updateOrCreate( 
                  [
                    'id' => $extra['extra_id']
                  ],             
                  $_fields
                );
              }//extra fields for
            }
          }
             
          return response()->json(
              [
                'status' => 200,
                'message' => 'Updated',
                'client_id' => $client->id
              ]
            ); 
        } 
        else 
        {
            // user already exist
            $this->commonClass->addLog($this->authUser, 'client-error');
            
            return response()->json(
              [
                'message' => "cannot update"
              ]
            , 422);
        }      
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $clientid
     * @return \Illuminate\Http\Response
     */
    public function updateCompanyStatus(Request $request, $clientid)
    {      
      if($this->authUser->role == 'super-admin' || $this->authUser->role == 'client-user')        
      {    
        if ($clientid) 
        {
          $client = Client::where('id', $clientid)->first();
          $update = Client::where('id', $clientid)->update(
              [                                        
                'status' => ($request->status == "true") ? 1 : 0
              ]
          );
        
          $this->commonClass->addLog($this->authUser, 'client-update-status', 
            [
              'Client Name' => $client->client_name,
              'Status Text' => $request->statustext
            ]
          );
                  

          $companies = Client::with([
              'vatregmain' => function ($query) {
                  $query->select(['id',//foreign_key -DON'T REMOVE
                    'client_id',//foreign_key -DON'T REMOVE
                    'country', 'status'
                  ]);                                 
              }
            ])           
            ->whereIn('id', $this->clientIds)
            ->get();
          //return response()->json($request->statustext.'d');       
          return response()->json(
            [
              'message' => $request->statustext.'d',
              'companies' => $companies,
            ]
          );       
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
      else 
      {
          // Authuser dont have permission
          $this->commonClass->addLog($this->authUser, 'client-error-auth',
            [
              'Client Name' => $client->client_name,
              'Status Text' => $request->statustext
            ]
          );
          
          return response()->json("Don't have permission to ".$request->statustext);
      }     
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function deleteCompany(Client $client)
    {       
        $this->commonClass->addLog($this->authUser, 'client-delete', 
          [
            'Client Name' => $client->client_name
          ]
        );

        $clients = Client::where('id', $client->id)->delete();
    }
    
    //GET company/{client_id}
    public function showCompany($client_id)
    {
      $show = true;
      if(($this->authUser->role == 'client-user') && !in_array($client_id, $this->clientIds))
        $show = false;      

      if($show)                          
      {
        /* -- CHECK and CREATE VAT Reg. Row -- */
          $vat_reg_main = $this->commonClass->checkAndCreateVATReg($this->authUser, $client_id);
        /* --/ CHECK and CREATE VAT Reg. Row -- */  
        
        /* -- PAGE CONFIG -- */
          $pageConfigs = $this->commonClass->getPageConfig($this->authUser);
        /* --/ PAGE CONFIG -- */  
            
        /* -- VAT REG. MAIN TAB -- */          
        $with_client = [
          'vatregmain',
          'vatregmain.clientapi',          
          'clientcomment',
          'clientcomment.user',                                  
          'clientcomment.user.roles',
          'clientcomment.user.dvuser',
          'userclient',
          'userclient.user',
          'userclient.user.dvuser',

          'clientqa',
          'clientqa.clientqafiles',

          'clientextrafield',
        ];  
        $where_client = [
          'id' => ['operator' => '=', 'value' => $client_id]
        ]; 
        $whereHas_client = [];    
        $orderBy_client = [];            
        $client = $this->commonClass->getLazy('client', $with_client, $where_client, $whereHas_client, $orderBy_client, 'first');
        /* --/ VAT REG. MAIN TAB -- */  

        if($client)
        {
          /* -- COVER PHOTO SECTION -- */   
          $system = $this->commonClass->getSystemInfoLazy(); 
          $systemapi = $system->systemapi->first();  

          foreach($client->clientfiles as $clientfile)       
          {      
            if($clientfile->file_id != NULL && $clientfile->file_for != 'other')
            {       
              $downloadfile = $this->apiClass->loadFromOneDriveLazy($clientfile, $systemapi);
              if(isset($downloadfile->error))   
              {

              } 
              else   
              {
                if ($clientfile)                
                  $clientfile->downloadurl = $downloadfile['download_url'];
              }
            }
          }

          /* -- COVER PHOTO SECTION -- */

          /* -- COVER PHOTO SECTION -- */
          $with_team_users = [
            'uservatregmain',
            'uservatregmain.user',
            'uservatregmain.user.dvuser',

            'vatreg',
            'vatreg.uservatreg',
            'vatreg.uservatreg.user',
            'vatreg.uservatreg.user.dvuser'                      
          ];      
          $where_team_users = [
            'client_id' => ['operator' => '=', 'value' => $client_id]
          ]; 
          $whereHas_team_users = [];     
          $orderBy_team_users = [];              
          $team_users = $this->commonClass->getLazy('vatregmain', $with_team_users, $where_team_users, $whereHas_team_users, $orderBy_team_users);                 
          /* --/ COVER PHOTO SECTION -- */

          /* -- API CONNECTION TAB -- */
            //$api_connection = $this->commonClass->getApiConnection($this->clientIds, $client->id);
            
            //$api_vat_acc_no = $this->commonClass->getApiVatAccNo($this->clientIds, $client->id);
          /* --/ API CONNECTION TAB -- */          

          /* -- CONTACTS TAB -- */            
            $with_client_users = ['dvuser', 'roles'];
            $where_client_users = []; 
            $whereHas_client_users = [
              'roles' => ['field' => 'name', 'value' => 'client-user'],
              'dvuser' => ['field' => 'is_deleted', 'value' => 0]
            ];
            $orderBy_client_users = [];                                
            $client_users = $this->commonClass->getLazy('user', $with_client_users, $where_client_users, $whereHas_client_users, $orderBy_client_users);                                
          /* --/ CONTACTS TAB -- */

          /* -- GET EXCEL COLUMNS -- */
          $excel_columns = $this->commonClass->listExcelColumns();
          /* --/ GET EXCEL COLUMNS -- */

          /* -- GET EXCEL COLUMN TEMPLATES -- */
          //$excel_column_templates = $this->commonClass->getExcelColumnTemplatesLazy();
          /* --/ GET EXCEL COLUMN TEMPLATES -- */  

          /* -- GET ANYEXCEL TEMPLATES -- */
          $anyexcel_templates_result = $this->commonClass->getAnyExcelTemplates();          
          $anyexcel_templates = $anyexcel_templates_result->filter(function ($anyexcel_template) use($client_id) {
              return ($anyexcel_template->client_id == $client_id); 
          });          
          /* --/ GET ANYEXCEL TEMPLATES -- */   

          $result = $this->commonClass->getAllVatRegQuery($this->authUser, $client_id, false); 

          $client_histories = $this->loadCompanyHistoryTab($client_id, true);
          
          $note_countries = VATRegistrationMain::distinct()->orderBy('country', 'asc')->pluck('country')->toArray();         
          
//dd($client_histories);
          // if($client->status == 0)
          //   return abort(403, 'Cannot view inactive client.');
          // else
          // {
            /* -- LOG -- */
              $this->commonClass->addLog($this->authUser, 'client-view',
                [
                  'Client Name' => $client->client_name
                ]
              );
            /* --/ LOG -- */

            return view('content.company.view',
              [
                'pageConfigs' => $pageConfigs,
                'authUser' => $this->authUser,
                'client' => $client,
                'otherClient' => (in_array($client_id, $this->clientIds)) ? false : true,
                
                'note_countries' => $note_countries,
                
                'client_histories' => $client_histories,

                'team_users' => $team_users,
                'clientusers' => $client_users,
               
                'accountnos' => [],
                'result' => $result,
                'excel_columns' => $excel_columns,
                //'excel_column_templates' => $excel_column_templates,
                'anyexcel_templates' => $anyexcel_templates
              ]
            );
        } 
        else
          return abort(404, 'Client not found.');
      }
      else
        return abort(403, 'User does not have the right to access this page.');
    }

    /* -- GET company/history/{client_id}*/
    public function loadCompanyHistoryTab($client_id, $direct = false)
    {       
        try    
        {    
          $extrafieldTimeline = ClientExtraField::leftJoin('dv_users', function($join) {
                              $join->on('dv_client_extra_fields.created_by', '=', 'dv_users.user_id');                      
                            })
                            ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'dv_users.user_id')
                            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')  
                            
                            ->select(                             
                              DB::raw("'primary' AS color"),     
                              DB::raw("'left' AS direction"), 
                              DB::raw("'Field' AS subject"), 
                              DB::raw('CONCAT_WS("", dv_client_extra_fields.subject, ": " , "<a href=\"javascript:void(0);\" class=\"extra-field\" data-extra_id=\"", dv_client_extra_fields.id , "\">",  dv_client_extra_fields.value, "</a>") AS message'),
                              'dv_users.firstname AS firstname', 'dv_users.lastname AS lastname',
                              'dv_client_extra_fields.created_at AS created_at', 'roles.name as role',
                              'dv_users.telephone AS telephone',
                                                
                              DB::raw("'field' AS filetype"),
                              'dv_client_extra_fields.id AS fileid',
                            )
                            ->where('dv_client_extra_fields.client_id', $client_id)                             
                            ->groupBy('firstname', 'lastname', 'created_at', 'role', 'telephone', 'dv_client_extra_fields.id', 'dv_client_extra_fields.subject')
                            ->orderBy('dv_client_extra_fields.id', 'DESC')    
                            ->get();
          foreach($extrafieldTimeline as $user)     
            $user->profile_photo_url = User::defaultProfilePhotoUrl($user, true);  

          $uploadFileTimeline = ClientFiles::leftJoin('dv_users', function($join) {
                              $join->on('dv_client_files.created_by', '=', 'dv_users.user_id');                      
                            })
                            ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'dv_users.user_id')
                            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')  
                            
                            ->select(                             
                              DB::raw("'secondary' AS color"),     
                              DB::raw("'left' AS direction"), 
                              DB::raw("'Upload File' AS subject"),                              
                              DB::raw('CONCAT_WS("", dv_client_files.file_for, ": " , "<a href=\"javascript:void(0);\" class=\"upload-field\" data-upload_id=\"", dv_client_files.id , "\">",    dv_client_files.subject, "</a>") AS message'),

                              'dv_users.firstname AS firstname', 'dv_users.lastname AS lastname',
                              'dv_client_files.created_at AS created_at', 'roles.name as role',
                              'dv_users.telephone AS telephone',
                                                
                              DB::raw("'field' AS filetype"),
                              'dv_client_files.id AS fileid',
                            )
                            ->where('dv_client_files.client_id', $client_id)                             
                            ->groupBy('firstname', 'lastname', 'created_at', 'role', 'telephone', 'dv_client_files.id', 'dv_client_files.subject')
                            ->orderBy('dv_client_files.id', 'DESC')    
                            ->get();
                          
          foreach($uploadFileTimeline as $user)     
            $user->profile_photo_url = User::defaultProfilePhotoUrl($user, true);  

          $timelines = array_merge(
                      $extrafieldTimeline->toarray(), 
                      $uploadFileTimeline->toarray()                      
                   );     
          $client_histories = collect($timelines)->sortByDesc('created_at')->values();
 
          if($direct)
            return $client_histories;
          else
          {
            /* -- RENDER VIEW -- */
            $view = view('_partials._content._company.history', 
                    compact(                        
                        'client_histories'
                    )
                )->render();
            /* --end RENDER VIEW -- */

            /* -- RETURN JSON -- */
            return response()->json(
                [
                  'status' => 200,             
                  'view' => $view
                ]
            );                            
            /* --end RETURN JSON -- */
          }
        }      
        catch (\Exception $e) 
        { 
          return $e->getMessage();
        } 
    }
    /* --end GET company/history/{client_id}*/

    /* -- GET company/qa/{client_id}*/
    public function loadCompanyQATab(Request $request, $client_id)
    {       
        try    
        {    
          /* -- GET QA's -- */          
          $client = Client::with(['clientqa', 'clientqa.clientqafiles'])->where('id', $client_id)->first();            
          /* --end GET QA's -- */
          
          /* -- AUTH USER -- */
          $authUser = $this->authUser;
          /* --end AUTH USER -- */
                   
          /* -- RENDER VIEW -- */
          $view = view('_partials._content._company.q-and-a', 
                  compact(
                      'authUser', 
                      'client'
                  )
              )->render();
          /* --end RENDER VIEW -- */

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
              'controller' => 'Company Controller',
              'method' => 'loadQATab',
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
    /* --end GET company/qa/{client_id}*/
      
    /* -- DELETE company/qa/{qa_id}*/
    public function deleteCompanyQA($qa_id)
    {             
      $clientqa = ClientQA::with(['client'])->where('id', $qa_id)->first();      
      
      $client_qa_delete = ClientQA::where('id', $qa_id)->delete();

      $this->commonClass->addLog($this->authUser, 'client-qa-delete', 
        [
          'Client Name' => $clientqa->client->client_name,
          'QA Country' => $clientqa->about_country
        ]
      );

      return response()->json(
        [
          'status' => 200,
          'message' => 'Deleted'
        ]
      ); 
    }
    /* --end DELETE company/qa/{qa_id}*/

    /* -- DELETE company/extrafield/{extra_id}*/
    public function deleteCompanyExtraFields($extra_id)
    {             
      $clientextrafield = ClientExtraField::with(['client'])->where('id', $extra_id)->first();      
      
      $clientextrafield_delete = ClientExtraField::where('id', $extra_id)->delete();

      $this->commonClass->addLog($this->authUser, 'client-extrafield-delete', 
        [
          'Client Name' => $clientextrafield->client->client_name,
          'Subject' => $clientextrafield->subject,
          'Value' => $clientextrafield->value
        ]
      );

      return response()->json(
        [
          'status' => 200,
          'message' => 'Deleted'
        ]
      ); 
    }
    /* --end DELETE company/extrafield/{extra_id}*/

  
    /*URL GET company/files/$client_id */
    public function loadCompanyFilesFromOneDrive(Request $request, $client_id)
    { 
      try {               
        $with_client_files = ['clientfiles'];
        $where_client_files = [
            'id' => ['operator' => '=', 'value' => $client_id]
        ]; 
        $whereHas_client_files = [];   
        $orderBy_client_files = [];     
        $client_files = $this->commonClass->getLazy('client', $with_client_files, $where_client_files, $whereHas_client_files, $orderBy_client_files); 
       
        // $client = Client::rightJoin('dv_client_files', function($join) {
        //             $join->on('dv_client_files.client_id', '=', 'dv_clients.id');
        //           })                  
        //           ->select('dv_clients.id AS client_id','dv_clients.client_name', 'dv_clients.vatno', 'dv_client_files.*')
        //           ->where('dv_clients.id', $client_id)
        //           ->get(); 
                
        return $client_files;                                    
      }
      catch (Exception $e) {
        return  $e->getMessage();
      }
    }

    /*URL POST company/files/$client_id */
    public function uploadCompanyFilesToOneDrive(Request $request, $client_id)
    { 
      try { 
        $with_client = [];
        $where_client = [
            'id' => ['operator' => '=', 'value' => $client_id]
        ]; 
        $whereHas_client = [];  
        $orderBy_client = [];          
        $client = $this->commonClass->getLazy('client', $with_client, $where_client, $whereHas_client, $orderBy_client, 'first');

        // $client = Client::                                  
        //           select('dv_clients.id AS client_id','dv_clients.client_name', 'dv_clients.vatno')                 
        //           ->where('dv_clients.id', $client_id)
        //           ->first();
        
        $system = $this->commonClass->getSystemInfoLazy();   
        $systemapi = $system->systemapi->first();     
        
        if(isset($client))
          //return $this->apiClass->uploadClientFilesToOneDrive($request, $client, $this->authUser, $system); 
          return $this->apiClass->uploadCompanyFilesToOneDriveLazy($request, $client, $this->authUser, $systemapi); 
        else                                            
          return false;
      }
      catch (Exception $e) {
        return  $e->getMessage();
      }
    }

    /*URL DELETE company/files/$file_id */
    public function deleteCompanyFilesFromOneDrive(Request $request, $id)
    { 
      try {   
        $maintable = ($request->file_type == 'name' || $request->file_type == 'address') ? 'clientqafiles' : 'client';
        $with_client = ($request->file_type == 'name' || $request->file_type == 'address') ? [] : ['clientfiles'];
        $where_client = []; 
        if($request->file_type == 'name' || $request->file_type == 'address')
        {
          $where_client = [
            'id' => ['operator' => '=', 'value' => $id]
          ]; 
          $whereHas_client = [
            //'clientqafiles' => ['field' => 'id', 'value' => $id]
          ];
        }
        else        
          $whereHas_client = [
            'clientfiles' => ['field' => 'id', 'value' => $id]
          ];   
        $orderBy_client = []; 
        $client = $this->commonClass->getLazy($maintable, $with_client, $where_client, $whereHas_client, $orderBy_client, 'first');
        
        // $client = Client::leftJoin('dv_client_files', function($join) {
        //               $join->on('dv_client_files.client_id', '=', 'dv_clients.id');                      
        //             })                                               
        //             ->select('dv_clients.id AS client_id','dv_clients.client_name', 'dv_clients.vatno', 'dv_client_files.*')                    
        //             ->where('dv_client_files.id', $id)                        
        //             ->first();
        
        //$client_id = $client->client_id;
        
        $file_type = ($request->file_type == 'name' || $request->file_type == 'address') ? 'clientqafiles' : 'clientfiles';
        $system = $this->commonClass->getSystemInfoLazy(); 
        $systemapi = $system->systemapi->first();         
        
        //$deleteResult = $this->apiClass->deleteFromOneDrive($client, $this->authUser, $system);   
        //$deleteResult = $this->apiClass->deleteFromOneDriveLazy($client, $this->authUser, $systemapi);          
        $deleteResult = $this->apiClass->deleteFromOneDriveLazy($client, $systemapi, $file_type);  

        //$clientfiles = ClientFiles::where('id', $id)->delete();
        $with_client_files = [];
        $where_client_files = [
            'id' => ['operator' => '=', 'value' => $id]
        ];         
        $whereHas_client_files = [];   
        $orderBy_client_files = []; 
        $client_files = $this->commonClass->getLazy($file_type, $with_client_files, $where_client_files, $whereHas_client_files, $orderBy_client_files, 'delete');
             
        if($request->file_type == 'name' || $request->file_type == 'address')  
        {     
          $with_client = ['clientqa'];
          $where_client = [];                
          $whereHas_client = [
            'clientqa' => ['field' => 'id', 'value' => $client->qa_id]
          ];   
          $orderBy_client = []; 
          $client = $this->commonClass->getLazy('client', $with_client, $where_client, $whereHas_client, $orderBy_client, 'first');
         
          $deleteResult = response()->json([
            'status'          => "deleted",
            'client_id' => $client->id,
            'id' => $id,
            'file_type' => $request->file_type
          ]);
          
          $this->commonClass->addLog($this->authUser, 'client-qa-file-delete', 
            [
              'Client Name' => $client->client_name,
              'Country' => $client->clientqa[0]->country
            ]
          ); 
        }
        else
          $this->commonClass->addLog($this->authUser, 'client-file-delete', 
            [
              'Client Name' => $client->client_name
            ]
          ); 

        return $deleteResult;                             
      }
      catch (Exception $e) {
        return  $e->getMessage();
      }
    }  

    public function loadCompanyComment(Request $request, $client_id)
    {        
        //GET VAT Returns                
        $with_client_comments = [
          'clientcomment',
          'clientcomment.user',                                  
          'clientcomment.user.roles',
          'clientcomment.user.dvuser'
        ];
        $where_client_comments = [];         
        $whereHas_client_comments = [
          'clientcomment' => ['field' => 'client_id', 'value' => $client_id]
        ];   
        $orderBy_client_comments = []; 
        $client_with_comments = $this->commonClass->getLazy('client', $with_client_comments, $where_client_comments, $whereHas_client_comments, $orderBy_client_comments);
      
        if ($client_with_comments->isEmpty())
          return response()->json(
            [
              'status' => 200,
              'view' => ''              
            ]
          );          
        else
        {           
          $authUser = $this->authUser;
          $client_comments = $client_with_comments->first()->clientcomment;
          $view = view('_partials._content._company.comment', compact('client_comments', 'authUser'))->render();            
          
          return response()->json(
            [
              'status' => 200,
              'client_id' => $client_id,
              'view' => $view                
            ]
          );          
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postCompanyComment(Request $request, $client_id)
    {       
        $clientID = $client_id;

        if ($clientID) 
        {      
          //$client = $this->commonClass->getClientDetails($clientID);    
          $with_client = [];  
          $where_client = [
            'id' => ['operator' => '=', 'value' => $client_id]
          ]; 
          $whereHas_client = [];  
          $orderBy_client = [];            
          $client = $this->commonClass->getLazy('client', $with_client, $where_client, $whereHas_client, $orderBy_client, 'first'); 

          $clientcomment = ClientComment::updateOrCreate(            
            [
                'client_id' => $clientID, 
                'comment' => $request->client_comment_quill,
                'created_by' => $this->authUser->user_id                 
            ]
          );         
         
          $this->commonClass->addLog($this->authUser, 'client-comment-add', 
            [
              'Client Name' => $client->client_name
            ]
          );
          
          return response()->json(
            [
              'status' => 200,
              'message' => 'Created',
              'client_id' => $clientID
            ]
          ); 
        } 
    }

    /**
     * Delete Company Comment.    
     */
    public function deleteCompanyComment(Request $request, $comment_id)
    {       
        $clientID = $request->client_id;

        if ($clientID) 
        {              
          $with_client = [];  
          $where_client = [
            'id' => ['operator' => '=', 'value' => $clientID]
          ]; 
          $whereHas_client = [];  
          $orderBy_client = [];            
          $client = $this->commonClass->getLazy('client', $with_client, $where_client, $whereHas_client, $orderBy_client, 'first'); 

          $clientcomment = ClientComment::where('id', $comment_id)
                              ->where('client_id', $clientID)
                              ->delete();
         
          $this->commonClass->addLog($this->authUser, 'client-comment-delete', 
            [
              'Client Name' => $client->client_name
            ]
          );
          
          return response()->json(
            [
              'status' => 200,
              'message' => 'Created',
              'client_id' => $clientID
            ]
          ); 
        } 
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function assignClientUser(Request $request)
    {
        $clientID = $request->client_id;
        $clientUserIDs = $request->chk_client_user;

        if ($clientID) { 
          //$client_client = UserClient::where('client_id', $clientID)->delete();
          $with_client_client = [];  
          $where_client_client = [
            'client_id' => ['operator' => '=', 'value' => $clientID]
          ]; 
          $whereHas_client_client = [];  
          $orderBy_client_client = [];            
          $client_client = $this->commonClass->getLazy('userclient', $with_client_client, $where_client_client, $whereHas_client_client, $orderBy_client_client, 'delete');     

          if(!empty($clientUserIDs))       
          {
            foreach($clientUserIDs as $clientUserID)
            {
                $clients = UserClient::Create(               
                  [                
                      'user_id' => $clientUserID,
                      'client_id' => $clientID
                  ]
                );                
            } 

            $with_client = [                  
              'userclient',
              'userclient.user',
              'userclient.user.dvuser'
            ];  
            $where_client = [
              'id' => ['operator' => '=', 'value' => $clientID]
            ]; 
            $whereHas_client = [];   
            $orderBy_client = [];        
            $client = $this->commonClass->getLazy('client', $with_client, $where_client, $whereHas_client, $orderBy_client, 'first');  

            // user updated
            return response()->json([
              'status' => 200,             
              'client' => $client,
              'message' => 'Assigned'
            ]);   
          }
          // user updated
          return response()->json('Not Selected');
        }         
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DVUser  $dvuser
     * @return \Illuminate\Http\Response
     */
    public function assignedClientUser($client_id)
    {
      $clientUsers = UserClient::leftJoin('dv_clients', function($join) {
                      $join->on('dv_clients.id', '=', 'dv_user_client.client_id');
                    })                                                          
                    ->where('dv_clients.id', $client_id)
                    ->get();

        return  $clientUsers;                  
    }     

    // /**
    //  * Store a newly created resource in storage.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @return \Illuminate\Http\Response
    //  */
    // public function assignCompanyUser(Request $request)
    // {
    //     $clientID = $request->client_id;
    //     $clientUserIDs = $request->chk_client_user;

    //     if ($clientID) { 
    //       //$client_client = UserClient::where('client_id', $clientID)->delete();     
    //       $with_client_client = [];  
    //       $where_client_client = [
    //         'client_id' => ['operator' => '=', 'value' => $clientID]
    //       ]; 
    //       $whereHas_client_client = [];             
    //       $client_client = $this->commonClass->getLazy('userclient', $with_client_client, $where_client_client, $whereHas_client_client, 'delete'); 

    //       if(!empty($clientUserIDs))       
    //       {
    //         foreach($clientUserIDs as $clientUserID)
    //         {
    //             $clients = UserClient::Create(               
    //               [                
    //                   'user_id' => $clientUserID,
    //                   'client_id' => $clientID
    //               ]
    //             );                
    //         } 
    //         // user updated
    //         return response()->json('Assigned');   
    //       }
    //       // user updated
    //       return response()->json('Not Selected');
    //     }         
    // }

    // /**
    //  * Display the specified resource.
    //  *
    //  * @param  \App\Models\DVUser  $dvuser
    //  * @return \Illuminate\Http\Response
    //  */
    // public function assignedClientUser($client_id)
    // {
    //   $clientUsers = UserClient::leftJoin('dv_clients', function($join) {
    //                   $join->on('dv_clients.id', '=', 'dv_user_client.client_id');
    //                 })                                                          
    //                 ->where('dv_clients.id', $client_id)
    //                 ->get();

    //     return  $clientUsers;                  
    // }   
}
