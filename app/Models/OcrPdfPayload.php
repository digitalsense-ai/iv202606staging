<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OcrPdfPayload extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_ocr_pdf_payloads';   

    protected $guarded = [];  

    public function getConnectionName()
    {
        return config('database.ocr_connection', 'ocr');
    }
}