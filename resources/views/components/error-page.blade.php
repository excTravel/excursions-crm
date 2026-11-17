@props([
    'code' => 500,
    'title' => 'Ошибка',
    'message' => 'Произошла непредвиденная ошибка. Попробуйте позже.',
])

<div
    x-data="{ show: false }"
    x-init="setTimeout(() => show = true, 100)"
    x-show="show"
    x-transition.opacity.duration.700ms
    class="flex flex-col items-center justify-center min-h-[70vh] text-center px-4 select-none"
>
    {{-- Иллюстрация в зависимости от кода --}}
    <div class="mb-6">
        @if ($code == 404)
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 150" class="w-40 h-40 text-gray-400">
                <path fill="currentColor" d="M60,100 L100,20 L140,100 Z" opacity="0.1"/>
                <circle cx="100" cy="100" r="8" fill="currentColor"/>
                <rect x="90" y="50" width="20" height="40" fill="currentColor" opacity="0.2"/>
            </svg>
        @elseif ($code == 403)
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 150" class="w-40 h-40 text-gray-400">
                <circle cx="100" cy="75" r="40" fill="none" stroke="currentColor" stroke-width="6"/>
                <line x1="70" y1="75" x2="130" y2="75" stroke="currentColor" stroke-width="6"/>
            </svg>
        @else
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 150" class="w-40 h-40 text-gray-400">
                <circle cx="100" cy="75" r="45" fill="none" stroke="currentColor" stroke-width="6" opacity="0.2"/>
                <path d="M90 60 L100 80 L110 60" fill="none" stroke="currentColor" stroke-width="6" stroke-linecap="round"/>
                <circle cx="100" cy="95" r="4" fill="currentColor"/>
            </svg>
        @endif
    </div>

    <h1 class="text-6xl font-bold text-gray-800 mb-2 tracking-tight">{{ $code }}</h1>
    <p class="text-xl text-gray-700 font-semibold mb-2">{{ $title }}</p>
    <p class="text-gray-500 mb-6 max-w-md">{{ $message }}</p>

    <a href="{{ route('excursions.list') }}"
       class="bg-blue-600 text-gray-700 px-6 py-3 rounded-lg shadow hover:bg-blue-700 transition-all duration-300 transform hover:scale-105">
        Вернуться на главную
    </a>
</div>
