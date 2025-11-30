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

use App\Models\Invoices;

use \App\Classes\CommonClass;

class InsertInvoices implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    // Set the timeout here (in seconds)
    public $timeout = 120;
    
    protected $vat_reg_id;
  
    protected $invoice_data;
    protected $vatreg;
    protected $authUser;
    protected $api_name;
    protected $taxcodelist;   

    protected $commonClass;   

    /**
     * Create a new job instance.
     *
     * @return void
     */   
    public function __construct($vat_reg_id, $invoice_data, $vatreg, $authUser, $api_name, $taxcodelist)
    {      
        $this->vat_reg_id = $vat_reg_id;
     
        $this->invoice_data = $invoice_data;
        $this->vatreg = $vatreg;
        $this->authUser = $authUser;
        $this->api_name = $api_name;    
        $this->taxcodelist = $taxcodelist;  

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
            foreach ($this->invoice_data as $invoice) 
            {              
                if($this->api_name == "Dynamics 365")
                {
                    $_customer = (isset($invoice->sellToCountry)) ? (object)$invoice->customer : (object)$invoice->vendor;

                    $_tax_code = "DSGS";
                    $_negative_symb = "";
                    if(isset($invoice->sellToCountry))
                    {
                      if(isset($invoice->creditMemoDate))
                      {
                        $_tax_code = "DSGS_CN";
                        $_negative_symb = "-";
                      }
                      else
                        $_tax_code = "DSGS";
                    }
                    else if(isset($invoice->buyFromCountry))
                    {
                      if(isset($invoice->creditMemoDate))
                      {
                        $_tax_code = "DPGS_CN";
                        $_negative_symb = "-";
                      }
                      else
                        $_tax_code = "DPGS";
                    }
                         
                    $_vat_percentage = ($invoice->totalTaxAmount == 0) ? 0 : round((($invoice->totalTaxAmount/$invoice->totalAmountExcludingTax) * 100));

                    $_box_sale_value = (isset($invoice->sellToCountry)) ? (((stripos($invoice->totalAmountExcludingTax, "-") !== false) ? '' : $_negative_symb) . $invoice->totalAmountExcludingTax) : 0;
                    $_box_purchase_value = (isset($invoice->sellToCountry)) ? 0 : (((stripos($invoice->totalTaxAmount, "-") !== false) ? '' : $_negative_symb) . $invoice->totalTaxAmount);

                    $insert_invoices = Invoices::updateOrCreate(
                      [
                        'vat_reg_id' => $this->vat_reg_id,
                        'invoice_no' => $invoice->number
                      ],
                      [                
                        'vat_reg_id' => $this->vat_reg_id,
                        'invoice_type' => (isset($invoice->sellToCountry)) ? "sale" : "purchase",
                        'invoice_id' => $invoice->id,
                        'tax_code' => $_tax_code,
                        'invoice_date' => (isset($invoice->creditMemoDate)) ? Carbon::parse($invoice->creditMemoDate)->format('Y-m-d') : Carbon::parse($invoice->invoiceDate)->format('Y-m-d'),
                        'invoice_no' => $invoice->number,
                        'currency_code' => $invoice->currencyCode,

                        'total_net' => ((stripos($invoice->totalAmountExcludingTax, "-") !== false) ? '' : $_negative_symb) . $invoice->totalAmountExcludingTax,
                        'vat_rate' => $_vat_percentage,
                        'total_vat' => ((stripos($invoice->totalTaxAmount, "-") !== false) ? '' : $_negative_symb) . $invoice->totalTaxAmount,
                        'total_gross' => ((stripos($invoice->totalAmountIncludingTax, "-") !== false) ? '' : $_negative_symb) . $invoice->totalAmountIncludingTax,

                        'local_currency_code' => NULL,
                        'exchange_rate' => NULL,
                        'local_total_net' => NULL,
                        'local_total_vat' => NULL,
                        'local_total_gross' => NULL,

                        'n' => NULL,
                        'o' => NULL,
                        'p' => NULL,
                        'q' => NULL,

                        'c_name' => (isset($invoice->sellToCountry)) ? $invoice->customerName : $invoice->vendorName,
                        'c_vat_no' => (isset($invoice->sellToCountry)) ? $_customer->taxRegistrationNumber : NULL,
                        'c_street' => (isset($invoice->sellToCountry)) ? $invoice->billToAddressLine1 : $invoice->buyFromAddressLine1,
                        'c_house_no' => NULL,
                        'c_city' => (isset($invoice->sellToCountry)) ? $invoice->billToCity : $invoice->buyFromCity,
                        'c_postcode' => (isset($invoice->sellToCountry)) ? $invoice->billToPostCode : $invoice->buyFromPostCode,           
                        'c_country' => (isset($invoice->sellToCountry)) ? $invoice->billToCountry : $invoice->buyFromCountry,

                        'created_by' => $this->authUser->user_id
                      ]
                    );
                }//Dynamics 365
                else if($this->api_name == "Dynamics 365 via SmartApi")
                {
                    $_tax_code = "DSGS";
                    $_negative_symb = "";
                    if(isset($invoice['bill-to-country-region-code']))
                    {              
                        $_tax_code = "DSGS";
                    }
                    else if(isset($invoice['pay-to-country-region-code']))
                    {              
                        $_tax_code = "DPGS";
                    }

                    $_invoice_date = str_replace('/', '-', $invoice['posting-date']);
                    $_arr_date = explode('-', str_replace('/', '-', $_invoice_date));
                    if(count($_arr_date) == 3)
                    {
                      if(strlen($_arr_date[2]) == 2)
                        $_invoice_date = $_arr_date[0] . '-' . $_arr_date[1] . '-20' . $_arr_date[2];
                    }

                    $_vat_amount = $this->commonClass->floatvalue($invoice['amount-including-vat'])-$this->commonClass->floatvalue($invoice['amount']); 


                    $_vat_percentage = ($this->commonClass->floatvalue($invoice['amount-including-vat']) == 0) ? 0 : round((($_vat_amount/$this->commonClass->floatvalue($invoice['amount'])) * 100));    
                  
                    $_box_sale_value = (isset($invoice['bill-to-country-region-code'])) ? (((stripos($this->commonClass->floatvalue($invoice['amount']), "-") !== false) ? '' : $_negative_symb) . $this->commonClass->floatvalue($invoice['amount'])) : 0;
                    $_box_purchase_value = (isset($invoice['bill-to-country-region-code'])) ? 0 : (((stripos($this->commonClass->floatvalue($invoice['amount-including-vat']), "-") !== false) ? '' : $_negative_symb) . $_vat_amount);

                    $insert_invoices = Invoices::updateOrCreate(
                      [
                        'vat_reg_id' => $this->vat_reg_id,
                        'invoice_no' => $invoice['no']
                      ],
                      [                
                        'vat_reg_id' => $this->vat_reg_id,
                        'invoice_type' => (isset($invoice['bill-to-country-region-code'])) ? "sale" : "purchase",
                        'invoice_id' => null,
                        'tax_code' => $_tax_code,
                        'invoice_date' => Carbon::parse($_invoice_date)->format('Y-m-d'),
                        'invoice_no' => $invoice['no'],
                        'currency_code' => ($invoice['currency-code'] == null) ? 'DKK' : $invoice['currency-code'],

                        'total_net' => ($invoice['amount'] == null) ? null : ((stripos($this->commonClass->floatvalue($invoice['amount']), "-") !== false) ? '' : $_negative_symb) . $this->commonClass->floatvalue($invoice['amount']),
                        'vat_rate' => $_vat_percentage,
                        'total_vat' => ($invoice['amount-including-vat'] == null) ? null : ((stripos($this->commonClass->floatvalue($invoice['amount-including-vat']), "-") !== false) ? '' : $_negative_symb) . $_vat_amount,
                        'total_gross' => ($invoice['amount-including-vat'] == null) ? null : ((stripos($this->commonClass->floatvalue($invoice['amount-including-vat']), "-") !== false) ? '' : $_negative_symb) . $this->commonClass->floatvalue($invoice['amount-including-vat']),

                        'local_currency_code' => NULL,
                        'exchange_rate' => NULL,
                        'local_total_net' => NULL,
                        'local_total_vat' => NULL,
                        'local_total_gross' => NULL,

                        'n' => NULL,
                        'o' => NULL,
                        'p' => NULL,
                        'q' => NULL,

                        'c_name' => (isset($invoice['bill-to-country-region-code'])) ? (($invoice['bill-to-name'] == null) ? null : $invoice['bill-to-name']) : (($invoice['pay-to-name'] == null) ? null : $invoice['pay-to-name']),
                        'c_vat_no' => (isset($invoice['bill-to-country-region-code'])) ? NULL : NULL,
                        'c_street' => (isset($invoice['bill-to-country-region-code'])) ? (($invoice['bill-to-address'] == null) ? null : $invoice['bill-to-address']) : (($invoice['pay-to-address'] == null) ? null : $invoice['pay-to-address']),
                        'c_house_no' => NULL,
                        'c_city' => (isset($invoice['bill-to-country-region-code'])) ? (($invoice['bill-to-city'] == null) ? null : $invoice['bill-to-city']) : (($invoice['pay-to-city'] == null) ? null : $invoice['pay-to-city']),
                        'c_postcode' => (isset($invoice['bill-to-country-region-code'])) ? (($invoice['bill-to-post-code'] == null) ? null : $invoice['bill-to-post-code']) : (($invoice['pay-to-post-code'] == null) ? null : $invoice['pay-to-post-code']),
                        'c_country' => (isset($invoice['bill-to-country-region-code'])) ? (($invoice['bill-to-country-region-code'] == null) ? null : $invoice['bill-to-country-region-code']) : (($invoice['pay-to-country-region-code'] == null) ? null : $invoice['pay-to-country-region-code']),
                        'created_by' => $this->authUser->user_id
                      ]
                    );  

                }//Dynamics 365 via SmartApi
                else if($this->api_name == "E-conomic")
                {                     
                  if(isset($invoice->account))
                  {                    
                    $_vat_percentage = isset($invoice->ratePercentage) ? $invoice->ratePercentage : 0;
                    $invoice_no = isset($invoice->invoiceNumber) ? $invoice->invoiceNumber : '';

                    if($invoice_no == '')
                    {
                      if(isset($invoice->invoiceNumber))
                        $invoice_no = $invoice->invoiceNumber;
                    }
                    
                    if($invoice_no == '')
                    {
                      if(isset($invoice->voucherNumber))
                        $invoice_no = $invoice->voucherNumber;
                    }

                    if($invoice_no == '')
                    {
                      $_invoice_text = $invoice->text;
                      if(stripos($_invoice_text, ";") !== false) 
                      {
                        $_arr_invoice_text = explode(';', $_invoice_text);
                        foreach ($_arr_invoice_text as $invoice_text)
                        {
                          if(stripos($invoice_text, "Invoice:") !== false)                       
                            $invoice_no = str_replace('Invoice:', '', $invoice_text);    
                          else if(stripos($invoice_text, "Credit:") !== false)
                          {   
                            $invoice_no = str_replace('Credit:', '', $invoice_text);                           
                          }
                        }
                      }
                      else
                      {
                        if (preg_match('/\d+/', $_invoice_text)) 
                        {                
                          preg_match_all('/\d+/', $_invoice_text, $matches);
                          
                          if(count($matches[0]) > 0)
                            $invoice_no = $matches[0][count($matches)-1];
                        }
                      }
                    }
                   
                    $exists_invoice = Invoices::where('vat_reg_id', $this->vat_reg_id)                                    
                                      ->where('invoice_no', $invoice_no)  
                                      ->where('vat_rate', $_vat_percentage)    
                                      ->where('currency_code', $invoice->currency)                             
                                      ->first();  

                    $acc_nos = $invoice->account->accountNumber;
                    $exists_total_net = 0;
                    $exists_total_vat = 0;   
                    $exists_vat_perentage = 0;                 
                    if($exists_invoice)
                    {          
                      $exists_acc_no = $exists_invoice->acc_no;
                      if($exists_acc_no)
                      {
                        $arr_exists_acc_no = array_map('trim', explode(';', $exists_acc_no));
                        if (!in_array($invoice->account->accountNumber, $arr_exists_acc_no))
                          $arr_exists_acc_no[] = $invoice->account->accountNumber;

                        $acc_nos = implode('; ', $arr_exists_acc_no);
                      }

                      $exists_total_net = $exists_invoice->total_net;
                      $exists_total_vat = $exists_invoice->total_vat;     
                      $exists_vat_perentage = $exists_invoice->vat_rate;                      
                    }

                    /*Account No. - MAP COLUMN*/
                    $vat_amount = 0;
                    $acc_reverse = 1;
                    $net_or_vat = 'net';
                    $invoice_type = 'sale';
                    $accountnos = $this->vatreg->vatregmain->accnos;
                    if(count($accountnos) > 0)
                    {         
                      foreach ($accountnos as $accountno) 
                      {
                        if(($accountno->is_auto_vat_check == 0 || $accountno->is_auto_vat_check == 2) && 
                          ($invoice->account->accountNumber == $accountno->acc_no))
                        {
                          if($accountno->is_reverse)  
                            $acc_reverse = -1;

                          if($accountno->map_column == 'net_sales')
                          {        
                            $invoice_type = 'sale';                    
                            $net_or_vat = 'net';                              
                          }
                          else if($accountno->map_column == 'vat_sales')
                          {    
                            $invoice_type = 'sale';                        
                            $net_or_vat = 'vat';  
                          }
                          else if($accountno->map_column == 'net_purchases')                            
                          {
                            $invoice_type = 'purchase';
                            $net_or_vat = 'net';                           
                          }
                          else if($accountno->map_column == 'vat_purchases')                            
                          {
                            $invoice_type = 'purchase';
                            $net_or_vat = 'vat';                              
                          }
                        }
                      }
                    }

                    if($invoice_type == 'sale')
                      $_tax_code = "DSGS";
                    else if($invoice_type == 'purchase')
                      $_tax_code = "DPGS";

                    if(stripos((($net_or_vat == 'net') ? ($exists_total_net + ($acc_reverse * $invoice->amount)) : $exists_total_net), "-") !== false)
                    {
                      if($invoice_type == 'sale')
                        $_tax_code = "DSGS_CN";
                      else if($invoice_type == 'purchase')
                        $_tax_code = "DPGS_CN";
                    }                                                      

                    $update_vat_amount = true;
                    if($_vat_percentage > 0)
                    {
                      if($net_or_vat == 'net')
                      {
                        $net_amount = $exists_total_net + ($acc_reverse * $invoice->amount);
                        
                        $exists_total_vat = (($net_amount * $_vat_percentage) / 100);
                      }
                      else if($net_or_vat == 'vat')
                      {    
                        $vat_amount = $acc_reverse * $invoice->amount; 
                                             
                        //if (round((float)$vat_amount, 2) <= round((float)$exists_total_vat, 2))
                        if (round((float)$vat_amount, 2) == round((float)$exists_total_vat, 2))
                          $update_vat_amount = false;
                      }                      
                    }
                    /*end Account No. - MAP COLUMN*/
                    
                    try
                    {
                      $insert_invoices = Invoices::updateOrCreate(
                        [
                          'vat_reg_id' => $this->vat_reg_id,
                          'invoice_no' => $invoice_no,
                          'vat_rate' => $_vat_percentage,
                          'currency_code' => $invoice->currency,
                        ],
                        [                
                            'vat_reg_id' => $this->vat_reg_id,
                            'invoice_type' => $invoice_type,
                            'invoice_id' => NULL,
                            'tax_code' => $_tax_code,
                            'invoice_date' => Carbon::parse($invoice->date)->format('Y-m-d'),
                            'acc_no' => ($acc_nos == '') ? NULL : $acc_nos,
                            'invoice_no' => $invoice_no,
                            'currency_code' => $invoice->currency,

                            'total_net' => (($net_or_vat == 'net') ? ($exists_total_net + ($acc_reverse * $invoice->amount)) : $exists_total_net),                          
                            'vat_rate' => $_vat_percentage,
                            'total_vat' => ($_vat_percentage == 0) ? 0 : (($update_vat_amount) ? ((($net_or_vat == 'vat') ? ($exists_total_vat + ($acc_reverse * $invoice->amount)) : $exists_total_vat)) : $exists_total_vat),
                            'total_gross' => (($net_or_vat == 'net') ? ($exists_total_net + ($acc_reverse * $invoice->amount)) : $exists_total_net) + (($_vat_percentage == 0) ? 0 : (($update_vat_amount) ? ((($net_or_vat == 'vat') ? ($exists_total_vat + ($acc_reverse * $invoice->amount)) : $exists_total_vat)) : $exists_total_vat)), 
                            // 'total_gross' => ($update_vat_amount) ? ($exists_total_net + $exists_total_vat + ($acc_reverse * $invoice->amount)) : ($exists_total_net + ($acc_reverse * $invoice->amount)),    
                            //'total_vat' => (($net_or_vat == 'vat') ? ($exists_total_vat + ($acc_reverse * $invoice->amount)) : $vat_amount),                          
                            // 'total_gross' => ($exists_total_net + (($net_or_vat == 'vat') ? $exists_total_vat : $vat_amount) + ($acc_reverse * $invoice->amount)),
                            
                            'local_currency_code' => NULL,
                            'exchange_rate' => NULL,
                            'local_total_net' => NULL,
                            'local_total_vat' => NULL,
                            'local_total_gross' => NULL,

                            'n' => NULL,
                            'o' => NULL,
                            'p' => NULL,
                            'q' => NULL,

                            'c_name' => NULL,
                            'c_vat_no' => NULL,
                            'c_street' => NULL,
                            'c_house_no' => NULL,
                            'c_city' => NULL,
                            'c_postcode' => NULL,           
                            'c_country' => NULL,

                            'created_by' => $this->authUser->user_id
                        ]
                      );

if($invoice_type == 'purchase')
  Log::info('E-conomic : ' . $invoice_no . ' -- ' . $_vat_percentage . '% ' . ' ID: ' . $insert_invoices->id);

                      $check_invoice = Invoices::where('vat_reg_id', $this->vat_reg_id)                                    
                                      ->where('invoice_no', $invoice_no)  
                                      ->where('vat_rate', $_vat_percentage)    
                                      ->where('currency_code', $invoice->currency)                             
                                      ->first();
if($invoice_type == 'purchase')
  Log::info('NET AMOUNT : ' . $check_invoice->total_net . ' VAT AMOUNT: ' . $check_invoice->total_vat);
                      if($check_invoice)
                      {
                        if($check_invoice->total_net == 0)
                        {                          
                          if($_vat_percentage == 0)
                            $check_invoice->delete();
                          else
                          {
                            $sales_net_amount = ($check_invoice->total_vat * 100) /$_vat_percentage;
if($invoice_type == 'purchase')
  Log::info('CALCULATED NET AMOUNT : ' . $sales_net_amount);
                            if($sales_net_amount == 0)              
                              $check_invoice->delete();
                            else
                            {
                              $check_invoice->total_net = $sales_net_amount;
                              $check_invoice->save();
                            }
                          }
                        }
                      } //delete if the NET amount is 0
                    }
                    catch (\Exception $e) {
                      Log::info($e->getMessage());
                    }

                      // Log::info('E-conomic : ' . $invoice_no . ' -- ' . $_vat_percentage . '% ' . $invoice_type);
                      // Log::info('net amount: ' .  (($net_or_vat == 'net') ? ($exists_total_net + ($acc_reverse * $invoice->amount)) : $exists_total_net) . ' vat amount: ' . ($_vat_percentage == 0) ? 0 : (($update_vat_amount) ? ((($net_or_vat == 'vat') ? ($exists_total_vat + ($acc_reverse * $invoice->amount)) : $exists_total_vat)) : $exists_total_vat));
                    
                  }//Account Invoice
                  else
                  {
                    $_tax_code = "DSGS";                      
                    if(stripos($invoice->netAmount, "-") !== false)                        
                      $_tax_code = "DSGS_CN";                                    

                    $_vat_percentage = ($invoice->vatAmount == 0) ? 0 : round((($invoice->vatAmount/$invoice->netAmount) * 100)); 

                    $_box_sale_value = $invoice->netAmount;
                    $_box_purchase_value = 0;

                    $insert_invoices = Invoices::updateOrCreate(
                      [
                        'vat_reg_id' => $this->vat_reg_id,
                        'invoice_no' => $invoice->bookedInvoiceNumber
                      ],
                      [                
                          'vat_reg_id' => $this->vat_reg_id,
                          'invoice_type' => "sale",
                          'invoice_id' => NULL,
                          'tax_code' => $_tax_code,
                          'invoice_date' => Carbon::parse($invoice->date)->format('Y-m-d'),
                          'invoice_no' => $invoice->bookedInvoiceNumber,
                          'currency_code' => $invoice->currency,

                          'total_net' => $invoice->netAmount,
                          'vat_rate' => $_vat_percentage,
                          'total_vat' => $invoice->vatAmount,
                          'total_gross' => $invoice->grossAmount,

                          'local_currency_code' => NULL,
                          'exchange_rate' => NULL,
                          'local_total_net' => NULL,
                          'local_total_vat' => NULL,
                          'local_total_gross' => NULL,

                          'n' => NULL,
                          'o' => NULL,
                          'p' => NULL,
                          'q' => NULL,

                          'c_name' => $invoice->recipient->name,
                          'c_vat_no' => (isset($invoice->sellToCountry)) ? $_customer->taxRegistrationNumber : NULL,
                          'c_street' => (isset($invoice->recipient->address)) ? $invoice->recipient->address : NULL,
                          'c_house_no' => NULL,
                          'c_city' => (isset($invoice->recipient->city)) ? $invoice->recipient->city : NULL,
                          'c_postcode' => (isset($invoice->recipient->zip)) ? $invoice->recipient->zip : NULL,           
                          'c_country' => (isset($invoice->recipient->country)) ? $invoice->recipient->country : NULL,

                          'created_by' => $this->authUser->user_id
                      ]
                    );
                  }//Invoice
                }//E-conomic
                else if($this->api_name == "Uniconta")
                {   
                    $_tax_code = "DSGS"; 
                    if(isset($invoice->CostValue))
                    {              
                      if(stripos($invoice->NetAmount, "-") !== false)                        
                        $_tax_code = "DSGS_CN"; 
                      else
                        $_tax_code = "DSGS";
                    }
                    else
                    {
                      if(stripos($invoice->NetAmount, "-") !== false)                        
                        $_tax_code = "DPGS_CN"; 
                      else
                        $_tax_code = "DPGS"; 
                    }             

                    $_vat_percentage = ($invoice->VatPct) ? round($invoice->VatPct) : (($invoice->VatAmount == 0) ? 0 : round((($invoice->VatAmount/$invoice->NetAmount) * 100))); 

                    $_box_sale_value = (isset($invoice->CostValue)) ? $invoice->NetAmount : 0;
                    $_box_purchase_value = (isset($invoice->CostValue)) ? 0 : $invoice->VatAmount;

                    $insert_invoices = Invoices::updateOrCreate(
                      [
                        'vat_reg_id' => $this->vat_reg_id,
                        'invoice_no' => $invoice->InvoiceNumber
                      ],
                      [                
                          'vat_reg_id' => $this->vat_reg_id,
                          'invoice_type' => (isset($invoice->CostValue)) ? "sale" : "purchase",
                          'invoice_id' => NULL,
                          'tax_code' => $_tax_code,
                          'invoice_date' => Carbon::parse($invoice->Date)->format('Y-m-d'),
                          'invoice_no' => $invoice->InvoiceNumber,
                          'currency_code' => $invoice->Currency,

                          'total_net' => $invoice->NetAmount,
                          'vat_rate' => $_vat_percentage,
                          'total_vat' => $invoice->VatAmount,
                          'total_gross' => $invoice->TotalAmount,

                          'local_currency_code' => NULL,
                          'exchange_rate' => NULL,
                          'local_total_net' => NULL,
                          'local_total_vat' => NULL,
                          'local_total_gross' => NULL,

                          'n' => NULL,
                          'o' => NULL,
                          'p' => NULL,
                          'q' => NULL,

                          'c_name' => $invoice->Name,
                          'c_vat_no' => NULL,
                          'c_street' => $invoice->DeliveryAddress1,
                          'c_house_no' => $invoice->DeliveryAddress2,
                          'c_city' => $invoice->DeliveryCity,
                          'c_postcode' => $invoice->DeliveryZipCode,           
                          'c_country' => $invoice->DeliveryCountry,

                          'created_by' => $this->authUser->user_id
                      ]
                    );
                }//Uniconta
                else if($this->api_name == "Shopify")
                {        
                    $_vat_percentage = ($invoice->total_tax == 0) ? 0 : round((($invoice->total_tax/$invoice->subtotal_price) * 100)); 

                    $_box_sale_value = round(($invoice->subtotal_price + $invoice->total_shipping_price_set->shop_money->amount),2);
                    $_box_purchase_value = 0;
          
                    $insert_invoices = Invoices::updateOrCreate(
                      [
                        'vat_reg_id' => $this->vat_reg_id,
                        'invoice_no' => $invoice->order_number
                      ],
                      [                
                          'vat_reg_id' => $this->vat_reg_id,
                          'invoice_type' => "sale",
                          'invoice_id' => NULL,
                          'tax_code' => "DSGS",
                          'invoice_date' => Carbon::parse($invoice->created_at)->format('Y-m-d'),
                          'invoice_no' => $invoice->order_number,
                          'currency_code' => $invoice->currency,

                          'total_net' => round(($invoice->subtotal_price + $invoice->total_shipping_price_set->shop_money->amount),2),
                          'vat_rate' => $_vat_percentage,
                          'total_vat' => $invoice->total_tax,
                          'total_gross' => $invoice->total_price,

                          'local_currency_code' => NULL,
                          'exchange_rate' => NULL,
                          'local_total_net' => NULL,
                          'local_total_vat' => NULL,
                          'local_total_gross' => NULL,

                          'n' => NULL,
                          'o' => NULL,
                          'p' => NULL,
                          'q' => NULL,

                          'c_name' => ($invoice->billing_address) ? $invoice->billing_address->name : null,
                          'c_vat_no' => NULL,
                          'c_street' => ($invoice->billing_address) ? $invoice->billing_address->address1 : null,
                          'c_house_no' => ($invoice->billing_address) ? $invoice->billing_address->address2 : null,
                          'c_city' => ($invoice->billing_address) ? $invoice->billing_address->city : null,
                          'c_postcode' => ($invoice->billing_address) ? $invoice->billing_address->zip : null,        
                          'c_country' => ($invoice->billing_address) ? $invoice->billing_address->country_code : null,

                          'created_by' => $this->authUser->user_id
                      ]
                    );
                }//Shopify
                else if($this->api_name == "Billy")
                {        
                    $_vat_percentage = ($invoice->tax == 0) ? 0 : round((($invoice->tax/$invoice->amount) * 100)); 

                    $_box_sale_value = $invoice->amount;
                    $_box_purchase_value = 0;
          
                    $insert_invoices = Invoices::updateOrCreate(
                      [
                        'vat_reg_id' => $this->vat_reg_id,
                        'invoice_no' => $invoice->invoiceNo
                      ],
                      [                
                          'vat_reg_id' => $this->vat_reg_id,
                          'invoice_type' => "sale",
                          'invoice_id' => $invoice->downloadUrl,
                          'tax_code' => "DSGS",
                          'invoice_date' => Carbon::parse($invoice->createdTime)->format('Y-m-d'),
                          'invoice_no' => $invoice->invoiceNo,
                          'currency_code' => $invoice->currencyId,

                          'total_net' => $invoice->amount,
                          'vat_rate' => $_vat_percentage,
                          'total_vat' => $invoice->tax,
                          'total_gross' => $invoice->grossAmount,

                          'local_currency_code' => NULL,
                          'exchange_rate' => $invoice->exchangeRate,
                          'local_total_net' => NULL,
                          'local_total_vat' => NULL,
                          'local_total_gross' => NULL,

                          'n' => NULL,
                          'o' => NULL,
                          'p' => NULL,
                          'q' => NULL,

                          'c_name' => NULL,
                          'c_vat_no' => NULL,
                          'c_street' => NULL,
                          'c_house_no' => NULL,
                          'c_city' => NULL,
                          'c_postcode' => NULL,        
                          'c_country' => NULL,

                          'created_by' => $this->authUser->user_id
                      ]
                    );
                }//Billy
                else if($this->api_name == "FTP" || $this->api_name == null)
                {   
                    $_tax_code = (in_array($invoice['tax_code'], $this->taxcodelist, true)) ? $invoice['tax_code']: NULL;
                    $_negative_symb = ($_tax_code == "DSGS_CN" || $_tax_code == "EXG_CN" || $_tax_code == "EXS_CN") ? "-" : "";            

                    $chk_invoice = Invoices::where('vat_reg_id', $this->vat_reg_id)
                                      ->where('invoice_type', $invoice['type'])
                                      ->where('invoice_no', $invoice['invoice_no'])                   
                                      ->get();

                    $_where = [];  
                    $_total_net = ((stripos($invoice['amount'], "-") !== false) ? '' : $_negative_symb) . $this->commonClass->floatvalue($invoice['amount']);
                    $_total_vat = ((stripos($invoice['total_invoice_vat'], "-") !== false) ? '' : $_negative_symb) . $this->commonClass->floatvalue($invoice['total_invoice_vat']); 
                    $_total_gross = ((stripos($invoice['amount_incl_vat'], "-") !== false) ? '' : $_negative_symb) . $this->commonClass->floatvalue($invoice['amount_incl_vat']);                
                   
                    if(count($chk_invoice) > 0)
                    {                                                             
                        $_where = [
                          'id' => $chk_invoice[0]->id
                        ]; 

                          
                        $_total_net = $this->commonClass->floatvalue($chk_invoice[0]->total_net) + $this->commonClass->floatvalue(((stripos($invoice['amount'], "-") !== false) ? '' : $_negative_symb) . $invoice['amount']); 
                        $_total_vat = $this->commonClass->floatvalue($chk_invoice[0]->total_vat) + $this->commonClass->floatvalue(((stripos($invoice['total_invoice_vat'], "-") !== false) ? '' : $_negative_symb) . $invoice['total_invoice_vat']); 
                        $_total_gross = $this->commonClass->floatvalue($chk_invoice[0]->total_gross) + $this->commonClass->floatvalue(((stripos($invoice['amount_incl_vat'], "-") !== false) ? '' : $_negative_symb) . $invoice['amount_incl_vat']);
                    }
                     
                    $_vat_percentage = $invoice['vat_percentage']; 
                    
                    $_box_sale_value = ($invoice['type'] == 'sale') ? ((stripos($invoice['amount'], "-") !== false) ? '' : $_negative_symb) . $this->commonClass->floatvalue($invoice['amount']) : 0;
                    $_box_purchase_value = ($invoice['type'] == 'sale') ? 0 : ((stripos($invoice['amount'], "-") !== false) ? '' : $_negative_symb) . $this->commonClass->floatvalue($invoice['amount']);

                    $_invoice_date = str_replace('/', '-', $invoice['vat_date']);
                    $_arr_date = explode('-', str_replace('/', '-', $_invoice_date));
                    if(count($_arr_date) == 3)
                    {
                      if(strlen($_arr_date[0]) == 4 || strlen($_arr_date[2]) == 4)
                      {

                      }
                      else
                      {               
                        if(strlen($_arr_date[0]) == 2 && strlen($_arr_date[2]) == 2)
                          $_invoice_date = $_arr_date[0] . '-' . $_arr_date[1] . '-20' . $_arr_date[2];
                      }
                    }
                    else if(count($_arr_date) == 2)            
                      $_invoice_date = $_arr_date[0] . '-' . $_arr_date[1] . '-' . Carbon::now()->format('Y');              
                             
                    $_fields =  [                
                        'vat_reg_id' => $this->vat_reg_id,
                        'invoice_type' => $invoice['type'],
                        'invoice_id' => NULL,
                        'tax_code' => $_tax_code,
                        'invoice_date' => Carbon::parse($_invoice_date)->format('Y-m-d'),
                        'invoice_no' => $invoice['invoice_no'],
                        'currency_code' => $invoice['currency_code'],
                        
                        'total_net' => $_total_net,
                        'vat_rate' => $_vat_percentage,
                        'total_vat' => $_total_vat,
                        'total_gross' => $_total_gross,
                     
                        'local_currency_code' => $invoice['local_currency_code'],
                        'exchange_rate' => $this->commonClass->floatvalue($invoice['exchange_rate']),
                        'local_total_net' => $this->commonClass->floatvalue($invoice['local_amount']),
                        'local_total_vat' => $this->commonClass->floatvalue($invoice['local_total_invoice_vat']),
                        'local_total_gross' => $this->commonClass->floatvalue($invoice['local_amount_incl_vat']),

                        'n' => NULL,
                        'o' => NULL,
                        'p' => NULL,
                        'q' => NULL,

                        'c_name' => $invoice['account_name'],
                        'c_vat_no' => $invoice['vat_no'],
                        'c_street' => $invoice['client_street'],
                        'c_house_no' => $invoice['client_houseno'],
                        'c_city' => $invoice['client_city'],
                        'c_postcode' => $invoice['client_postcode'],           
                        'c_country' => $invoice['client_countrycode'],

                        'created_by' => $this->authUser->user_id
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

                }//FTP/NULL
              //}//for data
            }//for chunk
          });//DB              
        }        
        catch (\Exception $e) {
            // Handle the exception (e.g., log the error, retry, etc.)
            Log::error('Transaction failed: ' . $e->getMessage());
        }
    }

    public function failed(\Exception $exception) {
        // Log the error or send a notification
        dd($exception);
    }
}