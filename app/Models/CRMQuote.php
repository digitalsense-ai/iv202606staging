<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CRMQuote extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'crm_quotes';
    
    protected $guarded = []; 

    public function lead()
    {
        return $this->belongsTo(CRMLead::class);
    }

    public function addons()
    {
        return $this->hasMany(CRMQuoteAddon::class, 'quote_id');
    }

    // public function parent()
    // {
    //     return $this->belongsTo(CRMQuote::class,'parent_quote_id');
    // }

    // public function versions()
    // {
    //     return $this->hasMany(CRMQuote::class,'parent_quote_id');
    // }

    // public function children()
    // {
    //     return $this->hasMany(
    //         CRMQuote::class,
    //         'parent_quote_id'
    //     );
    // }

    // public function children()
    // {
    //     return $this->hasMany(CRMQuote::class, 'parent_quote_id');
    // }

    // public function root()
    // {
    //     return $this->belongsTo(CRMQuote::class, 'root_quote_id');
    // }

    public function parent()
    {
        return $this->belongsTo(CRMQuote::class, 'parent_quote_id');
    }

    public function children()
    {
        return $this->hasMany(CRMQuote::class, 'parent_quote_id');
    }

    // All versions under same root
    public function versions()
    {
        return $this->hasMany(
            CRMQuote::class,
            'root_quote_id',
            'root_quote_id'
        )->orderBy('version');
    }

    // Original/root quote
    public function root()
    {
        return $this->belongsTo(
            CRMQuote::class,
            'root_quote_id'
        );
    }
}