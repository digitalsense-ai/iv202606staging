<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Services\AzureStorageService;

class OcrPdf extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_ocr_pdfs';

    protected $guarded = []; 

    protected $casts = [
        'azure_url' => 'encrypted',
        'extracted_data' => 'array'
    ]; 

    protected $appends = [
        'sync_status_value',
        'is_locked_value'
    ];

    public function getConnectionName()
    {
        return config('database.ocr_connection', 'ocr');
    }

    /**
     * Get the client for the ocr pdf
     */
    public function client()
    {        
        return $this->belongsTo('App\Models\Client', 'client_id');
    } 

    public function payload()
    {
        return $this->hasOne(OcrPdfPayload::class, 'ocr_pdf_id');
    }

    public function getOgExtractedDataAttribute()
    {        
        $value = $this->payload?->og_extracted_data;

        if (is_string($value)) {
            return json_decode($value, true) ?: [];
        }

        return $value ?: [];
    }

    public function setOgExtractedDataAttribute($value): void
    {
        $this->payload()->updateOrCreate(
            ['ocr_pdf_id' => $this->id],
            ['og_extracted_data' => is_string($value) ? $value : json_encode($value)]
        );
    }    

    // public function getSyncStatusAttribute()
    // {        
    //     return $this->syncStatus()
    //         ->currentEnvironment()
    //         ->first();
    // }
    
    // public function getSyncStatusAttribute()
    // {
    //     return $this->syncStatus?->sync_status ?? false;
    // }

    // public function getIsLockedAttribute()
    // {
    //     return $this->syncStatus?->is_locked ?? false;
    // }  

    /**
     * OCR sync status.
     */
    public function syncStatus()
    {
        return $this->hasOne(OcrSyncStatus::class, 'ocr_pdf_id')
                    ->currentEnvironment();
    }  

    /**
     * Appended attribute.
     */
    public function getSyncStatusValueAttribute()
    {
        return $this->syncStatus?->sync_status ?? false;
    }

    /**
     * Appended attribute.
     */
    public function getIsLockedValueAttribute()
    {
        return $this->syncStatus?->is_locked ?? false;
    }
}