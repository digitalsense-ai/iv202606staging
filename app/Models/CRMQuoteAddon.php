<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CRMQuoteAddon extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'crm_quote_addons';
    
    protected $guarded = []; 

    protected $casts = [
        'enabled' => 'boolean',
        'price' => 'decimal:2'
    ];

    /**
     * Relationship
     */
    public function quote()
    {
        return $this->belongsTo(CRMQuote::class);
    }

    /**
     * Scope: enabled only
     */
    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }
}