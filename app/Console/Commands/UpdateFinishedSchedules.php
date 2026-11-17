<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Schedule;
use Carbon\Carbon;

class UpdateFinishedSchedules extends Command
{
    protected $signature = 'schedules:update-finished';
    protected $description = 'Обновляет статусы расписаний (upcoming/ongoing/finished)';

    public function handle(): int
    {
        $now = now();

        // Чанкуем, чтобы не бить БД большими апдейтами
        Schedule::with(['excursion:id,duration'])
            ->orderBy('id')
            ->chunkById(200, function ($chunk) use ($now) {
                foreach ($chunk as $s) {
                    // Надёжно считаем start/end с учётом кастов и возможных строк
                    $start = $s->start_at; // см. аксессор getStartAtAttribute в модели Schedule
                    $end = $s->ends_at;    // ends_at либо из БД, либо рассчитается аксессором, если ты так сделал

                    // Если ends_at нет, но у экскурсии есть duration — посчитаем на лету
                    if (!$end && $start && ($s->excursion->duration ?? null)) {
                        $end = (clone $start)->addMinutes((int) $s->excursion->duration);
                    }

                    // Определяем новый статус
                    $newStatus = 'upcoming';
                    if ($start && $end) {
                        if ($end->lte($now)) {
                            $newStatus = 'finished';
                        } elseif ($start->lte($now) && $end->gt($now)) {
                            $newStatus = 'ongoing';
                        }
                    } elseif ($start) {
                        // На случай, если нет ends_at и нет duration
                        $newStatus = $start->lte($now) ? 'ongoing' : 'upcoming';
                    }

                    if ($newStatus !== $s->status) {
                        $s->updateQuietly(['status' => $newStatus]);
                    }
                }
            });

        $this->info('Статусы расписаний обновлены.');
        return self::SUCCESS;
    }
}
