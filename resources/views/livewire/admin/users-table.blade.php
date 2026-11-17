<div class="voyage-admin-users max-w-7xl mx-auto py-10 px-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Пользователи</h1>

    {{-- Фильтры --}}
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
        <div class="voyage-admin-filter-row flex-wrap">
            <input
                wire:model.live="search"
                type="text"
                placeholder="Поиск по имени или email"
                class="w-full md:w-72 border-gray-300 rounded-full shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />

            <select wire:model.live="roleFilter" class="rounded-full border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Все роли</option>
                <option value="client">Клиенты</option>
                <option value="guide">Гиды</option>
                <option value="admin">Администраторы</option>
            </select>

            <select wire:model.live="statusFilter" class="rounded-full border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Все</option>
                <option value="active">Активные</option>
                <option value="blocked">Заблокированные</option>
            </select>
        </div>

        @if (session()->has('success'))
        <span class="text-green-600 font-medium">{{ session('success') }}</span>
        @elseif (session()->has('error'))
        <span class="text-red-600 font-medium">{{ session('error') }}</span>
        @endif
    </div>

    {{-- Таблица --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Имя</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Роль</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Статус</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Действия</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($users as $user)
            <tr class="hover:bg-gray-50 @if($user->is_blocked) bg-red-50 @endif">
                <td class="px-6 py-3 text-sm text-gray-700">{{ $user->id }}</td>
                <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ $user->name }}</td>
                <td class="px-6 py-3 text-sm text-gray-700">{{ $user->email }}</td>
                <td class="px-6 py-3 text-sm">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold
                            @if($user->role === 'admin') bg-purple-100 text-purple-800
                            @elseif($user->role === 'guide') bg-blue-100 text-blue-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ ucfirst($user->role) }}
                        </span>
                </td>
                <td class="px-6 py-3 text-sm">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold
                            @if($user->is_blocked)
                                bg-red-100 text-red-800
                            @else
                                bg-green-100 text-green-800
                            @endif">
                            {{ $user->is_blocked ? 'Заблокирован' : 'Активен' }}
                        </span>
                </td>
                <td class="px-6 py-3 text-right text-sm font-medium space-x-2">
                    {{-- Смена роли --}}
                    <select wire:change="changeRole({{ $user->id }}, $event.target.value)"
                            class="voyage-user-role-select border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Изменить роль</option>
                        <option value="client">Клиент</option>
                        <option value="guide">Гид</option>
                        <option value="admin">Админ</option>
                    </select>

                    {{-- Блокировка / разблокировка --}}
                    @if(!$user->is_blocked)
                    <button
                        wire:click="openBlockModal({{ $user->id }})"
                        class="voyage-user-action bg-red-100 text-red-700 hover:bg-red-200">
                        Заблокировать
                    </button>
                    @else
                    <button
                        wire:click="openUnblockModal({{ $user->id }})"
                        class="voyage-user-action bg-green-100 text-green-700 hover:bg-green-200">
                        Разблокировать
                    </button>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-6 text-center text-gray-500">Нет пользователей</td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>

    {{-- Подтверждение блокировки --}}
    @if($confirmBlockModal && $selectedUser)
    <div class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-xl p-6 max-w-sm text-center">
            <h3 class="text-lg font-semibold mb-4">
                Заблокировать пользователя <span class="font-bold">{{ $selectedUser->name }}</span>?
            </h3>
            <p class="text-sm text-gray-600 mb-4">Он не сможет войти в систему, пока не будет разблокирован.</p>
            <div class="flex justify-center gap-4">
                <button wire:click="block" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">Да</button>
                <button wire:click="$set('confirmBlockModal', false)" class="bg-gray-100 px-4 py-2 rounded-lg hover:bg-gray-200">Отмена</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Подтверждение разблокировки --}}
    @if($confirmUnblockModal && $selectedUser)
    <div class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-xl p-6 max-w-sm text-center">
            <h3 class="text-lg font-semibold mb-4">
                Разблокировать пользователя <span class="font-bold">{{ $selectedUser->name }}</span>?
            </h3>
            <p class="text-sm text-gray-600 mb-4">Он сможет снова войти в систему и пользоваться сервисом.</p>
            <div class="flex justify-center gap-4">
                <button wire:click="unblock" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">Да</button>
                <button wire:click="$set('confirmUnblockModal', false)" class="bg-gray-100 px-4 py-2 rounded-lg hover:bg-gray-200">Отмена</button>
            </div>
        </div>
    </div>
    @endif
</div>
