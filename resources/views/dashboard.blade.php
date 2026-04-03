<x-app-layout>
    <x-slot name="header">
        <h2 class="app-page-title">
            Mi cuenta
        </h2>
    </x-slot>

    <div class="app-page">
        <div class="app-shell">
            <div class="app-surface">
                <div class="app-surface-body text-stone-900">
                    <p class="app-section-kicker">Zona privada</p>
                    <h3 class="mt-2 text-3xl font-black tracking-tight">Sesion iniciada correctamente</h3>
                    <p class="mt-4 max-w-2xl text-sm leading-6 text-stone-600">
                        Esta vista te sirve de base para la zona del usuario. Desde aqui luego podras enlazar pedidos, carrito, perfil y funcionalidades privadas.
                    </p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('products.index') }}" class="app-button-primary">
                            Ver catalogo
                        </a>
                        <a href="{{ route('profile.edit') }}" class="app-button-secondary px-5 py-3">
                            Editar perfil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
