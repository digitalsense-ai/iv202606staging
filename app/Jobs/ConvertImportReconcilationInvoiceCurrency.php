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

use App\Models\ImportReconciliationComInvoices;
use App\Models\ImportReconciliationSalesInvoices;
use App\Models\JobLog;

use App\Events\ImportReconciliationComSalesInvoicesEvent;

use \App\Classes\CommonClass;

class ConvertImportReconcilationInvoiceCurrency implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $joblog_id;
    protected $selected_month;
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
    public function __construct($joblog_id, $selected_month, $vat_reg_id, $authUserId, $from_currency, $to_currency, $exchange_rate = NULL)
    {
        $this->joblog_id = $joblog_id;
        $this->selected_month = $selected_month;
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
            $decimal_separator = '.';
            $thousands_separator = '';

            $selected_month = $this->selected_month;
            $vat_reg_id = $this->vat_reg_id;
            $authUserId = $this->authUserId;
            $from_currency = $this->from_currency;
            $to_currency = $this->to_currency;
            $exchange_rate = $this->exchange_rate;

            $com_invoices = ImportReconciliationComInvoices::with('salesinvoices')
                                ->where('vat_reg_id', $vat_reg_id)
                                ->where('month_year', $selected_month)
                                ->where('currency_code', '!=', 'CHF')
                                ->get();
          
            foreach ($com_invoices as $com_invoice)
            {                
                //get exchange rate based on the invoice date
                if($exchange_rate == null)
                {
                    $invoice_date = Carbon::parse('01-' . $selected_month)->format('Y-m-d');
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
                            $exchange_rate = number_format($to_per_rate, 2, $decimal_separator, $thousands_separator);                            
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
                                                                                 
                                $exchange_rate = number_format($from_per_rate/$to_per_rate, 2, $decimal_separator, $thousands_separator);
                            }
                            else
                            {
                                $from_per_rate = 1/$to_per_rate;               
                                $exchange_rate = number_format($from_per_rate,2, $decimal_separator, $thousands_separator);
                            }
                        }
                    }//other than DKK
                }//exchange_rate NULL

                if($exchange_rate != null)
                {                   
                    $total_net_exchange_rate = ($com_invoice->net_amount) ? ($com_invoice->net_amount * $exchange_rate) : 0;    
                    $total_vat_exchange_rate = ($com_invoice->vat_amount) ? ($com_invoice->vat_amount * $exchange_rate) : 0;
                    $total_shipping_exchange_rate = ($com_invoice->shipping) ? ($com_invoice->shipping * $exchange_rate) : 0;
                                
                    $com_invoice->convert_currency_code = $to_currency;
                    $com_invoice->exchange_rate = $exchange_rate;
                    $com_invoice->convert_net_amount = ($total_net_exchange_rate) ? number_format($total_net_exchange_rate, 2, $decimal_separator, $thousands_separator) : 0;
                    $com_invoice->convert_vat_amount = ($total_vat_exchange_rate) ? number_format($total_vat_exchange_rate, 2, $decimal_separator, $thousands_separator) : 0;                   

                    $total_amount = ($total_net_exchange_rate + $total_vat_exchange_rate + $total_shipping_exchange_rate);
                    $com_invoice->convert_total_amount = number_format($total_amount, 2, $decimal_separator, $thousands_separator);
                  
                    $com_invoice->updated_by = $authUserId;

                    $com_invoice->save();

                    //update Sales Invoices   
                    foreach ($com_invoice->salesinvoices as $sales_invoice)
                    {
                        if($sales_invoice->currency_code != 'CHF')
                        {
                            $total_net_exchange_rate = ($sales_invoice->net_amount) ? ($sales_invoice->net_amount * $exchange_rate) : 0;    
                            $total_vat_exchange_rate = ($sales_invoice->vat_amount) ? ($sales_invoice->vat_amount * $exchange_rate) : 0;
                            $total_shipping_exchange_rate = ($sales_invoice->shipping) ? ($sales_invoice->shipping * $exchange_rate) : 0;

                            $sales_invoice->convert_currency_code = $to_currency;
                            $sales_invoice->exchange_rate = $exchange_rate;
                            $sales_invoice->convert_net_amount = ($total_net_exchange_rate) ? number_format($total_net_exchange_rate, 2, $decimal_separator, $thousands_separator) : 0;
                            $sales_invoice->convert_vat_amount = ($total_vat_exchange_rate) ? number_format($total_vat_exchange_rate, 2, $decimal_separator, $thousands_separator) : 0;

                            $total_amount = ($total_net_exchange_rate + $total_vat_exchange_rate + $total_shipping_exchange_rate);
                            $sales_invoice->convert_total_amount = number_format($total_amount, 2, $decimal_separator, $thousands_separator);
                          
                            $sales_invoice->updated_by = $authUserId;

                            $sales_invoice->save();
                        } //not CHF
                    }
                    //update Sales Invoices                
                } //exchange_rate not null 
            } //loop com. invoices  

            // Broadcast the event                         
            JobLog::whereNot('id', $this->joblog_id)->where('status', 'completed')->delete();     
            JobLog::where('id', $this->joblog_id)->update(['status' => 'completed']);
        }
        catch (\Exception $e) {
            return  $e->getMessage();
        }
    }

    public function failed(\Exception $exception) {
        JobLog::where('id', $this->joblog_id)->update(['status' => 'failed']);
        // Log the error or send a notification
        dd($exception);
    }
}
