<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VATReturnFiles extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_vatreturn_files';

    protected $guarded = []; 

    /**
     * Get the vat reg. for the vatreturnfiles
     */
    public function vatreg()
    {
        return $this->belongsTo('App\Models\VATRegistration', 'vat_reg_id', 'id');
    }

    /**
     * Get the orginal files for the vatreturnfiles
     */
    public function vatreturnofiles()
    {        
        return $this->hasMany('App\Models\VATReturnOFiles', 'vatreturn_file_id');
    }

    // /**
    //  * Get the template for the vat reg.
    //  */
    // public function excelcolumntemplate()
    // {        
    //     return $this->belongsTo('App\Models\ExcelColumnTemplates', 'excel_column_template_id');
    // }

    /**
     * Get the excel template for the vat reg.
     */
    public function anyexceltemplate()
    {        
        return $this->belongsTo('App\Models\AnyExcelTemplates', 'anyexcel_template_id');
    }
}
