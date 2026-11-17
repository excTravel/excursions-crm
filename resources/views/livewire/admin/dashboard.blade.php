<div class="voyage-admin-dashboard max-w-7xl mx-auto py-10 px-6 space-y-10">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Панель администратора — отчёты</h1>

    {{-- === ОБЩИЕ МЕТРИКИ === --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <x-report-card label="Всего экскурсий" value="{{ $totalExcursions }}" color="blue"/>
        <x-report-card label="Активные экскурсии" value="{{ $approvedExcursions }}" color="green"/>
        <x-report-card label="Пользователей" value="{{ $totalUsers }}" color="indigo"/>
        <x-report-card label="Бронирований" value="{{ $totalBookings }}" color="amber"/>
    </div>

    {{-- === ПОЛЬЗОВАТЕЛИ === --}}
    <h2 class="text-xl font-semibold mt-10">Пользователи</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <x-report-card label="Всего" value="{{ $totalUsers }}" color="blue"/>
        <x-report-card label="Гиды" value="{{ $guides }}" color="purple"/>
        <x-report-card label="Клиенты" value="{{ $clients }}" color="teal"/>
        <x-report-card label="Заблокированные" value="{{ $blockedUsers }}" color="red"/>
    </div>

    {{-- === ЭКСКУРСИИ === --}}
    <h2 class="text-xl font-semibold mt-10">Экскурсии</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <x-report-card label="Всего" value="{{ $totalExcursions }}" color="blue"/>
        <x-report-card label="Одобренные" value="{{ $approvedExcursions }}" color="green"/>
        <x-report-card label="На модерации" value="{{ $pendingExcursions }}" color="yellow"/>
        <x-report-card label="Архив" value="{{ $archivedExcursions }}" color="gray"/>
    </div>

    {{-- === БРОНИРОВАНИЯ === --}}
    <h2 class="text-xl font-semibold mt-10">Бронирования</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <x-report-card label="Всего" value="{{ $totalBookings }}" color="blue"/>
        <x-report-card label="Завершённые" value="{{ $completedBookings }}" color="green"/>
        <x-report-card label="Отменённые" value="{{ $cancelledBookings }}" color="red"/>
        <x-report-card label="Доход" value="{{ number_format($totalRevenue, 0, '.', ' ') }} ₽" color="emerald"/>
    </div>

    {{-- === ОТЗЫВЫ === --}}
    <h2 class="text-xl font-semibold mt-10">Отзывы</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <x-report-card label="Всего отзывов" value="{{ $reviewsCount }}" color="blue"/>
        <x-report-card label="Средний рейтинг" value="★ {{ $avgRating }}" color="amber"/>
    </div>

    {{-- === ГРАФИК РОСТА ПОЛЬЗОВАТЕЛЕЙ === --}}
    <h2 class="text-xl font-semibold mt-10">Рост пользователей по месяцам</h2>
    <div class="bg-white p-6 rounded-xl shadow border">
        <canvas id="userGrowthChart" height="100"></canvas>
    </div>

    {{-- === ГРАФИК БРОНИРОВАНИЙ === --}}
    <h2 class="text-xl font-semibold mt-10">Бронирования по неделям</h2>
    <div class="bg-white p-6 rounded-xl shadow border">
        <canvas id="bookingTrendsChart" height="100"></canvas>
    </div>

    {{-- === ТОП-5 ЭКСКУРСИЙ === --}}
    <h2 class="text-xl font-semibold mt-10">Топ-5 популярных экскурсий</h2>
    <div class="bg-white p-6 rounded-xl shadow border">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
            <tr>
                <th class="text-left text-xs font-semibold text-gray-500 uppercase px-3 py-2">Экскурсия</th>
                <th class="text-right text-xs font-semibold text-gray-500 uppercase px-3 py-2">Бронирований</th>
            </tr>
            </thead>
            <tbody>
            @forelse($topExcursions as $exc)
            <tr class="border-t">
                <td class="px-3 py-2 text-gray-800">{{ $exc->title }}</td>
                <td class="px-3 py-2 text-right font-semibold text-blue-600">{{ $exc->bookings_count }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="2" class="px-3 py-4 text-center text-gray-500">Нет данных</td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- CHARTS --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        new Chart(document.getElementById('userGrowthChart'), {
            type: 'line',
            data: {
                labels: @json($userGrowth->pluck('month')),
                datasets: [{
                    label: 'Новые пользователи',
                    data: @json($userGrowth->pluck('total')),
                    borderColor: '#03483f',
                    backgroundColor: 'rgba(3,72,63,0.12)',
                    fill: true,
                    tension: 0.4
                }]
            }
        });

        new Chart(document.getElementById('bookingTrendsChart'), {
            type: 'bar',
            data: {
                labels: @json($bookingTrends->pluck('week')),
                datasets: [{
                    label: 'Бронирования по неделям',
                    data: @json($bookingTrends->pluck('total')),
                    backgroundColor: '#03483f',
                }]
            }
        });
    </script>
</div>
