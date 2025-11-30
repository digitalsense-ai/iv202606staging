<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoices extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_invoices';

    protected $guarded = [];   

    protected $casts = [
        'total_net' => 'encrypted',
        'total_vat' => 'encrypted',
        'total_gross' => 'encrypted',
        'local_total_net' => 'encrypted',
        'local_total_vat' => 'encrypted',
        'local_total_gross' => 'encrypted',
        'acc_no' => 'encrypted'
    ];   
   
    
}