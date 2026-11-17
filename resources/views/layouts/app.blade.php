<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ExTravel — экскурсии по России') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>


    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="font-sans antialiased">
<div class="min-h-screen bg-gray-50">
    @include('layouts.navigation')

    <!-- Page Heading -->
    @isset($header)
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            {{ $header }}
        </div>
    </header>
    @endisset

    <!-- Page Content -->
    <main>
        @yield('content')
        {{ $slot ?? '' }}
    </main>

    <div x-data="{ visible: false }" @scroll.window.passive="visible = window.scrollY > 320" x-cloak>
        <button type="button" x-show="visible" x-transition.opacity.duration.200ms
                @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
                class="voyage-top-button" aria-label="Наверх" title="Наверх">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 19V5m-7 7 7-7 7 7"/></svg>
        </button>
    </div>
</div>

@livewireScripts

{{-- ✅ Подключаем Yandex Maps только один раз --}}
@if (!defined('YANDEX_MAPS_LOADED'))
@php define('YANDEX_MAPS_LOADED', true); @endphp
<script src="https://api-maps.yandex.ru/2.1/?apikey=0ca08475-495d-4a77-aef4-dfabe16fb2ac&lang=ru_RU"></script>
@endif

{{-- ✅ Глобальная инициализация карты --}}
<script>
    // Функция для ожидания загрузки Yandex Maps API
    function waitForYmaps(callback, interval = 100, maxTries = 50) {
        let tries = 0;
        const timer = setInterval(() => {
            if (typeof ymaps !== 'undefined' && ymaps.ready) {
                clearInterval(timer);
                ymaps.ready(callback);
            } else if (tries++ > maxTries) {
                clearInterval(timer);
                console.warn('⚠️ Не удалось дождаться Yandex Maps');
            }
        }, interval);
    }

    // Главная функция инициализации карты
    window.initExcursionMap = function(points = []) {
        waitForYmaps(() => {
            const mapEl = document.getElementById('route-map');
            if (!mapEl) return;

            console.log('✅ Инициализация карты с точками:', points);

            const map = new ymaps.Map(mapEl, {
                center: points.length ? [points[0].lat, points[0].lng] : [55.751574, 37.573856],
                zoom: 12,
                controls: ['zoomControl', 'fullscreenControl']
            });

            const markers = [];

            // 🔹 Функция перерисовки всех точек
            function redrawRoute(routePoints) {
                map.geoObjects.removeAll();
                markers.length = 0;

                routePoints.forEach((p, i) => {
                    const mark = new ymaps.Placemark(
                        [p.lat, p.lng],
                        { balloonContent: p.name ?? 'Точка ' + (i + 1) },
                        { preset: 'islands#blueDotIcon' }
                    );
                    map.geoObjects.add(mark);
                    markers.push(mark);
                });

                if (routePoints.length >= 2) {
                    const line = new ymaps.Polyline(
                        routePoints.map(p => [p.lat, p.lng]),
                        {},
                        { strokeColor: '#1E98FF', strokeWidth: 3, strokeOpacity: 0.9 }
                    );
                    map.geoObjects.add(line);
                }
            }

            // 🔹 Временная зелёная точка при добавлении
            function addTempPoint(payload) {
                const mark = new ymaps.Placemark(
                    [payload.lat, payload.lng],
                    { balloonContent: 'Новая точка маршрута' },
                    { preset: 'islands#greenDotIcon' }
                );
                map.geoObjects.add(mark);
            }

            // Сохраняем функции для доступа из Livewire
            window.windowEditMap = { redrawRoute, addTempPoint };

            // Первичная отрисовка
            redrawRoute(points);

            // 🔹 Добавление точки по клику
            map.events.add('click', e => {
                const coords = e.get('coords');
                const payload = { lat: coords[0], lng: coords[1] };

                addTempPoint(payload); // показать маркер сразу

                const compEl = document.querySelector('[wire\\:id]');
                if (!compEl || !window.Livewire || !Livewire.find) return;

                const component = Livewire.find(compEl.getAttribute('wire:id'));
                if (!component) return;

                component.call('addRoutePoint', payload);
            });

            // 🔹 Обновление маршрута из Livewire (перерисовка)
            window.addEventListener('routeUpdated', e => {
                // Livewire v3 кладёт данные внутрь detail[0]
                const payload = Array.isArray(e.detail) ? e.detail[0] : e.detail;
                const newPoints = Array.isArray(payload) ? payload : [];
                redrawRoute(newPoints);
            });
        });
    };
</script>

@stack('scripts')
</body>
</html>
