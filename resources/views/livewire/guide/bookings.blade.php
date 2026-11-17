<div class="max-w-5xl mx-auto bg-white p-6 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-6">Заявки на бронирование</h1>

    {{-- 🔹 Фильтр статусов --}}
    <div class="flex gap-3 mb-6">
        <button wire:click="$set('filter','all')" class="px-3 py-1 rounded {{ $filter==='all'?'bg-blue-600 text-white':'bg-gray-200 hover:bg-gray-300' }}">Все</button>
        <button wire:click="$set('filter','pending')" class="px-3 py-1 rounded {{ $filter==='pending'?'bg-blue-600 text-white':'bg-gray-200 hover:bg-gray-300' }}">Ожидают</button>
        <button wire:click="$set('filter','confirmed')" class="px-3 py-1 rounded {{ $filter==='confirmed'?'bg-blue-600 text-white':'bg-gray-200 hover:bg-gray-300' }}">Подтверждённые</button>
        <button wire:click="$set('filter','rejected')" class="px-3 py-1 rounded {{ $filter==='rejected'?'bg-blue-600 text-white':'bg-gray-200 hover:bg-gray-300' }}">Отклонённые</button>
        <button wire:click="$set('filter','canceled')" class="px-3 py-1 rounded {{ $filter==='canceled'?'bg-blue-600 text-white':'bg-gray-200 hover:bg-gray-300' }}">Отменённые</button>
    </div>

    {{-- 🔹 Список заявок --}}
    @forelse($bookings as $booking)
    @php
    $extraServices = $booking->extraServices ?? collect();
    @endphp

    <div class="border-b py-3 flex justify-between items-center">
        <div>
            <p class="font-semibold text-gray-900">
                {{ $booking->schedule->excursion->title }}
            </p>
            <p class="text-sm text-gray-600">
                {{ $booking->user->name }} — {{ $booking->people_count }} чел.
                <br>
                {{ $booking->schedule->date }} в {{ $booking->schedule->start_time }}
            </p>

            {{-- 🔹 Дополнительные услуги --}}
            @if($extraServices->count())
            <div class="mt-1 text-xs text-gray-700">
                <p class="font-semibold">Доп. услуги:</p>
                <ul class="mt-0.5 space-y-0.5">
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
            @endif

            <span class="text-xs uppercase text-gray-500 block mt-1">
                Статус: {{ $booking->status }}
            </span>
        </div>

        @if($booking->status === 'pending')
        <div class="space-x-2">
            <button wire:click="confirm({{ $booking->id }})"
                    class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600">
                ✅ Подтвердить
            </button>
            <button wire:click="reject({{ $booking->id }})"
                    class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600">
                ❌ Отклонить
            </button>
        </div>
        @endif
    </div>
    @empty
    <p class="text-gray-500">Нет заявок по выбранному фильтру.</p>
    @endforelse

</div>
