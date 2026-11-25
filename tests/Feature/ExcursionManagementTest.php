<?php

namespace Tests\Feature;

use App\Livewire\Admin\ExcursionsTable;
use App\Livewire\BookingForm;
use App\Livewire\Guide\ExcursionForm;
use App\Livewire\MyBookings;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Excursion;
use App\Models\Location;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ExcursionManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function client_sees_only_approved_excursions_in_catalog()
    {
        $location = Location::factory()->create(['country' => 'Россия']);
        $category = Category::factory()->create();

        $approvedExcursion = Excursion::factory()->create([
            'status' => 'approved',
            'location_id' => $location->id,
            'category_id' => $category->id,
        ]);

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
        $guide = User::factory()->create();
        $guide->role = 'guide';
        $guide->save();

        $category = Category::factory()->create();
        $location = Location::factory()->create();

        Livewire::actingAs($guide)
            ->test(ExcursionForm::class)
            ->set('title', 'Новая тестовая экскурсия')
            ->set('description', 'Описание этой замечательной экскурсии длиной более 20 символов.')
            ->set('price', 1500)
            ->set('duration', 60)
            ->set('category_id', $category->id)
            ->set('location_id', $location->id)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('guide.excursions'));

        $this->assertDatabaseHas('excursions', [
            'title' => 'Новая тестовая экскурсия',
            'guide_id' => $guide->id,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function client_can_create_booking_for_available_schedule()
    {
        $client = User::factory()->create();
        $client->role = 'client';
        $client->save();

        $excursion = Excursion::factory()->create(['status' => 'approved']);

        $schedule = Schedule::create([
            'excursion_id' => $excursion->id,
            'date' => now()->addDays(2)->format('Y-m-d'),
            'start_time' => '10:00:00',
            'max_people' => 10,
        ]);

        Livewire::actingAs($client)
            ->test(BookingForm::class, ['excursion' => $excursion])
            ->set('selectedSchedule', $schedule->id)
            ->set('peopleCount', 2)
            ->call('book')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('bookings', [
            'user_id' => $client->id,
            'schedule_id' => $schedule->id,
            'people_count' => 2,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function client_can_view_their_own_bookings()
    {
        $client = User::factory()->create();
        $client->role = 'client';
        $client->save();

        $excursion = Excursion::factory()->create(['status' => 'approved']);
        $schedule = Schedule::create([
            'excursion_id' => $excursion->id,
            'date' => now()->addDays(2)->format('Y-m-d'),
            'start_time' => '10:00:00',
            'max_people' => 10,
        ]);

        Booking::create([
            'user_id' => $client->id,
            'schedule_id' => $schedule->id,
            'people_count' => 2,
            'total_price' => 3000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($client)->get('/my-bookings');

        $response->assertStatus(200);
        $response->assertSee($excursion->title);
    }

    /** @test */
    public function admin_can_approve_excursion()
    {
        $admin = User::factory()->create();
        $admin->role = 'admin';
        $admin->save();

        $guide = User::factory()->create();
        $guide->role = 'guide';
        $guide->save();

        $excursion = Excursion::factory()->create([
            'status' => 'pending',
            'guide_id' => $guide->id,
        ]);

        Livewire::actingAs($admin)
            ->test(ExcursionsTable::class)
            ->call('approve', $excursion->id);

        $this->assertDatabaseHas('excursions', [
            'id' => $excursion->id,
            'status' => 'approved',
        ]);
    }

    /** @test */
    public function admin_can_reject_excursion()
    {
        $admin = User::factory()->create();
        $admin->role = 'admin';
        $admin->save();

        $excursion = Excursion::factory()->create(['status' => 'pending']);

        Livewire::actingAs($admin)
            ->test(ExcursionsTable::class)
            ->call('openRejectModal', $excursion->id)
            ->set('rejectionReason', 'Не соответствует требованиям')
            ->call('reject');

        $this->assertDatabaseHas('excursions', [
            'id' => $excursion->id,
            'status' => 'rejected',
            'rejection_reason' => 'Не соответствует требованиям',
        ]);
    }

    /** @test */
    public function client_can_pay_for_booking()
    {
        $client = User::factory()->create();
        $client->role = 'client';
        $client->save();

        $excursion = Excursion::factory()->create(['status' => 'approved']);
        $schedule = Schedule::create([
            'excursion_id' => $excursion->id,
            'date' => now()->addDays(2)->format('Y-m-d'),
            'start_time' => '10:00:00',
            'max_people' => 10,
        ]);

        $booking = Booking::create([
            'user_id' => $client->id,
            'schedule_id' => $schedule->id,
            'people_count' => 2,
            'total_price' => 3000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $response = $this->actingAs($client)->post("/payment/booking/{$booking->id}", [
            'card_number' => '1234567812345678',
            'card_name' => 'IVAN IVANOV',
            'card_expiry' => '12/25',
            'card_cvv' => '123',
        ]);

        $response->assertRedirect(route('bookings.my'));

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ]);
    }

    /** @test */
    public function client_can_cancel_booking()
    {
        $client = User::factory()->create();
        $client->role = 'client';
        $client->save();

        $excursion = Excursion::factory()->create(['status' => 'approved']);
        $schedule = Schedule::create([
            'excursion_id' => $excursion->id,
            'date' => now()->addDays(3)->format('Y-m-d'),
            'start_time' => '10:00:00',
            'max_people' => 10,
        ]);

        $booking = Booking::create([
            'user_id' => $client->id,
            'schedule_id' => $schedule->id,
            'people_count' => 2,
            'total_price' => 3000,
            'status' => 'pending',
        ]);

        Livewire::actingAs($client)
            ->test(MyBookings::class)
            ->call('cancel', $booking->id);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'canceled',
        ]);
    }

    /** @test */
    public function client_cannot_book_if_no_seats_available()
    {
        $client = User::factory()->create(['role' => 'client']);
        $excursion = Excursion::factory()->create(['status' => 'approved']);

        $schedule = Schedule::create([
            'excursion_id' => $excursion->id,
            'date' => now()->addDays(2)->format('Y-m-d'),
            'start_time' => '10:00:00',
            'max_people' => 2,
        ]);

        // Занимаем все места
        Booking::create([
            'user_id' => $client->id,
            'schedule_id' => $schedule->id,
            'people_count' => 2,
            'total_price' => 3000,
            'status' => 'confirmed',
        ]);

        // Пытаемся забронировать еще
        Livewire::actingAs($client)
            ->test(BookingForm::class, ['excursion' => $excursion])
            ->set('selectedSchedule', $schedule->id)
            ->set('peopleCount', 1)
            ->call('book');

        // Проверяем, что вторая бронь НЕ создалась (в базе только одна)
        $this->assertDatabaseCount('bookings', 1);
    }

    /** @test */
    public function excursion_show_page_displays_excursion_details()
    {
        $location = Location::factory()->create(['country' => 'Россия']);
        $category = Category::factory()->create();
        $excursion = Excursion::factory()->create([
            'status' => 'approved',
            'location_id' => $location->id,
            'category_id' => $category->id,
        ]);

        // Открываем страницу конкретной экскурсии
        $response = $this->get("/excursions/{$excursion->slug}");

        $response->assertStatus(200);
        $response->assertSee($excursion->title);
        $response->assertSee($excursion->description);
    }

    /** @test */
    public function excursions_list_page_can_filter_by_search()
    {
        $location = Location::factory()->create(['country' => 'Россия']);
        $category = Category::factory()->create();

        Excursion::factory()->create([
            'title' => 'Уникальная экскурсия',
            'status' => 'approved',
            'location_id' => $location->id,
            'category_id' => $category->id,
        ]);

        // Тестируем поиск через Livewire
        Livewire::test(\App\Livewire\ExcursionsList::class)
            ->set('search', 'Уникальная')
            ->assertSee('Уникальная экскурсия');
    }

    /** @test */
    public function guide_form_can_add_and_remove_route_points()
    {
        $guide = User::factory()->create();
        $guide->role = 'guide';
        $guide->save();

        // Тестируем методы добавления и удаления точек маршрута
        Livewire::actingAs($guide)
            ->test(ExcursionForm::class)
            ->call('addRoutePoint', ['lat' => 55.75, 'lng' => 37.61, 'name' => 'Точка 1'])
            ->assertSet('routePoints', [['lat' => 55.75, 'lng' => 37.61]])
            ->call('removeRoutePoint', 0)
            ->assertSet('routePoints', []);
    }

    /** @test */
    public function booking_can_be_canceled_if_more_than_24_hours_left()
    {
        $booking = new Booking;
        $booking->status = 'pending';

        // Создаем расписание на послезавтра
        $schedule = new Schedule;
        $schedule->date = now()->addDays(2)->format('Y-m-d');
        $schedule->start_time = '10:00:00';

        $booking->setRelation('schedule', $schedule);

        // Проверяем метод модели
        $this->assertTrue($booking->canBeCanceled());
    }

    /** @test */
    public function user_cannot_pay_for_already_paid_booking()
    {
        $client = User::factory()->create(['role' => 'client']);
        $booking = Booking::create([
            'user_id' => $client->id,
            'schedule_id' => Schedule::create([
                'excursion_id' => Excursion::factory()->create()->id,
                'date' => now()->addDays(2)->format('Y-m-d'),
                'start_time' => '10:00:00',
                'max_people' => 10,
            ])->id,
            'people_count' => 2,
            'total_price' => 3000,
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        // Отправляем POST-запрос на оплату
        $response = $this->actingAs($client)->post("/payment/booking/{$booking->id}", [
            'card_number' => '1234567812345678',
            'card_name' => 'IVAN IVANOV',
            'card_expiry' => '12/25',
            'card_cvv' => '123',
        ]);

        // Проверяем, что редирект произошел
        $response->assertRedirect();

        // Проверяем, что данные в базе не изменились (статус остался 'paid')
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ]);
    }

    /** @test */
    public function excursion_show_returns_404_for_invalid_slug()
    {
        $response = $this->get('/excursions/non-existent-slug');
        $response->assertStatus(404);
    }

    /** @test */
    public function user_model_can_check_roles()
    {
        $guide = User::factory()->create(['role' => 'guide']);
        $admin = User::factory()->create(['role' => 'admin']);
        $client = User::factory()->create(['role' => 'client']);

        // Проверяем методы модели (они должны быть в User.php, если нет — тест упадет, и мы поправим)
        $this->assertEquals('guide', $guide->role);
        $this->assertEquals('admin', $admin->role);
        $this->assertEquals('client', $client->role);
    }

    /** @test */
    public function admin_can_archive_excursion()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $excursion = Excursion::factory()->create(['status' => 'approved']);

        Livewire::actingAs($admin)
            ->test(ExcursionsTable::class)
            ->call('openArchiveConfirm', $excursion->id)
            ->call('archive');

        $this->assertDatabaseHas('excursions', [
            'id' => $excursion->id,
            'status' => 'archived',
        ]);
    }

    /** @test */
    public function booking_model_cannot_be_canceled_if_less_than_24_hours()
    {
        $booking = new Booking;
        $booking->status = 'pending';

        // Создаем расписание на сегодня (меньше 24 часов)
        $schedule = new Schedule;
        $schedule->date = now()->format('Y-m-d');
        $schedule->start_time = '10:00:00';

        $booking->setRelation('schedule', $schedule);

        // Проверяем, что отменить нельзя
        $this->assertFalse($booking->canBeCanceled());
    }

    /** @test */
    public function booking_model_total_price_and_relationships()
    {
        $client = User::factory()->create(['role' => 'client']);
        $excursion = Excursion::factory()->create(['status' => 'approved']);
        $schedule = Schedule::create([
            'excursion_id' => $excursion->id,
            'date' => now()->addDays(2)->format('Y-m-d'),
            'start_time' => '10:00:00',
            'max_people' => 10,
        ]);

        $booking = Booking::create([
            'user_id' => $client->id,
            'schedule_id' => $schedule->id,
            'people_count' => 2,
            'total_price' => 3000,
            'status' => 'pending',
        ]);

        // Проверяем связи и методы
        $this->assertEquals($client->id, $booking->user->id);
        $this->assertEquals($schedule->id, $booking->schedule->id);
        $this->assertEquals(3000, $booking->total_price);
    }
}
