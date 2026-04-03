<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Nueva categoria
            </h2>
            <a href="{{ route('admin.index') }}" class="inline-flex items-center rounded-full border border-stone-300 px-4 py-2 text-sm font-bold text-stone-700 transition hover:border-stone-900 hover:text-stone-950">
                Volver al panel
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-8 sm:px-6 lg:px-8">
            <section class="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
                <div class="border-b border-stone-200 bg-gradient-to-r from-stone-950 via-stone-900 to-amber-500/80 px-6 py-8 text-white">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-amber-200">Gestion de categorias</p>
                    <h3 class="mt-2 text-3xl font-black tracking-tight">Crear categoria</h3>
                    <p class="mt-4 max-w-3xl text-sm leading-6 text-stone-200">
                        Da de alta una categoria nueva para organizar mejor el catalogo y usarla despues en el alta de productos.
                    </p>
                </div>

                <div class="p-6 lg:p-8">
                    @if ($errors->any())
                        <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800">
                            Revisa los campos marcados. Hay datos de la categoria que no se han podido guardar.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.categories.store') }}" class="grid gap-8 lg:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
                        @csrf

                        <div class="space-y-8">
                            <section class="rounded-3xl border border-stone-200 bg-stone-50 p-6">
                                <h4 class="text-lg font-bold text-stone-900">Datos de la categoria</h4>
                                <div class="mt-6 grid gap-6 md:grid-cols-2">
                                    <div>
                                        <x-input-label for="name">
                                            Nombre <span class="text-red-600">*</span>
                                        </x-input-label>
                                        <x-text-input id="name" name="name" type="text" class="mt-2 block w-full" :value="old('name')" required />
                                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="url" value="Slug o URL amigable" />
                                        <x-text-input id="url" name="url" type="text" class="mt-2 block w-full" :value="old('url')" />
                                        <p class="mt-2 text-xs text-stone-500">Opcional. Si la rellenas, la tienda podra enlazar la categoria con una URL limpia.</p>
                                        <x-input-error :messages="$errors->get('url')" class="mt-2" />
                                    </div>

                                    <div class="md:col-span-2">
                                        <x-input-label for="description" value="Descripcion" />
                                        <textarea id="description" name="description" rows="5" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                                    </div>
                                </div>
                            </section>
                        </div>

                        <aside class="space-y-6">
                            <section class="rounded-3xl border border-stone-200 bg-amber-50 p-6 shadow-sm">
                                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-amber-700">Antes de guardar</p>
                                <div class="mt-5 space-y-3 text-sm leading-6 text-stone-700">
                                    <p>El nombre sera el texto visible para el cliente en tienda y filtros.</p>
                                    <p>La descripcion puede ayudarte a presentar la categoria en la portada o en listados.</p>
                                    <p>El slug es opcional, pero conviene rellenarlo si quieres URLs limpias para navegar por categorias.</p>
                                </div>
                            </section>

                            <section class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm">
                                <label class="flex items-start gap-3">
                                    <input
                                        type="checkbox"
                                        name="is_active"
                                        value="1"
                                        class="mt-1 rounded border-gray-300 text-stone-900 shadow-sm focus:ring-stone-900"
                                        @checked(old('is_active', '1'))
                                    >
                                    <span>
                                        <span class="block text-sm font-bold text-stone-900">Categoria activa</span>
                                        <span class="mt-1 block text-xs leading-5 text-stone-500">Si la desmarcas, quedara creada pero no aparecera como categoria activa en la tienda.</span>
                                    </span>
                                </label>

                                <button type="submit" class="mt-6 inline-flex w-full items-center justify-center rounded-full bg-stone-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-stone-700">
                                    Crear categoria
                                </button>
                            </section>
                        </aside>
                    </form>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
