<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmittingFieldsCH extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_submitting_fields_ch';

    protected $guarded = [];  

    protected $casts = [
        'box_200' => 'encrypted',
        'box_205' => 'encrypted',
        'box_220' => 'encrypted',        
        'box_221' => 'encrypted',
        'box_225' => 'encrypted',
        'box_230' => 'encrypted',
        'box_235' => 'encrypted',
        'box_280' => 'encrypted',
        'box_289' => 'encrypted',

        'box_299' => 'encrypted',

        'box_303' => 'encrypted',
        'box_303_1' => 'encrypted',
        'box_313' => 'encrypted',
        'box_313_1' => 'encrypted',
        'box_343' => 'encrypted',
        'box_343_1' => 'encrypted',

        'box_379' => 'encrypted',

        'box_383' => 'encrypted',
        'box_383_1' => 'encrypted',

        'box_399' => 'encrypted',  

        'box_400' => 'encrypted',
        'box_405' => 'encrypted',
        'box_410' => 'encrypted',
        'box_415' => 'encrypted',
        'box_420' => 'encrypted',
        'box_479' => 'encrypted',
        'box_500' => 'encrypted',
        'box_510' => 'encrypted'
    ];
}