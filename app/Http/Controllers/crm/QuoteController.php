<?php

namespace App\Http\Controllers\crm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use App\Models\CRMQuote;
use App\Models\CRMQuoteAddon;
use App\Models\CRMLead;
use App\Models\CRMReminder;
use DB;


use App\Classes\CommonClass;
use App\Classes\CVRApiClass;

class QuoteController extends Controller
{
    public $authUser;

    public $commonClass;

    public $packages = [
        'essential' => 0,
        'basic' => 2500,
        'plus_basic' => 3500,
        'premium' => 5000,
        'ultimate' => 7500
    ];

    public $addons = [
            'EORI number',
            'Customs credit',
            'Enterprise',
            'Purchase invoice per unit',
            'Document per unit',
            'Registration and authority fees',
            'VAT reconciliation',
            'Transfer from another provider',
            'Transfer to another provider',
            'CASH account statement per month'
        ];
    
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->commonClass = new CommonClass();
            $this->authUser = $this->commonClass->getAuthUser();   
                                    
            return $next($request);
        });
    }

    /**
     * Display all quotes grouped by status
     */
    public function index(Request $request)
    {
        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser);      
        /* --end PAGE CONFIG -- */
       
        $quotes = CRMQuote::with(['lead', 'lead.contact', 'addons', 'children'])                    
                    ->get();

        return view('content.crm.quotes.index', compact('pageConfigs', 'quotes'));       
    }

    /**
     * Display all quotes grouped by status
     */
    public function index1(Request $request)
    {
        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser);      
        /* --end PAGE CONFIG -- */

        $status = $request->status ?? 'active';

        $quotes = CRMQuote::with(['lead', 'lead.contact', 'addons', 'children'])
                    ->where('status', $status)
                    ->whereNull('parent_quote_id')
                    ->latest()
                    ->paginate(20);
                            
        return view('content.crm.quotes.index2', compact('pageConfigs', 'quotes', 'status'));
    }

    /**
     * Create quote from lead
     */
    public function create(Request $request, $lead_id)
    {
        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser);      
        /* --end PAGE CONFIG -- */

        $lead = CRMLead::findOrFail($lead_id);

        // $packages = [
        //     'essential' => 0,
        //     'basic' => 2500,
        //     'plus_basic' => 3500,
        //     'premium' => 5000,
        //     'ultimate' => 7500
        // ];

        // $addons = [
        //     'EORI number',
        //     'Customs credit',
        //     'Enterprise',
        //     'Purchase invoice per unit',
        //     'Document per unit',
        //     'Registration and authority fees',
        //     'VAT reconciliation',
        //     'Transfer from another provider',
        //     'Transfer to another provider',
        //     'CASH account statement per month'
        // ];

        $packages = $this->packages;        
        //$addons = $this->addons;

        $addons = CRMAddons::where('enabled', 1);
        
        return view('content.crm.quotes.create', compact(
            'pageConfigs',
            'lead',
            'packages',
            'addons'
        ));
    }

    /**
     * Store quote
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {

            $quote_id = ($request->quote_id) ?? null;           
 
            $exists_quote = CRMQuote::where('id', $quote_id)->first();

            $quote = CRMQuote::updateOrCreate(
                [
                    'id' => $quote_id
                ],
                [
                    'lead_id' => $request->lead_id,
                    'package' => $request->package,
                    'base_price' => $request->base_price,
                    'registration_price' => $request->registration_price,
                    'status' => ($quote_id) ? $exists_quote->status : 'active',
                    'version' => ($quote_id) ? $exists_quote->version : 1,
                    'parent_quote_id' => ($quote_id) ? $exists_quote->parent_quote_id : null,
                    'root_quote_id' => ($quote_id) ? $exists_quote->root_quote_id : null,                      
                    'created_by' => ($quote_id) ? $exists_quote->created_by : $this->authUser->user_id,
                    'updated_by' => $this->authUser->user_id
                ]
            );
            
            if($quote_id)
            {

            }
            else
            {
                // Set root_quote_id = own id
                $quote->update([
                    'root_quote_id' => $quote->id
                ]);
            }

            /**
             * Save addons
             */
            if ($request->addons) {

                foreach ($request->addons as $name => $addon) {

                    CRMQuoteAddon::updateOrCreate(
                        [
                            'quote_id' => $quote_id,
                            'addon_name' => $name
                        ],
                        [
                            'quote_id' => $quote->id,
                            'addon_name' => $name,
                            'enabled' => isset($addon['enabled']),
                            'price' => $addon['price'] ?? 0
                        ]
                    );
                }
            }

            /**
             * Lead becomes converted
             */
            if($quote_id)
            {

            }
            else
            {
                $quote->lead->update([
                    'status' => 'converted'
                ]);
            }

            DB::commit();

            // return redirect()
            //     ->route('content.crm.quotes.index', $quote->id)
            //     ->with('success', 'Quote created successfully');

            /* -- PAGE CONFIG -- */
            $pageConfigs = $this->commonClass->getPageConfig($this->authUser);      
            /* --end PAGE CONFIG -- */
           
            $quotes = CRMQuote::with(['lead', 'lead.contact', 'addons', 'children'])                        
                        ->get();

            return view('content.crm.quotes.index', compact('pageConfigs', 'quotes'));

        } catch (\Exception $e) {dd($e);

            DB::rollback();

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display quote details
     */
    public function show(string $id)
    {
        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser);      
        /* --end PAGE CONFIG -- */

        $quote = CRMQuote::with([
            // 'lead.contact',
            // 'addons',
            // 'versions'
            'lead', 'lead.contact', 'addons', 'children'
        ])->findOrFail($id);

        return view('content.crm.quotes.show', compact('pageConfigs', 'quote'));
    }

    /**
     * Edit quote
     */
    public function edit(string $id)
    {
        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser);      
        /* --end PAGE CONFIG -- */

        $quote = CRMQuote::with([
            'lead', 'lead.contact', 'addons', 'children'
        ])->findOrFail($id);

        // $packages = [
        //     'essential' => 0,
        //     'basic' => 2500,
        //     'plus_basic' => 3500,
        //     'premium' => 5000,
        //     'ultimate' => 7500
        // ];

        // return view('content.crm.quotes.edit', compact(
        //     'pageConfigs',
        //     'quote',
        //     'packages'
        // ));

        $packages = $this->packages;
        $addons = $this->addons;

        return view('content.crm.quotes.create', compact(
            'pageConfigs',
            'quote',
            'packages',
            'addons'
        ));
    }

    // /**
    //  * Update quote
    //  */
    // public function update(Request $request, string $id)
    // {
    //     DB::beginTransaction();

    //     try {

    //         $quote = CRMQuote::findOrFail($id);

    //         $quote->update([
    //             'package' => $request->package,
    //             'base_price' => $request->base_price,
    //             'registration_price' => $request->registration_price,
    //         ]);

    //         /**
    //          * Update addons
    //          */
    //         $quote->addons()->delete();

    //         if ($request->addons) {

    //             foreach ($request->addons as $name => $addon) {

    //                 CRMQuoteAddon::create([
    //                     'quote_id' => $quote->id,
    //                     'addon_name' => $name,
    //                     'enabled' => isset($addon['enabled']),
    //                     'price' => $addon['price'] ?? 0
    //                 ]);
    //             }
    //         }

    //         DB::commit();

    //         return redirect()
    //             ->route('content.crm.quotes.show', $quote->id)
    //             ->with('success', 'Quote updated');

    //     } catch (\Exception $e) {

    //         DB::rollback();

    //         return back()->with('error', $e->getMessage());
    //     }
    // }

    /**
     * Delete quote
     */
    public function destroy(string $id)
    {
        $quote = CRMQuote::findOrFail($id);

        $quote->addons()->delete();
        $quote->delete();

        return redirect()
            ->route('content.crm.quotes.index')
            ->with('success', 'Quote deleted');
    }

    /**
     * Change quote status
     */
    public function changeStatus(Request $request, $id)
    {
        if($request->module_type == 'lead')        
            $module = CRMLead::findOrFail($id);       
        else if($request->module_type == 'quote')        
            $module = CRMQuote::findOrFail($id);
        
        $status = $request->status;

        if($module)
        {
            $module->update([
                'status' => $status
            ]);
            
            $crm_reminder_sentto = $request->crm_reminder_sentto;

            $crm_reminder_datetime = $request->crm_reminder_datetime;
            $reminder_date = Carbon::parse($crm_reminder_datetime)->format('Y-m-d');
            $reminder_time = Carbon::parse($crm_reminder_datetime)->format('H:i');

            $notes = $request->crm_reminder_reason_quill;

            /**
             * Rejected reminder logic
             */
            if (($status == 'rejected' || $status == 'reminder') && $reminder_date) {
                CRMReminder::create([
                    'module_type' => $request->module_type,
                    'module_id' => $module->id,
                    'sent_to' => $crm_reminder_sentto,
                    'reminder_date' => $reminder_date,
                    'reminder_time' => $reminder_time,
                    'notes' => $notes,
                    'created_by' => $this->authUser->user_id
                ]);

                /* -- LOG -- */
                $this->commonClass->addLog($this->authUser, 'crm-reminder-add',
                    [
                        'Company Name' => $module->company_name,
                    ]
                );
                /* --end LOG -- */
            }

            if($request->module_type == 'lead')
            {
                $leads = CRMLead::with(['contact', 'quotes'])->get();

                /* -- RETURN JSON -- */
                return response()->json([   
                  'status' => 'success',
                  'leads' => $leads
                ]);
                /* --end RETURN JSON -- */
            }
            else if($request->module_type == 'quote')
            {
                $quotes = CRMQuote::with(['lead', 'lead.contact', 'addons', 'children'])                            
                            ->get();

                /* -- RETURN JSON -- */
                return response()->json([   
                  'status' => 'success',
                  'quotes' => $quotes
                ]);
                /* --end RETURN JSON -- */
            }
        }
        else
        {
            /* -- RETURN JSON -- */
            return response()->json([   
              'status' => 'error',
              'message' => 'Error in status'
            ]);
            /* --end RETURN JSON -- */
        }
    }

    /**
     * Negotiation copy/version
     */
    public function negotiate($id)
    {
        DB::beginTransaction();

        try {

            $quote = CRMQuote::with('addons')->findOrFail($id);

            $newQuote = $quote->replicate();

            $newQuote->parent_quote_id = $quote->id;

            // keep same root
            $newQuote->root_quote_id = $quote->root_quote_id ?: $quote->id;

            // next version number
            // $maxVersion = CRMQuote::where('root_quote_id', $newQuote->root_quote_id)
            //                 ->max('version');
            // $maxVersion = CRMQuote::where('parent_quote_id', $quote->parent_quote_id)
            //                 ->max('version');

            //$newQuote->version = ($maxVersion ?? 0) + 1;

            //$newQuote->version = $quote->version + 1;

            $childrenCount = CRMQuote::where('parent_quote_id', $quote->id)->count();

            $newQuote->version = $quote->version . '.' . ($childrenCount + 1);
            
            $newQuote->status = 'negotiation';

            $newQuote->save();

            $quote->status = 'negotiation';
            $quote->save();

            /**
             * Duplicate addons
             */
            foreach ($quote->addons as $addon) {

                CRMQuoteAddon::create([
                    'quote_id' => $newQuote->id,
                    'addon_name' => $addon->addon_name,
                    'enabled' => $addon->enabled,
                    'price' => $addon->price
                ]);
            }

            DB::commit();

            // return redirect()
            //     ->route('content.crm.quotes.edit', $newQuote->id)
            //     ->with('success', 'Negotiation version created');
            
           
            $quotes = CRMQuote::with(['lead', 'lead.contact', 'addons', 'children'])                       
                        ->get();

            /* -- RETURN JSON -- */
            return response()->json([   
              'status' => 'success',
              'quotes' => $quotes
            ]);
            /* --end RETURN JSON -- */ 

        } catch (\Exception $e) {

            DB::rollback();

            //return back()->with('error', $e->getMessage());

            /* -- RETURN JSON -- */
            return response()->json([   
              'status' => 'error',
              'message' => 'Error in negotiation'
            ]);
            /* --end RETURN JSON -- */
        }
    }

    /**
     * Display all quotes grouped by status
     */
    public function approved(Request $request)
    {
        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser);      
        /* --end PAGE CONFIG -- */
       
        $quotes = CRMQuote::with(['lead', 'lead.contact', 'addons', 'children'])                  
                    ->get();

        $tabName = 'approved';
        return view('content.crm.quotes.index', compact('pageConfigs', 'quotes', 'tabName'));       
    }

    /**
     * Display all quotes grouped by status
     */
    public function rejected(Request $request)
    {
        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser);      
        /* --end PAGE CONFIG -- */
       
        $quotes = CRMQuote::with(['lead', 'lead.contact', 'addons', 'children'])                  
                    ->get();

        $tabName = 'rejected';
        return view('content.crm.quotes.index', compact('pageConfigs', 'quotes', 'tabName'));       
    }
}
