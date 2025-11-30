<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class System extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_system';

    protected $guarded = [];  

    /**
     * Get the systemapi for the system
     */
    public function systemapi()
    {
        return $this->hasMany('App\Models\SystemApis', 'system_id');
    }

    /**
     * Get the systemfiles for the system
     */
    public function systemfiles()
    {
        return $this->hasMany('App\Models\SystemFiles', 'system_id');
    }


    /**
     * Get the systemtaskdate for the system
     */
    public function systemtaskdate()
    {
        return $this->hasMany('App\Models\SystemTaskDate', 'system_id');
    }
}