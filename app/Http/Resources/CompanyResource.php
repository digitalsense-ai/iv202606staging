<?php

namespace App\Http\Resources;

use App\Http\Resources\VatRegMainResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {      
        // dd(get_object_vars($this->vatregmain));
        if($request->path() === "api/companies")        
            return [
                'company_id' => $this->id,
                'company_name'=> $this->client_name,
                'countries'  => VatRegMainResource::collection($this->vatregmain)                                                        
            ];         
        else       
            return [
                'company_id' => $this->id,
                'company_name'=> $this->client_name,
                // 'countries' => $this->when(property_exists($this, 'vatregmain'), function () {
                //     return VatRegMainResource::collection($this->vatregmain);
                // }),   
                // 'countries' => $this->when($this->vatregmain, function () {
                //     return VatRegMainResource::collection($this->vatregmain);
                // }),   
                //'countries' => optional(VatRegMainResource::collection($this->vatregmain))                                
            ];
    }
}