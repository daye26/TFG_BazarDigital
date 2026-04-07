<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Nombre -->
        <div>
            <x-input-label for="name" :value="__('Nombre')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Correo electronico -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Correo electronico')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Telefono -->
        <div class="mt-4">
            <x-input-label for="phone_country_code" :value="__('Telefono')" />
            <div class="form-phone-row">
                <x-text-input id="phone_country_code" class="form-phone-code" type="text" name="phone_country_code" :value="old('phone_country_code', '+34')" required autocomplete="tel-country-code" maxlength="4" />
                <x-text-input id="phone_number" class="block flex-1" type="tel" name="phone_number" :value="old('phone_number')" required autocomplete="tel-national" placeholder="612345678" />
            </div>
            <x-input-error :messages="$errors->get('phone_country_code')" class="mt-2" />
            <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <!-- Contraseña -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Contraseña')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirmar contraseña -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirmar contraseña')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="form-actions-end">
            <a class="form-link-muted" href="{{ route('login') }}">
                {{ __('Ya tienes cuenta?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Crear cuenta') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
