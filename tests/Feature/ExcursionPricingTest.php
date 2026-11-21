<?php

namespace Tests\Feature;

use App\Models\Excursion;
use App\Models\PricingRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExcursionPricingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_calculates_price_correctly_for_per_group_rule()
    {
        $excursion = Excursion::create([
            'title' => 'Прогулка по крышам',
            'description' => 'Тестовая экскурсия',
            'price' => 5000, // Базовая цена
            'duration' => 90,
        ]);
        PricingRule::create([
            'excursion_id' => $excursion->id,
            'min_people' => 1,
            'max_people' => 5,
            'mode' => 'per_group',
            'price' => 4000,
        ]);

        // 3. Проверяем расчет для 3 человек
        $calculatedPrice = $excursion->priceForPeople(3);

        // Ожидаем, что цена будет 4000 (фиксированная за группу), а не 15000 (5000 * 3)
        $this->assertEquals('4000.00', $calculatedPrice);
    }

    /** @test */
    public function it_generates_slug_from_title_automatically()
    {
        // Проверяем работу твоего boot-метода
        $excursion = Excursion::create([
            'title' => 'Вечерняя Москва',
            'description' => 'Описание',
            'price' => 1000,
            'duration' => 60,
        ]);

        // Slug должен создаться автоматически из заголовка
        $this->assertEquals('vechernyaya-moskva', $excursion->slug);
    }
}
