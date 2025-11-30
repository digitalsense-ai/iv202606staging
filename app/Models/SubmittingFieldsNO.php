<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmittingFieldsNO extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_submitting_fields_no';

    protected $guarded = [];  

    protected $casts = [
        'box_3' => 'encrypted',
        'box_31' => 'encrypted',
        'box_33' => 'encrypted',        
        'box_5' => 'encrypted',
        'box_6' => 'encrypted',

        'box_52' => 'encrypted',

        'box_1' => 'encrypted',
        'box_11' => 'encrypted',
        'box_13' => 'encrypted',

        'box_32' => 'encrypted',
        'box_12' => 'encrypted',

        'box_51' => 'encrypted',
        'box_91' => 'encrypted',
        'box_92' => 'encrypted',

        'box_86' => 'encrypted',
        'box_87' => 'encrypted',        
        'box_88' => 'encrypted',
        'box_89' => 'encrypted',

        'box_81' => 'encrypted',
        'box_14' => 'encrypted',        
        'box_82' => 'encrypted',
        'box_15' => 'encrypted',
        'box_83' => 'encrypted',
        'box_84' => 'encrypted',
        'box_85' => 'encrypted'
    ];
}