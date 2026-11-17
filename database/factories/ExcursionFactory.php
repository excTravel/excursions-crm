<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\User;
use App\Models\Location;

class ExcursionFactory extends Factory
{
    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(3);

        // гарантируем, что есть связанные данные
        $categoryId = Category::inRandomOrder()->value('id') ?? Category::factory()->create()->id;
        $locationId = Location::inRandomOrder()->value('id') ?? Location::factory()->create()->id;

        // найдём гида; если нет — создадим
        $guideId = User::where('role','guide')->inRandomOrder()->value('id')
            ?? User::factory()->state(['role'=>'guide'])->create()->id;

        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . Str::random(4),
            'description' => $this->faker->paragraph(4),
            'price' => $this->faker->numberBetween(500, 5000),
            'duration' => $this->faker->numberBetween(60, 240), // минуты
            'category_id' => $categoryId,
            'guide_id' => $guideId,
            'status' => 'approved',
            'location_id' => $locationId,
            // meeting_point_* можно опустить если nullable
        ];
    }
}
