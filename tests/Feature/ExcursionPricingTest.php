<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Excursion;
use App\Models\Location;
use App\Models\PricingRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExcursionPricingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_calculates_price_correctly_for_per_group_rule()
    {

        $category = Category::factory()->create();
        $location = Location::factory()->create();


        $excursion = Excursion::create([
            'title' => 'Прогулка по крышам',
            'description' => 'Тестовая экскурсия',
            'price' => 5000,
            'duration' => 90,
            'category_id' => $category->id,
            'location_id' => $location->id,
            'status' => 'draft',
        ]);


        PricingRule::create([
            'excursion_id' => $excursion->id,
            'min_people' => 1,
            'max_people' => 5,
            'mode' => 'per_group',
            'price' => 4000,
        ]);


        $calculatedPrice = $excursion->priceForPeople(3);
        $this->assertEquals('4000.00', $calculatedPrice);
    }

    /** @test */
    public function it_generates_slug_from_title_automatically()
    {

        $category = Category::factory()->create();
        $location = Location::factory()->create();


        $excursion = Excursion::create([
            'title' => 'Вечерняя Москва',
            'description' => 'Описание',
            'price' => 1000,
            'duration' => 60,
            'category_id' => $category->id,
            'location_id' => $location->id,
            'status' => 'draft',
        ]);


        $this->assertEquals('vechernyaya-moskva', $excursion->slug);
    }
}
