<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VATRegistrationMainAccNos extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_vat_registration_main_acc_nos';

    protected $guarded = []; 

    protected $casts = [       
        'acc_no' => 'encrypted'
    ];

    /**
     * Get the vatregmain for the acc no.
     */
    public function vatregmain()
    {        
        return $this->belongsTo('App\Models\VATRegistrationMain', 'vat_reg_main_id');
    }
}
