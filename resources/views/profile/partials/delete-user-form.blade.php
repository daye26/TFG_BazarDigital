<section class="space-y-6">
    <header>
        <h2 class="form-section-title">
            {{ __('Eliminar cuenta') }}
        </h2>

        <p class="form-section-copy">
            {{ __('Una vez eliminada tu cuenta, todos sus recursos y datos se borraran de forma permanente. Antes de eliminarla, descarga cualquier dato o informacion que quieras conservar.') }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Eliminar cuenta') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="form-section-title">
                {{ __('Estas seguro de que quieres eliminar tu cuenta?') }}
            </h2>

            <p class="form-section-copy">
                {{ __('Una vez eliminada tu cuenta, todos sus recursos y datos se borraran de forma permanente. Introduce tu contraseña para confirmar que quieres eliminar tu cuenta definitivamente.') }}
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Contraseña') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="form-modal-input"
                    placeholder="{{ __('Contraseña') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="form-actions-modal">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancelar') }}
                </x-secondary-button>

                <x-danger-button class="form-button-offset">
                    {{ __('Eliminar cuenta') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
