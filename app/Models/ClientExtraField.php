<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientExtraField extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_client_extra_fields';

    protected $guarded = []; 

    /**
     * Get the client for the Extra
     */
    public function client()
    {        
        return $this->belongsTo('App\Models\Client', 'client_id', 'id');
    }    
}
