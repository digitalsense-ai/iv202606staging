<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VatRegMainResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)//: array
    {        
        return [                
            'country_code' => $this->country,
            'general_periods' => $this->general_periods,           
            'periods'  => VatRegResource::collection($this->vatreg)
        ]; 
    }
}