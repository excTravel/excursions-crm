<?php

namespace App\Livewire\Guide;

use App\Models\Category;
use App\Models\Excursion;
use App\Models\ExcursionPhoto;
use App\Models\Location;
use Livewire\Component;
use Livewire\WithFileUploads;

class ExcursionEdit extends Component
{
    use WithFileUploads;

    public $excursion;

    public $title;

    public $description;

    public $price;

    public $duration;

    public $category_id;

    public $location_id;

    public $newPhotos = [];

    public array $routePoints = [];

    /** @var array<int, array<string, mixed>> */
    public $extraServices = [];

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'required|string|min:20',
        'price' => 'required|numeric|min:0',
        'duration' => 'required|integer|min:10',
        'category_id' => 'required|exists:categories,id',
        'location_id' => 'required|exists:locations,id',
        'newPhotos.*' => 'nullable|image|max:4096',

        // Доп. услуги
        'extraServices.*.name' => 'nullable|string|max:255',
        'extraServices.*.description' => 'nullable|string|max:1000',
        'extraServices.*.price' => 'nullable|numeric|min:0',
        'extraServices.*.per_person' => 'boolean',
        'extraServices.*.is_active' => 'boolean',
    ];

    public function mount(Excursion $excursion)
    {
        $this->excursion = $excursion;
        $this->title = $excursion->title;
        $this->description = $excursion->description;
        $this->price = $excursion->price;
        $this->duration = $excursion->duration;
        $this->category_id = $excursion->category_id;
        $this->location_id = $excursion->location_id;
        $this->routePoints = $excursion->routes->first()?->coordinates ?? [];

        // Загружаем существующие доп. услуги в форму
        $this->extraServices = $this->excursion->extraServices
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'description' => $service->description,
                    'price' => $service->price,
                    'per_person' => (bool) $service->per_person,
                    'is_active' => (bool) $service->is_active,
                ];
            })
            ->toArray();
    }

    // Добавляем точку маршрута (вызов напрямую из JS)
    public function addRoutePoint(array $payload): void
    {
        $lat = isset($payload['lat']) ? (float) $payload['lat'] : null;
        $lng = isset($payload['lng']) ? (float) $payload['lng'] : null;

        if ($lat === null || $lng === null) {
            return;
        }

        $this->routePoints[] = [
            'lat' => round($lat, 6),
            'lng' => round($lng, 6),
        ];

        $this->dispatch('routeUpdated', $this->routePoints);
    }

    public function removeRoutePoint($index)
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
            'id' => null,
            'name' => '',
            'description' => '',
            'price' => null,
            'per_person' => false,
            'is_active' => true,
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

    public function deletePhoto($photoId)
    {
        $photo = ExcursionPhoto::find($photoId);
        if ($photo && $photo->excursion_id === $this->excursion->id) {
            \Storage::disk('public')->delete($photo->path);
            $photo->delete();
            $this->excursion->refresh();
        }
    }

    public function save()
    {
        $this->validate();

        $this->excursion->update([
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'duration' => $this->duration,
            'category_id' => $this->category_id,
            'location_id' => $this->location_id,
            'status' => 'pending',
        ]);

        if (! empty($this->routePoints)) {
            $this->excursion->routes()->updateOrCreate(
                ['excursion_id' => $this->excursion->id],
                ['coordinates' => $this->routePoints, 'description' => 'Обновлённый маршрут']
            );
        }

        if (! empty($this->newPhotos)) {
            foreach ($this->newPhotos as $index => $photo) {
                $path = $photo->store('excursions', 'public');
                $this->excursion->photos()->create([
                    'path' => $path,
                    'sort_order' => $this->excursion->photos()->count() + $index,
                ]);
            }
        }

        // 💡 Сохраняем/обновляем/удаляем доп. услуги
        $this->saveExtraServices();

        session()->flash('success', 'Экскурсия успешно обновлена!');

        return redirect()->route('guide.excursions');
    }

    /**
     * Сохранение доп. услуг для редактируемой экскурсии
     */
    private function saveExtraServices(): void
    {
        // Берём только строки, где есть имя и цена
        $rows = collect($this->extraServices)
            ->filter(function ($row) {
                return ! empty($row['name'])
                    && $row['price'] !== null
                    && $row['price'] !== '';
            });

        // ID услуг, которые остались в форме (остальные нужно удалить из БД)
        $idsFromForm = $rows
            ->pluck('id')
            ->filter()
            ->all();

        // Удаляем услуги, которых больше нет в форме
        $this->excursion->extraServices()
            ->when(! empty($idsFromForm), function ($q) use ($idsFromForm) {
                $q->whereNotIn('id', $idsFromForm);
            }, function ($q) {
                // если формально нет ни одной услуги с id — удаляем все
                $q->whereRaw('1 = 1');
            })
            ->delete();

        foreach ($rows as $row) {
            if (! empty($row['id'])) {
                // обновляем существующую услугу
                $service = $this->excursion->extraServices()
                    ->where('id', $row['id'])
                    ->first();

                if ($service) {
                    $service->update([
                        'name' => $row['name'],
                        'description' => $row['description'] ?? null,
                        'price' => $row['price'],
                        'per_person' => ! empty($row['per_person']),
                        'is_active' => ! empty($row['is_active']),
                    ]);
                }
            } else {
                // создаём новую услугу
                $this->excursion->extraServices()->create([
                    'name' => $row['name'],
                    'description' => $row['description'] ?? null,
                    'price' => $row['price'],
                    'per_person' => ! empty($row['per_person']),
                    'is_active' => ! empty($row['is_active']),
                ]);
            }
        }
    }

    public function render()
    {
        return view('livewire.guide.excursion-edit', [
            'categories' => Category::all(),
            'locations' => Location::all(),
        ])->layout('layouts.app');
    }
}
