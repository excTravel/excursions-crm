<div class="voyage-admin-excursions max-w-7xl mx-auto py-10 px-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Модерация экскурсий</h1>

    {{-- Фильтры --}}
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
        <div class="voyage-admin-filter-row flex-wrap">
            <input
                wire:model.live="search"
                type="text"
                placeholder="Поиск по названию или гиду"
                class="w-full md:w-72 border-gray-300 rounded-full shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />

            <select wire:model.live="statusFilter"
                    class="rounded-full border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="active">Активные</option>
                <option value="archived">Архив</option>
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
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Название</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Гид</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Статус</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Действия</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($excursions as $exc)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ $exc->title }}</td>
                <td class="px-6 py-3 text-sm text-gray-700">{{ $exc->guide->name ?? '—' }}</td>
                <td class="px-6 py-3 text-sm">
                    @switch($exc->status)
                    @case('pending') <span class="text-yellow-600">На модерации</span> @break
                    @case('approved') <span class="text-green-600">Одобрена</span> @break
                    @case('rejected') <span class="text-red-600">Отклонена</span> @break
                    @case('archived') <span class="text-gray-600">В архиве</span> @break
                    @default <span class="text-gray-500">{{ ucfirst($exc->status ?? '—') }}</span>
                    @endswitch
                </td>
                <td class="px-6 py-3 text-right text-sm font-medium space-x-2">
                    <button wire:click="openModal({{ $exc->id }})"
                            class="px-2 py-1 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200">
                        Подробнее
                    </button>

                    @if($exc->status === 'pending')
                    <button wire:click="approve({{ $exc->id }})"
                            class="px-2 py-1 bg-green-100 text-green-700 rounded-md hover:bg-green-200">
                        Одобрить
                    </button>
                    <button wire:click="openRejectModal({{ $exc->id }})"
                            class="px-2 py-1 bg-red-100 text-red-700 rounded-md hover:bg-red-200">
                        Отклонить
                    </button>
                    @elseif($exc->status === 'approved')
                    <button wire:click="openArchiveConfirm({{ $exc->id }})"
                            class="px-2 py-1 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                        В архив
                    </button>
                    @elseif($exc->status === 'archived')
                    <button wire:click="restore({{ $exc->id }})"
                            class="px-2 py-1 bg-green-100 text-green-700 rounded-md hover:bg-green-200">
                        Восстановить
                    </button>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-6 py-6 text-center text-gray-500">Нет экскурсий</td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $excursions->links() }}</div>

    {{-- Просмотр экскурсии --}}
    @if($showModal && $selectedExcursion)
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-xl max-w-4xl w-full p-6 relative overflow-y-auto max-h-[90vh]">
            <button wire:click="closeModal"
                    class="absolute top-3 right-3 text-gray-500 hover:text-gray-800">✕</button>

            <h2 class="text-2xl font-bold mb-4">{{ $selectedExcursion->title }}</h2>

            {{-- Фото --}}
            @if($selectedExcursion->photos && $selectedExcursion->photos->count())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 mb-6">
                @foreach($selectedExcursion->photos as $photo)
                <img src="{{ asset('storage/' . $photo->path) }}"
                     alt="Фото экскурсии"
                     class="w-full h-48 object-cover rounded-lg border border-gray-200 shadow-sm hover:scale-[1.02] transition" />
                @endforeach
            </div>
            @else
            <div class="w-full mb-6 h-48 bg-gray-100 border border-gray-200 rounded-lg flex items-center justify-center text-gray-500">
                Фото отсутствуют
            </div>
            @endif



            {{-- Карта маршрута --}}
            @php
            // Берём координаты из первой записи маршрута, если есть
            $routePoints = $selectedExcursion->routes->first()->coordinates ?? [];
            @endphp

            @if(!empty($routePoints))
            <div id="admin-excursion-map" class="w-full h-72 rounded-lg border border-gray-200 mb-6"></div>

            <script>
                (function () {
                    const container = document.getElementById('admin-excursion-map');
                    if (!container || container.classList.contains('initialized')) return;

                    container.classList.add('initialized');

                    const map = L.map('admin-excursion-map').setView([0, 0], 12);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap'
                    }).addTo(map);

                    const routePoints = @json($routePoints);

                    if (routePoints.length > 0) {
                        const latlngs = routePoints.map(p => [p.lat, p.lng]);

                        const polyline = L.polyline(latlngs, {
                            color: 'blue',
                            weight: 4,
                            opacity: 0.8,
                        }).addTo(map);

                        latlngs.forEach(([lat, lng], index) => {
                            L.marker([lat, lng]).addTo(map)
                                .bindPopup(`Точка маршрута #${index + 1}`);
                        });

                        map.fitBounds(polyline.getBounds());
                    }
                })();
            </script>


            @elseif($selectedExcursion->meeting_point_lat && $selectedExcursion->meeting_point_lng)
            {{-- fallback — одна точка встречи --}}
            <div id="admin-excursion-map" class="w-full h-64 rounded-lg border border-gray-200 mb-6"></div>

            <script>
                (function () {
                    const container = document.getElementById('admin-excursion-map');
                    if (!container || container.classList.contains('initialized')) return;

                    container.classList.add('initialized');

                    const map = L.map('admin-excursion-map')
                        .setView([{{ $selectedExcursion->meeting_point_lat }}, {{ $selectedExcursion->meeting_point_lng }}], 13);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap'
                    }).addTo(map);

                    L.marker([{{ $selectedExcursion->meeting_point_lat }}, {{ $selectedExcursion->meeting_point_lng }}]).addTo(map)
                        .bindPopup('Место встречи');
                })();
            </script>

            @else
            <div class="w-full h-64 mb-6 bg-gray-100 border border-gray-200 rounded-lg flex items-center justify-center text-gray-500">
                Маршрут не указан
            </div>
            @endif



            {{-- Информация --}}
            <div class="space-y-3">
                <p class="text-gray-700">
                    <strong>Описание:</strong> {{ $selectedExcursion->description }}
                </p>
                <p class="text-gray-700">
                    <strong>Категория:</strong> {{ $selectedExcursion->category->name ?? '—' }}
                </p>
                <p class="text-gray-700">
                    <strong>Гид:</strong> {{ $selectedExcursion->guide->name ?? '—' }}
                </p>
                <p class="text-gray-700">
                    <strong>Цена:</strong> {{ $selectedExcursion->price }} ₽ / чел
                </p>
                <p class="text-gray-700">
                    <strong>Длительность:</strong> {{ $selectedExcursion->duration }} мин
                </p>
                <p class="text-gray-700">
                    <strong>Дата создания:</strong> {{ $selectedExcursion->created_at->format('d.m.Y H:i') }}
                </p>

                {{-- 🔹 Активные дополнительные услуги --}}
                @php
                $activeExtra = $selectedExcursion->extraServices
                ? $selectedExcursion->extraServices->where('is_active', true)
                : collect();
                @endphp

                @if($activeExtra->count())
                <div class="mt-2">
                    <p class="text-gray-700 font-semibold">Активные доп. услуги:</p>
                    <ul class="mt-1 space-y-1 text-sm text-gray-700">
                        @foreach($activeExtra as $service)
                        <li class="flex justify-between gap-4">
                            <div>
                                <span class="font-medium">{{ $service->name }}</span>
                                @if($service->description)
                                <span class="text-gray-500">— {{ $service->description }}</span>
                                @endif
                            </div>
                            <div class="text-gray-900">
                                {{ number_format($service->price, 0, '.', ' ') }} ₽
                                @if($service->per_person)
                                <span class="text-gray-500 text-xs">(за человека)</span>
                                @endif
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @else
                <p class="text-sm text-gray-500">
                    <strong>Доп. услуги:</strong> не настроены
                </p>
                @endif
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

    {{-- Модалка отклонения --}}
    @if($showRejectModal && $selectedExcursion)
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-xl font-semibold mb-4">Причина отклонения</h3>
            <textarea wire:model="rejectionReason" rows="3"
                      class="w-full border-gray-300 rounded-lg focus:border-red-500 focus:ring-red-500"></textarea>
            <div class="mt-4 flex justify-end gap-3">
                <button wire:click="$set('showRejectModal', false)" class="px-4 py-2 bg-gray-100 rounded-lg hover:bg-gray-200">Отмена</button>
                <button wire:click="reject" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Отклонить</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Подтверждение архивации --}}
    @if($showArchiveConfirm && $selectedExcursion)
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold mb-3">
                Архивировать экскурсию "{{ $selectedExcursion->title }}"?
            </h3>
            <p class="text-gray-600 mb-6">
                После архивации она будет недоступна пользователям, но сохранится в базе.
            </p>
            <div class="flex justify-end gap-3">
                <button wire:click="$set('showArchiveConfirm', false)"
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                    Отмена
                </button>
                <button wire:click="archive"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Да, архивировать
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
