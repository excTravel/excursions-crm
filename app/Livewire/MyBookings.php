<?php

namespace App\Livewire;

use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MyBookings extends Component
{
    public $bookings = [];

    public function mount()
    {
        $this->loadBookings();
    }

    public function loadBookings(): void
    {
        // Загружаем бронирования текущего пользователя с расписанием и доп. услугами
        $this->bookings = Booking::with([
            'schedule.excursion',
            'extraServices',
        ])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();
    }

    public function cancel(int $id): void
    {
        $booking = Booking::with('schedule')
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        // Проверяем, можно ли отменить бронирование (зависит от времени до начала)
        if (! $booking->canBeCanceled()) {
            $this->dispatch('notify', message: '❌ Бронирование уже нельзя отменить (меньше 24 ч)');

            return;
        }

        $booking->update(['status' => 'canceled']);

        // Обновляем список бронирований после отмены
        $this->loadBookings();

        $this->dispatch('notify', message: '✅ Бронирование успешно отменено');
    }

    public function render()
    {
        return view('livewire.my-bookings')
            ->layout('layouts.app');
    }
}
