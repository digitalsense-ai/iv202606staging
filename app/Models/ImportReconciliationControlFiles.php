<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportReconciliationControlFiles extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_importreconciliationcontrol_files';

    protected $guarded = []; 

    /**
     * Get the vat reg. for the ircontrolfiles
     */
    public function vatreg()
    {
        return $this->belongsTo('App\Models\VATRegistration', 'vat_reg_id', 'id');
    }

    /**
     * Get the orginal files for the ircontrolfiles
     */
    public function ircontrolofiles()
    {        
        return $this->hasMany('App\Models\ImportReconciliationControlOFiles', 'ircontrol_file_id');
    }
    
    /**
     * Get the excel template for the vat reg.
     */
    public function anyexceltemplate()
    {        
        return $this->belongsTo('App\Models\AnyExcelTemplates', 'anyexcel_template_id');
    }
}
