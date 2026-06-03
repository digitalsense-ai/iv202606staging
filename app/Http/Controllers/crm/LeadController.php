<?php

namespace App\Http\Controllers\crm;

use App\Http\Controllers\Controller;
use App\Models\CRMLead;
use App\Models\CRMLeadContact;
use App\Models\CRMReminder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use DB;

use App\Classes\CommonClass;
use App\Classes\CVRApiClass;

class LeadController extends Controller
{
    public $authUser;

    public $commonClass;
    public $cvrApiClass;
    
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->commonClass = new CommonClass();
            $this->authUser = $this->commonClass->getAuthUser();   
            
            $this->cvrApiClass = new CVRApiClass();

            return $next($request);
        });
    }

    /* -- GET /crm/leads -- */
    public function index()
    {
        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser);      
        /* --end PAGE CONFIG -- */
        
        $leads = CRMLead::with(['contact', 'quotes'])->get();
        return view('content.crm.leads.index', compact(
            'leads',             
            'pageConfigs'
        ));
    }
    /* --end GET /crm/leads -- */

    /* -- GET /crm/leads/create -- */
    public function create()
    {
        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser);      
        /* --end PAGE CONFIG -- */

        /* -- USER LIST -- */
        $users = $this->commonClass->getUsersLazy();
        /* --end USER LIST -- */

        return view('content.crm.leads.create', compact('pageConfigs', 'users'));
    }
    /* --end GET /crm/leads/create -- */

    /* -- GET /crm/leads/{cvr_no}/company -- */
    public function getCompany($cvr_no)
    {
        /* -- GET COMPANY DETAILS -- */
        $company = $this->cvrApiClass->getCVRCompany($cvr_no, null, [], 'crm');
        /* --end GET COMPANY DETAILS -- */

        $company_view = view('_partials._content._crm.company', 
            compact(
                'company'
            )
        )->render(); 

        return $company_view;
    }
    /* --end GET /crm/leads/{cvr_no}/company -- */

    /* -- GET /crm/leads/{user_id}/user -- */
    public function getUser($user_id)
    {        
        /* -- GET USER -- */
        $user = $this->commonClass->getUsersLazy($user_id);        
        /* --end GET USER -- */

        $user_view = view('_partials._content._crm.user', 
            compact(
                'user'
            )
        )->render(); 

        return $user_view;
    }
    /* --end GET /crm/leads/{user_id}/user -- */

    /* -- POST /crm/leads/create -- */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {  
            $lead_id = ($request->crm_lead_id) ?? null;          

            $exists_lead = CRMLead::where('id', $lead_id)->first();

            $lead = CRMLead::updateOrCreate(
                [
                    'id' => $lead_id
                ],
                [
                    'cvr_number' => $request->crm_cvr_no,
                    'company_name' => $request->crm_client_name,
                    'company_address' => $request->crm_off_address,
                    'company_postcode' => $request->crm_off_postcode,
                    'company_city' => $request->crm_off_city,
                    'company_country' => $request->crm_off_country,
                    'company_telephone' => $request->crm_telephone,
                    'company_email' => $request->crm_lrep_email,
                    'company_website' => $request->crm_website,
                    'company_desc' => $request->crm_short_desc,
                    'company_employees' => $request->crm_employees,
                    'financial_year' => $request->crm_financial_year,
                    'revenue' => $request->crm_revenue,
                    'rating' => $request->crm_rating,
                    'potential_countries' => is_array($request->crm_potential_countries)
                        ? $request->crm_potential_countries
                        : json_decode($request->crm_potential_countries, true),

                    'potential_products' => is_array($request->crm_potential_products)
                        ? $request->crm_potential_products
                        : json_decode($request->crm_potential_products, true),
                    'lead_date' => ($request->crm_lead_date) ?? Carbon::now()->format('Y-m-d'),
                    'status' => $request->status ?? 'new',
                    'created_by' => ($lead_id) ? $exists_lead->created_by : $this->authUser->user_id,
                    'updated_by' => $this->authUser->user_id
                ]
            );

            CRMLeadContact::updateOrCreate(
                [
                    'lead_id' => $lead_id
                ],
                [
                    'lead_id' => $lead->id,
                    'first_name' => $request->crm_user_firstname,
                    'last_name' => $request->crm_user_lastname,
                    'email' => $request->crm_user_email,
                    'phone' => $request->crm_user_telephone,                
                    'designation' => $request->crm_user_designation,
                    'lang' => $request->crm_user_lang,
                    'role' => $request->crm_user_role,
                    'created_by' => ($lead_id) ? $exists_lead->created_by : $this->authUser->user_id,
                    'updated_by' => $this->authUser->user_id
                ]
            );

            if($lead_id && $request->status == 'rejected')
            {
                $reminder = CRMReminder::where('module_type', 'lead')
                                ->where('module_id', $lead_id)
                                ->where('email_sent', 0)
                                ->first();
                  
                if($reminder)
                    $reminder->delete();
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();

            /* -- RETURN JSON -- */
            return response()->json([   
              'status' => 'error',                 
              'message' => $e->getMessage()
            ]);
            /* --end RETURN JSON -- */

            //throw $e;
        }

        // if($request->status == 'rejected' || $request->status == 'reminder')
        // {
            /* -- RETURN JSON -- */
            return response()->json([   
              'status' => 'success',
              'lead_id' => $lead->id
            ]);
            /* --end RETURN JSON -- */
        // }
        // return redirect()->route('leads.index');
    }
    /* --end POST /crm/leads/create -- */

    /* -- GET /crm/leads/{lead} -- */
    public function edit(CRMLead $lead)
    {
        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser);      
        /* --end PAGE CONFIG -- */

        return view('content.crm.leads.create', compact('pageConfigs', 'lead'));
    }
    /* --end GET /crm/leads/{lead} -- */

    // public function update(Request $request, Lead $lead)
    // {
    //     $lead->update($request->all());
    //     $lead->contact()->update($request->only([
    //         'first_name','last_name','email','phone','role'
    //     ]));

    //     return redirect()->route('content.leads.index');
    // }

    // public function destroy(CRMLead $lead)
    // {
    //     $lead->delete();
    //     return back();
    // }
}