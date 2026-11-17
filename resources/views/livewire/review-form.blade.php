<div class="voyage-review-form max-w-xl mx-auto py-6 px-6 bg-white rounded-2xl shadow-sm mt-6">
    <h1 class="text-2xl font-semibold mb-6 text-gray-900">
        Оставить отзыв о экскурсии "{{ $excursion->title }}"
    </h1>

    @if (session('success'))
    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded-lg border border-green-200">
        {{ session('success') }}
    </div>
    @endif

    @if (session('error'))
    <div class="mb-4 p-3 bg-red-100 text-red-800 rounded-lg border border-red-200">
        {{ session('error') }}
    </div>
    @endif

    <form wire:submit.prevent="submit" class="space-y-4">
        <div>
            <label class="block text-gray-700 font-medium mb-1">Оценка:</label>
            <select wire:model="rating" class="border rounded-lg w-full p-2">
                @for($i=5; $i>=1; $i--)
                <option value="{{ $i }}">{{ $i }} ★</option>
                @endfor
            </select>
            @error('rating') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-gray-700 font-medium mb-1">Комментарий (необязательно):</label>
            <textarea wire:model="comment" rows="4" class="border rounded-lg w-full p-2" placeholder="Поделитесь впечатлениями..."></textarea>
            @error('comment') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="voyage-review-submit px-6 py-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition">
            Отправить отзыв
        </button>
    </form>
</div>
