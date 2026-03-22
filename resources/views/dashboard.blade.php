<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Mi cuenta
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
                <div class="p-6 text-stone-900">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-stone-500">Zona privada</p>
                    <h3 class="mt-2 text-3xl font-black tracking-tight">Sesion iniciada correctamente</h3>
                    <p class="mt-4 max-w-2xl text-sm leading-6 text-stone-600">
                        Esta vista te sirve de base para la zona del usuario. Desde aqui luego podras enlazar pedidos, carrito, perfil y funcionalidades privadas.
                    </p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('products.index') }}" class="rounded-full bg-stone-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-stone-700">
                            Ver catalogo
                        </a>
                        <a href="{{ route('profile.edit') }}" class="rounded-full border border-stone-300 px-5 py-3 text-sm font-bold text-stone-700 transition hover:border-stone-900 hover:text-stone-950">
                            Editar perfil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
