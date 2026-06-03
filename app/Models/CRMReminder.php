<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

use App\Models\CRMLead;
use App\Models\CRMQuote;

class CRMReminder extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'crm_reminders';
    
    protected $guarded = []; 
    
    // protected $fillable = [
    //     'module_type',
    //     'module_id',
    //     'reminder_date',
    //     'reminder_time',
    //     'notes'
    // ];

    protected $casts = [
        'reminder_date' => 'date',
        'email_sent' => 'boolean'
    ];

    // public function module()
    // {
    //     return $this->morphTo();
    // }

    // /**
    //  * Status constants
    //  */
    // const STATUS_PENDING = 'pending';
    // const STATUS_COMPLETED = 'completed';

    // /**
    //  * Assigned CRM user
    //  */
    // public function assignedUser()
    // {
    //     return $this->belongsTo(
    //         User::class,
    //         'assigned_to'
    //     );
    // }

    // /**
    //  * Lead relation
    //  */
    // public function lead()
    // {
    //     // return $this->belongsTo(
    //     //     CRMLead::class,
    //     //     'module_id'
    //     // )->where('module_type', 'lead');

    //     return $this->belongsTo(CRMLead::class, 'module_id')
    //         ->where($this->table . '.module_type', 'lead');
    // }

    // /**
    //  * Quote relation
    //  */
    // public function quote()
    // {
    //     // return $this->belongsTo(
    //     //     CRMQuote::class,
    //     //     'module_id'
    //     // )->where('module_type', 'quote');

    //     return $this->belongsTo(CRMQuote::class, 'module_id')
    //         ->where($this->table . '.module_type', 'quote');
    // }

    // public function module()
    // {
    //     return $this->morphTo(null, 'module_type', 'module_id');
    // }
    
    // /**
    //  * Dynamic module accessor
    //  */
    // public function module()
    // {
    //     if ($this->module_type == 'lead') {
    //         return $this->lead();
    //     }

    //     if ($this->module_type == 'quote') {
    //         return $this->quote();
    //     }

    //     return null;
    // }

    // /**
    //  * Scope pending reminders
    //  */
    // public function scopePending($query)
    // {
    //     return $query->where(
    //         'status',
    //         self::STATUS_PENDING
    //     );
    // }

    // /**
    //  * Scope completed reminders
    //  */
    // public function scopeCompleted($query)
    // {
    //     return $query->where(
    //         'status',
    //         self::STATUS_COMPLETED
    //     );
    // }

    // /**
    //  * Scope today's reminders
    //  */
    // public function scopeToday($query)
    // {
    //     return $query->whereDate(
    //         'reminder_date',
    //         now()->toDateString()
    //     );
    // }

    // /**
    //  * Scope unsent emails
    //  */
    // public function scopeUnsent($query)
    // {
    //     return $query->where(
    //         'email_sent',
    //         false
    //     );
    // }

    // /**
    //  * Combined datetime accessor
    //  */
    // public function getReminderDateTimeAttribute()
    // {
    //     return $this->reminder_date . ' ' . $this->reminder_time;
    // }

    // /**
    //  * Is overdue
    //  */
    // public function getIsOverdueAttribute()
    // {
    //     return now()->gt(
    //         \Carbon\Carbon::parse(
    //             $this->reminder_datetime
    //         )
    //     ) && $this->status != self::STATUS_COMPLETED;
    // }
}