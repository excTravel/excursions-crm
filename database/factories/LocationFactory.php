<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LocationFactory extends Factory
{
    public function definition(): array
    {
        $city = $this->faker->unique()->city();

        return [
            'city' => $city,
            'country' => $this->faker->country(),
            'slug' => Str::slug($city) . '-' . Str::random(4),
        ];
    }
}
