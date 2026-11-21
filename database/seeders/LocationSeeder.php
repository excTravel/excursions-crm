<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['city' => 'Москва', 'country' => 'Россия'],
            ['city' => 'Санкт-Петербург', 'country' => 'Россия'],
            ['city' => 'Казань', 'country' => 'Россия'],
            ['city' => 'Сочи', 'country' => 'Россия'],
            ['city' => 'Калининград', 'country' => 'Россия'],
            ['city' => 'Нижний Новгород', 'country' => 'Россия'],
            ['city' => 'Ярославль', 'country' => 'Россия'],
            ['city' => 'Владимир', 'country' => 'Россия'],
            ['city' => 'Великий Новгород', 'country' => 'Россия'],
            ['city' => 'Екатеринбург', 'country' => 'Россия'],
            ['city' => 'Новосибирск', 'country' => 'Россия'],
            ['city' => 'Иркутск', 'country' => 'Россия'],
            ['city' => 'Петрозаводск', 'country' => 'Россия'],
            ['city' => 'Мурманск', 'country' => 'Россия'],
            ['city' => 'Владивосток', 'country' => 'Россия'],
            ['city' => 'Псков', 'country' => 'Россия'],
            ['city' => 'Суздаль', 'country' => 'Россия'],
            ['city' => 'Архангельск', 'country' => 'Россия'],
        ];

        foreach ($items as $it) {
            $base = trim($it['city'].'-'.$it['country']);

            // 1) пробуем обычный slug
            $slug = Str::slug($base, '-');

            // 2) если пусто (часто бывает без intl), пробуем ascii()
            if ($slug === '') {
                $slug = Str::of($base)->ascii()->slug('-')->value();
            }

            // 3) крайний случай — генерим технический
            if ($slug === '') {
                $slug = 'loc-'.Str::random(8);
            }

            Location::updateOrCreate(
                ['city' => $it['city']],
                ['country' => $it['country'], 'slug' => $slug]
            );
        }
    }
}
