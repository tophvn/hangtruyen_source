<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
        'last_login_at',
        'last_seen_at',
        'role',
        'banned_until',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'banned_until' => 'datetime',
    ];

    public function isBanned()
    {
        if (!$this->banned_until) {
            return false;
        }

        return $this->banned_until->isFuture();
    }

    public function isActive()
    {
        if ($this->isBanned()) {
            return false;
        }

        if ($this->last_login_at) {
            return $this->last_login_at->diffInDays(now()) <= 30;
        }

        return false;
    }
    public function isOnline()
    {
        $lastSeen = $this->last_seen_at ?: $this->last_login_at;

        if (!$lastSeen) {
            return false;
        }

        return $lastSeen->diffInMinutes(now()) <= 5;
    }
}
