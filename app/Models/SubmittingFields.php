<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmittingFields extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_submitting_fields';

    protected $guarded = [];  

    protected $casts = [
        'box_1' => 'encrypted',
        'box_2' => 'encrypted',
        'box_3' => 'encrypted',
        'box_4' => 'encrypted',
        'box_5' => 'encrypted',
        'box_6' => 'encrypted',
        'box_7' => 'encrypted',
        'box_8' => 'encrypted',
        'box_9' => 'encrypted',
        'processing_date' => 'encrypted',
        'payment_indicator' => 'encrypted',
        'form_bundle_number' => 'encrypted',
        'charge_ref_number' => 'encrypted'
    ];

}