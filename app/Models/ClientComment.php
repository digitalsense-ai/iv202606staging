<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientComment extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_client_comment';

    protected $guarded = [];      

    /**
     * Get the user for the clientcomment
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }   
}