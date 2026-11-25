<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Excursion;
use App\Models\Location;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExcursionManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function client_sees_only_approved_excursions_in_catalog()
    {
        $location = Location::factory()->create(['country' => 'Россия']);
        $category = Category::factory()->create();

        // Одобренная экскурсия
        $approvedExcursion = Excursion::factory()->create([
            'status' => 'approved',
            'location_id' => $location->id,
            'category_id' => $category->id,
        ]);

        // Экскурсия на модерации
        $pendingExcursion = Excursion::factory()->create([
            'status' => 'pending',
            'location_id' => $location->id,
            'category_id' => $category->id,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee($approvedExcursion->title);
        $response->assertDontSee($pendingExcursion->title);
    }

    /** @test */
    public function guide_can_create_new_excursion_with_pending_status()
    {
        $guide = User::factory()->create(['role' => 'guide']);
        $location = Location::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($guide)->post('/guide/excursions', [
            'title' => 'Новая тестовая экскурсия',
            'description' => 'Описание этой замечательной экскурсии длиной более 20 символов.',
            'price' => 1500,
            'duration' => 60,
            'category_id' => $category->id,
            'location_id' => $location->id,
        ]);

        $this->assertDatabaseHas('excursions', [
            'title' => 'Новая тестовая экскурсия',
            'status' => 'pending',
            'guide_id' => $guide->id,
        ]);
    }

    /** @test */
    public function client_can_create_booking_for_available_schedule()
    {
        $client = User::factory()->create(['role' => 'client']);
        
        // Создаем экскурсию и расписание для нее
        $excursion = Excursion::factory()->create(['status' => 'approved']);
        $schedule = Schedule::factory()->create([
            'excursion_id' => $excursion->id,
            'date' => now()->addDays(2)->format('Y-m-d'),
            'start_time' => '10:00:00',
            'available_seats' => 10,
        ]);

        // Отправляем запрос на создание брони
        $response = $this->actingAs($client)->post('/bookings', [ // Проверь роут в routes/web.php, может быть он другой
            'schedule_id' => $schedule->id,
            'participants' => 2,
        ]);

        // Проверяем, что бронь создалась со статусом pending
        $this->assertDatabaseHas('bookings', [
            'user_id' => $client->id,
            'schedule_id' => $schedule->id,
            'status' => 'pending',
            'participants' => 2,
        ]);
    }
}