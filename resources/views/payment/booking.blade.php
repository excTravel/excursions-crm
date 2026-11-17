@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Оплата бронирования #{{ $booking->id }}</h1>

    {{-- Резюме брони --}}
    <div class="mb-6 bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
        <p class="text-gray-800">
            <strong>Экскурсия:</strong> {{ $booking->schedule->excursion->title ?? '—' }}
        </p>
        <p class="text-gray-800">
            <strong>Дата:</strong>
            @if($booking->schedule)
            {{ \Carbon\Carbon::parse($booking->schedule->date)->format('d.m.Y') }}
            в {{ \Carbon\Carbon::parse($booking->schedule->start_time)->format('H:i') }}
            @else
            —
            @endif
        </p>
        <p class="text-gray-800">
            <strong>Кол-во человек:</strong> {{ $booking->people_count }}
        </p>
        <p class="text-gray-800 mt-1">
            <strong>Итого к оплате:</strong>
            <span class="text-lg font-semibold text-blue-600">
                {{ number_format($booking->total_price, 0, '.', ' ') }} ₽
            </span>
        </p>

        @if($booking->extraServices && $booking->extraServices->count())
        <div class="mt-3">
            <p class="text-gray-800 font-semibold">Включая доп. услуги:</p>
            <ul class="mt-1 space-y-1 text-sm text-gray-700">
                @foreach($booking->extraServices as $extra)
                <li class="flex justify-between gap-4">
                    <div>
                        {{ $extra->name }}
                        @if($extra->pivot?->quantity)
                        <span class="text-gray-500">
                                        × {{ $extra->pivot->quantity }}
                                        @if($extra->per_person)
                                            (за человека)
                                        @endif
                                    </span>
                        @endif
                    </div>
                    <div>
                        {{ number_format($extra->pivot->total_price ?? 0, 0, '.', ' ') }} ₽
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>

    {{-- Форма оплаты --}}
    <form method="POST" action="{{ route('payment.booking.pay', $booking) }}"
          class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Номер карты</label>
            <input
                type="text"
                name="card_number"
                value="{{ old('card_number') }}"
                inputmode="numeric"
                autocomplete="cc-number"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                placeholder="0000 0000 0000 0000"
            >
            @error('card_number')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Срок действия (MM/YY)</label>
                <input
                    type="text"
                    name="card_expiry"
                    value="{{ old('card_expiry') }}"
                    placeholder="12/27"
                    autocomplete="cc-exp"
                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                >
                @error('card_expiry')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">CVV</label>
                <input
                    type="password"
                    name="card_cvv"
                    autocomplete="cc-csc"
                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    placeholder="000"
                >
                @error('card_cvv')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Имя на карте</label>
                <input
                    type="text"
                    name="card_name"
                    value="{{ old('card_name', auth()->user()->name ?? '') }}"
                    autocomplete="cc-name"
                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    placeholder="IVAN IVANOV"
                >
                @error('card_name')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex items-center justify-between mt-4">
            <a href="{{ url()->previous() }}"
               class="text-sm text-gray-500 hover:text-gray-700">
                ← Назад
            </a>

            <button type="submit"
                    class="px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                Оплатить {{ number_format($booking->total_price, 0, '.', ' ') }} ₽
            </button>
        </div>
    </form>

    <p class="mt-3 text-xs text-gray-500">
        Оплата условная, используется тренажёр оплаты без реального списания средств.
        Номера карт и CVV нигде не сохраняются, кроме последних 4 цифр.
    </p>
</div>
@endsection
