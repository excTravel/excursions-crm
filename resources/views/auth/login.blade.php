<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Войти в аккаунт</h1>
        <p class="mt-1 text-sm text-gray-500">Продолжите планировать поездки по России</p>
    </div>

    <!-- Статус сессии -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Адрес электронной почты -->
        <div>
            <x-input-label for="email" :value="'Электронная почта'" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Пароль -->
        <div class="mt-4">
            <x-input-label for="password" :value="'Пароль'" />

            <x-text-input id="password" class="block mt-1 w-full"
                          type="password"
                          name="password"
                          required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Запомнить меня -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">Запомнить меня</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('register'))
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('register') }}">
                Ещё нет аккаунта? Зарегистрироваться
            </a>
            @endif

            <x-primary-button class="ms-3">
                Войти
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
