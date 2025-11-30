<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientQAFiles extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_client_qa_files';

    protected $guarded = []; 

    /**
     * Get the client for the vat reg.
     */
    public function clientqa()
    {        
        return $this->belongsTo('App\Models\ClientQA', 'qa_id', 'id');
    }
}
