<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExcelColumnTemplates extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_excel_column_templates';

    protected $guarded = [];   

    /**
     * Get the vat reg. for the template
     */
    public function vatreg()
    {        
        return $this->hasMany('App\Models\VATRegistration', 'excel_column_template_id', 'id');
    } 
}
