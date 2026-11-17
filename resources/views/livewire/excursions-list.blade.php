<div class="min-h-screen bg-gray-50 py-10">
    {{-- Приветственный блок временно отключён; разметка сохранена для дальнейшей доработки.
    <div class="voyage-hero-wrap mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-10">
        <section class="voyage-hero" aria-labelledby="voyage-hero-title">
            <img class="voyage-hero-image" src="{{ asset('images/extravel-hero.jpg') }}" alt="Горный пейзаж Дагестана">
            <div class="voyage-hero-shade" aria-hidden="true"></div>
            <h1 id="voyage-hero-title" class="voyage-hero-title">Выбери свою<br>экскурсию</h1>
            <div class="voyage-hero-card">
                <p>Твой спутник отдыха с семьёй или друзьями</p>
                <a href="#excursion-catalog" class="voyage-primary-button">
                    <span>Изучить экскурсии</span>
                    <span class="voyage-action-arrow" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 17 17 7M7 7h10v10"/></svg></span>
                </a>
            </div>
        </section>
    </div>
    --}}
    <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-10">
        <div id="excursion-catalog" class="mb-8 scroll-mt-6">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Каталог экскурсий</h1>
            <p class="mt-2 text-gray-600">Выберите экскурсию и удобное время</p>
        </div>

        <section class="voyage-catalog-filters mb-8 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="catalog-search" class="mb-1.5 block text-sm font-medium text-gray-700">Поиск</label>
                    <input id="catalog-search" wire:model.live.debounce.350ms="search" type="search" placeholder="Название, описание или город"
                           class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">
                </div>
                <div>
                    <label for="catalog-date" class="mb-1.5 block text-sm font-medium text-gray-700">Дата экскурсии</label>
                    <input id="catalog-date" type="date" min="{{ today()->toDateString() }}" wire:model.live="selectedDate"
                           class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">
                </div>
                <div>
                    <label for="catalog-location" class="mb-1.5 block text-sm font-medium text-gray-700">Город</label>
                    <select id="catalog-location" wire:model.live="selectedLocation" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">
                        <option value="">Все города</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}">{{ $location->city }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="catalog-sort" class="mb-1.5 block text-sm font-medium text-gray-700">Сортировка</label>
                    <select id="catalog-sort" wire:model.live="sortBy" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">
                        <option value="created_at">Сначала новые</option>
                        <option value="price_asc">Сначала доступные</option>
                        <option value="price_desc">Сначала дороже</option>
                        <option value="duration_asc">Короче по времени</option>
                        <option value="duration_desc">Дольше по времени</option>
                    </select>
                </div>
            </div>

            <div class="mt-5 flex flex-col gap-3 border-t border-gray-100 pt-4 sm:flex-row sm:items-end sm:justify-between">
                <fieldset>
                    <legend class="mb-2 text-sm font-medium text-gray-700">Категории</legend>
                    <div class="flex flex-wrap gap-x-5 gap-y-2">
                        @foreach($categories as $category)
                            <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                                <input type="checkbox" wire:model.live="selectedCategories" value="{{ $category->id }}"
                                       class="rounded border-gray-300 text-gray-800 focus:ring-gray-500">
                                {{ $category->name }}
                            </label>
                        @endforeach
                    </div>
                </fieldset>
                <button type="button" wire:click="resetFilters" class="voyage-reset-button shrink-0">
                    Сбросить фильтры
                </button>
            </div>
        </section>

        <div wire:loading.class="opacity-50 pointer-events-none">
            @if($excursions->count())
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                    @foreach($excursions as $excursion)
                        @php
                            $photo = $excursion->photos->first();
                            $priceRule = $excursion->pricingRules->first();
                            $priceMode = $priceRule?->mode ?? 'per_person';
                            $shownPrice = $priceRule?->price ?? $excursion->price;
                        @endphp
                        <article class="flex min-w-0 flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                            <a href="{{ route('excursions.show', $excursion->slug) }}" class="relative block aspect-[16/10] overflow-hidden bg-gray-100">
                                @if($photo)
                                    <img src="{{ asset('storage/' . $photo->path) }}" alt="{{ $excursion->title }}" loading="lazy" class="h-full w-full object-cover transition duration-300 hover:scale-[1.02]">
                                @else
                                    <div class="flex h-full items-center justify-center text-sm text-gray-400">Фото скоро появится</div>
                                @endif
                                @if($excursion->category)
                                    <span class="absolute left-3 top-3 rounded-full bg-white/95 px-3 py-1 text-xs font-medium text-gray-700 shadow-sm">{{ $excursion->category->name }}</span>
                                @endif
                            </a>

                            <div class="flex flex-1 flex-col p-5">
                                <div class="mb-2 flex flex-wrap items-center gap-x-2 text-sm text-gray-500">
                                    <span>{{ $excursion->location->city ?? 'Россия' }}</span><span aria-hidden="true">·</span><span>{{ $excursion->duration }} мин</span>
                                </div>
                                <h2 class="text-lg font-semibold leading-snug text-gray-900">
                                    <a href="{{ route('excursions.show', $excursion->slug) }}" class="hover:text-gray-600">{{ $excursion->title }}</a>
                                </h2>
                                <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-gray-600">{{ $excursion->description }}</p>

                                <div class="mt-4 flex items-baseline gap-2">
                                    <span class="text-xl font-bold text-gray-900">{{ number_format((float) $shownPrice, 0, ',', ' ') }} ₽</span>
                                    <span class="text-xs text-gray-500">{{ $priceMode === 'per_group' ? 'за группу' : 'за человека' }}</span>
                                </div>

                                <div class="mt-4 border-t border-gray-100 pt-3">
                                    <p class="mb-2 text-xs font-medium text-gray-500">Ближайшие сеансы</p>
                                    @php $visibleSlots = $excursion->schedules->take(3); @endphp
                                    @forelse($visibleSlots as $slot)
                                        @php $placesLeft = max(0, $slot->max_people - $slot->bookings->sum('people_count')); @endphp
                                        @if($placesLeft > 0)
                                            <a class="flex items-center justify-between gap-2 rounded-lg px-2 py-2 text-sm text-gray-700 hover:bg-gray-50" href="{{ route('excursions.show', ['slug' => $excursion->slug, 'schedule' => $slot->id]) }}">
                                                <span>{{ $slot->date->format('d.m.Y') }} · {{ \Illuminate\Support\Carbon::parse($slot->start_time)->format('H:i') }}</span>
                                                <span class="whitespace-nowrap text-xs font-medium text-gray-500">Выбрать →</span>
                                            </a>
                                        @else
                                            <div class="flex justify-between px-2 py-2 text-sm text-gray-400"><span>{{ $slot->date->format('d.m.Y') }} · {{ \Illuminate\Support\Carbon::parse($slot->start_time)->format('H:i') }}</span><span>Мест нет</span></div>
                                        @endif
                                    @empty
                                        <p class="px-2 py-2 text-sm text-gray-400">Расписание скоро появится</p>
                                    @endforelse
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="mt-8">{{ $excursions->onEachSide(1)->links() }}</div>
            @else
                <div class="rounded-2xl border border-gray-200 bg-white p-10 text-center shadow-sm">
                    <p class="text-lg font-medium text-gray-800">По выбранным параметрам экскурсий нет</p>
                </div>
            @endif
        </div>
    </div>
</div>
