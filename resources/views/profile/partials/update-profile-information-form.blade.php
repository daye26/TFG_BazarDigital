<section>
    <header>
        <h2 class="form-section-title">
            {{ __('Informacion del perfil') }}
        </h2>

        <p class="form-section-copy">
            {{ __('Actualiza la informacion de tu cuenta y tu direccion de correo electronico.') }}
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
            <x-input-label for="email" :value="__('Correo electronico')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Tu direccion de correo electronico no esta verificada.') }}

                        <button form="send-verification" class="form-link-muted">
                            {{ __('Haz clic aqui para reenviar el correo de verificacion.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('Hemos enviado un nuevo enlace de verificacion a tu correo electronico.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="phone_country_code" :value="__('Telefono')" />
            <div class="form-phone-row">
                <x-text-input id="phone_country_code" name="phone_country_code" type="text" class="form-phone-code" :value="old('phone_country_code', $phoneParts['phone_country_code'])" required autocomplete="tel-country-code" maxlength="4" />
                <x-text-input id="phone_number" name="phone_number" type="tel" class="block flex-1" :value="old('phone_number', $phoneParts['phone_number'])" required autocomplete="tel-national" placeholder="612345678" />
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('phone_country_code')" />
            <x-input-error class="mt-2" :messages="$errors->get('phone_number')" />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Guardar') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="form-status-inline"
                >{{ __('Guardado.') }}</p>
            @endif
        </div>
    </form>
</section>
