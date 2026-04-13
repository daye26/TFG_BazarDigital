<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Correo electronico')" />
            <x-text-input id="email" class="form-control-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="form-field">
            <x-input-label for="password" :value="__('Contraseña')" />

            <x-text-input id="password" class="form-control-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="form-checkbox" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Recordarme') }}</span>
            </label>
        </div>

        <div class="form-actions-auth">
            @if (Route::has('password.request'))
                <a class="form-link-muted" href="{{ route('password.request') }}">
                    {{ __('Has olvidado tu contraseña?') }}
                </a>
            @endif

            <x-primary-button>
                {{ __('Iniciar sesión') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
