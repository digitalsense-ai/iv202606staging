<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReminderUser extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_reminder_user';

    protected $guarded = []; 

    /**
     * Get the user for the reminder task
     */
    public function user()
    {        
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }    
}
