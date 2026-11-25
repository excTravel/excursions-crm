<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Excursion;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;

class LoadTestSeeder extends Seeder
{
    public function run(): void
    {
        // Создаем одного гида, локацию и категорию
        $guide = User::factory()->create(['role' => 'guide']);
        $location = Location::factory()->create(['country' => 'Россия']);
        $category = Category::factory()->create();

        // Генерируем 10 000 экскурсий
        Excursion::factory()->count(10000)->create([
            'status' => 'approved',
            'guide_id' => $guide->id,
            'location_id' => $location->id,
            'category_id' => $category->id,
        ]);
    }
}
