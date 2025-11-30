<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_clients';

    protected $guarded = [];   

    protected $casts = [
        'economics_id' => 'encrypted',
        'adm_fee' => 'encrypted',
        'consultancy_low' => 'encrypted',
        'consultancy_high' => 'encrypted'
    ];   

    /**
     * Get the clientapi for the client
     */
    public function clientapi()
    {      
        return $this->hasMany('App\Models\ClientApi', 'client_id');
    }
   
    /**
     * Get the userclient for the client
     */
    public function userclient()
    {
        return $this->hasMany('App\Models\UserClient', 'client_id');
    }

    /**
     * Get the vat reg. main for the client
     */
    public function vatregmain()
    {
        return $this->hasMany('App\Models\VATRegistrationMain', 'client_id');
    }

    /**
     * Get the clientcomment for the client
     */
    public function clientcomment()
    {
        return $this->hasMany('App\Models\ClientComment', 'client_id')->orderBy('id','desc');
    }

    /**
     * Get the clientfiles for the client
     */
    public function clientfiles()
    {
        return $this->hasMany('App\Models\ClientFiles', 'client_id');
    }

    /**
     * Get the cvr for the client
     */
    public function clientcvr()
    {        
        return $this->belongsTo('App\Models\ClientCvr', 'client_id', 'id');       
    }

    /**
     * Get the clientqa for the client
     */
    public function clientqa()
    {      
        return $this->hasMany('App\Models\ClientQA', 'client_id');
    }

    /**
     * Get the clientextrafield for the client
     */
    public function clientextrafield()
    {      
        return $this->hasMany('App\Models\ClientExtraField', 'client_id');
    }

    /**
     * Get the clientlegalrep for the client
     */
    public function clientlegalrep()
    {      
        return $this->hasMany('App\Models\ClientLegalRep', 'client_id');
    }
}