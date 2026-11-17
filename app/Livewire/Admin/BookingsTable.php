<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Booking;

class BookingsTable extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $showModal = false;
    public $selectedBooking = null;

    protected $paginationTheme = 'tailwind';
    protected $queryString = ['search', 'statusFilter'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function openModal($id)
    {
        $this->selectedBooking = Booking::with([
            'excursion',
            'user',
            'excursion.guide',
            'schedule',
            'extraServices', // 🔹 доп. услуги для модалки
        ])
            ->findOrFail($id);

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->selectedBooking = null;
        $this->showModal = false;
    }

    public function updateStatus($id, $status)
    {
        $booking = Booking::findOrFail($id);
        $booking->status = $status;
        $booking->save();

        session()->flash('success', "Статус бронирования обновлён на '{$status}'");
    }

    public function delete($id)
    {
        Booking::findOrFail($id)->delete();
        session()->flash('success', 'Бронирование удалено');
    }

    public function render()
    {
        $bookings = Booking::with([
            'user',
            'excursion.guide',
            'extraServices', // 🔹 чтобы не было N+1, если захочешь показывать их и в таблице
        ])
            ->when($this->search, function ($q) {
                $q->whereHas('user', fn($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                )
                    ->orWhereHas('excursion', fn($q) =>
                    $q->where('title', 'like', "%{$this->search}%")
                    );
            })
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('livewire.admin.bookings-table', [
            'bookings' => $bookings,
        ])->layout('layouts.app');
    }
}
