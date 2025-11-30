<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportReconciliationControlOFiles extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_importreconciliationcontrol_o_files';

    protected $guarded = [];     

    /**
     * Get the vat reg. for the ircontrolofiles
     */
    public function vatreg()
    {
        return $this->belongsTo('App\Models\VATRegistration', 'vat_reg_id', 'id');
    }
}
