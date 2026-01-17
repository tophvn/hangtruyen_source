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
        'banned_until' => 'datetime',
    ];
    
    /**
     * Kiểm tra user có bị ban không
     */
    public function isBanned()
    {
        if (!$this->banned_until) {
            return false;
        }
        
        // Nếu banned_until trong tương lai = đang bị ban
        // Nếu banned_until trong quá khứ = hết hạn ban
        return $this->banned_until->isFuture();
    }
    
    /**
     * Kiểm tra user có hoạt động không (không bị ban và có last_login gần đây)
     */
    public function isActive()
    {
        if ($this->isBanned()) {
            return false;
        }
        
        // Nếu có last_login trong vòng 30 ngày = active
        if ($this->last_login_at) {
            return $this->last_login_at->diffInDays(now()) <= 30;
        }
        
        // Nếu chưa login bao giờ = không active
        return false;
    }
    
    /**
     * Kiểm tra user có đang online không (last_login trong vòng 15 phút)
     */
    public function isOnline()
    {
        if (!$this->last_login_at) {
            return false;
        }
        
        // Nếu last_login trong vòng 15 phút = online
        return $this->last_login_at->diffInMinutes(now()) <= 15;
    }
}
