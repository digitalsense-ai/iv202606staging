<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VATRegistrationMain extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_vat_registration_main';

    protected $guarded = []; 

    protected $casts = [       
        'vat_no' => 'encrypted',
        'eori_no' => 'encrypted',
        'cash_account_no' => 'encrypted',
        'mva_no' => 'encrypted',
        'org_no' => 'encrypted',
        'zaz_no' => 'encrypted',
        'steuer_no' => 'encrypted',
        'cvr_no' => 'encrypted',
        'omz_no' => 'encrypted',
        'nip_no' => 'encrypted',
        'fo_no' => 'encrypted',
        'siret_no' => 'encrypted',
        'nif_no' => 'encrypted',
        'nipc_no' => 'encrypted',
        'uk_gateway_userid' => 'encrypted',
        'uk_gateway_password' => 'encrypted',
        'cds_gateway_userid' => 'encrypted',
        'cds_gateway_password' => 'encrypted',
        'dda_acc_no' => 'encrypted'
    ];

    /**
     * Get the vatreg for the vat reg. main
     */
    public function vatreg()
    {
        return $this->hasMany('App\Models\VATRegistration', 'vat_reg_main_id');        
    }  

    /**
     * Get the client for the vat reg. main
     */
    public function client()
    {
        return $this->belongsTo('App\Models\Client', 'client_id', 'id');
    }   

    /**
     * Get the clientapi for the client
     */
    public function clientapi()
    {
        return $this->belongsTo('App\Models\ClientApi', 'id', 'vat_reg_main_id');
    }

    /**
     * Get the acc nos. for the vat reg. main
     */
    public function accnos()
    {
        return $this->hasMany('App\Models\VATRegistrationMainAccNos', 'vat_reg_main_id');      
    } 

    /**
     * Get the months for the vat reg. main CAS/DDA
     */
    public function casddamonths()
    {
        return $this->hasMany('App\Models\VATRegistrationMainCasDdaMonths', 'vat_reg_main_id');      
    }    

    /**
     * Get the uservatregmain for the vat reg. main
     */
    public function uservatregmain()
    {
        return $this->hasMany('App\Models\UserVATRegistrationMain', 'vat_reg_main_id');        
    }
}
