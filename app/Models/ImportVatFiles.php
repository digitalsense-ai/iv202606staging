<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportVatFiles extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_import_vat_files';

    protected $guarded = [];  

    protected $casts = [
        'fee_number' => 'encrypted',
        'statistical_number' => 'encrypted',
        'e_fee_number' => 'encrypted',
        'e_statistical_number' => 'encrypted',
        'adjustment_no' => 'encrypted',
        'invoice_total' => 'encrypted',

        'box_85' => 'encrypted'
    ];

    /**
     * Get the vat reg. for the importvatfiles
     */
    public function vatreg()
    {
        return $this->belongsTo('App\Models\VATRegistration', 'vat_reg_id', 'id');
    }

    /**
     * Get the cargo files for the importvatfile
     */
    public function cargodeclarationfiles()
    {
        return $this->hasMany('App\Models\CargoDeclarationFiles', 'import_vat_id');
    }
}