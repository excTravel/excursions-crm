<div class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8 bg-white shadow rounded-lg">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Расписание для "{{ $excursion->title }}"</h1>

    @if (session('success'))
    <div class="p-3 bg-green-100 border border-green-300 text-green-800 rounded mb-4">
        {{ session('success') }}
    </div>
    @endif

    {{-- Форма добавления --}}
    <form wire:submit.prevent="save" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div>
            <label class="block text-sm font-medium text-gray-700">Дата</label>
            <input type="date" wire:model="date"
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-blue-500">
            @error('date') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Время начала</label>
            <input type="time" wire:model="start_time"
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-blue-500">
            @error('start_time') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Макс. человек</label>
            <input type="number" wire:model="max_people"
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-blue-500">
            @error('max_people') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-3 text-right">
            <button type="submit"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                Добавить
            </button>
        </div>
    </form>

    {{-- Список сеансов --}}
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Текущие сеансы</h2>

    @if($schedules->count())
    <div class="space-y-3">
        @foreach($schedules as $schedule)
        <div class="flex items-center justify-between bg-gray-50 border rounded-lg px-4 py-3">
            <div>
                        <span class="text-gray-800 font-medium">
                            {{ \Carbon\Carbon::parse($schedule->date)->format('d.m.Y') }},
                            {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}
                        </span>
                @if($schedule->ends_at)
                <span class="text-gray-600">– {{ $schedule->ends_at->format('H:i') }}</span>
                @endif
                <span class="text-gray-500 ml-2">(до {{ $schedule->max_people }} чел.)</span>
            </div>
            <button wire:click="delete({{ $schedule->id }})"
                    class="text-red-600 hover:underline text-sm">Удалить</button>
        </div>
        @endforeach
    </div>
    @else
    <p class="text-gray-500">Пока нет сеансов.</p>
    @endif
</div>
