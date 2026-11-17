<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Excursion;
use App\Models\Category;
use App\Models\Location;

class ExcursionsList extends Component
{
    use WithPagination;

    public $search = '';
    public array $selectedCategories = [];
    public $selectedLocation = '';
    public $selectedDate = '';
    public $sortBy = 'created_at';

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedCategories' => ['except' => []],
        'selectedLocation' => ['except' => ''],
        'selectedDate' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
    ];

    public function updating($property)
    {
        if (in_array($property, ['search', 'selectedCategories', 'selectedLocation', 'selectedDate', 'sortBy'])) {
            $this->resetPage();
        }
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->selectedCategories = [];
        $this->selectedLocation = '';
        $this->selectedDate = '';
        $this->sortBy = 'created_at';
        $this->resetPage();
    }



    public function render()
    {
        $query = Excursion::with([
            'category',
            'location',
            'photos',
            'pricingRules' => fn ($rules) => $rules->orderBy('min_people')->orderBy('max_people'),
            'schedules' => fn ($schedules) => $schedules
                ->upcoming()
                ->when($this->selectedDate, fn ($slots) => $slots->whereDate('date', $this->selectedDate))
                ->orderBy('date')
                ->orderBy('start_time')
                ->with(['bookings' => fn ($bookings) => $bookings->whereIn('status', ['pending', 'confirmed'])]),
        ])
            ->where('status', 'approved')
            ->whereHas('location', fn ($locations) => $locations->where('country', 'Россия'));


        // 🔍 Поиск по названию
        if ($this->search) {
            $query->where(function ($matches) {
                $matches->where('title', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%')
                    ->orWhereHas('location', fn ($location) => $location->where('city', 'like', '%' . $this->search . '%'));
            });
        }

        // 🎨 Фильтр по категории
        if ($this->selectedCategories) {
            $query->whereIn('category_id', $this->selectedCategories);
        }

        // 📍 Фильтр по локации
        if ($this->selectedLocation) {
            $query->where('location_id', $this->selectedLocation);
        }

        if ($this->selectedDate) {
            $query->whereHas('schedules', fn ($schedules) => $schedules
                ->upcoming()
                ->whereDate('date', $this->selectedDate));
        }

        // ↕️ Сортировка
        match ($this->sortBy) {
            'price_asc'     => $query->orderBy('price', 'asc'),
            'price_desc'    => $query->orderBy('price', 'desc'),
            'duration_asc'  => $query->orderBy('duration', 'asc'),
            'duration_desc' => $query->orderBy('duration', 'desc'),
            default          => $query->orderBy('created_at', 'desc'),
        };



        return view('livewire.excursions-list', [
            'excursions' => $query->paginate(12),
            'categories' => Category::whereIn('name', [
                'Исторические', 'Архитектурные', 'Гастрономические',
                'Природные', 'Современные', 'Еда и напитки',
            ])->orderBy('name')->get(),
            'locations'  => Location::where('country', 'Россия')->orderBy('city')->get(),
        ])->layout('layouts.app');
    }
}
