<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CRMLead extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'crm_leads';
    
    protected $guarded = []; 

    protected $casts = [
        'potential_countries' => 'array',
        'potential_products' => 'array'
    ];

    public function contact()
    {
        return $this->hasOne(CRMLeadContact::class, 'lead_id');
    }

    public function quotes()
    {
        return $this->hasMany(CRMQuote::class, 'lead_id');
    }

    public function reminders()
    {
        return $this->morphMany(CRMReminder::class, 'reminder_id');
    }
}