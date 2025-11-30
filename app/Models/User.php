<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];    

    /**
     * Get the dvuser for the user
     */
    public function dvuser()
    {        
        return $this->belongsTo('App\Models\DVUser', 'id', 'user_id');
    }

    /**
     * Get the userclient for the user
     */
    public function userclient()
    {
        return $this->hasMany('App\Models\UserClient', 'user_id');
    }

    /**
     * Get the email notification for the user
     */
    public function notificationsettings()
    {        
        return $this->hasMany('App\Models\NotificationSettings', 'user_id');
    }
    
    /**
     * Get the default profile photo URL if no profile photo has been uploaded.
     *
     * @return string
     */
    protected function defaultProfilePhotoUrl($user = null, $history = false)
    {    
        $role = '';    
        if($user)
        {
            $role = $user->role;    
            if($role == 'team-user' || $role == 'company-admin')
            {                
                $fullname = ($user->firstname . ' ' . $user->lastname);

                $name = trim(collect(explode(' ', $fullname))->map(function ($segment) {            
                    return mb_substr($segment, 0, 1);
                })->join(' '));
            }
            else
            {
                $fullname = ($user->name) ? $user->name : $user->firstname;
                if($history)
                {
                    if(($fullname == 'System') || ($user->firstname == 'Super' && $user->lastname == 'Admin'))
                        $name = trim(collect(explode(' ', $fullname))->map(function ($segment) {            
                            return mb_substr($segment, 0, 1);
                        })->join(' '));  
                    else
                    {
                        $fullname = ($user->name) ? $user->name : ($user->firstname . ' ' . $user->lastname);
                        $name = trim(collect(explode(' ', $fullname))->map(function ($segment) {            
                            return mb_substr($segment, 0, 2);
                        })->join(' '));    
                    }                  
                }                
                else
                    $name = trim(collect(explode(' ', $fullname))->map(function ($segment) {            
                        return mb_substr($segment, 0, 2);
                    })->join(' '));
            }  
        }
        else
        {
            $role = (count($this->roles) > 0) ? $this->roles->first()->name : $this->role;    
            if($role == 'team-user' || $role == 'company-admin')
            {
                $user_id = (isset($this->id)) ? $this->id : $this->user_id;
                $dvUser = DVUser::where('user_id', $user_id)->first();
                
                $fullname = ($dvUser->firstname . ' ' . $dvUser->lastname);

                $name = trim(collect(explode(' ', $fullname))->map(function ($segment) {            
                    return mb_substr($segment, 0, 1);
                })->join(' '));
            }
            else
            {
                $fullname = $this->name;
                $name = trim(collect(explode(' ', $fullname))->map(function ($segment) {            
                    return mb_substr($segment, 0, 2);
                })->join(' '));
            }  
        }      

        $textcolor = '7F9CF5';
        $bgcolor = 'EBF4FF';
        if(!$history)
        {
            switch ($role) {                
                case "super-admin":
                    $textcolor = '69809a';
                    $bgcolor = 'e7ebef';
                    break; 
                case "company-admin":
                    $textcolor = '00cfdd';
                    $bgcolor = 'd6f7fa';
                    break; 
                case "team-user":
                    $textcolor = '5a8dee';
                    $bgcolor = 'e5edfc';
                    break; 
                case "client-user":
                    $textcolor = 'fdac41';
                    $bgcolor = 'fff2e1';
                    break;             
                default:
                    $textcolor = '7F9CF5';
                    $bgcolor = 'EBF4FF';
            }   
        } 

        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&color='.$textcolor.'&background='.$bgcolor;
    }    
}
