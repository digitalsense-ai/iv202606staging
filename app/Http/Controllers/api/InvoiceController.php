<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

use App\Http\Controllers\Controller;
use App\Http\Controllers\api\BaseController as BaseController;

use App\Http\Resources\CompanyResource;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\VatRegResource;

use App\Models\Client;
use App\Models\Invoices;
use App\Models\VATRegistration;

use Validator;

use \App\Classes\CommonClass;

class InvoiceController extends BaseController
{
  public $clientIds;
  public $commonClass;
  public $authUser;

  /**
  * Display a listing of the resource.
  *
  * @return \Illuminate\Http\Response
  */    
  public function __construct()
  {
      $this->middleware('auth');
  
      $this->middleware(function ($request, $next) {                            
          $this->commonClass = new CommonClass();
          $this->authUser = $this->commonClass->getAuthUser();              
                    
          if($this->authUser->role == 'team-user')            
            $this->clientIds = $this->commonClass->getClientIdsFromVatReg($this->authUser);    
          else if($this->authUser->role == 'client-user')            
            $this->clientIds = $this->commonClass->getClientIdsForClientUser($this->authUser);            
          else            
            $this->clientIds = $this->commonClass->getClientIdsFromClient();

          return $next($request);
      });                   
  }  

  public function index($vat_reg_id): JsonResponse
  {      
    //$invoices = Invoices::where('vat_reg_id', $vat_reg_id)->get();

    //$vatreg = VATRegistration::where('id', $vat_reg_id)->first();
   
    //if($vatreg)
    //{
      // $invoices = Client::with([
      //               'vatregmain' => function ($query) use ($vatreg) {
      //                 $query->where('vat_reg_main_id', $vatreg->vat_reg_main_id);                                 
      //               },
      //               'vatregmain.vatreg' => function ($query) use ($vatreg) {
      //                   $query->where('id', $vat_reg_id);                                 
      //               },             
      //               'vatregmain.vatreg.invoices' => function ($query) use ($vat_reg_id) {
      //                   $query->where('vat_reg_id', $vat_reg_id);                                 
      //               }
      //             ])
      //             //->where('vat_reg_id', $vat_reg_id)
      //             ->get();

      // $invoices = Client::with([
      //               'vatregmain',
      //               'vatregmain.vatreg',
      //               'vatregmain.vatreg.invoices' => function ($query) use ($vat_reg_id) {
      //                   $query->where('vat_reg_id', $vat_reg_id);                                 
      //               }
      //             ])
      //             //->where('vat_reg_id', $vat_reg_id)
      //             ->get();

      $vatreg = VATRegistration::with([
                    'client',
                    //'client.vatregmain',
                    'invoices'
                    // 'invoices' => function ($query) use ($vat_reg_id) {
                    //     $query->where('vat_reg_id', $vat_reg_id);                                 
                    // }
                  ]) 
                  ->where('id', $vat_reg_id)
                  ->first();
        
    //}

    //return $this->sendResponse(InvoiceResource::collection($invoices), 'Invoices retrieved successfully.');
    return $this->sendResponse(new VatRegResource($vatreg), 'Invoices retrieved successfully.');
  }
  
  public function companies() : JsonResponse
  {                      
    $companies = Client::with([                          
                    'vatregmain','vatregmain.vatreg'
                  ])                       
                  ->whereIn('id', $this->clientIds)
                  ->get();

    return $this->sendResponse(CompanyResource::collection($companies), 'Comapany list.');                      
  }  
}
