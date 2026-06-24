<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OcrPdfPayload extends Model
{
    use HasFactory;

    protected $table = 'dv_ocr_pdf_payloads';

    protected $guarded = [];

    public function getConnectionName()
    {
        return config('database.ocr_connection', 'ocr');
    }

    public function ocrPdf()
    {
        return $this->belongsTo(OcrPdf::class, 'ocr_pdf_id');
    }
}
