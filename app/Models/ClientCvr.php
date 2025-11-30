<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientCvr extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_client_cvr';

    protected $guarded = []; 

    /**
     * Get the client for the vat reg.
     */
    public function client()
    {        
        return $this->belongsTo('App\Models\Client', 'client_id', 'id');
    }
}
