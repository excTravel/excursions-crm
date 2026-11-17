<nav x-data="{ open: false }" class="voyage-nav">
    <div class="voyage-nav-inner mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
        <a href="{{ route('excursions.list') }}" class="voyage-brand" aria-label="Каталог экскурсий">
            <span class="voyage-brand-icon"><x-application-logo class="h-5 w-5 fill-current" /></span>
            <span>ExTravel</span>
        </a>

        <div class="voyage-nav-menu">
            <a href="{{ route('excursions.list') }}" class="voyage-nav-link {{ request()->routeIs('excursions.list') ? 'is-active' : '' }}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 11 9-8 9 8"/><path d="M5 10v10h14V10"/><path d="M9 20v-6h6v6"/></svg><span>{{ Auth::check() && Auth::user()->role === 'admin' ? 'Каталог' : 'Экскурсии' }}</span>
            </a>
            @auth
                @if(Auth::user()->role === 'client')
                    <a href="{{ route('bookings.my') }}" class="voyage-nav-link {{ request()->routeIs('bookings.my') ? 'is-active' : '' }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v15H4z"/><path d="M8 3v4m8-4v4M4 10h16"/></svg><span>Бронирования</span></a>
                @elseif(Auth::user()->role === 'guide')
                    <a href="{{ route('guide.excursions') }}" class="voyage-nav-link {{ request()->routeIs('guide.excursions*') ? 'is-active' : '' }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16v13H4zM9 7V4h6v3M4 12h16m-11 0v2h6v-2"/></svg><span>Мои экскурсии</span></a>
                    <a href="{{ route('guide.bookings') }}" class="voyage-nav-link {{ request()->routeIs('guide.bookings') ? 'is-active' : '' }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v15H4z"/><path d="M8 9h8m-8 4h8m-8 4h5"/></svg><span>Заявки</span></a>
                @elseif(Auth::user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" class="voyage-nav-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg><span>Панель</span></a>
                    <a href="{{ route('admin.excursions') }}" class="voyage-nav-link {{ request()->routeIs('admin.excursions') ? 'is-active' : '' }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/><path d="M8 7h8m-8 4h8"/></svg><span>Экскурсии</span></a>
                    <a href="{{ route('admin.users') }}" class="voyage-nav-link {{ request()->routeIs('admin.users') ? 'is-active' : '' }}"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/></svg><span>Пользователи</span></a>
                    <a href="{{ route('admin.bookings') }}" class="voyage-nav-link {{ request()->routeIs('admin.bookings') ? 'is-active' : '' }}"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/></svg><span>Брони</span></a>
                    <a href="{{ route('admin.reviews') }}" class="voyage-nav-link {{ request()->routeIs('admin.reviews') ? 'is-active' : '' }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2L12 17.3l-5.6 2.9 1.1-6.2L3 9.6l6.2-.9L12 3Z"/></svg><span>Отзывы</span></a>
                @endif
            @endauth
        </div>

        <div class="voyage-nav-account">
            @auth
                <a class="voyage-profile" href="{{ route('profile.edit') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg><span>{{ Auth::user()->name }}</span></a>
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button type="submit" class="voyage-logout"><span class="voyage-logout-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5M21 12H9"/></svg></span><span>Выйти</span></button>
                </form>
            @else
                <a href="{{ route('login') }}" class="voyage-login"><span>Войти</span><span class="voyage-login-arrow" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 17 17 7M7 7h10v10"/></svg></span></a>
            @endauth
        </div>

        <button type="button" class="voyage-menu-toggle" @click="open = !open" aria-label="Открыть меню">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
        </button>
    </div>

    <div x-show="open" x-transition class="voyage-mobile-menu">
        <a href="{{ route('excursions.list') }}">Экскурсии</a>
        @auth
            @if(Auth::user()->role === 'client')<a href="{{ route('bookings.my') }}">Мои бронирования</a>@endif
            @if(Auth::user()->role === 'guide')<a href="{{ route('guide.excursions') }}">Мои экскурсии</a><a href="{{ route('guide.bookings') }}">Заявки</a>@endif
            @if(Auth::user()->role === 'admin')<a href="{{ route('admin.dashboard') }}">Панель</a><a href="{{ route('admin.excursions') }}">Экскурсии</a><a href="{{ route('admin.users') }}">Пользователи</a><a href="{{ route('admin.bookings') }}">Брони</a><a href="{{ route('admin.reviews') }}">Отзывы</a>@endif
            <a href="{{ route('profile.edit') }}">Профиль</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Выйти</button></form>
        @else
            <a href="{{ route('login') }}">Войти</a>
        @endauth
    </div>
</nav>