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
use Illuminate\Support\Facades\Log;

use App\Models\Invoices;
use App\Models\VATReturns;

use \App\Classes\CommonClass;

class ConvertInvoiceCurrency implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $selected_invoices;
    protected $vat_reg_id;
    protected $authUserId;
    protected $from_currency;
    protected $to_currency;
    protected $exchange_rate;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($selected_invoices, $vat_reg_id, $authUserId, $from_currency, $to_currency, $exchange_rate = NULL)
    {
        $this->selected_invoices = $selected_invoices;
        $this->vat_reg_id = $vat_reg_id;
        $this->authUserId = $authUserId;
        $this->from_currency = $from_currency;
        $this->to_currency = $to_currency;
        $this->exchange_rate = $exchange_rate;
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
            $selected_invoices = $this->selected_invoices;
            $vat_reg_id = $this->vat_reg_id;
            $authUserId = $this->authUserId;
            $from_currency = $this->from_currency;
            $to_currency = $this->to_currency;
            $exchange_rate = $this->exchange_rate;
          
            foreach ($selected_invoices as $selected_invoice)
            {
                $invoice = Invoices::where('id', $selected_invoice['id'])->first();

                //get exchange rate based on the invoice date
                if($exchange_rate == null)
                {
                    $invoice_date = Carbon::parse($selected_invoice['invoice_date'])->format('Y-m-d');
                    //Get start year
                    $start_year = Carbon::parse($invoice_date)->format('Y');
                   
                    //Get partition      
                    $partitions = ['exchange_'.$start_year];

                    $commonClass = new CommonClass();

                    if($to_currency == 'DKK')
                    {
                        //Get exchange rate                    
                        $to_exchange_rate = $commonClass->getExchangeRateLazy($invoice_date, $from_currency, $partitions);
                        
                        if($to_exchange_rate)
                        {
                            $to_per_rate = $to_exchange_rate->exchange_rate/$to_exchange_rate->per_unit;  
                            $exchange_rate = number_format($to_per_rate,5);                            
                        }
                    } // DKK
                    else
                    {
                        //Get exchange rate                    
                        $to_exchange_rate = $commonClass->getExchangeRateLazy($invoice_date, $to_currency, $partitions);
                        
                        if($to_exchange_rate)
                        {
                            $to_per_rate = $to_exchange_rate->exchange_rate/$to_exchange_rate->per_unit;  

                            $from_exchange_rate = $commonClass->getExchangeRateLazy($invoice_date, $from_currency, $partitions);

                            if($from_exchange_rate)
                            {
                                $from_per_rate = $from_exchange_rate->exchange_rate/$from_exchange_rate->per_unit;  
                                                                                 
                                $exchange_rate = number_format($from_per_rate/$to_per_rate,5);
                            }
                            else
                            {
                                $from_per_rate = 1/$to_per_rate;               
                                $exchange_rate = number_format($from_per_rate,5);
                            }
                        }
                    }//other than DKK
                }

                if($exchange_rate != null)
                {
                    $total_vat_exchange_rate = $invoice->total_vat * $exchange_rate;
                    $total_net_exchange_rate = $invoice->total_net * $exchange_rate;
                    $total_gross_exchange_rate = $invoice->total_gross * $exchange_rate;

                    $invoice->local_currency_code = $to_currency;
                    $invoice->exchange_rate = $exchange_rate;
                    $invoice->local_total_net = number_format($total_net_exchange_rate,2);
                    $invoice->local_total_vat = number_format($total_vat_exchange_rate,2);
                    $invoice->local_total_gross = number_format($total_gross_exchange_rate,2);
                    $invoice->updated_by = $authUserId;

                    $invoice->save();   

                    //update VatReturns           
                    $from_vatreturn = VATReturns::where('vat_reg_id', $vat_reg_id)
                                    ->where('invoice_type', $invoice->invoice_type)      
                                    ->where('vat_percentage', $invoice->vat_rate)
                                    ->where('currency_code', $from_currency)
                                    ->first(); 

                    if($from_vatreturn)                
                    {
                        //Log::info('NET Amount: ' . $from_vatreturn->net_amount);

                        if($from_vatreturn->invoice_count - 1 == 0)  
                            $from_vatreturn->delete();  
                        else if($from_vatreturn->net_amount == 0)  
                            $from_vatreturn->delete();  
                        else
                        {
                            $from_vatreturn->vat_amount = $from_vatreturn->vat_amount - $invoice->total_vat;
                            $from_vatreturn->net_amount = $from_vatreturn->net_amount - $invoice->total_net;
                            $from_vatreturn->invoice_count = $from_vatreturn->invoice_count - 1;
                            $from_vatreturn->updated_by = $authUserId;
                            $from_vatreturn->save();  

                            if($from_vatreturn->net_amount == 0)  
                                $from_vatreturn->delete();    
                        }     
                    }        

                    $to_vatreturn = VATReturns::where('vat_reg_id', $vat_reg_id)
                                    ->where('invoice_type', $invoice->invoice_type)      
                                    ->where('vat_percentage', $invoice->vat_rate)
                                    ->where('currency_code', $to_currency)
                                    ->first();   

                    if($to_vatreturn)
                    {                    
                        $to_vatreturn->vat_amount = $to_vatreturn->vat_amount + $total_vat_exchange_rate;
                        $to_vatreturn->net_amount = $to_vatreturn->net_amount + $total_net_exchange_rate;
                        $to_vatreturn->invoice_count = $to_vatreturn->invoice_count + 1;
                        $to_vatreturn->updated_by = $authUserId;
                        $to_vatreturn->save();  

                        if($to_vatreturn->net_amount == 0)  
                            $to_vatreturn->delete(); 
                    }   
                    else
                    {               
                        $to_vatreturn_insert = VATReturns::create(
                            [
                                'vat_reg_id' => $vat_reg_id,
                                'invoice_type' => $invoice->invoice_type,
                                'vat_percentage' => $invoice->vat_rate,
                                'vat_amount' => $total_vat_exchange_rate,
                                'net_amount' => $total_net_exchange_rate,
                                'currency_code' => $to_currency,
                                'invoice_count' => 1,
                                'created_by' => $authUserId,
                            ]
                        ); 

                        if($to_vatreturn_insert->net_amount == 0)  
                            $to_vatreturn_insert->delete();              
                    }      
                }  //exchange_rate not null 
            } //selected invoices        
        }
        catch (\Exception $e) {
            return  $e->getMessage();
        }
    }

    public function failed(\Exception $exception) {
        // Log the error or send a notification
        dd($exception);
    }
}
