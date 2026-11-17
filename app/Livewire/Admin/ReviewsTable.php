<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Review;

class ReviewsTable extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $selectedReview = null;

    protected $paginationTheme = 'tailwind';
    protected $queryString = ['search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal($id)
    {
        $this->selectedReview = Review::with(['user', 'excursion.guide'])->findOrFail($id);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->selectedReview = null;
        $this->showModal = false;
    }

    public function delete($id)
    {
        Review::findOrFail($id)->delete();
        session()->flash('success', 'Отзыв удалён.');
    }

    public function render()
    {
        $reviews = Review::with(['user', 'excursion'])
            ->when($this->search, function ($q) {
                $q->whereHas('user', fn($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                )->orWhereHas('excursion', fn($q) =>
                $q->where('title', 'like', "%{$this->search}%")
                );
            })
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('livewire.admin.reviews-table', [
            'reviews' => $reviews,
        ])->layout('layouts.app');
    }
}
