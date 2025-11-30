<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
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
            'id' => $this->id,
            //'vat_reg_id' => $this->vat_reg_id,
            'invoice_type' => $this->invoice_type,
            'invoice_id' => $this->invoice_id,
            'tax_code' => $this->tax_code,
            'invoice_date' => $this->invoice_date,
            'invoice_no' => $this->invoice_no,
            'currency_code' => $this->currency_code,
            'total_net' => $this->total_net,
            'vat_rate' => $this->vat_rate,
            'total_vat' => $this->total_vat,
            'total_gross' => $this->total_gross,
            'local_currency_code' => $this->local_currency_code,
            'exchange_rate' => $this->exchange_rate,
            'local_total_net' => $this->local_total_net,
            'local_total_vat' => $this->local_total_vat,
            'local_total_gross' => $this->local_total_gross,
            'n' => $this->n,
            'o' => $this->o,
            'p' => $this->p,
            'q' => $this->q,
            'c_name' => $this->c_name,
            'c_vat_no' => $this->c_vat_no,
            'c_street' => $this->c_street,
            'c_house_no' => $this->c_house_no,
            'c_city' => $this->c_city,
            'c_postcode' => $this->c_postcode,
            'c_country' => $this->c_country,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,          
            'created_at' => $this->created_at->format('d/m/Y'),
            'updated_at' => $this->updated_at->format('d/m/Y'),
        ];
    }
}