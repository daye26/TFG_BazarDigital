<x-app-layout>
    <div class="app-page">
        <div class="app-shell-stack">
            <section class="app-surface">
                <x-admin.page-hero
                    kicker="Gestion de categorias"
                    title="Crear categoria"
                    description="Da de alta una categoria nueva para organizar mejor el catalogo y usarla despues en el alta de productos."
                />

                <div class="app-surface-body">
                    @if ($errors->any())
                        <div class="app-alert-error">
                            Revisa los campos marcados. Hay datos de la categoria que no se han podido guardar.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.categories.store') }}" class="grid gap-8 lg:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
                        @csrf

                        <div class="space-y-8">
                            <section class="app-card-muted">
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
                                        <p class="app-helper-text">Opcional. Si la rellenas, la tienda podra enlazar la categoria con una URL limpia.</p>
                                        <x-input-error :messages="$errors->get('url')" class="mt-2" />
                                    </div>

                                    <div class="md:col-span-2">
                                        <x-input-label for="description" value="Descripcion" />
                                        <textarea id="description" name="description" rows="5" class="form-textarea mt-2">{{ old('description') }}</textarea>
                                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                                    </div>
                                </div>
                            </section>
                        </div>

                        <aside class="space-y-6">
                            <section class="app-note-card">
                                <p class="app-note-kicker">Antes de guardar</p>
                                <div class="app-note-copy">
                                    <p>El nombre sera el texto visible para el cliente en tienda y filtros.</p>
                                    <p>La descripcion puede ayudarte a presentar la categoria en la portada o en listados.</p>
                                    <p>El slug es opcional, pero conviene rellenarlo si quieres URLs limpias para navegar por categorias.</p>
                                </div>
                            </section>

                            <section class="app-card">
                                <label class="flex items-start gap-3">
                                    <input
                                        type="checkbox"
                                        name="is_active"
                                        value="1"
                                        class="app-checkbox"
                                        @checked(old('is_active', '1'))
                                    >
                                    <span>
                                        <span class="block text-sm font-bold text-stone-900">Categoria activa</span>
                                        <span class="mt-1 block text-xs leading-5 text-stone-500">Si la desmarcas, quedara creada pero no aparecera como categoria activa en la tienda.</span>
                                    </span>
                                </label>

                                <button type="submit" class="app-button-primary mt-6 w-full">
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
