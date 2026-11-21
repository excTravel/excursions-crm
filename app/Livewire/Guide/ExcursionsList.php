<?php

namespace App\Livewire\Guide;

use App\Models\Excursion;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ExcursionsList extends Component
{
    public function render()
    {
        $excursions = Excursion::where('guide_id', Auth::id())
            ->with(['category', 'location'])
            ->latest()
            ->get();

        return view('livewire.guide.excursions-list', [
            'excursions' => $excursions,
        ])->layout('layouts.app');
    }
}
