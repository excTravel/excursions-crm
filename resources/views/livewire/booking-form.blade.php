<div class="space-y-5">
    {{-- Дата экскурсии --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Дата экскурсии</label>
        <select wire:model.live="selectedSchedule"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 text-gray-800">
            <option value="">Выберите дату</option>
            @foreach($schedules as $schedule)
            @php $remaining = max(0, $schedule->max_people - $schedule->bookings->sum('people_count')); @endphp
            <option value="{{ $schedule->id }}" @disabled($remaining < 1)>
                {{ \Carbon\Carbon::parse($schedule->date)->format('d.m.Y') }}
                в {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}
                ({{ $remaining > 0 ? 'осталось мест: '.$remaining : 'мест нет' }})
            </option>
            @endforeach
        </select>
        @error('selectedSchedule') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Количество человек --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Количество человек</label>
        <input wire:model.live="peopleCount" type="number" min="1"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 text-gray-800">
    </div>

    {{-- Дополнительные услуги --}}
    @if($excursion->extraServices()->where('is_active', true)->count())
    <div class="mt-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Дополнительные услуги
        </label>

        <div class="space-y-2">
            @foreach($excursion->extraServices()->where('is_active', true)->get() as $service)
            <label class="flex items-center gap-2 text-sm text-gray-800">
                <input type="checkbox"
                       wire:model.live="selectedExtras"
                       value="{{ $service->id }}"
                       class="rounded border-gray-300">
                <span>
                        {{ $service->name }}
                        — {{ number_format($service->price, 0, '.', ' ') }} ₽
                        @if($service->per_person)
                            <span class="text-xs text-gray-500">(за человека)</span>
                        @else
                            <span class="text-xs text-gray-500">(за бронирование)</span>
                        @endif
                    </span>
            </label>
            @endforeach
        </div>
    </div>
    @endif


    {{-- Итоговая цена --}}
    <div class="text-lg font-semibold text-gray-900">
        Итого: <span class="text-blue-600">{{ number_format($totalPrice, 0, '.', ' ') }} ₽</span>
    </div>

    {{-- Сообщения --}}
    @if (session('error'))
    <div class="text-red-600 text-sm">{{ session('error') }}</div>
    @endif
    @if (session('success'))
    <div class="text-green-600 text-sm">{{ session('success') }}</div>
    @endif

    {{-- Кнопка --}}
    <button wire:click="book"
            class="voyage-book-button w-full px-5 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
        <span>Забронировать</span>
        <span class="voyage-book-arrow" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 17 17 7M7 7h10v10"/></svg></span>
    </button>
</div>
