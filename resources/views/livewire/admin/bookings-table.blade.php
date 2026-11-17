<div class="voyage-admin-bookings max-w-7xl mx-auto py-10 px-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Бронирования</h1>

    {{-- Фильтры --}}
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
        <div class="voyage-admin-filter-row flex-wrap">
            <input
                wire:model.live="search"
                type="text"
                placeholder="Поиск по клиенту или экскурсии"
                class="w-full md:w-96 border-gray-300 rounded-full shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />

            <select wire:model.live="statusFilter"
                    class="rounded-full border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Все статусы</option>
                <option value="pending">В ожидании</option>
                <option value="confirmed">Подтверждено</option>
                <option value="canceled">Отменено</option>
            </select>
        </div>

        @if (session()->has('success'))
        <span class="text-green-600 font-medium">{{ session('success') }}</span>
        @endif
    </div>

    {{-- Таблица --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">ID</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Клиент</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Экскурсия</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Гид</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Статус</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Действия</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($bookings as $b)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-3 text-sm text-gray-700">{{ $b->id }}</td>
                <td class="px-6 py-3 text-sm text-gray-900">{{ $b->user->name ?? '—' }}</td>
                <td class="px-6 py-3 text-sm">{{ $b->excursion->title ?? '—' }}</td>
                <td class="px-6 py-3 text-sm">{{ $b->excursion->guide->name ?? '—' }}</td>
                <td class="px-6 py-3 text-sm">
                    @switch($b->status)
                    @case('pending') <span class="text-yellow-600">В ожидании</span> @break
                    @case('confirmed') <span class="text-green-600">Подтверждено</span> @break
                    @case('canceled') <span class="text-red-600">Отменено</span> @break
                    @default <span class="text-gray-500">Неизвестно</span>
                    @endswitch
                </td>
                <td class="px-6 py-3 text-right text-sm font-medium space-x-2">
                    <button wire:click="openModal({{ $b->id }})"
                            class="px-2 py-1 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200">
                        Подробнее
                    </button>

                    @if($b->status === 'pending')
                    <button wire:click="updateStatus({{ $b->id }}, 'confirmed')"
                            class="px-2 py-1 bg-green-100 text-green-700 rounded-md hover:bg-green-200">
                        Подтвердить
                    </button>
                    <button wire:click="updateStatus({{ $b->id }}, 'canceled')"
                            class="px-2 py-1 bg-red-100 text-red-700 rounded-md hover:bg-red-200">
                        Отменить
                    </button>
                    @endif

                    <button wire:click="delete({{ $b->id }})"
                            class="px-2 py-1 bg-gray-100 text-gray-600 rounded-md hover:bg-gray-200">
                        Удалить
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-6 text-center text-gray-500">Нет бронирований</td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $bookings->links() }}</div>

    {{-- Модальное окно --}}
    @if($showModal && $selectedBooking)
    @php
    $extraServices = $selectedBooking->extraServices ?? collect();
    @endphp

    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full p-6 relative">
            <button wire:click="closeModal" class="absolute top-3 right-3 text-gray-500 hover:text-gray-800">✕</button>
            <h2 class="text-2xl font-bold mb-4">Бронирование #{{ $selectedBooking->id }}</h2>

            <div class="space-y-2 text-gray-700">
                <p><strong>Клиент:</strong> {{ $selectedBooking->user->name ?? '—' }}</p>
                <p><strong>Email:</strong> {{ $selectedBooking->user->email ?? '—' }}</p>
                <p><strong>Экскурсия:</strong> {{ $selectedBooking->excursion->title ?? '—' }}</p>
                <p><strong>Гид:</strong> {{ $selectedBooking->excursion->guide->name ?? '—' }}</p>
                <p>
                    <strong>Дата и время:</strong>
                    @if($selectedBooking->schedule)
                    {{ \Carbon\Carbon::parse($selectedBooking->schedule->date)->format('d.m.Y') }}
                    в {{ \Carbon\Carbon::parse($selectedBooking->schedule->start_time)->format('H:i') }}
                    —
                    {{ \Carbon\Carbon::parse($selectedBooking->schedule->end_time)->format('H:i') }}
                    @else
                    —
                    @endif
                </p>
                <p><strong>Кол-во человек:</strong> {{ $selectedBooking->people_count }}</p>

                <p>
                    <strong>Базовая цена экскурсии:</strong>
                    {{ $selectedBooking->excursion->price ?? '—' }} ₽ / чел
                </p>

                {{-- 🔹 Дополнительные услуги --}}
                @if($extraServices->count())
                <div class="mt-2">
                    <p class="font-semibold">Дополнительные услуги:</p>
                    <ul class="mt-1 space-y-1 text-sm">
                        @foreach($extraServices as $extra)
                        <li class="flex justify-between gap-4">
                            <div>
                                <span class="font-medium">{{ $extra->name }}</span>
                                @if($extra->pivot?->quantity)
                                <span class="text-gray-500">
                                                    × {{ $extra->pivot->quantity }}
                                                    @if($extra->per_person)
                                                        (за человека)
                                                    @endif
                                                </span>
                                @endif
                            </div>
                            <div class="text-gray-900">
                                {{ number_format($extra->pivot->total_price ?? 0, 0, '.', ' ') }} ₽
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @else
                <p class="text-sm text-gray-500"><strong>Доп. услуги:</strong> не выбраны</p>
                @endif

                <p class="mt-2">
                    <strong>Итого по бронированию:</strong>
                    {{ number_format($selectedBooking->total_price, 0, '.', ' ') }} ₽
                </p>

                <p><strong>Статус:</strong> {{ ucfirst($selectedBooking->status) }}</p>
                <p><strong>Создано:</strong> {{ $selectedBooking->created_at->format('d.m.Y H:i') }}</p>
            </div>

            <div class="mt-6 text-right">
                <button wire:click="closeModal"
                        class="px-4 py-2 bg-gray-100 rounded-lg hover:bg-gray-200">
                    Закрыть
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
