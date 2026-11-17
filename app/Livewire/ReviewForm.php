<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Review;
use App\Models\Excursion;
use Illuminate\Support\Facades\Auth;

class ReviewForm extends Component
{
    public $excursionId;
    public $rating = 5;
    public $comment = '';

    public function mount($excursion)
    {
        $this->excursionId = $excursion instanceof Excursion ? $excursion->id : $excursion;
    }

    public function submit()
    {
        $this->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        $user = Auth::user();

        // Проверка: уже оставлял отзыв?
        if (Review::where('user_id', $user->id)
            ->where('excursion_id', $this->excursionId)
            ->exists()) {
            session()->flash('error', 'Вы уже оставили отзыв на эту экскурсию.');
            return;
        }

        // Проверка: участвовал ли в экскурсии
        $hasBooking = $user->bookings()
            ->whereHas('schedule', fn($q) =>
            $q->where('excursion_id', $this->excursionId)
                ->whereNotNull('ends_at')
                ->where('ends_at', '<', now())
            )
            ->exists();

        if (!$hasBooking) {
            session()->flash('error', 'Вы можете оставить отзыв только после завершения экскурсии.');
            return;
        }

        Review::create([
            'user_id' => $user->id,
            'excursion_id' => $this->excursionId,
            'rating' => $this->rating,
            'comment' => $this->comment,
        ]);

        session()->flash('success', 'Спасибо за ваш отзыв!');
        $this->reset('rating', 'comment');
    }

    public function render()
    {
        $excursion = Excursion::findOrFail($this->excursionId);

        return view('livewire.review-form', [
            'excursion' => $excursion,
        ])->layout('layouts.app');
    }
}
