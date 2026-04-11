<x-app-layout>
    <div class="app-page">
        <div class="app-shell-stack">
            <section class="app-surface">
                <x-admin.page-hero
                    kicker="Gestion de categorias"
                    title="Actualizar categoria"
                    description="Edita las categorias del catalogo desde una vista sencilla y revisa cuántos productos tiene asociada cada una."
                    :back-href="route('admin.index')"
                    back-label="Volver al panel"
                />

                <div class="app-surface-body">
                    @if (session('status'))
                        <div class="app-alert-success">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="app-alert-error">Revisa los campos marcados. Hay cambios en la categoria que no se han podido guardar.</div>
                    @endif

                    <div class="grid gap-8 xl:grid-cols-[minmax(280px,0.8fr)_minmax(0,1.2fr)]">
                        <aside class="space-y-6">
                            <section class="app-card">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                                    <div>
                                        <p class="app-section-kicker">Lista de categorias</p>
                                        <h4 class="mt-1 text-2xl font-black tracking-tight text-stone-950">
                                            @if ($categoryScope === 'active')
                                                Categorias activas
                                            @elseif ($categoryScope === 'inactive')
                                                Categorias inactivas
                                            @else
                                                Todas las categorias
                                            @endif
                                        </h4>
                                    </div>
                                    <div class="flex flex-wrap gap-3">
                                        @if ($categoryScope !== 'all')
                                            <a href="{{ route('admin.categories.manage') }}" class="app-button-secondary">Ver todas</a>
                                        @endif
                                        <a href="{{ route('admin.categories.create') }}" class="app-button-secondary">Nueva categoria</a>
                                    </div>
                                </div>

                                <div class="app-product-picker-list">
                                    <div class="space-y-3">
                                        @forelse ($categories as $listedCategory)
                                            @php($isSelected = $selectedCategory?->id === $listedCategory->id)

                                            <a
                                                href="{{ route('admin.categories.manage', ['category' => $listedCategory->id]) }}"
                                                class="{{ $isSelected ? 'app-product-picker-card app-product-picker-card-active' : 'app-product-picker-card' }}"
                                            >
                                                <div class="flex items-start justify-between gap-4">
                                                    <div class="min-w-0 flex-1">
                                                        <div class="flex flex-wrap items-center gap-2">
                                                            <p class="text-lg font-black tracking-tight {{ $isSelected ? 'text-white' : 'text-stone-950' }}">
                                                                {{ $listedCategory->name }}
                                                            </p>
                                                            <span class="{{ $isSelected ? 'text-stone-400' : 'text-stone-300' }}">|</span>
                                                            <span class="text-[10px] font-semibold uppercase tracking-[0.14em] {{ $listedCategory->is_active ? ($isSelected ? 'text-emerald-200' : 'text-emerald-700') : ($isSelected ? 'text-stone-300' : 'text-stone-500') }}">
                                                                {{ $listedCategory->is_active ? 'Activa' : 'Inactiva' }}
                                                            </span>
                                                        </div>

                                                        <p class="mt-1 text-xs leading-5 {{ $isSelected ? 'text-stone-300' : 'text-stone-600' }}">
                                                            {{ $listedCategory->url ?: 'Sin slug' }}
                                                        </p>
                                                    </div>

                                                    <article class="{{ $isSelected ? 'app-product-picker-stat app-product-picker-stat-active' : 'app-product-picker-stat' }}">
                                                        <p class="text-[8px] font-semibold uppercase tracking-[0.18em] {{ $isSelected ? 'text-stone-300' : 'text-stone-500' }}">
                                                            Productos
                                                        </p>
                                                        <p class="mt-0.5 text-base font-black tracking-tight {{ $isSelected ? 'text-white' : 'text-stone-950' }}">
                                                            {{ $listedCategory->products_count }}
                                                        </p>
                                                    </article>
                                                </div>
                                            </a>
                                        @empty
                                            <div class="rounded-2xl border border-dashed border-stone-300 bg-stone-50 px-5 py-6 text-sm text-stone-600">
                                                Todavia no hay categorias creadas.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </section>
                        </aside>

                        <div class="space-y-6">
                            @if ($selectedCategory)
                                <section class="app-note-card">
                                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                        <div>
                                            <p class="app-note-kicker">Categoria seleccionada</p>
                                            <h4 class="mt-2 text-3xl font-black tracking-tight text-stone-950">{{ $selectedCategory->name }}</h4>
                                            <p class="mt-3 text-sm leading-6 text-stone-600">
                                                {{ $selectedCategory->url ?: 'Sin slug definido' }} / {{ $selectedCategory->is_active ? 'Visible en tienda' : 'Oculta en tienda' }}
                                            </p>
                                        </div>

                                        <article class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-4">
                                            <p class="app-stat-label">Productos asociados</p>
                                            <p class="mt-3 text-2xl font-black tracking-tight text-stone-950">{{ $selectedCategory->products_count }}</p>
                                        </article>
                                    </div>
                                </section>

                                <section class="app-card">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                                        <div>
                                            <p class="app-section-kicker">Ficha</p>
                                            <h4 class="mt-1 text-2xl font-black tracking-tight text-stone-950">Datos de la categoria</h4>
                                        </div>
                                        <p class="text-sm text-stone-500">Aqui puedes cambiar nombre, descripcion, slug y estado.</p>
                                    </div>

                                    <form method="POST" action="{{ route('admin.categories.update', $selectedCategory) }}" class="mt-6 grid gap-8 lg:grid-cols-[minmax(0,1.6fr)_minmax(280px,0.7fr)]">
                                        @csrf
                                        @method('PATCH')

                                        <div class="space-y-6">
                                            <div class="grid gap-6 md:grid-cols-2">
                                                <div>
                                                    <x-input-label for="name">Nombre <span class="text-red-600">*</span></x-input-label>
                                                    <x-text-input id="name" name="name" type="text" class="mt-2 block w-full" :value="old('name', $selectedCategory->name)" required />
                                                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                                </div>

                                                <div>
                                                    <x-input-label for="url" value="Slug o URL amigable" />
                                                    <x-text-input id="url" name="url" type="text" class="mt-2 block w-full" :value="old('url', $selectedCategory->url)" />
                                                    <p class="app-helper-text">Opcional. Sirve para enlazar la categoria con una URL limpia.</p>
                                                    <x-input-error :messages="$errors->get('url')" class="mt-2" />
                                                </div>

                                                <div class="md:col-span-2">
                                                    <x-input-label for="description" value="Descripcion" />
                                                    <textarea id="description" name="description" rows="6" class="form-textarea mt-2">{{ old('description', $selectedCategory->description) }}</textarea>
                                                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                                                </div>
                                            </div>
                                        </div>

                                        <aside class="space-y-6">
                                            <section class="app-note-card">
                                                <p class="app-note-kicker">Resumen</p>
                                                <div class="app-note-copy">
                                                    <p>Esta categoria tiene {{ $selectedCategory->products_count }} producto{{ $selectedCategory->products_count === 1 ? '' : 's' }} asociado{{ $selectedCategory->products_count === 1 ? '' : 's' }}.</p>
                                                    <p>Si la desactivas, dejara de aparecer como categoria activa en la tienda.</p>
                                                </div>
                                            </section>

                                            <section class="app-card">
                                                <label class="flex items-start gap-3">
                                                    <input type="hidden" name="is_active" value="0">
                                                    <input
                                                        type="checkbox"
                                                        name="is_active"
                                                        value="1"
                                                        class="app-checkbox"
                                                        @checked((string) old('is_active', $selectedCategory->is_active ? '1' : '0') === '1')
                                                    >
                                                    <span>
                                                        <span class="block text-sm font-bold text-stone-900">Categoria activa</span>
                                                        <span class="mt-1 block text-xs leading-5 text-stone-500">Los productos pueden seguir existiendo aunque la categoria quede inactiva.</span>
                                                    </span>
                                                </label>

                                                <button type="submit" class="app-button-primary mt-6 w-full">Guardar categoria</button>
                                            </section>
                                        </aside>
                                    </form>
                                </section>
                            @else
                                <section class="app-note-card">
                                    <p class="app-note-kicker">Siguiente paso</p>
                                    <h4 class="mt-2 text-3xl font-black tracking-tight text-stone-950">Selecciona una categoria</h4>
                                    <div class="app-note-copy">
                                        <p>Elige una categoria del listado para editar su nombre, descripcion, slug o estado.</p>
                                        <p>En la lista veras tambien cuántos productos tiene asignados cada categoria.</p>
                                    </div>
                                </section>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
