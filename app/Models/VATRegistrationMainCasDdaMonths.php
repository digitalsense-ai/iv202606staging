<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VATRegistrationMainCasDdaMonths extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_vat_registration_main_cas_dda_months';

    protected $guarded = [];     

    /**
     * Get the vatregmain for the acc no.
     */
    public function vatregmain()
    {        
        return $this->belongsTo('App\Models\VATRegistrationMain', 'vat_reg_main_id');
    }
}
