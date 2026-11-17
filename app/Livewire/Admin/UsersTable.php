<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\Excursion;
use Livewire\Component;
use Livewire\WithPagination;

class UsersTable extends Component
{
    use WithPagination;

    public $search = '';
    public $roleFilter = '';
    public $statusFilter = '';
    public $confirmBlockModal = false;
    public $confirmUnblockModal = false;
    public ?User $selectedUser = null;

    protected $paginationTheme = 'tailwind';

    public function updatingSearch() { $this->resetPage(); }
    public function updatingRoleFilter() { $this->resetPage(); }
    public function updatingStatusFilter() { $this->resetPage(); }

    public function openBlockModal($id)
    {
        $this->selectedUser = User::find($id);
        $this->confirmBlockModal = true;
    }

    public function openUnblockModal($id)
    {
        $this->selectedUser = User::find($id);
        $this->confirmUnblockModal = true;
    }

    public function block()
    {
        if (!$this->selectedUser) return;

        $user = $this->selectedUser->fresh();

        $user->is_blocked = true;
        $user->block_reason = 'Заблокирован администратором';
        $user->save();

        // Архивируем экскурсии гида при блокировке
        if ($user->role === 'guide') {
            Excursion::where('guide_id', $user->id)->update(['status' => 'archived']);
        }

        session()->flash('success', "Пользователь {$user->name} заблокирован.");
        $this->reset(['confirmBlockModal', 'selectedUser']);
    }

    public function unblock()
    {
        if (!$this->selectedUser) return;

        $user = $this->selectedUser->fresh();

        $user->is_blocked = false;
        $user->block_reason = null;
        $user->save();

        session()->flash('success', "Пользователь {$user->name} разблокирован.");
        $this->reset(['confirmUnblockModal', 'selectedUser']);
    }

    public function changeRole($id, $role)
    {
        $user = User::find($id);
        if (!$user || !$role) return;

        $user->update(['role' => $role]);
        session()->flash('success', 'Роль пользователя изменена.');
    }

    public function render()
    {
        $query = User::query();

        // Поиск по имени и email
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            });
        }

        // Фильтр по роли
        if ($this->roleFilter) {
            $query->where('role', $this->roleFilter);
        }

        // Фильтр по статусу блокировки
        if ($this->statusFilter === 'blocked') {
            $query->where('is_blocked', 1);
        } elseif ($this->statusFilter === 'active') {
            $query->where('is_blocked', 0);
        }

        $users = $query->orderByDesc('id')->paginate(10);

        return view('livewire.admin.users-table', [
            'users' => $users,
        ])->layout('layouts.app');
    }
}
