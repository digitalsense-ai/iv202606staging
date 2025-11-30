<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportReconciliationFiles extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_import_reconciliation_files';

    protected $guarded = []; 

    /**
     * Get the vat reg. for the importreconciliationfiles
     */
    public function vatreg()
    {
        return $this->belongsTo('App\Models\VATRegistration', 'vat_reg_id', 'id');
    }  

    /**
     * Get the sales invoice datas for the importreconciliationfiles
     */
    public function salesinvoicesdata()
    {
        return $this->belongsTo('App\Models\ImportReconciliationSalesInvoicesData', 'id', 'ir_file_id');
    }    
}
