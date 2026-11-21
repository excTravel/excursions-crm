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
        $this->bookings = Booking::with([
            'schedule.excursion',
            'extraServices', // 🔹 подгружаем доп. услуги
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

        if (! $booking->canBeCanceled()) {
            $this->dispatch('notify', message: '❌ Бронирование уже нельзя отменить (меньше 24 ч)');

            return;
        }

        $booking->update(['status' => 'canceled']);

        $this->loadBookings(); // 🔄 обновляем список
        $this->dispatch('notify', message: '✅ Бронирование успешно отменено');
    }

    public function render()
    {
        return view('livewire.my-bookings')
            ->layout('layouts.app');
    }
}
