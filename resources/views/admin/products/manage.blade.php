<x-app-layout>
    <x-slot name="header">
        <x-admin.panel-header
            title="Actualizar producto"
            active="products"
            :back-href="route('admin.index')"
            back-label="Volver al panel"
        />
    </x-slot>

    <div class="app-page">
        <div class="mx-auto max-w-[95rem] space-y-8 sm:px-6 lg:px-8 2xl:max-w-[102rem]">
            <section class="app-surface">
                <div class="app-hero">
                    <p class="app-hero-kicker">Gestion de productos</p>
                    <h3 class="app-hero-title">Actualizar producto</h3>
                    <p class="app-hero-copy">
                        Busca por codigo de barras, nombre o categoria, abre productos activos o inactivos y separa la edicion entre ficha y precio.
                    </p>
                </div>

                <div class="app-surface-body">
                    @if (session('status'))
                        <div class="app-alert-success">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="app-alert-error">Revisa los campos marcados. Hay cambios que no se han podido guardar.</div>
                    @endif

                    @php
                        $pricingFields = [
                            'tax',
                            'cost_price',
                            'sale_price',
                            'margin_multiplier',
                            'pricing_mode',
                            'discount_value',
                            'discount_type',
                        ];
                        $activeEditorTab = collect($pricingFields)->contains(fn ($field) => $errors->has($field))
                            ? 'price'
                            : 'general';
                    @endphp

                    <div class="grid gap-8 xl:grid-cols-[minmax(290px,0.68fr)_minmax(0,1.32fr)] 2xl:grid-cols-[minmax(300px,0.64fr)_minmax(0,1.36fr)]">
                        <aside class="space-y-6">
                            <section class="app-card-muted">
                                <p class="app-section-kicker">Buscador admin</p>
                                <h4 class="app-section-title">Encuentra el producto correcto</h4>
                                <p class="mt-3 text-sm leading-6 text-stone-600">
                                    Este buscador da prioridad al codigo de barras para localizar antes el producto que quieres corregir.
                                </p>

                                <form method="GET" action="{{ route('admin.products.manage') }}" class="mt-6 space-y-4">
                                    <label for="admin-product-search" class="sr-only">Buscar productos para editar</label>
                                    <div class="rounded-2xl border border-stone-300 bg-white px-4 py-3 shadow-sm shadow-stone-200/50">
                                        <input
                                            id="admin-product-search"
                                            name="q"
                                            type="search"
                                            value="{{ $searchQuery }}"
                                            class="w-full border-0 bg-transparent p-0 text-sm font-medium text-stone-800 placeholder:text-stone-400 focus:outline-none focus:ring-0"
                                            placeholder="Codigo de barras, nombre o categoria"
                                        >
                                    </div>

                                    <div class="flex flex-wrap gap-3">
                                        <button type="submit" class="app-button-primary">Buscar producto</button>

                                        @if ($searchQuery !== '')
                                            <a href="{{ route('admin.products.manage') }}" class="app-button-secondary">Limpiar busqueda</a>
                                        @endif
                                    </div>
                                </form>
                            </section>

                            <section class="app-card">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                                    <div>
                                        <p class="app-section-kicker">Lista de productos</p>
                                        <h4 class="mt-1 text-2xl font-black tracking-tight text-stone-950">
                                            @if ($searchQuery !== '')
                                                Resultados de busqueda
                                            @elseif ($productScope === 'active')
                                                Productos activos
                                            @elseif ($productScope === 'inactive')
                                                Productos ocultos
                                            @else
                                                Todos los productos
                                            @endif
                                        </h4>
                                    </div>
                                    <div class="flex flex-wrap gap-3">
                                        @if ($productScope !== 'all')
                                            <a href="{{ route('admin.products.manage') }}" class="app-button-secondary">Ver todos</a>
                                        @endif
                                        <a href="{{ route('admin.products.create') }}" class="app-button-secondary">Nuevo producto</a>
                                    </div>
                                </div>

                                <div class="app-product-picker-list">
                                    <div class="space-y-3">
                                    @forelse ($products as $listedProduct)
                                        @php
                                            $isSelected = $selectedProduct?->id === $listedProduct->id;
                                            $discountLabel = null;

                                            if ($listedProduct->has_discount) {
                                                $discountLabel = $listedProduct->discount_type === 'percentage'
                                                    ? 'Desc. ' . rtrim(rtrim(number_format((float) $listedProduct->discount_value, 2, ',', ''), '0'), ',') . '%'
                                                    : 'Desc. -' . number_format((float) $listedProduct->discount_value, 2, ',', '.') . ' EUR';
                                            }
                                        @endphp
                                        <div>
                                            <a
                                                href="{{ route('admin.products.manage', array_filter(['product' => $listedProduct->id, 'q' => $searchQuery !== '' ? $searchQuery : null])) }}"
                                                class="{{ $isSelected ? 'app-product-picker-card app-product-picker-card-active' : 'app-product-picker-card' }}"
                                            >
                                                <div class="grid grid-cols-[minmax(0,1fr)_auto] items-start gap-x-3 gap-y-2">
                                                    <div class="min-w-0 sm:pr-2">
                                                        <div class="flex flex-wrap items-center gap-2">
                                                            <p class="text-lg font-black tracking-tight {{ $isSelected ? 'text-white' : 'text-stone-950' }}">
                                                                {{ $listedProduct->name }}
                                                            </p>
                                                            <span class="{{ $isSelected ? 'text-stone-400' : 'text-stone-300' }}">|</span>
                                                            <span class="text-[10px] font-semibold uppercase tracking-[0.14em] {{ $listedProduct->is_active ? ($isSelected ? 'text-emerald-200' : 'text-emerald-700') : ($isSelected ? 'text-stone-300' : 'text-stone-500') }}">
                                                                {{ $listedProduct->is_active ? 'Activo' : 'Inactivo' }}
                                                            </span>
                                                            @if ($discountLabel)
                                                                <span class="{{ $isSelected ? 'text-stone-400' : 'text-stone-300' }}">|</span>
                                                                <span class="text-[9px] font-semibold uppercase tracking-[0.12em] {{ $isSelected ? 'text-amber-200' : 'text-amber-700' }}">
                                                                    {{ $discountLabel }}
                                                                </span>
                                                            @endif
                                                        </div>

                                                    </div>

                                                    <div class="flex shrink-0 items-start gap-1.5 border-l {{ $isSelected ? 'border-white/15 pl-2.5' : 'border-stone-200 pl-2.5' }}">
                                                        <article class="{{ $isSelected ? 'app-product-picker-stat app-product-picker-stat-active' : 'app-product-picker-stat' }}">
                                                            <p class="text-[8px] font-semibold uppercase tracking-[0.18em] {{ $isSelected ? 'text-stone-300' : 'text-stone-500' }}">
                                                                Precio
                                                            </p>
                                                            <p class="mt-0.5 text-base font-black tracking-tight {{ $isSelected ? 'text-white' : 'text-stone-950' }}">
                                                                {{ number_format((float) ($listedProduct->has_discount ? $listedProduct->discounted_price : $listedProduct->sale_price), 2, ',', '.') }} &euro;
                                                            </p>
                                                        </article>

                                                        <article class="{{ $isSelected ? 'app-product-picker-stat app-product-picker-stat-active' : 'app-product-picker-stat' }}">
                                                            <p class="text-[8px] font-semibold uppercase tracking-[0.18em] {{ $isSelected ? 'text-stone-300' : 'text-stone-500' }}">
                                                                Stock
                                                            </p>
                                                            <p class="mt-0.5 text-base font-black tracking-tight {{ $isSelected ? 'text-white' : 'text-stone-950' }}">
                                                                {{ $listedProduct->qty }}
                                                            </p>
                                                        </article>
                                                    </div>

                                                    <p class="col-span-2 text-xs leading-5 {{ $isSelected ? 'text-stone-300' : 'text-stone-600' }}">
                                                        {{ $listedProduct->barcode }} / {{ $listedProduct->category?->name ?? 'Sin categoria' }} / IVA {{ $listedProduct->tax }}%
                                                    </p>
                                                </div>
                                            </a>
                                        </div>
                                    @empty
                                        <div class="rounded-2xl border border-dashed border-stone-300 bg-stone-50 px-5 py-6 text-sm text-stone-600">
                                            No hemos encontrado productos para esa busqueda.
                                        </div>
                                    @endforelse
                                    </div>
                                </div>
                            </section>
                        </aside>

                        <div class="space-y-6">
                            @if ($selectedProduct)
                                <div x-data="{ activeTab: @js($activeEditorTab) }" class="space-y-6">
                                <section class="app-note-card">
                                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                        <div>
                                            <p class="app-note-kicker">Producto seleccionado</p>
                                            <h4 class="mt-2 text-3xl font-black tracking-tight text-stone-950">{{ $selectedProduct->name }}</h4>
                                            <p class="mt-3 text-sm leading-6 text-stone-600">
                                                Codigo {{ $selectedProduct->barcode }} · {{ $selectedProduct->category?->name ?? 'Sin categoria' }} · {{ $selectedProduct->is_active ? 'Visible en tienda' : 'Oculto en tienda' }}
                                            </p>
                                        </div>

                                        <div class="grid gap-3 sm:grid-cols-2">
                                            <article class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-4">
                                                <p class="app-stat-label">Precio actual</p>
                                                <p class="mt-3 text-2xl font-black tracking-tight text-stone-950">{{ number_format((float) $selectedProduct->sale_price, 2, ',', '.') }} &euro;</p>
                                            </article>
                                            <article class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-4">
                                                <p class="app-stat-label">Stock actual</p>
                                                <p class="mt-3 text-2xl font-black tracking-tight text-stone-950">{{ $selectedProduct->qty }}</p>
                                            </article>
                                        </div>
                                    </div>
                                </section>

                                <section class="app-card">
                                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="inline-flex rounded-full border border-stone-300 bg-stone-100 p-1">
                                            <button
                                                type="button"
                                                @click="activeTab = 'general'"
                                                :class="activeTab === 'general' ? 'bg-stone-900 text-white shadow-sm' : 'text-stone-600 hover:text-stone-950'"
                                                class="rounded-full px-4 py-2 text-sm font-bold transition"
                                            >
                                                General
                                            </button>
                                            <button
                                                type="button"
                                                @click="activeTab = 'price'"
                                                :class="activeTab === 'price' ? 'bg-stone-900 text-white shadow-sm' : 'text-stone-600 hover:text-stone-950'"
                                                class="rounded-full px-4 py-2 text-sm font-bold transition"
                                            >
                                                Precio
                                            </button>
                                        </div>

                                        <p class="text-sm text-stone-500">Cambia entre la ficha general y el bloque de precio.</p>
                                    </div>
                                </section>

                                <section class="app-card" x-show="activeTab === 'general'" x-cloak>
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                                        <div>
                                            <p class="app-section-kicker">Caracteristicas</p>
                                            <h4 class="mt-1 text-2xl font-black tracking-tight text-stone-950">Ficha del producto</h4>
                                        </div>
                                        <p class="text-sm text-stone-500">Aqui cambias datos descriptivos, stock, imagen, categoria y estado.</p>
                                    </div>

                                    <form method="POST" action="{{ route('admin.products.update.details', $selectedProduct) }}" enctype="multipart/form-data" class="mt-6 space-y-6">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="return_query" value="{{ $searchQuery }}">

                                        <div class="grid gap-6 md:grid-cols-2">
                                            <div>
                                                <x-input-label for="barcode">Codigo de barras <span class="text-red-600">*</span></x-input-label>
                                                <x-text-input id="barcode" name="barcode" type="text" class="mt-2 block w-full" :value="old('barcode', $selectedProduct->barcode)" required />
                                                <x-input-error :messages="$errors->get('barcode')" class="mt-2" />
                                            </div>
                                            <div>
                                                <x-input-label for="name">Nombre <span class="text-red-600">*</span></x-input-label>
                                                <x-text-input id="name" name="name" type="text" class="mt-2 block w-full" :value="old('name', $selectedProduct->name)" required />
                                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                            </div>
                                            <div class="md:col-span-2">
                                                <x-input-label for="description" value="Descripcion" />
                                                <textarea id="description" name="description" rows="4" class="form-textarea mt-2">{{ old('description', $selectedProduct->description) }}</textarea>
                                                <x-input-error :messages="$errors->get('description')" class="mt-2" />
                                            </div>
                                            <div>
                                                <x-input-label for="category_id" value="Categoria" />
                                                <select id="category_id" name="category_id" class="form-select mt-2">
                                                    <option value="">Sin categoria</option>
                                                    @foreach ($categories as $category)
                                                        <option value="{{ $category->id }}" @selected((string) old('category_id', $selectedProduct->category_id) === (string) $category->id)>
                                                            {{ $category->name }}{{ $category->is_active ? '' : ' (inactiva)' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                                            </div>
                                            <div>
                                                <x-input-label for="qty" value="Stock disponible" />
                                                <x-text-input id="qty" name="qty" type="number" min="0" step="1" class="mt-2 block w-full" :value="old('qty', $selectedProduct->qty)" />
                                                <x-input-error :messages="$errors->get('qty')" class="mt-2" />
                                            </div>
                                            <div>
                                                <x-input-label for="image" value="Sustituir imagen" />
                                                <x-text-input id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="mt-2 block w-full" />
                                                <p class="app-helper-text">Sube una nueva imagen solo si quieres reemplazar la actual.</p>
                                                <x-input-error :messages="$errors->get('image')" class="mt-2" />
                                            </div>
                                            <div>
                                                <x-input-label for="url" value="Slug o URL amigable" />
                                                <x-text-input id="url" name="url" type="text" class="mt-2 block w-full" :value="old('url', $selectedProduct->url)" />
                                                <x-input-error :messages="$errors->get('url')" class="mt-2" />
                                            </div>

                                            <div class="md:col-span-2">
                                                <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_auto]">
                                                    <div class="rounded-2xl border border-dashed border-stone-300 bg-stone-50 p-4">
                                                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Imagen actual</p>
                                                        <div class="mt-4 flex min-h-52 items-center justify-center overflow-hidden rounded-2xl bg-white">
                                                            @if ($selectedProduct->image_url)
                                                                <img src="{{ $selectedProduct->image_url }}" alt="Imagen actual de {{ $selectedProduct->name }}" class="h-full max-h-64 w-full object-contain">
                                                            @else
                                                                <span class="px-4 text-center text-sm font-medium text-stone-500">Este producto no tiene imagen guardada.</span>
                                                            @endif
                                                        </div>
                                                        @if ($selectedProduct->image)
                                                            <p class="app-helper-text mt-3">{{ basename($selectedProduct->image) }}</p>
                                                        @endif
                                                    </div>

                                                    <div class="space-y-4 rounded-2xl border border-stone-200 bg-white p-4">
                                                        <label class="flex items-start gap-3">
                                                            <input type="hidden" name="remove_image" value="0">
                                                            <input type="checkbox" name="remove_image" value="1" class="app-checkbox" @checked(old('remove_image') === '1')>
                                                            <span>
                                                                <span class="block text-sm font-bold text-stone-900">Eliminar imagen actual</span>
                                                                <span class="mt-1 block text-xs leading-5 text-stone-500">Si subes una nueva imagen, la anterior se reemplazara automaticamente.</span>
                                                            </span>
                                                        </label>
                                                        <x-input-error :messages="$errors->get('remove_image')" class="mt-2" />

                                                        <label class="flex items-start gap-3">
                                                            <input type="hidden" name="is_active" value="0">
                                                            <input type="checkbox" name="is_active" value="1" class="app-checkbox" @checked((string) old('is_active', $selectedProduct->is_active ? '1' : '0') === '1')>
                                                            <span>
                                                                <span class="block text-sm font-bold text-stone-900">Producto activo</span>
                                                                <span class="mt-1 block text-xs leading-5 text-stone-500">Si lo desmarcas dejara de mostrarse en la tienda publica.</span>
                                                            </span>
                                                        </label>
                                                        <x-input-error :messages="$errors->get('is_active')" class="mt-2" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex justify-end">
                                            <button type="submit" class="app-button-primary">Guardar caracteristicas</button>
                                        </div>
                                    </form>
                                </section>

                                <section
                                    class="app-card"
                                    x-show="activeTab === 'price'"
                                    x-cloak
                                    x-data="productPricingForm({
                                        costPrice: @js(old('cost_price', number_format((float) $selectedProduct->cost_price, 4, '.', ''))),
                                        marginMultiplier: @js(old('margin_multiplier', number_format((float) $selectedProduct->margin_multiplier, 4, '.', ''))),
                                        salePrice: @js(old('sale_price', number_format((float) $selectedProduct->sale_price, 2, '.', ''))),
                                        discountValue: @js(old('discount_value', number_format((float) $selectedProduct->discount_value, 2, '.', ''))),
                                        discountType: @js(old('discount_type', $selectedProduct->discount_type)),
                                        tax: @js(old('tax', (string) $selectedProduct->tax)),
                                        pricingMode: @js(old('pricing_mode', 'margin')),
                                    })"
                                    x-init="init()"
                                    x-effect="refreshPreview()"
                                >
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                                        <div>
                                            <p class="app-section-kicker">Precio</p>
                                            <h4 class="mt-1 text-2xl font-black tracking-tight text-stone-950">Precio, margen e impuestos</h4>
                                        </div>
                                        <p class="text-sm text-stone-500">Este bloque solo afecta al coste, precio de venta, descuento e IVA.</p>
                                    </div>

                                    <form method="POST" action="{{ route('admin.products.update.pricing', $selectedProduct) }}" @submit="applyPreview()" class="mt-6 grid gap-8 xl:grid-cols-[minmax(0,1.8fr)_minmax(280px,0.7fr)] 2xl:grid-cols-[minmax(0,1.9fr)_minmax(300px,0.68fr)]">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="return_query" value="{{ $searchQuery }}">

                                        <div class="grid gap-6 md:grid-cols-2">
                                            <div>
                                                <x-input-label for="cost_price">Coste base <span class="text-red-600">*</span></x-input-label>
                                                <x-text-input id="cost_price" name="cost_price" type="number" min="0.0001" step="0.0001" class="mt-2 block w-full" x-model="costPrice" @focus="pricingMode = 'margin'" @input="pricingMode = 'margin'" @blur="applyPreview()" required />
                                                <p class="app-helper-text">Se guarda con 4 decimales.</p>
                                                <x-input-error :messages="$errors->get('cost_price')" class="mt-2" />
                                            </div>
                                            <div>
                                                <x-input-label for="tax">IVA (%) <span class="text-red-600">*</span></x-input-label>
                                                <x-text-input id="tax" name="tax" type="number" min="0" step="1" class="mt-2 block w-full" x-model="tax" @blur="applyPreview()" required />
                                                <p class="app-helper-text">Por defecto 21.</p>
                                                <x-input-error :messages="$errors->get('tax')" class="mt-2" />
                                            </div>
                                            <div>
                                                <x-input-label for="margin_multiplier">Multiplicador de margen <span class="text-red-600">*</span></x-input-label>
                                                <x-text-input id="margin_multiplier" name="margin_multiplier" type="number" min="0.01" step="0.0001" class="mt-2 block w-full" x-model="marginMultiplier" @focus="pricingMode = 'margin'" @input="pricingMode = 'margin'" @blur="applyPreview()" required />
                                                <p class="app-helper-text">Vista previa del precio base: <span class="font-semibold text-stone-700" x-text="formatCurrency(previewSalePrice, 2)"></span></p>
                                                <x-input-error :messages="$errors->get('margin_multiplier')" class="mt-2" />
                                            </div>
                                            <div>
                                                <x-input-label for="sale_price">Precio de venta base <span class="text-red-600">*</span></x-input-label>
                                                <x-text-input id="sale_price" name="sale_price" type="number" min="0.01" step="0.01" class="mt-2 block w-full" x-model="salePrice" @focus="pricingMode = 'sale_price'" @input="pricingMode = 'sale_price'" @blur="applyPreview()" required />
                                                <p class="app-helper-text">Vista previa del margen: <span class="font-semibold text-stone-700" x-text="formatNumber(previewMarginMultiplier, 4)"></span></p>
                                                <x-input-error :messages="$errors->get('sale_price')" class="mt-2" />
                                            </div>
                                            <div>
                                                <x-input-label for="discount_type" value="Tipo de descuento" />
                                                <select id="discount_type" name="discount_type" x-model="discountType" class="form-select mt-2" required>
                                                    <option value="fixed" @selected(old('discount_type', $selectedProduct->discount_type) === 'fixed')>Importe fijo</option>
                                                    <option value="percentage" @selected(old('discount_type', $selectedProduct->discount_type) === 'percentage')>Porcentaje</option>
                                                </select>
                                                <x-input-error :messages="$errors->get('discount_type')" class="mt-2" />
                                            </div>
                                            <div>
                                                <x-input-label for="discount_value" value="Valor del descuento" />
                                                <x-text-input id="discount_value" name="discount_value" type="number" min="0" step="0.01" class="mt-2 block w-full" x-model="discountValue" />
                                                <p class="app-helper-text">Precio final con descuento: <span class="font-semibold text-stone-700" x-text="formatCurrency(displayDiscountedSalePriceValue(), 2)"></span></p>
                                                <x-input-error :messages="$errors->get('discount_value')" class="mt-2" />
                                            </div>
                                        </div>

                                        <aside class="app-note-card">
                                            <p class="app-note-kicker">Resumen del precio</p>
                                            <dl class="mt-5 space-y-4 text-sm text-stone-700">
                                                <div class="flex items-center justify-between gap-4">
                                                    <dt>Coste base</dt>
                                                    <dd class="font-bold text-stone-950" x-text="formatCurrency(costPrice, 4)"></dd>
                                                </div>
                                                <div class="flex items-center justify-between gap-4">
                                                    <dt>Margen a guardar</dt>
                                                    <dd class="font-bold text-stone-950" x-text="formatNumber(displayMarginValue(), 4)"></dd>
                                                </div>
                                                <div class="flex items-center justify-between gap-4">
                                                    <dt>IVA aplicado</dt>
                                                    <dd class="font-bold text-stone-950" x-text="formatTax(tax)"></dd>
                                                </div>
                                                <div class="border-t border-amber-200 pt-4">
                                                    <dt class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Precio base a guardar</dt>
                                                    <dd class="mt-2 text-4xl font-black tracking-tight text-stone-950" x-text="formatCurrency(displaySalePriceValue(), 2)"></dd>
                                                </div>
                                                <div class="pt-2">
                                                    <dt class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Precio final con descuento</dt>
                                                    <dd class="mt-2 text-2xl font-black tracking-tight text-emerald-700" x-text="formatCurrency(displayDiscountedSalePriceValue(), 2)"></dd>
                                                </div>
                                            </dl>
                                            <p class="mt-5 text-xs leading-5 text-stone-500">
                                                Formula activa: coste x margen x (1 + IVA / 100). Puedes partir del margen o escribir el precio final.
                                            </p>
                                        </aside>

                                        <input type="hidden" name="pricing_mode" x-model="pricingMode">

                                        <div class="lg:col-span-2 flex justify-end">
                                            <button type="submit" class="app-button-primary">Guardar precio</button>
                                        </div>
                                    </form>
                                </section>
                                </div>
                            @else
                                <section class="app-note-card">
                                    <p class="app-note-kicker">Siguiente paso</p>
                                    <h4 class="mt-2 text-3xl font-black tracking-tight text-stone-950">Selecciona un producto de la lista</h4>
                                    <div class="app-note-copy">
                                        <p>Primero localiza el producto desde el listado de la izquierda. Puedes buscarlo por nombre, categoria o, sobre todo, por codigo de barras.</p>
                                        <p>Cuando elijas uno se abriran dos bloques de trabajo distintos: caracteristicas del producto y precio.</p>
                                    </div>
                                </section>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    @include('admin.products.partials.pricing-form-script')
</x-app-layout>
