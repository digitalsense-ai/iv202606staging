<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VATReturnNotes extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_vatreturn_notes';

    protected $guarded = []; 

    /**
     * Get the user for the vat return notes
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }   
}
