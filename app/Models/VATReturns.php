<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VATReturns extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_vat_returns';

    protected $guarded = []; 

    protected $casts = [
        'vat_amount' => 'encrypted',
        'net_amount' => 'encrypted'       
    ];
}
