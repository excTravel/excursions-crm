<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Excursion;
use App\Models\Location;
use Livewire\Component;
use Livewire\WithPagination;

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
        // Оптимизация N+1: используем жадную загрузку (eager loading) для всех связей
        $excursionsQuery = Excursion::query()
            ->with([
                'category',
                'location',
                'photos',
            ]);

        // Показываем клиентам только одобренные экскурсии
        $excursionsQuery->where('status', 'approved');
        $excursionsQuery->whereHas('location', fn ($locations) => $locations->where('country', 'Россия'));

        // Поиск по ключевым словам
        if (! empty($this->search)) {
            $excursionsQuery->where(function ($query) {
                $query->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%')
                    ->orWhereHas('location', fn ($loc) => $loc->where('city', 'like', '%'.$this->search.'%'));
            });
        }

        // Фильтрация по категориям
        if (count($this->selectedCategories) > 0) {
            $excursionsQuery->whereIn('category_id', $this->selectedCategories);
        }

        // Фильтрация по локации
        if (! empty($this->selectedLocation)) {
            $excursionsQuery->where('location_id', $this->selectedLocation);
        }

        // Фильтрация по дате
        if (! empty($this->selectedDate)) {
            $excursionsQuery->whereHas('schedules', fn ($schedules) => $schedules
                ->upcoming()
                ->whereDate('date', $this->selectedDate));
        }

        // Сортировка результатов
        switch ($this->sortBy) {
            case 'price_asc':
                $excursionsQuery->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $excursionsQuery->orderBy('price', 'desc');
                break;
            case 'duration_asc':
                $excursionsQuery->orderBy('duration', 'asc');
                break;
            case 'duration_desc':
                $excursionsQuery->orderBy('duration', 'desc');
                break;
            default:
                $excursionsQuery->orderBy('created_at', 'desc');
                break;
        }

        // Формируем данные для представления
        return view('livewire.excursions-list', [
            'excursions' => $excursionsQuery->paginate(12),
            'categories' => Category::whereIn('name', [
                'Исторические', 'Архитектурные', 'Гастрономические',
                'Природные', 'Современные', 'Еда и напитки',
            ])->orderBy('name')->get(),
            'locations' => Location::where('country', 'Россия')->orderBy('city')->get(),
        ])->layout('layouts.app');
    }
}
