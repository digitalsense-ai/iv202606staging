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

    // public function getAzureSignedUrlAttribute()
    // {
    //     if (!$this->azure_url) {
    //         return null;
    //     }

    //     $azureService = new AzureStorageService();

    //     return $azureService->getSignedUrl($this->azure_url);
    // }    
}