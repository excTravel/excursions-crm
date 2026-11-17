<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Schedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'excursion_id',
        'date',
        'start_time',
        'ends_at',
        'max_people',
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'ends_at' => 'datetime',
    ];

    public function excursion()
    {
        return $this->belongsTo(Excursion::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where(function (Builder $slots) {
            $slots->whereDate('date', '>', today())
                ->orWhere(function (Builder $todaySlots) {
                    $todaySlots->whereDate('date', today())
                        ->where('start_time', '>=', now()->format('H:i:s'));
                });
        });
    }

    /**
     * 🔹 Автоматически рассчитываем ends_at при создании
     */
    protected static function booted()
    {
        static::creating(function ($schedule) {
            if ($schedule->excursion && $schedule->excursion->duration && !$schedule->ends_at) {

                // приводим дату и время к Carbon
                $date = $schedule->date instanceof Carbon
                    ? $schedule->date->copy()
                    : Carbon::parse($schedule->date);

                $time = $schedule->start_time instanceof Carbon
                    ? $schedule->start_time
                    : Carbon::parse($schedule->start_time);

                // рассчитываем окончание экскурсии
                $schedule->ends_at = $date
                    ->setTimeFrom($time)
                    ->addMinutes($schedule->excursion->duration);
            }
        });
    }

    /**
     * 🔹 Проверка — экскурсия завершилась?
     */
    public function isFinished(): bool
    {
        // если ends_at явно задан — просто проверяем
        if ($this->ends_at instanceof Carbon) {
            return $this->ends_at->isPast();
        }

        if (!empty($this->ends_at)) {
            return Carbon::parse($this->ends_at)->isPast();
        }

        // если ends_at не задан — вычисляем "на лету"
        if ($this->date && $this->start_time && $this->excursion && $this->excursion->duration) {
            $date = $this->date instanceof Carbon ? $this->date->copy() : Carbon::parse($this->date);
            $time = $this->start_time instanceof Carbon ? $this->start_time : Carbon::parse($this->start_time);

            // аккуратно объединяем дату и время
            $start = $date->copy()->setTimeFrom($time);
            $approxEnd = $start->copy()->addMinutes($this->excursion->duration);

            return $approxEnd->isPast();
        }

        return false;
    }

}
