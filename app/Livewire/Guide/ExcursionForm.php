<?php

namespace App\Livewire\Guide;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Excursion;
use App\Models\Category;
use App\Models\Location;

class ExcursionForm extends Component
{
    use WithFileUploads;

    public $title;
    public $description;
    public $price;
    public $duration;
    public $category_id;
    public $location_id;
    public $photos = [];
    public array $routePoints = [];

    /** @var array<int, array<string, mixed>> */
    public array $extraServices = [];

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'required|string|min:20',
        'price' => 'required|numeric|min:0',
        'duration' => 'required|integer|min:10',
        'category_id' => 'required|exists:categories,id',
        'location_id' => 'required|exists:locations,id',
        'photos.*' => 'nullable|image|max:4096',

        // Доп. услуги
        'extraServices.*.name'        => 'nullable|string|max:255',
        'extraServices.*.description' => 'nullable|string|max:1000',
        'extraServices.*.price'       => 'nullable|numeric|min:0',
        'extraServices.*.per_person'  => 'boolean',
        'extraServices.*.is_active'   => 'boolean',
    ];

    // ВАЖНО: метод вызывается напрямую из JS через component.call('addRoutePoint', payload)
    public function addRoutePoint(array $payload): void
    {
        $lat = isset($payload['lat']) ? (float)$payload['lat'] : null;
        $lng = isset($payload['lng']) ? (float)$payload['lng'] : null;
        if ($lat === null || $lng === null) return;

        $this->routePoints[] = [
            'lat' => round($lat, 6),
            'lng' => round($lng, 6),
        ];

        // Сообщаем фронту перерисовать карту
        $this->dispatch('routeUpdated', $this->routePoints);
    }

    public function removeRoutePoint(int $index): void
    {
        unset($this->routePoints[$index]);
        $this->routePoints = array_values($this->routePoints);
        $this->dispatch('routeUpdated', $this->routePoints);
    }

    /**
     * Добавить строку доп. услуги в форме
     */
    public function addExtraServiceRow(): void
    {
        $this->extraServices[] = [
            'name'        => '',
            'description' => '',
            'price'       => null,
            'per_person'  => false,
            'is_active'   => true,
        ];
    }

    /**
     * Удалить строку доп. услуги
     */
    public function removeExtraServiceRow(int $index): void
    {
        unset($this->extraServices[$index]);
        $this->extraServices = array_values($this->extraServices);
    }

    public function save()
    {
        $this->validate();

        $excursion = Excursion::create([
            'title'       => $this->title,
            'description' => $this->description,
            'price'       => $this->price,
            'duration'    => $this->duration,
            'category_id' => $this->category_id,
            'guide_id'    => auth()->id(),
            'location_id' => $this->location_id,
            'status'      => 'pending',
        ]);

        if (!empty($this->routePoints)) {
            $excursion->routes()->create([
                'coordinates' => $this->routePoints,
                'description' => 'Основной маршрут',
            ]);
        }

        if (!empty($this->photos)) {
            foreach ($this->photos as $index => $photo) {
                $path = $photo->store('excursions', 'public');
                $excursion->photos()->create([
                    'path'       => $path,
                    'sort_order' => $index,
                ]);
            }
        }

        // 💡 Сохраняем дополнительные услуги
        $this->saveExtraServices($excursion);

        session()->flash('success', 'Экскурсия успешно создана!');
        return redirect()->route('guide.excursions');
    }

    /**
     * Сохранение доп. услуг для только что созданной экскурсии
     */
    private function saveExtraServices(Excursion $excursion): void
    {
        // Оставляем только осмысленные строки (с названием и ценой)
        $rows = collect($this->extraServices)
            ->filter(function ($row) {
                return !empty($row['name'])
                    && $row['price'] !== null
                    && $row['price'] !== '';
            });

        foreach ($rows as $row) {
            $excursion->extraServices()->create([
                'name'        => $row['name'],
                'description' => $row['description'] ?? null,
                'price'       => $row['price'],
                'per_person'  => !empty($row['per_person']),
                'is_active'   => !empty($row['is_active']),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.guide.excursion-form', [
            'categories' => Category::all(),
            'locations'  => Location::all(),
        ])->layout('layouts.app');
    }
}
