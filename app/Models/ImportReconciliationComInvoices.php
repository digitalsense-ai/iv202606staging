<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportReconciliationComInvoices extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_import_reconciliation_com_invoices';

    protected $guarded = [];   

    protected $casts = [
        'ivf_net_amount' => 'encrypted',
        'net_amount' => 'encrypted',
        'omr_kurs' => 'encrypted',
        'vat_amount' => 'encrypted',
        'total_amount' => 'encrypted',
        'shipping' => 'encrypted',
        'duties' => 'encrypted',
        'adjustment' => 'encrypted',
        'statistical_value' => 'encrypted',
        'convert_net_amount' => 'encrypted',
        'convert_vat_amount' => 'encrypted',
        'convert_total_amount' => 'encrypted'     
    ];   
   
    /**
     * Get the vat reg. for the importreconciliationcominvoices
     */
    public function vatreg()
    {
        return $this->belongsTo('App\Models\VATRegistration', 'vat_reg_id', 'id');
    }

    /**
     * Get the importreconciliationsalesinvoices for the importreconciliationcominvoices
     */
    public function salesinvoices()
    {        
        return $this->hasMany('App\Models\ImportReconciliationSalesInvoices', 'com_invoice_id')->orderBy('invoice_no','asc');
    }

    /**
     * Get the match no for the sales invoice
     */
    public function relationmatchno()
    {        
        return $this->belongsTo('App\Models\ImportReconciliationSalesInvoices', 'relation_match_no', 'invoice_no');
    }
}