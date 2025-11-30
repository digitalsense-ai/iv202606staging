<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientApi extends Model
{
    use HasFactory;
   
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_client_api';

    protected $guarded = [];  

    protected $casts = [
        'sales_invoice_url' => 'encrypted',
        'purchase_invoice_url' => 'encrypted',
        'api_tenant_id' => 'encrypted',
        'api_client_id' => 'encrypted',
        'api_secret_key' => 'encrypted',
        'api_company_id' => 'encrypted',
        'api_token' => 'encrypted',
        'api_token_expire' => 'encrypted'
    ];    
}