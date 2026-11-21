<?php

namespace App\Livewire\Guide;

use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Bookings extends Component
{
    public $bookings = [];

    public $filter = 'pending'; // фильтр по умолчанию

    public function mount(): void
    {
        $this->loadBookings();
    }

    public function updatedFilter(): void
    {
        $this->loadBookings();
    }

    public function loadBookings(): void
    {
        $query = Booking::with([
            'user',
            'schedule.excursion',
            'extraServices', // 🔹 подгружаем доп. услуги
        ])
            ->whereHas('schedule.excursion', fn ($q) => $q->where('guide_id', Auth::id()));

        if ($this->filter !== 'all') {
            $query->where('status', $this->filter);
        }

        $this->bookings = $query
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function confirm(int $id): void
    {
        $booking = Booking::findOrFail($id);
        $booking->update(['status' => 'confirmed']);
        $this->loadBookings();
        $this->dispatch('notify', message: '✅ Бронирование подтверждено!');
    }

    public function reject(int $id): void
    {
        $booking = Booking::findOrFail($id);
        $booking->update(['status' => 'rejected']);
        $this->loadBookings();
        $this->dispatch('notify', message: '❌ Бронирование отклонено!');
    }

    public function render()
    {
        return view('livewire.guide.bookings')
            ->layout('layouts.app');
    }
}
