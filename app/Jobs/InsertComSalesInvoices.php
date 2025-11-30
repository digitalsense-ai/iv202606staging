<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\ImportReconciliationFiles;
use App\Models\ImportReconciliationComInvoices;
use App\Models\ImportReconciliationSalesInvoices;
use App\Models\Invoices;
use App\Models\VATReturns;

use \App\Classes\CommonClass;

use App\Events\ImportReconciliationComSalesInvoicesEvent;

class InsertComSalesInvoices implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $invoice_data;
    protected $allvatregs;    
    protected $authUser;
    protected $from;
    
    protected $commonClass;   

    /**
     * Create a new job instance.
     *
     * @return void
     */   
    public function __construct($invoice_data, $allvatregs, $authUser, $from)
    {                  
      $this->invoice_data = $invoice_data;
      $this->allvatregs = $allvatregs;    
      $this->authUser = $authUser;     
      $this->from = $from;      
      
      $this->commonClass = new CommonClass();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {       
        try
        {           
          DB::transaction(function () {

            $unique_vat_reg_ids = [];
            $unique_logs = [];
            $totalJobs = count($this->invoice_data); 

            foreach ($this->invoice_data as $key => $value) 
            {                
              $commercial_invoice_no = ($this->from == 'ftp') ? '-' : (($value->commercial_invoice_no == "NULL") ? NULL : $value->commercial_invoice_no);
             
              $commercial_invoice_date = ($this->from == 'ftp') ? (($value['invoice_date'] == "NULL") ? NULL : $value['invoice_date']) : (($value->commercial_invoice_date == "NULL") ? NULL : $value->commercial_invoice_date);
             
              $document_status = ($this->from == 'ftp') ? 'Validation' : (($value->document_status == "NULL") ? NULL : $value->document_status);
              $swiss_declaration_sub_type = ($this->from == 'ftp') ? NULL : (($value->swiss_declaration_sub_type == "NULL") ? NULL : $value->swiss_declaration_sub_type);  
              $country = ($this->from == 'ftp') ? (($value['invoice_country'] == "NULL") ? NULL : $value['invoice_country']) : (($value->country == "NULL") ? NULL : $value->country); 
              $currency = ($this->from == 'ftp') ? (($value['invoice_currency'] == "NULL") ? NULL : $value['invoice_currency']) : (($value->currency == "NULL") ? NULL : $value->currency);
              $net_amount = ($this->from == 'ftp') ? NULL : (($value->net_amount == "NULL") ? NULL : $value->net_amount);
              $vat_amount = ($this->from == 'ftp') ? NULL : (($value->vat_amount == "NULL") ? NULL : $value->vat_amount);
              $total_amount = ($this->from == 'ftp') ? NULL : (($value->total_amount == "NULL") ? NULL : $value->total_amount);
              $shipping = ($this->from == 'ftp') ? NULL : (($value->shipping == "NULL") ? NULL : $value->shipping);
              $saved_at = ($this->from == 'ftp') ? NULL : (($value->saved_at == "NULL") ? NULL : $value->saved_at);
 
              $last_modified_at = ($this->from == 'ftp') ? NULL : (($value->last_modified_at == "NULL") ? NULL : $value->last_modified_at);

              $doc_id = ($this->from == 'ftp') ? NULL : (($value->doc_id == "NULL") ? NULL : $value->doc_id);
              $relation_match_no = ($this->from == 'ftp') ? NULL : (($value->relation_match_no == "NULL") ? NULL : $value->relation_match_no);

              $invoice_no = ($this->from == 'ftp') ? (($value['invoice_no'] == "NULL") ? NULL : $value['invoice_no']) : (($value->invoice_no == "NULL") ? NULL : $value->invoice_no);
              $invoice_date = ($this->from == 'ftp') ? (($value['invoice_date'] == "NULL") ? NULL : $value['invoice_date']) : (($value->invoice_date == "NULL") ? NULL : $value->invoice_date);
              $invoice_document_status = ($this->from == 'ftp') ? (($value['invoice_document_status'] == "NULL") ? NULL : $value['invoice_document_status']) : (($value->invoice_document_status == "NULL") ? NULL : $value->invoice_document_status);
              $invoice_swiss_declaration_sub_type = ($this->from == 'ftp') ? (($value['invoice_swiss_declaration_sub_type'] == "NULL") ? NULL : $value['invoice_swiss_declaration_sub_type']) : (($value->invoice_swiss_declaration_sub_type == "NULL") ? NULL : $value->invoice_swiss_declaration_sub_type);
              $invoice_country = ($this->from == 'ftp') ? (($value['invoice_country'] == "NULL") ? NULL : $value['invoice_country']) : (($value->invoice_country == "NULL") ? NULL : $value->invoice_country);
              $invoice_currency = ($this->from == 'ftp') ? (($value['invoice_currency'] == "NULL") ? NULL : $value['invoice_currency']) : (($value->invoice_currency == "NULL") ? NULL : $value->invoice_currency);
              $invoice_net_amount = ($this->from == 'ftp') ? (($value['invoice_net_amount'] == "NULL") ? NULL : $value['invoice_net_amount']) : (($value->invoice_net_amount == "NULL") ? NULL : $value->invoice_net_amount);
              $invoice_vat_amount = ($this->from == 'ftp') ? (($value['invoice_vat_amount'] == "NULL") ? NULL : $value['invoice_vat_amount']) : (($value->invoice_vat_amount == "NULL") ? NULL : $value->invoice_vat_amount);
              $invoice_total_amount = ($this->from == 'ftp') ? (($value['invoice_total_amount'] == "NULL") ? NULL : $value['invoice_total_amount']) : (($value->invoice_total_amount == "NULL") ? NULL : $value->invoice_total_amount);
              $invoice_shipping = ($this->from == 'ftp') ? (($value['invoice_shipping'] == "NULL") ? NULL : $value['invoice_shipping']) : (($value->invoice_shipping == "NULL") ? NULL : $value->invoice_shipping);
              $invoice_variance = ($this->from == 'ftp') ? (($value['invoice_variance'] == "NULL") ? NULL : $value['invoice_variance']) : (($value->invoice_variance == "NULL") ? NULL : $value->invoice_variance);
              $invoice_credit_note_value = ($this->from == 'ftp') ? (($value['invoice_credit_note'] == "NULL") ? NULL : $value['invoice_credit_note']) : (($value->invoice_credit_note == "NULL") ? NULL : $value->invoice_credit_note);
              $invoice_saved_at = ($this->from == 'ftp') ? (($value['invoice_saved_at'] == "NULL") ? NULL : $value['invoice_saved_at']) : (($value->invoice_saved_at == "NULL") ? NULL : $value->invoice_saved_at);              
              
              if($this->from != 'ftp')
              {
                if($invoice_no == NULL)                
                {
                  $invoice_no = $relation_match_no;
                  $invoice_date = ($invoice_date) ? $invoice_date : $commercial_invoice_date;
                  $invoice_document_status = ($invoice_document_status) ? $invoice_document_status : $document_status;
                  $invoice_swiss_declaration_sub_type = ($invoice_swiss_declaration_sub_type) ? $invoice_swiss_declaration_sub_type : $swiss_declaration_sub_type;
                  $invoice_country = ($invoice_country) ? $invoice_country : $country;
                  $invoice_currency = ($invoice_currency) ? $invoice_currency : $currency;                  
                }
              }

              if($commercial_invoice_date != "NULL")            
                $commercial_invoice_date = Carbon::parse($commercial_invoice_date)->format('Y-m-d');            
              else
                $commercial_invoice_date = NULL;

              if($invoice_date != "NULL")            
                $invoice_date = Carbon::parse($invoice_date)->format('Y-m-d');            
              else
                $invoice_date = NULL;

              $invoice_credit_note = 0;
              if($invoice_credit_note_value)
              {
                if(strtolower($invoice_credit_note_value) == "true")                  
                  $invoice_credit_note = 1;
              }              

              if($commercial_invoice_date)
                $match_invoice_date = ($this->from == 'ftp') ? Carbon::parse($invoice_date)->format('Ymd') : Carbon::parse($commercial_invoice_date)->format('Ymd');
              else
                $match_invoice_date = Carbon::parse($invoice_date)->format('Ymd');      

              $match_currency = ($currency) ? $currency : $invoice_currency;                        

              $matched_vatregid = '';
              $matched_country = '-';
              $matched_currency = '-';
              $client_name = '';
              $vatRegHeading = '';

              if($this->from == 'specific-invoice-global-search-refresh')
              {
                $vatreg = $this->allvatregs;
                $client_name = $vatreg->client->client_name;       

                $matched_vatregid = $vatreg->id;
                $vatRegHeading = $client_name . ' - ' . Carbon::parse($vatreg->service_start)->format('M Y') . ' ' . $vatreg->country . ' ' . $vatreg->general_periods; 

                $matched_country = $vatreg->country;

                if(!$match_currency)
                {
                  if($matched_country == "DK")     
                    $matched_currency = "DKK";          
                  elseif($matched_country == "NO")
                    $matched_currency = "NOK";
                  elseif($matched_country == "SE") 
                    $matched_currency = "SEK";
                  elseif($matched_country == "GB")
                    $matched_currency = "GBP";          
                  elseif($matched_country == "IN")  
                    $matched_currency = "INR";          
                  elseif($matched_country == "FR")      
                    $matched_currency = "EUR";
                  elseif($matched_country == "CH")      
                    $matched_currency = "CHF";  
                }
              }
              else
              {
                $filtered_vatreg = $this->allvatregs->filter(function ($vatreg)  use ($match_invoice_date, $match_currency) {
                  $frequency = $this->commonClass->getFrequency($vatreg->general_periods);     
                  
                  return  (
                    ($match_invoice_date >= Carbon::parse($vatreg->service_start)->format('Ymd')) && 
                    ($match_invoice_date <= Carbon::parse($vatreg->service_start)->addMonth($frequency-1)->endOfMonth()->format('Ymd'))
                    )                  
                  ;
                });

                // Reindex the array after filtering
                $reindexed = array_values($filtered_vatreg->toArray());
                
                if(count($reindexed) > 0)
                {                             
                  $matched_vatreg = $reindexed[0];
                  $matched_vatregid = $matched_vatreg['id'];

                  $client_id = $matched_vatreg['client_id'];  
                  $client = $this->commonClass->getCompanyLazy($client_id);

                  $client_name = $client->client_name;                
                  $vatRegHeading = $client_name . ' - ' . Carbon::parse($matched_vatreg['service_start'])->format('M Y') . ' ' . $matched_vatreg['country'] . ' ' . $matched_vatreg['general_periods'];

                  $matched_country = $matched_vatreg['country'];

                  if(!$match_currency)
                  {
                    if($matched_country == "DK")     
                      $matched_currency = "DKK";          
                    elseif($matched_country == "NO")
                      $matched_currency = "NOK";
                    elseif($matched_country == "SE") 
                      $matched_currency = "SEK";
                    elseif($matched_country == "GB")
                      $matched_currency = "GBP";          
                    elseif($matched_country == "IN")  
                      $matched_currency = "INR";          
                    elseif($matched_country == "FR")      
                      $matched_currency = "EUR";
                    elseif($matched_country == "CH")      
                      $matched_currency = "CHF";  
                  }
                }
              }

              //matched vat reg.
              if($matched_vatregid != '')
              {   
                if(!in_array($matched_vatregid, $unique_vat_reg_ids, true))                
                  array_push($unique_vat_reg_ids, $matched_vatregid);

                if(!in_array($vatRegHeading, $unique_logs, true))                
                  array_push($unique_logs, $vatRegHeading);
                                
                  if($this->from == 'ftp')
                  {
                    $check_already_exist_cominvoice = ImportReconciliationComInvoices::where('vat_reg_id', $matched_vatregid)
                                                        ->where('invoice_no', $commercial_invoice_no)            
                                                        ->whereYear('invoice_date', Carbon::parse($invoice_date)->format('Y'))
                                                        ->whereMonth('invoice_date', Carbon::parse($invoice_date)->format('m'))
                                                        ->first();

                    if($check_already_exist_cominvoice)
                    {
                      $check_already_exist_cominvoice->vat_reg_id = $matched_vatregid;

                      if(!$check_already_exist_cominvoice->data_from)
                        $check_already_exist_cominvoice->data_from = $this->from;
                      if(!$check_already_exist_cominvoice->month_year)
                        $check_already_exist_cominvoice->month_year = Carbon::parse($invoice_date)->format('m-Y');

                      $check_already_exist_cominvoice->relation_match_no = $relation_match_no;
                      $check_already_exist_cominvoice->doc_id = $doc_id;

                      $check_already_exist_cominvoice->invoice_no = $commercial_invoice_no;
                      if(!$check_already_exist_cominvoice->invoice_date)
                        $check_already_exist_cominvoice->invoice_date = $invoice_date;

                      $check_already_exist_cominvoice->gs_invoice_date = $invoice_date;

                      $check_already_exist_cominvoice->doc_status = $document_status;
                      $check_already_exist_cominvoice->swiss_declaration_sub_type = $swiss_declaration_sub_type;

                      $check_already_exist_cominvoice->country = ($country) ? $country : (($invoice_country) ? $invoice_country : $matched_country);
                      if(!$check_already_exist_cominvoice->currency_code) 
                        $check_already_exist_cominvoice->currency_code = ($match_currency) ? $match_currency : $matched_currency;

                      $check_already_exist_cominvoice->net_amount = $net_amount;
                      $check_already_exist_cominvoice->vat_amount = $vat_amount;
                      $check_already_exist_cominvoice->total_amount = $total_amount;
                      $check_already_exist_cominvoice->shipping = $shipping;

                      $check_already_exist_cominvoice->updated_by = $this->authUser->id;
                      $check_already_exist_cominvoice->saved_at = $saved_at;

                      $check_already_exist_cominvoice->last_modified_at = $last_modified_at;

                      $check_already_exist_cominvoice->save();

                      $insert_cominvoice = $check_already_exist_cominvoice;
                    }
                    else
                      $insert_cominvoice = ImportReconciliationComInvoices::updateOrCreate(                        
                        [                
                          'vat_reg_id' => $matched_vatregid,

                          'data_from' => $this->from,
                          'month_year' => Carbon::parse($invoice_date)->format('m-Y'),

                          'relation_match_no' => $relation_match_no,
                          'doc_id' => $doc_id,    
                          
                          'invoice_no' => $commercial_invoice_no,
                          'invoice_date' => $invoice_date,
                          'gs_invoice_date' => $invoice_date,
                         
                          'doc_status' => $document_status,
                          'swiss_declaration_sub_type' => $swiss_declaration_sub_type,
                          'country' => ($country) ? $country : (($invoice_country) ? $invoice_country : $matched_country),
                          'currency_code' => ($match_currency) ? $match_currency : $matched_currency,
                          'net_amount' => $net_amount,
                          'vat_amount' => $vat_amount,
                          'total_amount' => $total_amount,
                          'shipping' => $shipping,
                          'created_by' => $this->authUser->id,

                          'saved_at' => $saved_at,

                          'last_modified_at' => $last_modified_at
                        ]
                      );
                  }  
                  else                 
                  {   
                    $check_already_exist_cominvoice = ImportReconciliationComInvoices::where('vat_reg_id', $matched_vatregid)
                                                        ->where('invoice_no', $commercial_invoice_no)         
                                                        ->first();

                    if($check_already_exist_cominvoice)
                    {                      
                      if(!$check_already_exist_cominvoice->data_from)                   
                        $check_already_exist_cominvoice->data_from = $this->from;
                      if(!$check_already_exist_cominvoice->month_year)
                        $check_already_exist_cominvoice->month_year = Carbon::parse($commercial_invoice_date)->format('m-Y');

                      $check_already_exist_cominvoice->relation_match_no = $relation_match_no;
                      $check_already_exist_cominvoice->doc_id = $doc_id;

                      $check_already_exist_cominvoice->invoice_no = $commercial_invoice_no;
                      if(!$check_already_exist_cominvoice->invoice_date)
                        $check_already_exist_cominvoice->invoice_date = $commercial_invoice_date;
                      $check_already_exist_cominvoice->gs_invoice_date = $commercial_invoice_date;

                      $check_already_exist_cominvoice->doc_status = $document_status;
                      $check_already_exist_cominvoice->swiss_declaration_sub_type = $swiss_declaration_sub_type;

                      $check_already_exist_cominvoice->country = ($country) ? $country : (($invoice_country) ? $invoice_country : $matched_country);
                      if(!$check_already_exist_cominvoice->currency_code)  
                        $check_already_exist_cominvoice->currency_code = ($match_currency) ? $match_currency : $matched_currency;

                      $check_already_exist_cominvoice->net_amount = $net_amount;
                      $check_already_exist_cominvoice->vat_amount = $vat_amount;
                      $check_already_exist_cominvoice->total_amount = $total_amount;
                      $check_already_exist_cominvoice->shipping = $shipping;

                      $check_already_exist_cominvoice->updated_by = $this->authUser->id;
                      $check_already_exist_cominvoice->saved_at = $saved_at;

                      $check_already_exist_cominvoice->last_modified_at = $last_modified_at;

                      $check_already_exist_cominvoice->save();

                      $insert_cominvoice = $check_already_exist_cominvoice;
                    }
                    else
                    {
                      //Again check without match vat_reg_id
                      $again_check_already_exist_cominvoice = ImportReconciliationComInvoices::where('invoice_no', $commercial_invoice_no)
                                                        ->where('doc_id', $doc_id)
                                                        ->where('gs_invoice_date', $commercial_invoice_date)         
                                                        ->first();

                      if($again_check_already_exist_cominvoice)
                      {
                        if(!$again_check_already_exist_cominvoice->data_from)                   
                          $again_check_already_exist_cominvoice->data_from = $this->from;
                        if(!$again_check_already_exist_cominvoice->month_year)
                          $again_check_already_exist_cominvoice->month_year = Carbon::parse($commercial_invoice_date)->format('m-Y');

                        $again_check_already_exist_cominvoice->relation_match_no = $relation_match_no;
                        $again_check_already_exist_cominvoice->doc_id = $doc_id;

                        $again_check_already_exist_cominvoice->invoice_no = $commercial_invoice_no;
                        if(!$again_check_already_exist_cominvoice->invoice_date)
                          $again_check_already_exist_cominvoice->invoice_date = $commercial_invoice_date;
                        $again_check_already_exist_cominvoice->gs_invoice_date = $commercial_invoice_date;

                        $again_check_already_exist_cominvoice->doc_status = $document_status;
                        $again_check_already_exist_cominvoice->swiss_declaration_sub_type = $swiss_declaration_sub_type;

                        $again_check_already_exist_cominvoice->country = ($country) ? $country : (($invoice_country) ? $invoice_country : $matched_country);
                        if(!$again_check_already_exist_cominvoice->currency_code)  
                          $again_check_already_exist_cominvoice->currency_code = ($match_currency) ? $match_currency : $matched_currency;

                        $again_check_already_exist_cominvoice->net_amount = $net_amount;
                        $again_check_already_exist_cominvoice->vat_amount = $vat_amount;
                        $again_check_already_exist_cominvoice->total_amount = $total_amount;
                        $again_check_already_exist_cominvoice->shipping = $shipping;

                        $again_check_already_exist_cominvoice->updated_by = $this->authUser->id;
                        $again_check_already_exist_cominvoice->saved_at = $saved_at;

                        $again_check_already_exist_cominvoice->last_modified_at = $last_modified_at;

                        $again_check_already_exist_cominvoice->save();

                        $insert_cominvoice = $again_check_already_exist_cominvoice;
                      }
                      else
                        $insert_cominvoice = ImportReconciliationComInvoices::updateOrCreate(
                          [
                            'vat_reg_id' => $matched_vatregid,
                            'invoice_no' => $commercial_invoice_no
                          ],
                          [                
                            'vat_reg_id' => $matched_vatregid,

                            'data_from' => 'azure',
                            'month_year' => Carbon::parse($commercial_invoice_date)->format('m-Y'),

                            'relation_match_no' => $relation_match_no,
                            'doc_id' => $doc_id,    
                            
                            'invoice_no' => $commercial_invoice_no,
                            'invoice_date' => $commercial_invoice_date,
                            'gs_invoice_date' => $commercial_invoice_date,
                          
                            'doc_status' => $document_status,
                            'swiss_declaration_sub_type' => $swiss_declaration_sub_type,
                            'country' => ($country) ? $country : (($invoice_country) ? $invoice_country : $matched_country),
                            'currency_code' => ($match_currency) ? $match_currency : $matched_currency,
                            'net_amount' => $net_amount,
                            'vat_amount' => $vat_amount,
                            'total_amount' => $total_amount,
                            'shipping' => $shipping,
                            'created_by' => $this->authUser->id,

                            'saved_at' => $saved_at,

                            'last_modified_at' => $last_modified_at
                          ]
                        );
                    }
                  }
                           
                if($insert_cominvoice)
                {                  
                  if($invoice_no)
                  {
                    $check_already_exist_salesinvoice = ImportReconciliationSalesInvoices::where('vat_reg_id', $matched_vatregid)
                                                        ->where('invoice_no', $invoice_no)         
                                                        ->first();

                    if($check_already_exist_salesinvoice)
                    {                               
                      if($check_already_exist_salesinvoice->com_invoice_id == $insert_cominvoice->id)
                      {
                        $check_already_exist_salesinvoice->com_invoice_id = $insert_cominvoice->id;
                        
                        $check_already_exist_salesinvoice->net_amount = $invoice_net_amount;
                        $check_already_exist_salesinvoice->vat_amount = $invoice_vat_amount;
                        $check_already_exist_salesinvoice->total_amount = $invoice_total_amount;
                        $check_already_exist_salesinvoice->shipping = $invoice_shipping;
                        $check_already_exist_salesinvoice->variance = $invoice_variance;
                        $check_already_exist_salesinvoice->credit_note = $invoice_credit_note;
                        $check_already_exist_salesinvoice->currency_code = ($invoice_currency) ? $invoice_currency : (($match_currency) ? $match_currency : $matched_currency);
                        $check_already_exist_salesinvoice->updated_by = $this->authUser->id;

                        $check_already_exist_salesinvoice->save(); 
                      } //same invoice 
                      else
                      {
                        $insert_salesinvoice = ImportReconciliationSalesInvoices::updateOrCreate(
                          [
                            'vat_reg_id' => $matched_vatregid,
                            'invoice_no' => $invoice_no,
                            'com_invoice_id' => $insert_cominvoice->id,
                          ],
                          [                
                            'com_invoice_id' => $insert_cominvoice->id,
                            'vat_reg_id' => $matched_vatregid,
                            'invoice_no' => $invoice_no,
                            'invoice_date' => $invoice_date,
                            'doc_status' => $invoice_document_status,
                            'swiss_declaration_sub_type' => $invoice_swiss_declaration_sub_type,
                            'country' => ($invoice_country) ? $invoice_country : (($country) ? $country : $matched_country),
                            'currency_code' => ($invoice_currency) ? $invoice_currency : (($match_currency) ? $match_currency : $matched_currency),
                            'net_amount' => $invoice_net_amount,
                            'vat_amount' => $invoice_vat_amount,
                            'total_amount' => $invoice_total_amount,
                            'shipping' => $invoice_shipping,
                            'variance' => $invoice_variance,
                            'credit_note' => $invoice_credit_note,                        
                            'created_by' => $this->authUser->id,

                            'saved_at' => $invoice_saved_at
                          ]
                        ); 
                      }//same invoice but with diffrent com invoice
                    } //sales invoice - exist
                    else
                    {   
                      $again_check_already_exist_salesinvoice = ImportReconciliationSalesInvoices::where('com_invoice_id', $insert_cominvoice->id)
                                                        ->where('invoice_no', $invoice_no)         
                                                        ->first();

                      if($again_check_already_exist_salesinvoice)
                      {                             
                        $again_check_already_exist_salesinvoice->net_amount = $invoice_net_amount;
                        $again_check_already_exist_salesinvoice->vat_amount = $invoice_vat_amount;
                        $again_check_already_exist_salesinvoice->total_amount = $invoice_total_amount;
                        $again_check_already_exist_salesinvoice->shipping = $invoice_shipping;
                        $again_check_already_exist_salesinvoice->variance = $invoice_variance;
                        $again_check_already_exist_salesinvoice->credit_note = $invoice_credit_note;
                        $again_check_already_exist_salesinvoice->currency_code = ($invoice_currency) ? $invoice_currency : (($match_currency) ? $match_currency : $matched_currency);
                        $again_check_already_exist_salesinvoice->updated_by = $this->authUser->id;

                        $again_check_already_exist_salesinvoice->save();                              
                      }
                      else
                        $insert_salesinvoice = ImportReconciliationSalesInvoices::updateOrCreate(
                          [
                            'vat_reg_id' => $matched_vatregid,
                            'invoice_no' => $invoice_no                          
                          ],
                          [                
                            'com_invoice_id' => $insert_cominvoice->id,
                            'vat_reg_id' => $matched_vatregid,
                            'invoice_no' => $invoice_no,
                            'invoice_date' => $invoice_date,
                            'doc_status' => $invoice_document_status,
                            'swiss_declaration_sub_type' => $invoice_swiss_declaration_sub_type,
                            'country' => ($invoice_country) ? $invoice_country : (($country) ? $country : $matched_country),
                            'currency_code' => ($invoice_currency) ? $invoice_currency : (($match_currency) ? $match_currency : $matched_currency),
                            'net_amount' => $invoice_net_amount,
                            'vat_amount' => $invoice_vat_amount,
                            'total_amount' => $invoice_total_amount,
                            'shipping' => $invoice_shipping,
                            'variance' => $invoice_variance,
                            'credit_note' => $invoice_credit_note,                        
                            'created_by' => $this->authUser->id,

                            'saved_at' => $invoice_saved_at
                          ]
                        ); 
                    } //sales invoice - NEW
                  }   //sales invoice 

                    //INSERT INTO INVOICES TABLE FOR VAT RETURN FOLDER
                    if($this->from == 'ftp')
                    {
                      $account_name = ($this->from == 'ftp') ? (($value['account_name'] == "NULL") ? NULL : $value['account_name']) : (($value->account_name == "NULL") ? NULL : $value->account_name);
                      $vat_no = ($this->from == 'ftp') ? (($value['vat_no'] == "NULL") ? NULL : $value['vat_no']) : (($value->vat_no == "NULL") ? NULL : $value->vat_no);
                      $client_street = ($this->from == 'ftp') ? (($value['client_street'] == "NULL") ? NULL : $value['client_street']) : (($value->client_street == "NULL") ? NULL : $value->client_street);
                      $client_houseno = ($this->from == 'ftp') ? (($value['client_houseno'] == "NULL") ? NULL : $value['client_houseno']) : (($value->client_houseno == "NULL") ? NULL : $value->client_houseno);
                      $client_city = ($this->from == 'ftp') ? (($value['client_city'] == "NULL") ? NULL : $value['client_city']) : (($value->client_city == "NULL") ? NULL : $value->client_city);
                      $client_postcode = ($this->from == 'ftp') ? (($value['client_postcode'] == "NULL") ? NULL : $value['client_postcode']) : (($value->client_postcode == "NULL") ? NULL : $value->client_postcode);
                      $client_countrycode = ($this->from == 'ftp') ? (($value['client_countrycode'] == "NULL") ? NULL : $value['client_countrycode']) : (($value->client_countrycode == "NULL") ? NULL : $value->client_countrycode);

                      $_tax_code = ($invoice_credit_note) ? 'DSGS_CN' : 'DSGS';
                      $_negative_symb = ($_tax_code == "DSGS_CN" || $_tax_code == "EXG_CN" || $_tax_code == "EXS_CN") ? "-" : "";            

                      $chk_invoice = Invoices::where('vat_reg_id', $matched_vatregid)
                                        ->where('invoice_type', 'sale')
                                        ->where('invoice_no', $invoice_no)                             
                                        ->first();

                      $_where = [];                  
                      if($chk_invoice)
                      {
                        if($chk_invoice->total_net == (((stripos($invoice_net_amount, "-") !== false) ? '' : $_negative_symb) . $invoice_net_amount))
                          $_where = [
                            'id' => $chk_invoice->id
                          ];                                          
                      }
                       
                      $_vat_percentage = ($invoice_net_amount > 0) ? number_format(round(($invoice_vat_amount / $invoice_net_amount) * 100),2) : number_format(0,2); 
                                                             
                      $_fields =  [                
                          'vat_reg_id' => $matched_vatregid,
                          'invoice_type' => 'sale',
                          'invoice_id' => NULL,
                          'tax_code' => $_tax_code,
                          'invoice_date' => $invoice_date,
                          'invoice_no' => $invoice_no,
                          'currency_code' => $invoice_currency,

                          'total_net' => ((stripos($invoice_net_amount, "-") !== false) ? '' : $_negative_symb) . $invoice_net_amount,
                          'vat_rate' => $_vat_percentage,
                          'total_vat' => ((stripos($invoice_vat_amount, "-") !== false) ? '' : $_negative_symb) . $invoice_vat_amount,
                          'total_gross' => ((stripos($invoice_total_amount, "-") !== false) ? '' : $_negative_symb) . $invoice_total_amount,
                       
                          'local_currency_code' => NULL,
                          'exchange_rate' => NULL,
                          'local_total_net' => NULL,
                          'local_total_vat' => NULL,
                          'local_total_gross' => NULL,

                          'n' => NULL,
                          'o' => NULL,
                          'p' => NULL,
                          'q' => NULL,

                          'c_name' => $account_name,
                          'c_vat_no' => $vat_no,
                          'c_street' => $client_street,
                          'c_house_no' => $client_houseno,
                          'c_city' => $client_city,
                          'c_postcode' => $client_postcode,           
                          'c_country' => $client_countrycode,

                          'created_by' => $this->authUser->id
                      ];
                    
                      if($_where)
                        $insert_invoices = Invoices::updateOrCreate(             
                          $_where,
                          $_fields
                        );
                      else                                          
                        $insert_invoices = Invoices::updateOrCreate(             
                          $_fields
                        );                        
                        
                      $chk_vatreturns = VATReturns::where('vat_reg_id', $matched_vatregid)
                                      ->where('invoice_type', 'sale')
                                      ->where('vat_percentage', $_vat_percentage)                             
                                      ->where('currency_code', $invoice_currency)     
                                      ->first();

                      //VATRETURNS
                      if($chk_vatreturns)
                      {
                        $_vat_amount =  $chk_vatreturns->vat_amount + (((stripos($invoice_vat_amount, "-") !== false) ? '' : $_negative_symb) . $invoice_vat_amount);
                        $_net_amount =  $chk_vatreturns->net_amount + (((stripos($invoice_net_amount, "-") !== false) ? '' : $_negative_symb) . $invoice_net_amount);
                        $_invoice_count =  $chk_vatreturns->invoice_count + 1;

                        $chk_vatreturns->vat_amount = $_vat_amount;
                        $chk_vatreturns->net_amount = $_net_amount;
                        $chk_vatreturns->invoice_count = $_invoice_count;

                        $chk_vatreturns->save();
                      }
                      else  
                      {              
                        $sale_vatreturns = VATReturns::updateOrCreate(  
                          [
                            'vat_reg_id' => $matched_vatregid, 
                            'invoice_type' => 'sale', 
                            'vat_percentage' => $_vat_percentage, 
                            'currency_code' => $invoice_currency
                          ],                
                          [
                            'vat_reg_id' => $matched_vatregid,
                            'invoice_type' => 'sale', 
                            'vat_percentage' => str_replace('%', '', $_vat_percentage),
                            'vat_amount' => ((stripos($invoice_vat_amount, "-") !== false) ? '' : $_negative_symb) . $invoice_vat_amount,
                            'net_amount' => ((stripos($invoice_net_amount, "-") !== false) ? '' : $_negative_symb) . $invoice_net_amount,
                            'currency_code' => $invoice_currency,
                            'invoice_count' => 1
                          ]
                        );
                      }//save VATRETURNS    

                      //update ImportReconciliationFiles invoice no.
                      $chk_imr_file = ImportReconciliationFiles::where('vat_reg_id', $matched_vatregid)
                                      ->where('o_file_name', $value['o_filename'])                                     
                                      ->first();

                      if($chk_imr_file)                
                      {
                        $chk_imr_file->invoice_no = $invoice_no;

                        $chk_imr_file->save();
                      }                     
                    }//only FTP - insert into BOTH
                    //INSERT INTO INVOICES TABLE FOR VAT RETURN FOLDER                                
                } //com invoice ID
              }//match if                 
            }//for chunk

            foreach ($unique_vat_reg_ids as $vat_reg_id) 
            {
              if($this->from == 'global-search-refresh')
              {
                $this->commonClass->addLog($this->authUser, 'importreconcilation-global-search-refresh',
                  [                        
                    'VAT Reg.' => $unique_logs,            
                  ]
                );          
              }
              else if($this->from == 'specific-global-search-refresh')
              {
                $this->commonClass->addLog($this->authUser, 'importreconcilation-global-search-refresh',
                  [                        
                    'VAT Reg.' => $unique_logs, 
                    'Specific Period' => 'Specific Period',           
                  ]
                );          
              }
              else
              {
                $this->commonClass->addLog($this->authUser, 'importreconcilation-control-refresh',
                  [                    
                    'VAT Reg.' => $unique_logs,            
                  ]
                );
              }   

              // Broadcast the event              
              event(new ImportReconciliationComSalesInvoicesEvent($vat_reg_id, 'Updated the com./sales invoice')); 
            } //loop unique_vat_reg_ids
          });//DB        
        }       
        catch (\Exception $e) {
            // Handle the exception (e.g., log the error, retry, etc.)
            Log::error('Transaction failed for Com. & Sales Invoices from Azure: ' . $e->getMessage());
        }
    }

    public function failed(\Exception $exception) {
        // Log the error or send a notification
        dd($exception);
    }
}