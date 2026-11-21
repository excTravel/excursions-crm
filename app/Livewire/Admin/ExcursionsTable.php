<?php

namespace App\Livewire\Admin;

use App\Models\Excursion;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ExcursionsTable extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'active'; // active | archived | deleted

    public bool $showModal = false;

    public bool $showRejectModal = false;

    public bool $showArchiveConfirm = false;

    public ?Excursion $selectedExcursion = null;

    public string $rejectionReason = '';

    protected $paginationTheme = 'tailwind';

    protected $queryString = ['search', 'statusFilter'];

    // 📦 Получение экскурсий
    public function getExcursionsProperty()
    {
        return Excursion::query()
            ->with(['guide', 'extraServices', 'routes']) // 🔹 добавили routes
            ->when($this->statusFilter === 'archived', fn ($q) => $q->where('status', 'archived'))
            ->when($this->statusFilter === 'active', fn ($q) => $q->where('status', '!=', 'archived'))
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%")
                ->orWhereHas('guide', fn ($g) => $g->where('name', 'like', "%{$this->search}%"))
            )
            ->orderByDesc('created_at')
            ->paginate(10);
    }

    // ✅ Одобрить экскурсию
    public function approve(int $id)
    {
        if ($exc = Excursion::find($id)) {
            $exc->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
            session()->flash('success', '✅ Экскурсия одобрена');
        }
    }

    public function openRejectModal(int $id)
    {
        $this->selectedExcursion = Excursion::find($id);
        $this->showRejectModal = true;
        $this->rejectionReason = '';
    }

    public function restore($id)
    {
        $exc = Excursion::find($id);
        if ($exc && $exc->status === 'archived') {
            $exc->update(['status' => 'approved']);
            session()->flash('success', '✅ Экскурсия восстановлена');
        }
    }

    // ❌ Отклонить экскурсию
    public function reject()
    {
        if (! $this->selectedExcursion) {
            return;
        }

        $this->selectedExcursion->update([
            'status' => 'rejected',
            'rejection_reason' => $this->rejectionReason,
        ]);

        $this->showRejectModal = false;
        $this->rejectionReason = '';
        session()->flash('success', '❌ Экскурсия отклонена');
    }

    // 📁 Открыть модалку подтверждения архивации
    public function openArchiveConfirm(int $id)
    {
        $this->selectedExcursion = Excursion::find($id);
        $this->showArchiveConfirm = true;
    }

    // 🗂 Архивировать экскурсию
    public function archive()
    {
        if (! $this->selectedExcursion) {
            return;
        }

        $this->selectedExcursion->update(['status' => 'archived']);
        $this->showArchiveConfirm = false;
        session()->flash('success', '🚫 Экскурсия отправлена в архив');
    }

    // 🗑 Перемещение в корзину
    public function deleteExcursion(int $id)
    {
        if ($exc = Excursion::find($id)) {
            $exc->delete();
            session()->flash('success', '🗑 Экскурсия перемещена в корзину');
        }
    }

    public function restoreExcursion(int $id)
    {
        if ($exc = Excursion::onlyTrashed()->find($id)) {
            $exc->restore();
            session()->flash('success', '✅ Экскурсия восстановлена');
        }
    }

    public function openModal(int $id)
    {
        $this->selectedExcursion = Excursion::with([
            'guide',
            'category',
            'location',
            'photos',
            'extraServices',
            'routes', // 🔹 сюда тоже
        ])
            ->withTrashed()
            ->findOrFail($id);

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->showRejectModal = false;
        $this->showArchiveConfirm = false;
        $this->selectedExcursion = null;
    }

    public function render()
    {
        return view('livewire.admin.excursions-table', [
            'excursions' => $this->excursions,
        ])->layout('layouts.app');
    }
}
