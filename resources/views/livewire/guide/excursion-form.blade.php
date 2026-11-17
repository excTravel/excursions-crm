<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10 bg-white">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Добавить экскурсию</h1>

    @if (session('success'))
    <div class="p-4 mb-4 bg-green-100 border border-green-300 text-green-800 rounded-lg">
        {{ session('success') }}
    </div>
    @endif

    <form wire:submit.prevent="save" class="space-y-6">
        {{-- Название --}}
        <div>
            <label class="block text-gray-700 font-medium mb-1">Название</label>
            <input type="text" wire:model="title"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 text-gray-900">
            @error('title') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Описание --}}
        <div>
            <label class="block text-gray-700 font-medium mb-1">Описание</label>
            <textarea wire:model="description" rows="5"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 text-gray-900"></textarea>
            @error('description') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Цена и длительность --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 font-medium mb-1">Цена (₽)</label>
                <input type="number" wire:model="price"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 text-gray-900">
                @error('price') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-1">Длительность (мин)</label>
                <input type="number" wire:model="duration"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 text-gray-900">
                @error('duration') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Категория и локация --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 font-medium mb-1">Категория</label>
                <select wire:model="category_id"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 text-gray-900">
                    <option value="">Выберите категорию</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-1">Локация</label>
                <select wire:model="location_id"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 text-gray-900">
                    <option value="">Выберите локацию</option>
                    @foreach($locations as $loc)
                    <option value="{{ $loc->id }}">{{ $loc->city }}</option>
                    @endforeach
                </select>
                @error('location_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- 🔹 Дополнительные услуги --}}
        <div class="border rounded-xl p-4 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-800">
                    Дополнительные услуги
                </h3>
                <button
                    type="button"
                    wire:click="addExtraServiceRow"
                    class="text-xs px-2 py-1 rounded border border-gray-300 hover:bg-gray-50"
                >
                    + Добавить услугу
                </button>
            </div>

            @if(empty($extraServices))
            <p class="text-xs text-gray-500">
                Пока нет дополнительных услуг. Нажмите «Добавить услугу», чтобы создать первую (например, фотосессия +500 ₽).
            </p>
            @endif

            <div class="space-y-4">
                @foreach($extraServices as $index => $service)
                <div class="border rounded-lg p-3 space-y-2 bg-gray-50/60">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 space-y-2">
                            <div class="grid grid-cols-12 gap-2">
                                <div class="col-span-5">
                                    <label class="block text-xs text-gray-600 mb-1">Название</label>
                                    <input
                                        type="text"
                                        class="w-full rounded border-gray-300 text-sm"
                                        placeholder="Например, Фотосессия"
                                        wire:model.defer="extraServices.{{ $index }}.name"
                                    >
                                    @error("extraServices.$index.name")
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="col-span-3">
                                    <label class="block text-xs text-gray-600 mb-1">Цена, ₽</label>
                                    <input
                                        type="number"
                                        step="1"
                                        min="0"
                                        class="w-full rounded border-gray-300 text-sm"
                                        wire:model.defer="extraServices.{{ $index }}.price"
                                    >
                                    @error("extraServices.$index.price")
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="col-span-2 flex items-center gap-2 mt-5">
                                    <input
                                        type="checkbox"
                                        class="rounded border-gray-300"
                                        wire:model.defer="extraServices.{{ $index }}.per_person"
                                    >
                                    <span class="text-xs text-gray-700">За человека</span>
                                </div>

                                <div class="col-span-2 flex items-center gap-2 mt-5">
                                    <input
                                        type="checkbox"
                                        class="rounded border-gray-300"
                                        wire:model.defer="extraServices.{{ $index }}.is_active"
                                    >
                                    <span class="text-xs text-gray-700">Активна</span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Описание (опционально)</label>
                                <textarea
                                    class="w-full rounded border-gray-300 text-sm"
                                    rows="2"
                                    wire:model.defer="extraServices.{{ $index }}.description"
                                    placeholder="Кратко опишите услугу для клиента"
                                ></textarea>
                            </div>
                        </div>

                        <button
                            type="button"
                            wire:click="removeExtraServiceRow({{ $index }})"
                            class="text-xs text-red-600 hover:text-red-700 mt-1"
                        >
                            Удалить
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Фото --}}
        <div>
            <label class="block text-gray-700 font-medium mb-1">Фото экскурсии</label>
            <input type="file" wire:model="photos" multiple
                   class="w-full text-gray-700 border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
            <div wire:loading wire:target="photos" class="text-sm text-gray-500 mt-2">Загрузка...</div>

            @if ($photos)
            <div class="mt-4 grid grid-cols-3 gap-2">
                @foreach ($photos as $photo)
                <img src="{{ $photo->temporaryUrl() }}" class="rounded-lg border h-24 w-full object-cover">
                @endforeach
            </div>
            @endif
        </div>

        {{-- Карта маршрута --}}
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Маршрут экскурсии</h3>
            <div id="route-map" wire:ignore class="w-full h-96 border border-gray-300 rounded-lg mb-4"></div>

            {{-- ВАЖНО: просто вызываем глобальную функцию, как в "редактировании" --}}
            @push('scripts')
            <script>
                document.addEventListener('livewire:load', () => {
                    const points = @json($routePoints ?? []);
                    if (typeof initExcursionMap === 'function') {
                        initExcursionMap(points);
                    }
                });

                document.addEventListener('livewire:navigated', () => {
                    const points = @json($routePoints ?? []);
                    if (typeof initExcursionMap === 'function') {
                        initExcursionMap(points);
                    }
                });
            </script>
            @endpush

            {{-- Список точек --}}
            @if(!empty($routePoints))
            <ul class="space-y-2">
                @foreach($routePoints as $index => $point)
                <li class="flex items-center justify-between bg-gray-50 p-3 rounded-lg border border-gray-200">
                            <span class="text-gray-700">
                                Точка {{ $index + 1 }} — {{ $point['lat'] }}, {{ $point['lng'] }}
                            </span>
                    <button type="button" wire:click="removeRoutePoint({{ $index }})"
                            class="text-red-600 hover:underline text-sm">Удалить</button>
                </li>
                @endforeach
            </ul>
            @else
            <p class="text-gray-500 text-sm">Кликните по карте, чтобы добавить точки маршрута.</p>
            @endif
        </div>

        {{-- Кнопки --}}
        <div class="pt-4">
            <button type="submit"
                    class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
                Сохранить
            </button>
            <a href="{{ route('guide.excursions') }}"
               class="ml-3 text-gray-600 hover:text-gray-900">Отмена</a>
        </div>
    </form>
</div>
