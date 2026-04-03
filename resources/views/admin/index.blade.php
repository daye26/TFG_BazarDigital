<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Panel de control
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-8 sm:px-6 lg:px-8">
            <section class="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
                <div class="border-b border-stone-200 bg-gradient-to-r from-stone-950 via-stone-900 to-amber-500/80 px-6 py-8">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-amber-200">Zona admin</p>
                    <h3 class="mt-2 text-3xl font-black tracking-tight">Panel de control</h3>
                    <p class="mt-4 max-w-3xl text-sm leading-6 text-stone-200">
                        Desde aqui puedes entrar al alta de productos y categorias cuando haga falta y seguir ampliando el panel con mas utilidades de gestion.
                    </p>
                </div>

                <div class="p-6 lg:p-8">
                    @if (session('status'))
                        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800">
                            {{ session('status') }}
                        </div>
                    @endif

                    <section class="grid gap-5 lg:grid-cols-[minmax(0,1.35fr)_minmax(320px,0.85fr)]">
                        <div class="rounded-3xl border border-stone-200 bg-stone-50 p-6">
                            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-stone-500">Panel admin</p>
                            <h4 class="mt-2 text-2xl font-black tracking-tight text-stone-950">Acciones rapidas</h4>

                            <div class="mt-6 flex flex-wrap gap-3">
                                <a href="{{ route('admin.products.create') }}" class="inline-flex items-center rounded-full bg-stone-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-stone-700">
                                    Nuevo producto
                                </a>

                                <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center rounded-full bg-stone-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-stone-700">
                                    Nueva categoria
                                </a>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <article class="flex min-h-[9.5rem] flex-col justify-between overflow-hidden rounded-3xl border border-stone-200 bg-white px-6 py-6 shadow-sm">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">Productos totales</p>
                                <p class="text-5xl font-black leading-none tracking-tight text-stone-950">{{ $stats['total_products'] }}</p>
                            </article>

                            <article class="flex min-h-[9.5rem] flex-col justify-between overflow-hidden rounded-3xl border border-stone-200 bg-white px-6 py-6 shadow-sm">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">Productos activos</p>
                                <p class="text-5xl font-black leading-none tracking-tight text-emerald-700">{{ $stats['active_products'] }}</p>
                            </article>

                            <article class="flex min-h-[9.5rem] flex-col justify-between overflow-hidden rounded-3xl border border-stone-200 bg-white px-6 py-6 shadow-sm">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">Productos ocultos</p>
                                <p class="text-5xl font-black leading-none tracking-tight text-stone-950">{{ $stats['inactive_products'] }}</p>
                            </article>

                            <article class="flex min-h-[9.5rem] flex-col justify-between overflow-hidden rounded-3xl border border-stone-200 bg-white px-6 py-6 shadow-sm">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">Categorias activas</p>
                                <p class="text-5xl font-black leading-none tracking-tight text-stone-950">{{ $stats['active_categories'] }}</p>
                            </article>
                        </div>
                    </section>

                    <section class="mt-8 rounded-3xl border border-stone-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-stone-500">Actividad reciente</p>
                                <h4 class="mt-1 text-2xl font-black tracking-tight text-stone-950">Ultimos productos</h4>
                            </div>
                            <a href="{{ route('admin.products.create') }}" class="inline-flex items-center rounded-full border border-stone-300 px-4 py-2 text-sm font-bold text-stone-700 transition hover:border-stone-900 hover:text-stone-950">
                                Crear producto
                            </a>
                        </div>

                        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            @forelse ($latestProducts as $product)
                                <article class="rounded-2xl border border-stone-200 px-4 py-4">
                                    <p class="text-sm font-bold text-stone-900">{{ $product->name }}</p>
                                    <p class="mt-1 text-xs uppercase tracking-[0.2em] text-stone-500">{{ $product->barcode }}</p>
                                    <div class="mt-3 flex items-center justify-between gap-3 text-sm">
                                        <span class="text-stone-500">{{ $product->category?->name ?? 'Sin categoria' }}</span>
                                        <span class="font-bold text-stone-950">{{ number_format((float) $product->sale_price, 2, ',', '.') }} &euro;</span>
                                    </div>
                                </article>
                            @empty
                                <p class="text-sm text-stone-500">Todavia no hay productos creados.</p>
                            @endforelse
                        </div>
                    </section>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
