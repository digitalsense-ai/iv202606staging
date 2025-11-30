<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportReconciliationSalesInvoicesDataItems extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_import_reconciliation_sales_invoices_data_items';

    protected $guarded = [];   

    protected $casts = [
        'line_amount' => 'encrypted',
        'accounting_cost' => 'encrypted',
        'tax_amount' => 'encrypted',
        'net_amount' => 'encrypted',
        'price' => 'encrypted'
    ];
    
}