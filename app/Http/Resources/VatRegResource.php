<?php

namespace App\Http\Resources;

use \App\Classes\CommonClass;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\InvoiceResource;

use Illuminate\Http\Resources\Json\JsonResource;

class VatRegResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {    
        $commonClass = new CommonClass();   
        $frequency = $commonClass->getFrequency($this->general_periods);
        
        if($request->path() === "api/companies")      
            return [                
                'period_id'=> $this->id,                
                'period' => ($frequency > 1) ? (\Carbon\Carbon::parse($this->service_start)->format('M y') . '-' . \Carbon\Carbon::parse($this->service_start)->addMonth(($frequency-1))->format('M y')) : (\Carbon\Carbon::parse($this->service_start)->format('M y'))                
            ];
        else
            return [
                'company' => new CompanyResource($this->client),
                'period_id'=> $this->id,                
                'period' => ($frequency > 1) ? (\Carbon\Carbon::parse($this->service_start)->format('M y') . '-' . \Carbon\Carbon::parse($this->service_start)->addMonth(($frequency-1))->format('M y')) : (\Carbon\Carbon::parse($this->service_start)->format('M y')),
                'country_code' => $this->country,
                'invoices' => InvoiceResource::collection($this->invoices)
            ];    
    }
}