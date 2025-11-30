<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientFiles extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_client_files';

    protected $guarded = []; 

    /**
     * Get the client for the Files
    */
    public function client()
    {        
        return $this->belongsTo('App\Models\Client', 'client_id', 'id');
    }  
}
