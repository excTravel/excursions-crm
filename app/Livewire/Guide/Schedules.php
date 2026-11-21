<?php

namespace App\Livewire\Guide;

use App\Models\Excursion;
use App\Models\Schedule;
use Livewire\Component;

class Schedules extends Component
{
    public Excursion $excursion;

    public $date;

    public $start_time;

    public $max_people = 10;

    protected $rules = [
        'date' => 'required|date|after_or_equal:today',
        'start_time' => 'required|date_format:H:i',
        'max_people' => 'required|integer|min:1|max:100',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'ends_at' => 'datetime',
    ];

    public function mount(Excursion $excursion)
    {
        $this->excursion = $excursion;
    }

    public function save()
    {
        $this->validate();

        Schedule::create([
            'excursion_id' => $this->excursion->id,
            'date' => $this->date,
            'start_time' => $this->start_time,
            'max_people' => $this->max_people,
        ]);

        $this->reset(['date', 'start_time', 'max_people']);
        session()->flash('success', 'Расписание успешно добавлено!');
    }

    public function delete($id)
    {
        Schedule::where('id', $id)->where('excursion_id', $this->excursion->id)->delete();
    }

    public function render()
    {
        return view('livewire.guide.schedules', [
            'schedules' => $this->excursion->schedules()->orderBy('date')->orderBy('start_time')->get(),
        ])->layout('layouts.app');
    }
}
