<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'schedule_id',
        'people_count',
        'total_price',
        'status',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    // Клиент, оформивший бронь
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Расписание, на которое забронировано
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    // Экскурсия через расписание
    public function excursion()
    {
        return $this->hasOneThrough(
            Excursion::class,
            Schedule::class,
            'id',             // Foreign key on schedules
            'id',             // Foreign key on excursions
            'schedule_id',    // Local key on bookings
            'excursion_id'    // Local key on schedules
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getStartAtAttribute(): ?Carbon
    {
        if (!$this->schedule) return null;

        $date = $this->schedule->date instanceof Carbon
            ? $this->schedule->date->copy()
            : Carbon::parse($this->schedule->date);

        $time = $this->schedule->start_time instanceof Carbon
            ? $this->schedule->start_time
            : Carbon::parse($this->schedule->start_time);

        return $date->setTimeFrom($time);
    }

    public function getEndAtAttribute(): ?Carbon
    {
        if (!$this->schedule) return null;

        $date = $this->schedule->date instanceof Carbon
            ? $this->schedule->date->copy()
            : Carbon::parse($this->schedule->date);

        $time = $this->schedule->start_time instanceof Carbon
            ? $this->schedule->start_time
            : Carbon::parse($this->schedule->start_time);

        return $date->setTimeFrom($time)->addMinutes($this->schedule->excursion->duration ?? 0);
    }

    /*
    |--------------------------------------------------------------------------
    | Business Logic
    |--------------------------------------------------------------------------
    */

    public function canBeCanceled(): bool
    {
        if ($this->status === 'canceled') {
            return false;
        }

        if (!$this->start_at) {
            return false;
        }

        // Можно отменить только если до старта экскурсии > 24 часов
        return now()->lt($this->start_at->copy()->subDay());
    }

    public function extraServices()
    {
        return $this->belongsToMany(
            \App\Models\ExtraService::class,
            'booking_extra_services'
        )
            ->withPivot(['quantity', 'unit_price', 'total_price'])
            ->withTimestamps();
    }


}
