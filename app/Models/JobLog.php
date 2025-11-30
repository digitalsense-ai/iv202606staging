<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'job',
        'status',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
