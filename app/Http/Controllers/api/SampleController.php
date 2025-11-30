<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\api\BaseController as BaseController;
use App\Models\Invoices;
use Validator;
use App\Http\Resources\InvoiceResource;
use Illuminate\Http\JsonResponse;

class SampleController extends BaseController
{
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index(): JsonResponse
    {
        //$invoices = Invoices::all();
        $invoices = Invoices::where('vat_reg_id', 64)->get();
        return $this->sendResponse(InvoiceResource::collection($invoices), 'Invoices retrieved successfully.');
    }

    // /**
    // * Store a newly created resource in storage.
    // *
    // * @param  \Illuminate\Http\Request  $request
    // * @return \Illuminate\Http\Response
    // */    
    // public function store(Request $request): JsonResponse
    // {
    //     $input = $request->all();
    //     $validator = Validator::make($input, [
    //         'name' => 'required',
    //         'detail' => 'required'
    //     ]);
        
    //     if($validator->fails()){
    //         return $this->sendError('Validation Error.', $validator->errors());       
    //     }
    //     $invoice = Invoices::create($input);
        
    //     return $this->sendResponse(new InvoiceResource($invoice), 'Invoice created successfully.');
    // } 

    // /**
    // * Display the specified resource.
    // *
    // * @param  int  $id
    // * @return \Illuminate\Http\Response
    // */    
    // public function show($id): JsonResponse
    // {
    //     $invoice = invoices::find($id);
    //     if (is_null($invoice)) {
    //         return $this->sendError('Invoice not found.');
    //     }
    //     return $this->sendResponse(new InvoiceResource($invoice), 'Invoice retrieved successfully.');
    // }

    // /**
    // * Update the specified resource in storage.
    // *
    // * @param  \Illuminate\Http\Request  $request
    // * @param  int  $id
    // * @return \Illuminate\Http\Response
    // */
    // public function update(Request $request, Invoices $invoice): JsonResponse
    // {
    //     $input = $request->all();
    //     $validator = Validator::make($input, [
    //         'name' => 'required',
    //         'detail' => 'required'
    //     ]);
    //     if($validator->fails()){
    //         return $this->sendError('Validation Error.', $validator->errors());       
    //     }
    //     $invoice->name = $input['name'];
    //     $invoice->detail = $input['detail'];
    //     $invoice->save();
        
    //     return $this->sendResponse(new InvoiceResource($invoice), 'Invoice updated successfully.');
    // }

    // /**
    // * Remove the specified resource from storage.
    // *
    // * @param  int  $id
    // * @return \Illuminate\Http\Response
    // */
    // public function destroy(Invoices $invoice): JsonResponse
    // {
    //     $invoice->delete();
    //     return $this->sendResponse([], 'Invoice deleted successfully.');
    // }
}
