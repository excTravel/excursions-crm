<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 bg-white">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Мои экскурсии</h1>

    <div class="mb-6">
        <a href="{{ route('guide.excursions.create') }}"
           class="inline-block px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            + Добавить экскурсию
        </a>
    </div>

    @if($excursions->count())
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($excursions as $exc)
        <div class="border border-gray-200 rounded-xl p-5 shadow-sm flex flex-col justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-1">{{ $exc->title }}</h2>
                <p class="text-sm text-gray-700 mb-3 line-clamp-2">{{ $exc->description }}</p>

                <p class="text-sm text-gray-600 mb-1">
                    <span class="font-medium">Категория:</span> {{ $exc->category->name ?? '—' }}
                </p>
                <p class="text-sm text-gray-600 mb-1">
                    <span class="font-medium">Город:</span> {{ $exc->location->city ?? '—' }}
                </p>
                <p class="text-sm">
                    <span class="font-medium">Статус:</span>
                    <span class="guide-status guide-status-{{ $exc->status }}">
                        {{ ['pending' => 'На проверке', 'approved' => 'Опубликована', 'rejected' => 'Отклонена'][$exc->status] ?? $exc->status }}
                    </span>
                </p>
            </div>

            <div class="mt-4 flex gap-2">
                <a href="{{ route('guide.excursions.schedules', $exc) }}"
                   class="flex-1 px-3 py-2 bg-gray-100 text-gray-800 rounded-lg hover:bg-gray-200 transition text-center text-sm">
                    Расписание
                </a>
                <a href="{{ route('excursions.show', $exc->slug) }}"
                   class="flex-1 px-3 py-2 bg-gray-100 text-gray-800 rounded-lg hover:bg-gray-200 transition text-center text-sm">
                    Просмотр
                </a>
                <a href="{{ route('guide.excursions.edit', $exc->id) }}"
                   class="flex-1 px-3 py-2 bg-blue-100 text-blue-800 rounded-lg hover:bg-blue-200 transition text-center text-sm">
                    Редактировать
                </a>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="text-center py-16 text-gray-600">
        <p class="text-lg">😕 У вас пока нет экскурсий.</p>
        <a href="{{ route('guide.excursions.create') }}"
           class="mt-4 inline-block px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            Создать первую экскурсию
        </a>
    </div>
    @endif
</div>
