<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'Emp_Id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'state',
        'district',
        'aadhar_number',
        'pan_number',
        'aadhar_photo',
        'pan_photo',
        'passbook_photo',
        'service_type',
        'percentage',
        'address_1',
        'address_2',
        'landmark',
        'pincode',
        'password',
        'otp',
        'otp_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'otp_expires_at' => 'datetime',
    ];

    public function channelUser()
    {
        return $this->hasMany(ChannelUser::class, 'associate_channel_id', 'id');
    }
    public function channelUserParent()
    {
        return $this->hasMany(ChannelUser::class, 'channel_id', 'id');
    }
    public function bankData()
    {
        return $this->hasMany(BankData::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function hasPermission($permissionName, $type)
    {
        // Get all roles assigned to the user
        $roles = $this->roles()->with('permissions')->first();

        $permissions = $roles->permissions;
        // Check if any of the roles have the required permission
        foreach ($permissions as $permission) {

            if ($permission->name == $permissionName) {
                if ($permission->$type) {
                    return true;
                }
            }
        }

        return false;
    }
    public function partners()
    {
        return $this->belongsToMany(StaffAssign::class);
    }
}
