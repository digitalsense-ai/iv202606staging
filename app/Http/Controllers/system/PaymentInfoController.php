<?php

namespace App\Http\Controllers\system;

use App\Http\Controllers\Controller;

use App\Models\PaymentInfo;

use Illuminate\Http\Request;

use \App\Classes\CommonClass;

class PaymentInfoController extends Controller
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
   
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {        
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser);      
        
        $paymentInfos = PaymentInfo::get();
             
        $this->commonClass->addLog($this->authUser, 'paymentinfo-list');

        return view('content.paymentinfo.index', ['pageConfigs' => $pageConfigs, 'authUser' => $this->authUser, 'paymentinfos' => $paymentInfos]);
    }    

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {       
        $paymentInfoID = $request->id;

        if ($paymentInfoID) {
          // update the value
          $clients = PaymentInfo::updateOrCreate(
            ['id' => $paymentInfoID],
            [
                'countrycode' => $request->countrycode, 
                'bankname' => $request->bankname,
                'address' => $request->address,
                'city' => $request->city,
                'country' => $request->country,
                'postcode' => $request->postcode,
                'sortcode' => $request->sortcode,
                'accountno' => $request->accountno,
                'accountname' => $request->accountname,
                'paymentref' => $request->paymentref,
                'bic' => $request->bic,
                'iban' => $request->iban
            ]
          );
         
          $this->commonClass->addLog($this->authUser, 'paymentinfo-update', 
            [
              'Country' => $request->countrycode
            ]
          );

          // user updated
          return response()->json('Updated');
        }
    } 
}
