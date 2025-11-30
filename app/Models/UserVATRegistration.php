<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVATRegistration extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_user_vat_registration';

    protected $guarded = []; 

    /**
     * Get the user for the user vat reg.
     */
    public function user()
    {        
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
}
