<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_GUIDE = 'guide';

    public const ROLE_CLIENT = 'client';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function bookings()
    {
        return $this->hasMany(\App\Models\Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(\App\Models\Review::class);
    }

    public function isBlocked(): bool
    {
        return (bool) $this->is_blocked;
    }
}
