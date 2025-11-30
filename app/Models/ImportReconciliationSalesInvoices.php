<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportReconciliationSalesInvoices extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_import_reconciliation_sales_invoices';

    protected $guarded = [];   

    protected $casts = [
        'net_amount' => 'encrypted',
        'vat_amount' => 'encrypted',
        'total_amount' => 'encrypted',
        'shipping' => 'encrypted',
        'variance' => 'encrypted',
        'convert_net_amount' => 'encrypted',
        'convert_vat_amount' => 'encrypted',
        'convert_total_amount' => 'encrypted'
    ];
    
    /**
     * Get the vat reg. for the vatreturnfiles
     */
    public function vatreg()
    {
        return $this->belongsTo('App\Models\VATRegistration', 'vat_reg_id', 'id');
    }
}