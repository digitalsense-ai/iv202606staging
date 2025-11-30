<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnyExcelTemplates extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_anyexcel_templates';

    protected $guarded = [];   

    /**
     * Get the client for the template
     */
    public function client()
    {        
        return $this->belongsTo('App\Models\Client', 'client_id', 'id');
    }

    /**
     * Get the vat reg. for the template
     */
    public function vatreg()
    {        
        return $this->hasMany('App\Models\VATRegistration', 'anyexcel_template_id', 'id');
    }

    /**
     * Get the vat return files for the template
     */
    public function vatreturnfiles()
    {        
        return $this->hasMany('App\Models\VATReturnFiles', 'anyexcel_template_id', 'id');
    }    
}