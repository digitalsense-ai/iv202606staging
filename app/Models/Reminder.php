<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_reminder';

    protected $guarded = []; 

    /**
     * Get the users for the reminder
     */
    public function reminderuser()
    {
        return $this->hasMany('App\Models\ReminderUser', 'reminder_id');        
    }

    /**
     * Get the histories for the reminder
     */
    public function reminderhistory()
    {
        return $this->hasMany('App\Models\ReminderHistory', 'reminder_id')->orderBy('sent_at','desc');     
    }
    
    /**
     * Get the vatregmain for the reminder task
     */
    public function vatregmain()
    {        
        return $this->belongsTo('App\Models\VATRegistrationMain', 'vat_reg_main_id');
    }

    /**
     * Get the reminderactionoption for the reminder task
     */
    public function reminderactionoption()
    {        
        return $this->belongsTo('App\Models\ReminderActionOption', 'action_id');
    }
}
