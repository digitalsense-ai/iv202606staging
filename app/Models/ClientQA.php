<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientQA extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_client_qa';

    protected $guarded = []; 

    /**
     * Get the client for the QA
     */
    public function client()
    {        
        return $this->belongsTo('App\Models\Client', 'client_id', 'id');
    }

    /**
     * Get the client for the QA
     */
    public function clientqafiles()
    {               
        return $this->hasMany('App\Models\ClientQAFiles', 'qa_id');
    }
}
