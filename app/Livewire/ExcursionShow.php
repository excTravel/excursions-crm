<?php

namespace App\Livewire;

use App\Models\Excursion;
use Livewire\Component;

class ExcursionShow extends Component
{
    public ?Excursion $excursion = null;

    public array $routePoints = []; // [[lat,lng,"name"], ...]

    public function mount(string $slug): void
    {
        $this->excursion = Excursion::with([
            'category', 'guide', 'location', 'photos', 'reviews.user', 'routes', 'pricingRules',
        ])->where('slug', $slug)->first();

        if (! $this->excursion) {
            abort(404);
        }

        // Берём ОДИН маршрут (например, последний утверждённый) и вытаскиваем точки
        $route = $this->excursion->routes()->latest('id')->first();

        if ($route && is_array($route->coordinates)) {
            // ожидается формат: [['lat'=>..,'lng'=>..,'name'=>..], ...]
            $this->routePoints = collect($route->coordinates)
                ->filter(fn ($p) => isset($p['lat'], $p['lng']))
                ->map(fn ($p) => [
                    (float) $p['lat'],
                    (float) $p['lng'],
                    $p['name'] ?? null,
                ])
                ->values()
                ->all();
        }
    }

    public function render()
    {
        return view('livewire.excursion-show')
            ->layout('layouts.app');
    }
}
