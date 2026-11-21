<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Livewire\ExcursionShow;
use App\Livewire\ExcursionsList;
use App\Livewire\Guide\ExcursionEdit;
use App\Livewire\Guide\ExcursionForm;
use App\Livewire\Guide\ExcursionsList as GuideExcursionsList;
use App\Livewire\Guide\Schedules;
use App\Livewire\MyBookings;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Гостевой доступ
|--------------------------------------------------------------------------
*/

// Главная страница — каталог экскурсий
Route::get('/', ExcursionsList::class)->name('excursions.list');

// Просмотр конкретной экскурсии
Route::get('/excursions/{slug}', ExcursionShow::class)->name('excursions.show');

// Перенаправление с dashboard на главную
Route::get('/dashboard', fn () => redirect('/'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| Профиль пользователя
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/reviews/create/{excursion}', \App\Livewire\ReviewForm::class)
        ->name('reviews.create');
    Route::get('/my-bookings', MyBookings::class)->name('bookings.my');
    Route::get('/payment/booking/{booking}', [PaymentController::class, 'show'])
        ->name('payment.booking.show');
    Route::post('/payment/booking/{booking}', [PaymentController::class, 'pay'])
        ->name('payment.booking.pay');
});

/*
|--------------------------------------------------------------------------
| Гид — управление экскурсиями
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'guide'])->prefix('guide')->name('guide.')->group(function () {
    Route::get('/excursions', GuideExcursionsList::class)->name('excursions');
    Route::get('/excursions/create', ExcursionForm::class)->name('excursions.create');
    Route::get('/excursions/{excursion}/edit', ExcursionEdit::class)->name('excursions.edit');
    Route::get('/excursions/{excursion}/schedules', Schedules::class)
        ->name('excursions.schedules');
    Route::get('/bookings', \App\Livewire\Guide\Bookings::class)->name('bookings');
});

/*
|--------------------------------------------------------------------------
| Админ — панель управления
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', \App\Livewire\Admin\Dashboard::class)->name('dashboard');

        // Экскурсии
        Route::get('/excursions', \App\Livewire\Admin\ExcursionsTable::class)->name('excursions');

        // Пользователи
        Route::get('/users', \App\Livewire\Admin\UsersTable::class)->name('users');

        // Бронирования
        Route::get('/bookings', \App\Livewire\Admin\BookingsTable::class)->name('bookings');

        // Отзывы
        Route::get('/reviews', \App\Livewire\Admin\ReviewsTable::class)->name('reviews');
    });

/*
|--------------------------------------------------------------------------
| Вход / Регистрация
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
// test run 3
// test run 4
// test run 5
// test cache 2
// test cache 3
