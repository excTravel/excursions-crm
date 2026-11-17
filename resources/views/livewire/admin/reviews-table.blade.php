<div class="voyage-admin-reviews max-w-7xl mx-auto py-10 px-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Отзывы пользователей</h1>

    {{-- Поиск --}}
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
        <input
            wire:model.live="search"
            type="text"
            placeholder="Поиск по пользователю или экскурсии"
            class="border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 w-full md:w-1/3"
        />

        @if (session()->has('success'))
        <span class="text-green-600 font-medium">{{ session('success') }}</span>
        @endif
    </div>

    {{-- Таблица --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Пользователь</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Экскурсия</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Рейтинг</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Действия</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($reviews as $r)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-3 text-sm text-gray-900">{{ $r->user->name ?? '—' }}</td>
                <td class="px-6 py-3 text-sm">{{ $r->excursion->title ?? '—' }}</td>
                <td class="px-6 py-3 text-sm text-amber-500 font-semibold">
                    ★ {{ $r->rating ?? '—' }}
                </td>
                <td class="px-6 py-3 text-right text-sm font-medium space-x-2">
                    <button wire:click="openModal({{ $r->id }})"
                            class="px-2 py-1 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200">
                        Подробнее
                    </button>
                    <button wire:click="delete({{ $r->id }})"
                            class="px-2 py-1 bg-red-100 text-red-700 rounded-md hover:bg-red-200">
                        Удалить
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-6 py-6 text-center text-gray-500">Нет отзывов</td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $reviews->links() }}</div>

    {{-- Модальное окно просмотра --}}
    @if($showModal && $selectedReview)
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full p-6 relative">
            <button wire:click="closeModal" class="absolute top-3 right-3 text-gray-500 hover:text-gray-800">✕</button>

            <h2 class="text-2xl font-bold mb-4">Отзыв от {{ $selectedReview->user->name ?? '—' }}</h2>

            <p class="text-gray-700 mb-3"><strong>Экскурсия:</strong> {{ $selectedReview->excursion->title ?? '—' }}</p>
            <p class="text-gray-700 mb-3"><strong>Комментарий:</strong></p>
            <p class="text-gray-800 border p-3 rounded-lg bg-gray-50 mb-4">
                {{ $selectedReview->comment }}
            </p>

            <p class="text-amber-500 font-semibold">★ {{ $selectedReview->rating }}/5</p>

            <p class="text-sm text-gray-500 mt-2">
                Создан: {{ $selectedReview->created_at->format('d.m.Y H:i') }}
            </p>

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
