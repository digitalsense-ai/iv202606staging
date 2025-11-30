<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportReconciliationSalesInvoicesData extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_import_reconciliation_sales_invoices_data';

    protected $guarded = [];   

    protected $casts = [
        'payment_amount' => 'encrypted',
        'tax_total_amount' => 'encrypted',
        'tax_total_net_amount' => 'encrypted',
        'total_line_amount' => 'encrypted',
        'total_tax_excl_amount' => 'encrypted',
        'total_tax_incl_amount' => 'encrypted',
        'total_payable_amount' => 'encrypted',
        'allowance_charge' => 'encrypted'
    ];
    
    /**
     * Get the sales invoice data items for the sales invoice data
     */
    public function items()
    {
        return $this->hasMany('App\Models\ImportReconciliationSalesInvoicesDataItems', 'ir_sales_invoice_data_id');
    }    
}