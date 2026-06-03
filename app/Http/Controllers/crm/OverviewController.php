<?php

namespace App\Http\Controllers\crm;

use App\Http\Controllers\Controller;
use App\Models\CRMLead;
use App\Models\CRMLeadContact;
use App\Models\CRMQuote;
use Illuminate\Http\Request;
use DB;

use App\Classes\CommonClass;

class OverviewController extends Controller
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

    /* -- GET /crm/overview -- */
    public function index()
    {
        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser);      
        /* --end PAGE CONFIG -- */

        $data = CRMQuote::selectRaw('
            status,
            SUM(registration_price) as registration_total,
            SUM(base_price) as package_total
        ')
        ->groupBy('status')
        ->get()
        ->keyBy('status');

        $lead_total = CRMLead::count();
        $active_quote_total = CRMQuote::where('status', 'active')->count();
        $approved_quote_total = CRMQuote::where('status', 'approved')->count();
        $rejected_quote_total = CRMQuote::where('status', 'rejected')->count();

        return view('content.crm.overview', compact(
            'pageConfigs',
            'lead_total',
            'active_quote_total',
            'approved_quote_total',
            'rejected_quote_total',
            'data'
        ));        
    }
    /* --end GET /crm/overview -- */
}
