<x-guest-layout>
    <div class="form-copy">
        {{ __('Gracias por registrarte. Antes de empezar, confirma tu direccion de correo electronico haciendo clic en el enlace que te hemos enviado. Si no lo has recibido, te enviaremos otro.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 form-status-success">
            {{ __('Hemos enviado un nuevo enlace de verificacion al correo electronico indicado durante el registro.') }}
        </div>
    @endif

    <div class="form-actions-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    {{ __('Reenviar correo de verificacion') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="form-link-muted">
                {{ __('Cerrar sesion') }}
            </button>
        </form>
    </div>
</x-guest-layout>
