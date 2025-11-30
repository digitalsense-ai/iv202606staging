<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemApis extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_system_apis';

    protected $guarded = [];  

    protected $casts = [
        'api_tenant_id' => 'encrypted',
        'api_client_id' => 'encrypted',
        'api_secret_key' => 'encrypted',        
        'api_token' => 'encrypted',
        'api_token_expire' => 'encrypted',
        'access_token' => 'encrypted',
        'api_user_id' => 'encrypted',
        'one_drive_root_id' => 'encrypted'
    ];

}