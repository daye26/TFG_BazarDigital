<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Informacion del perfil') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Actualiza la informacion de tu cuenta y tu direccion de email.') }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Nombre')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        @php
            $storedPhone = old('phone', $user->phone);
            $phoneCountryCode = old('phone_country_code');
            $phoneNumber = old('phone_number');

            if ($phoneCountryCode === null || $phoneNumber === null) {
                if (preg_match('/^(\+\d{1,4})(\d+)$/', (string) $storedPhone, $matches)) {
                    $phoneCountryCode ??= $matches[1];
                    $phoneNumber ??= $matches[2];
                } else {
                    $phoneCountryCode ??= '+34';
                    $phoneNumber ??= '';
                }
            }
        @endphp

        <div>
            <x-input-label for="phone_country_code" :value="__('Telefono')" />
            <div class="mt-1 flex items-start gap-3">
                <x-text-input id="phone_country_code" name="phone_country_code" type="text" class="block shadow-none" :value="$phoneCountryCode" required autocomplete="tel-country-code" maxlength="4" style="width: 80px;" />
                <x-text-input id="phone_number" name="phone_number" type="tel" class="block flex-1" :value="$phoneNumber" required autocomplete="tel-national" placeholder="612345678" />
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('phone_country_code')" />
            <x-input-error class="mt-2" :messages="$errors->get('phone_number')" />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
