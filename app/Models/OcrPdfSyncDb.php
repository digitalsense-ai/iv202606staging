<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Services\AzureStorageService;

class OcrPdfSyncDb extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_ocr_pdf_sync_db';

    protected $guarded = []; 

    protected $casts = [
        //'azure_url' => 'encrypted',
        'related_sales_invoices' => 'array'
    ]; 
    
    public function getConnectionName()
    {
        return config('database.ocr_connection', 'ocr');
    }    
}