<div class="bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Заголовок --}}
        <h1 class="text-4xl font-bold text-gray-900 mb-8">{{ $excursion->title }}</h1>

        {{-- Фото и бронирование --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10 mb-12">

            {{-- Галерея с автоперелистыванием --}}
            <div class="lg:col-span-2"
                 x-data="{ active: 0, count: {{ $excursion->photos->count() }}, interval: null }"
                 x-init="if(count > 1){ interval = setInterval(() => { active = (active + 1) % count }, 4000) }"
                 @mouseenter="clearInterval(interval)"
                 @mouseleave="if(count > 1){ interval = setInterval(() => { active = (active + 1) % count }, 4000) }">
                @if($excursion->photos->count())
                <div class="relative aspect-[16/9] bg-gray-100 rounded-xl overflow-hidden border border-gray-200">

                    {{-- Основные фото --}}
                    @foreach($excursion->photos as $index => $photo)
                    <img
                        x-show="active === {{ $index }}"
                        src="{{ asset('storage/' . $photo->path) }}"
                        alt="{{ $excursion->title }}"
                        class="absolute inset-0 w-full h-full object-cover transition-opacity duration-700"
                        x-transition.opacity
                    >
                    @endforeach

                    {{-- Кнопки переключения --}}
                    <button
                        @click="active = (active - 1 + count) % count"
                        class="absolute left-3 top-1/2 -translate-y-1/2 bg-white/70 hover:bg-white rounded-full p-2 shadow text-xl">
                        ‹
                    </button>
                    <button
                        @click="active = (active + 1) % count"
                        class="absolute right-3 top-1/2 -translate-y-1/2 bg-white/70 hover:bg-white rounded-full p-2 shadow text-xl">
                        ›
                    </button>

                    {{-- Индикаторы --}}
                    <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex space-x-2">
                        @foreach($excursion->photos as $index => $photo)
                        <button
                            @click="active = {{ $index }}"
                            :class="active === {{ $index }} ? 'bg-white' : 'bg-gray-400'"
                            class="w-3 h-3 rounded-full transition"></button>
                        @endforeach
                    </div>
                </div>

                {{-- Миниатюры --}}
                <div class="grid grid-cols-4 gap-2 mt-3">
                    @foreach($excursion->photos as $index => $photo)
                    <img
                        src="{{ asset('storage/' . $photo->path) }}"
                        @click="active = {{ $index }}"
                        class="cursor-pointer rounded-lg border object-cover w-full h-20 hover:opacity-80 transition"
                        :class="active === {{ $index }} ? 'ring-2 ring-blue-500' : ''"
                    >
                    @endforeach
                </div>
                @else
                <div class="aspect-[16/9] flex items-center justify-center bg-gray-100 text-gray-400 rounded-xl border border-gray-200">
                    Фото экскурсии отсутствует
                </div>
                @endif
            </div>

            {{-- Бронирование --}}
            <div class="lg:sticky lg:top-8 self-start bg-white border border-gray-200 shadow-lg rounded-2xl p-6">
                <h2 class="text-2xl font-semibold text-gray-900 mb-6">Забронировать</h2>

                <dl class="space-y-3 text-sm text-gray-700 mb-6">
                    @php $priceRule = $excursion->pricingRules->sortBy('min_people')->first(); @endphp
                    <div><dt class="font-medium">Цена:</dt> <dd>{{ number_format((float) ($priceRule?->price ?? $excursion->price), 0, ',', ' ') }} ₽ {{ ($priceRule?->mode ?? 'per_person') === 'per_group' ? 'за группу' : 'за человека' }}</dd></div>
                    <div><dt class="font-medium">Длительность:</dt> <dd>{{ $excursion->duration }} мин</dd></div>
                    <div><dt class="font-medium">Категория:</dt> <dd>{{ $excursion->category->name ?? '—' }}</dd></div>
                    <div><dt class="font-medium">Локация:</dt> <dd>{{ $excursion->location->city ?? '—' }}</dd></div>
                </dl>

                @auth
                @livewire('booking-form', ['excursion' => $excursion])
                @else
                <div class="border border-gray-200 rounded-lg p-5 text-center bg-gray-50">
                    <p class="text-gray-700 mb-4">Чтобы забронировать экскурсию, войдите в систему.</p>
                    <a href="{{ route('login') }}"
                       class="inline-block px-5 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                        Войти
                    </a>
                    <p class="mt-2 text-sm text-gray-600">
                        Нет аккаунта?
                        <a href="{{ route('register') }}" class="text-blue-600 hover:underline">Зарегистрироваться</a>
                    </p>
                </div>
                @endauth
            </div>
        </div>

        {{-- Описание --}}
        <section class="mb-16">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Описание</h2>
            <p class="text-lg text-gray-700 leading-relaxed">{{ $excursion->description }}</p>
        </section>

        {{-- Гид --}}
        @if($excursion->guide)
        <section class="mb-16">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Ваш гид</h2>
            <div class="flex items-center gap-4 p-5 bg-white border border-gray-200 rounded-xl shadow-sm max-w-md">
                <div class="w-14 h-14 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-lg font-bold">
                    {{ mb_substr($excursion->guide->name, 0, 1) }}
                </div>
                <div>
                    <p class="font-semibold text-gray-900 text-lg">{{ $excursion->guide->name }}</p>
                    <p class="text-sm text-gray-600">Гид экскурсии</p>
                </div>
            </div>
        </section>
        @endif

        {{-- Маршрут --}}
        <section class="mb-16">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Маршрут</h2>

            @if (!empty($routePoints))
            <div id="excursion-map" class="w-full h-96 rounded-xl border border-gray-200"></div>
            @else
            <div class="border border-dashed border-gray-300 rounded-lg p-6 text-center text-gray-600">
                Маршрут пока не добавлен.
            </div>
            @endif
        </section>

        {{-- Отзывы --}}
        <section class="mb-16">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Отзывы</h2>

            @if ($excursion->reviews->count())
            <div class="space-y-6">
                @foreach ($excursion->reviews as $review)
                <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
                    <div class="flex justify-between items-center mb-2">
                        <p class="font-medium text-gray-900">{{ $review->user->name ?? 'Аноним' }}</p>
                        <span class="text-sm text-gray-500">{{ $review->created_at->format('d.m.Y') }}</span>
                    </div>
                    <p class="text-gray-700">{{ $review->comment }}</p>
                    @if($review->rating)
                    <div class="text-amber-500 text-sm mt-2">★ {{ $review->rating }}/5</div>
                    @endif
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-700">Пока нет отзывов.</p>
            @endif

            {{-- 👉 Форма для оставления отзыва (только для авторизованных) --}}
            @auth
            <div class="mt-8">
                @livewire('review-form', ['excursion' => $excursion])
            </div>
            @else
            <p class="mt-4 text-gray-600">
                Чтобы оставить отзыв, <a href="{{ route('login') }}" class="text-blue-600 hover:underline">войдите в систему</a>.
            </p>
            @endauth
        </section>


        {{-- Назад --}}
        <div class="text-center">
            <a href="{{ route('excursions.list') }}"
               class="inline-block px-6 py-2 border border-gray-300 text-gray-800 rounded-lg hover:bg-gray-100 transition">
                ← Назад к каталогу
            </a>
        </div>
    </div>
</div>

@push('scripts')

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const pointsRaw = @json($routePoints ?? []);
        const points = pointsRaw.map(p => ({
            lat: p[0],
            lng: p[1],
            name: p[2] || null,
        }));

        const fallbackCenter = [
            parseFloat(@json($excursion->meeting_point_lat ?? 55.751574)),
            parseFloat(@json($excursion->meeting_point_lng ?? 37.573856))
        ];

        if (!document.getElementById('excursion-map') || typeof ymaps === 'undefined') return;

        ymaps.ready(() => {
            const first = points.length ? points[0] : null;
            const center = first ? [first.lat, first.lng] : fallbackCenter;
            const map = new ymaps.Map('excursion-map', {
                center: center,
                zoom: 13,
                controls: ['zoomControl', 'fullscreenControl']
            });

            const geoObjects = [];
            points.forEach((p, i) => {
                const placemark = new ymaps.Placemark([p.lat, p.lng], {
                    balloonContent: (p.name ? p.name + '<br>' : '') + 'Точка ' + (i + 1),
                    hintContent: p.name ?? ('Точка ' + (i + 1)),
                }, { preset: 'islands#blueCircleDotIcon' });
                geoObjects.push(placemark);
                map.geoObjects.add(placemark);
            });

            if (points.length >= 2) {
                const lineCoords = points.map(p => [p.lat, p.lng]);
                const line = new ymaps.Polyline(lineCoords, {}, {
                    strokeColor: '#1E98FF',
                    strokeWidth: 3,
                    strokeOpacity: 0.9
                });
                geoObjects.push(line);
                map.geoObjects.add(line);
            }

            if (geoObjects.length) {
                const bounds = ymaps.geoQuery(geoObjects).getBounds();
                if (bounds) map.setBounds(bounds, { checkZoomRange: true, zoomMargin: 40 });
            }
        });
    });
</script>
@endpush
