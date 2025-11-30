<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VATReturnComments extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_vatreturn_comments';

    protected $guarded = []; 

    /**
     * Get the vatreturncommentfile for the vatreturn comments
     */
    public function vatreturncommentfile()
    {
        return $this->hasMany('App\Models\VATReturnCommentFiles', 'comment_id');        
    }
}
