<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserClient extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_user_client';

    protected $guarded = []; 

    /**
     * Get the user for the userclient
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }   

    /**
     * Get the client for the userclient
     */
    public function client()
    {
        return $this->belongsTo('App\Models\Client', 'client_id');        
    }
}
