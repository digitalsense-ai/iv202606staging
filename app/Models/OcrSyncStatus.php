<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Helpers\EnvironmentHelper;

class OcrSyncStatus extends Model
{
    use HasFactory;

    protected $table = 'dv_ocr_sync_status';

    protected $guarded = [];

    public function getConnectionName()
    {
        return config('database.ocr_connection', 'ocr');
    }  

    public function ocrPdf()
    {
        return $this->belongsTo(OcrPdf::class, 'ocr_pdf_id');
    }
    
    public function scopeCurrentEnvironment($query)
    {
        return $query->where(
            'environment',
            //app()->environment('production') ? 'live' : 'staging'
            EnvironmentHelper::getEnvironment()
        );
    }  
}