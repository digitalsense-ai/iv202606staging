<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailBoxFiles extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_mailbox_files';

    protected $guarded = [];

    /**
     * Get the vat reg. main for the mailboxfiles
     */
    public function vatregmain()
    {
        return $this->belongsTo('App\Models\VATRegistrationMain', 'vat_reg_main_id', 'id');
    }
}
