<div class="relative">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 bg-white min-h-[80vh]">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Мои бронирования</h1>

        @if(count($bookings))
        <div class="space-y-5">
            @foreach($bookings as $booking)
            @php
            $excursion = $booking->schedule->excursion ?? null;
            $schedule = $booking->schedule ?? null;
            $canCancel = $booking->canBeCanceled();
            $isFinished = $schedule && $schedule->isFinished();

            $statusClass = match($booking->status) {
            'pending'   => 'text-yellow-600',
            'confirmed' => 'text-green-600',
            'canceled'  => 'text-red-600',
            default     => 'text-gray-600',
            };

            $statusLabel = match($booking->status) {
            'pending'   => 'Ожидает подтверждения',
            'confirmed' => 'Подтверждено',
            'canceled'  => 'Отменено',
            default     => ucfirst($booking->status),
            };

            // Проверяем — оставлял ли пользователь отзыв
            $alreadyReviewed = false;
            if ($excursion) {
            $alreadyReviewed = \App\Models\Review::where('user_id', auth()->id())
            ->where('excursion_id', $excursion->id)
            ->exists();
            }

            $extraServices = $booking->extraServices ?? collect();
            @endphp

            @if($excursion)
            <div class="border border-gray-200 rounded-xl p-5 shadow-sm flex flex-col md:flex-row md:items-center justify-between">
                <div class="space-y-2">
                    <h2 class="text-lg font-semibold text-gray-900">{{ $excursion->title }}</h2>

                    <p class="text-sm text-gray-700">
                        {{ \Carbon\Carbon::parse($booking->schedule->date)->format('d.m.Y') }}
                        в {{ \Carbon\Carbon::parse($booking->schedule->start_time)->format('H:i') }}
                    </p>

                    <p class="text-sm text-gray-700">
                        Кол-во человек: {{ $booking->people_count }}
                    </p>

                    <p class="text-sm text-gray-700">
                        Итого:
                        <span class="font-semibold text-blue-600">
                                        {{ number_format($booking->total_price, 0, '.', ' ') }} ₽
                                    </span>
                    </p>

                    {{-- 🔹 Доп. услуги --}}
                    @if($extraServices->count())
                    <div class="text-sm text-gray-700">
                        <p class="font-medium">Дополнительные услуги:</p>
                        <ul class="mt-1 space-y-1">
                            @foreach($extraServices as $extra)
                            <li class="flex justify-between gap-4">
                                <div>
                                    <span class="font-medium">{{ $extra->name }}</span>
                                    @if($extra->pivot?->quantity)
                                    <span class="text-gray-500">
                                                                × {{ $extra->pivot->quantity }}
                                                                @if($extra->per_person)
                                                                    (за человека)
                                                                @endif
                                                            </span>
                                    @endif
                                </div>
                                <div class="text-gray-900">
                                    {{ number_format($extra->pivot->total_price ?? 0, 0, '.', ' ') }} ₽
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <p class="text-sm">
                        Статус брони:
                        <span class="{{ $statusClass }}">{{ $statusLabel }}</span>
                    </p>

                    {{-- Статус экскурсии --}}
                    @if($isFinished)
                    <p class="text-sm text-emerald-700 font-medium">Экскурсия завершена ✅</p>
                    @else
                    <p class="text-sm text-blue-700 font-medium">Экскурсия ещё не состоялась ⏳</p>
                    @endif
                </div>

                <div class="flex flex-col gap-2 mt-4 md:mt-0">
                    {{-- Перейти к экскурсии --}}
                    <a href="{{ route('excursions.show', $excursion->slug) }}"
                       class="inline-block text-center px-4 py-2 bg-blue-100 text-blue-800 border border-blue-300 rounded-lg font-medium hover:bg-blue-200 transition">
                        Перейти
                    </a>

                    {{-- Отменить бронь --}}
                    @if($canCancel && !$isFinished)
                    <button wire:click="cancel({{ $booking->id }})"
                            class="px-4 py-2 bg-red-100 text-red-700 border border-red-300 rounded-lg font-medium hover:bg-red-200 transition">
                        Отменить бронь
                    </button>
                    @endif

                    {{-- Оставить отзыв --}}
                    @if($isFinished && !$alreadyReviewed)
                    <a href="{{ route('reviews.create', ['excursion' => $excursion->id]) }}"
                       class="px-4 py-2 bg-amber-100 text-amber-800 border border-amber-300 rounded-lg font-medium hover:bg-amber-200 transition">
                        Оставить отзыв
                    </a>
                    @elseif($isFinished && $alreadyReviewed)
                    <span class="text-gray-500 text-sm italic text-center">Отзыв уже оставлен</span>
                    @endif
                </div>
            </div>
            @endif
            @endforeach
        </div>
        @else
        <div class="text-center py-16 text-gray-700">
            <p class="text-lg">😕 У вас пока нет бронирований.</p>
            <a href="{{ route('excursions.list') }}"
               class="mt-4 inline-block px-5 py-2 bg-blue-100 text-blue-800 border border-blue-300 rounded-lg hover:bg-blue-200 transition">
                Перейти в каталог
            </a>
        </div>
        @endif
    </div>

    {{-- Toast уведомления --}}
    <script>
        document.addEventListener('livewire:load', () => {
            Livewire.on('notify', data => {
                const el = document.createElement('div');
                el.textContent = data.message;
                el.className = 'fixed bottom-6 right-6 bg-white border border-gray-300 shadow-lg rounded-lg px-4 py-3 text-gray-900 animate-fade-in';
                document.body.appendChild(el);
                setTimeout(() => el.remove(), 3000);
            });
        });
    </script>

    <style>
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fade-in 0.3s ease-out;
        }
    </style>
</div>
