<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OcrCorrectionFeedback extends Model
{
    use HasFactory;

    protected $table = 'dv_ocr_correction_feedback';

    protected $guarded = [];

    public function getConnectionName()
    {
        return config('database.ocr_connection', 'ocr');
    }
}
