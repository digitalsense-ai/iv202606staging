<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashAccountStatement extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_cash_acc_stmt_files';

    protected $guarded = []; 

    protected $casts = [
        'month_total' => 'encrypted'
    ];

}
