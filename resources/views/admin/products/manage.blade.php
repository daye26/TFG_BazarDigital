<x-app-layout>
    <div class="app-page">
        <div class="app-shell-stack-wide">
            <section class="app-surface">
                <x-admin.page-hero
                    kicker="Gestion de productos"
                    title="Actualizar producto"
                    description="Busca por codigo de barras, nombre o categoria, abre productos activos o inactivos y separa la edicion entre ficha y precio."
                />

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
                        $currentProductsPage = method_exists($products, 'currentPage')
                            ? $products->currentPage()
                            : 1;
                    @endphp

                    <div class="app-admin-manage-layout-wide">
                        <aside class="space-y-6">
                            <section class="app-card-muted">
                                <p class="app-section-kicker">Buscador admin</p>
                                <h4 class="app-section-title">Encuentra el producto correcto</h4>
                                <p class="app-lead-copy">
                                    Este buscador da prioridad al codigo de barras para localizar antes el producto que quieres corregir.
                                </p>

                                <form method="GET" action="{{ route('admin.products.manage') }}" class="mt-6 space-y-4">
                                    @if ($productScope !== 'all')
                                        <input type="hidden" name="scope" value="{{ $productScope }}">
                                    @endif
                                    @if ($stockFilter !== '')
                                        <input type="hidden" name="stock" value="{{ $stockFilter }}">
                                    @endif

                                    <label for="admin-product-search" class="sr-only">Buscar productos para editar</label>
                                    <div class="app-search-shell">
                                        <input
                                            id="admin-product-search"
                                            name="q"
                                            type="search"
                                            value="{{ $searchQuery }}"
                                            class="app-search-input"
                                            placeholder="Codigo de barras, nombre o categoria"
                                        >
                                    </div>

                                    <div class="flex flex-wrap gap-3">
                                        <button type="submit" class="app-button-primary">Buscar producto</button>

                                        @if ($searchQuery !== '')
                                            <a href="{{ route('admin.products.manage', array_filter([
                                                'scope' => $productScope !== 'all' ? $productScope : null,
                                                'stock' => $stockFilter !== '' ? $stockFilter : null,
                                            ])) }}" class="app-button-secondary">Limpiar busqueda</a>
                                        @endif
                                    </div>
                                </form>
                            </section>

                            <section class="app-card">
                                <x-admin.section-header
                                    kicker="Lista de productos"
                                    :title="$stockFilter === 'out'
                                        ? 'Productos sin stock'
                                        : ($stockFilter === 'low'
                                            ? 'Productos con stock bajo (<= ' . $lowStockThreshold . ' uds)'
                                            : ($searchQuery !== ''
                                                ? 'Resultados de busqueda'
                                                : ($productScope === 'active'
                                                    ? 'Productos activos'
                                                    : ($productScope === 'inactive'
                                                        ? 'Productos ocultos'
                                                        : 'Todos los productos'))))"
                                >
                                    <x-slot name="aside">
                                        <a href="{{ route('admin.products.create') }}" class="app-button-secondary">Nuevo producto</a>
                                    </x-slot>
                                </x-admin.section-header>

                                <div class="mt-6 flex flex-wrap gap-3">
                                    <a href="{{ route('admin.products.manage', array_filter(['q' => $searchQuery !== '' ? $searchQuery : null])) }}" class="{{ $stockFilter === '' ? 'app-button-primary' : 'app-button-secondary' }}">Todos</a>
                                    <a href="{{ route('admin.products.manage', array_filter(['stock' => 'out', 'q' => $searchQuery !== '' ? $searchQuery : null])) }}" class="{{ $stockFilter === 'out' ? 'app-button-primary' : 'app-button-secondary' }}">Sin stock</a>
                                    <a href="{{ route('admin.products.manage', array_filter(['stock' => 'low', 'q' => $searchQuery !== '' ? $searchQuery : null])) }}" class="{{ $stockFilter === 'low' ? 'app-button-primary' : 'app-button-secondary' }}">Stock bajo</a>
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
                                                href="{{ route('admin.products.manage', array_filter([
                                                    'product' => $listedProduct->id,
                                                    'q' => $searchQuery !== '' ? $searchQuery : null,
                                                    'scope' => $productScope !== 'all' ? $productScope : null,
                                                    'stock' => $stockFilter !== '' ? $stockFilter : null,
                                                    'page' => $currentProductsPage > 1 ? $currentProductsPage : null,
                                                ])) }}"
                                                class="{{ $isSelected ? 'app-product-picker-card app-product-picker-card-active' : 'app-product-picker-card' }}"
                                            >
                                                <div class="app-product-picker-head">
                                                    <div class="min-w-0 sm:pr-2">
                                                        <div class="flex flex-wrap items-center gap-2">
                                                            <p class="app-picker-title {{ $isSelected ? 'text-white' : 'text-stone-950' }}">
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
                                                            <p class="app-picker-stat-label {{ $isSelected ? 'text-stone-300' : 'text-stone-500' }}">
                                                                Precio
                                                            </p>
                                                            <p class="app-picker-stat-value {{ $isSelected ? 'text-white' : 'text-stone-950' }}">
                                                                {{ number_format((float) $listedProduct->discounted_price, 2, ',', '.') }} &euro;
                                                            </p>
                                                        </article>

                                                        <article class="{{ $isSelected ? 'app-product-picker-stat app-product-picker-stat-active' : 'app-product-picker-stat' }}">
                                                            <p class="app-picker-stat-label {{ $isSelected ? 'text-stone-300' : 'text-stone-500' }}">
                                                                Stock
                                                            </p>
                                                            <p class="app-picker-stat-value {{ $isSelected ? 'text-white' : 'text-stone-950' }}">
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
                                        <div class="app-empty-card">
                                            {{ $stockFilter === 'out' ? 'No hay productos activos sin stock.' : ($stockFilter === 'low' ? 'No hay productos activos con stock bajo.' : 'No hemos encontrado productos para esa busqueda.') }}
                                        </div>
                                    @endforelse
                                    </div>

                                    @if ($products->hasPages())
                                        <div class="border-t border-stone-200 pt-5">
                                            {{ $products->links() }}
                                        </div>
                                    @endif
                                </div>
                            </section>
                        </aside>

                        <div class="space-y-6">
                            @if ($selectedProduct)
                                <div x-data="{ activeTab: @js($activeEditorTab) }" class="space-y-6">
                                <section class="app-note-card">
                                    <div class="app-split-layout">
                                        <div>
                                            <p class="app-note-kicker">Producto seleccionado</p>
                                            <h4 class="app-note-title">{{ $selectedProduct->name }}</h4>
                                            <p class="app-lead-copy">
                                                Codigo {{ $selectedProduct->barcode }} · {{ $selectedProduct->category?->name ?? 'Sin categoria' }} · {{ $selectedProduct->is_active ? 'Visible en tienda' : 'Oculto en tienda' }}
                                            </p>
                                        </div>

                                        <div class="grid gap-3 sm:grid-cols-2">
                                            <article class="app-inline-stat-card">
                                                <p class="app-stat-label">Precio actual</p>
                                                <p class="app-inline-stat-value">{{ number_format((float) $selectedProduct->sale_price, 2, ',', '.') }} &euro;</p>
                                            </article>
                                            <article class="app-inline-stat-card">
                                                <p class="app-stat-label">Stock actual</p>
                                                <p class="app-inline-stat-value">{{ $selectedProduct->qty }}</p>
                                            </article>
                                        </div>
                                    </div>
                                </section>

                                <section class="app-card">
                                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="app-segmented-control">
                                            <button
                                                type="button"
                                                @click="activeTab = 'general'"
                                                :class="activeTab === 'general' ? 'app-segmented-control-button-active' : 'app-segmented-control-button-idle'"
                                                class="app-segmented-control-button"
                                            >
                                                General
                                            </button>
                                            <button
                                                type="button"
                                                @click="activeTab = 'price'"
                                                :class="activeTab === 'price' ? 'app-segmented-control-button-active' : 'app-segmented-control-button-idle'"
                                                class="app-segmented-control-button"
                                            >
                                                Precio
                                            </button>
                                        </div>

                                        <p class="app-section-description">Cambia entre la ficha general y el bloque de precio.</p>
                                    </div>
                                </section>

                                <section class="app-card" x-show="activeTab === 'general'" x-cloak>
                                    <x-admin.section-header kicker="Caracteristicas" title="Ficha del producto">
                                        <x-slot name="aside">
                                            <p class="app-section-description">Aqui cambias datos descriptivos, stock, imagen, categoria y estado.</p>
                                        </x-slot>
                                    </x-admin.section-header>

                                    <form method="POST" action="{{ route('admin.products.update.details', $selectedProduct) }}" enctype="multipart/form-data" class="mt-6 space-y-6">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="return_query" value="{{ $searchQuery }}">
                                        <input type="hidden" name="return_scope" value="{{ $productScope }}">
                                        <input type="hidden" name="return_stock" value="{{ $stockFilter }}">
                                        <input type="hidden" name="return_page" value="{{ $currentProductsPage }}">

                                        <div class="app-form-grid-2">
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
                                                <div class="app-product-media-layout">
                                                    <div class="app-image-preview-card bg-stone-50">
                                                        <p class="app-meta-label">Imagen actual</p>
                                                        <div class="app-image-preview-media min-h-52 bg-white">
                                                            @if ($selectedProduct->image_url)
                                                                <img src="{{ $selectedProduct->image_url }}" alt="Imagen actual de {{ $selectedProduct->name }}" class="h-full max-h-64 w-full object-contain">
                                                            @else
                                                                <span class="app-placeholder-copy">Este producto no tiene imagen guardada.</span>
                                                            @endif
                                                        </div>
                                                        @if ($selectedProduct->image)
                                                            <p class="app-helper-text mt-3">{{ basename($selectedProduct->image) }}</p>
                                                        @endif
                                                    </div>

                                                    <div class="space-y-4 rounded-2xl border border-stone-200 bg-white p-4">
                                                        <label class="app-form-option-card">
                                                            <input type="hidden" name="remove_image" value="0">
                                                            <input type="checkbox" name="remove_image" value="1" class="app-checkbox" @checked(old('remove_image') === '1')>
                                                            <span>
                                                                <span class="app-form-option-title">Eliminar imagen actual</span>
                                                                <span class="app-form-option-copy">Si subes una nueva imagen, la anterior se reemplazara automaticamente.</span>
                                                            </span>
                                                        </label>
                                                        <x-input-error :messages="$errors->get('remove_image')" class="mt-2" />

                                                        <label class="app-form-option-card">
                                                            <input type="hidden" name="is_active" value="0">
                                                            <input type="checkbox" name="is_active" value="1" class="app-checkbox" @checked((string) old('is_active', $selectedProduct->is_active ? '1' : '0') === '1')>
                                                            <span>
                                                                <span class="app-form-option-title">Producto activo</span>
                                                                <span class="app-form-option-copy">Si lo desmarcas dejara de mostrarse en la tienda publica.</span>
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
                                    <x-admin.section-header kicker="Precio" title="Precio, margen e impuestos">
                                        <x-slot name="aside">
                                            <p class="app-section-description">Este bloque solo afecta al coste, precio de venta, descuento e IVA.</p>
                                        </x-slot>
                                    </x-admin.section-header>

                                    <form method="POST" action="{{ route('admin.products.update.pricing', $selectedProduct) }}" @submit="applyPreview()" class="app-pricing-layout-wide mt-6">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="return_query" value="{{ $searchQuery }}">
                                        <input type="hidden" name="return_scope" value="{{ $productScope }}">
                                        <input type="hidden" name="return_stock" value="{{ $stockFilter }}">
                                        <input type="hidden" name="return_page" value="{{ $currentProductsPage }}">

                                        <div class="app-form-grid-2">
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
                                            <dl class="app-summary-list mt-5">
                                                <div class="app-summary-row">
                                                    <dt>Coste base</dt>
                                                    <dd class="app-summary-value" x-text="formatCurrency(costPrice, 4)"></dd>
                                                </div>
                                                <div class="app-summary-row">
                                                    <dt>Margen a guardar</dt>
                                                    <dd class="app-summary-value" x-text="formatNumber(displayMarginValue(), 4)"></dd>
                                                </div>
                                                <div class="app-summary-row">
                                                    <dt>IVA aplicado</dt>
                                                    <dd class="app-summary-value" x-text="formatTax(tax)"></dd>
                                                </div>
                                                <div class="border-t border-amber-200 pt-4">
                                                    <dt class="app-meta-label">Precio base a guardar</dt>
                                                    <dd class="app-summary-emphasis-value" x-text="formatCurrency(displaySalePriceValue(), 2)"></dd>
                                                </div>
                                                <div class="pt-2">
                                                    <dt class="app-meta-label">Precio final con descuento</dt>
                                                    <dd class="app-summary-emphasis-value-success" x-text="formatCurrency(displayDiscountedSalePriceValue(), 2)"></dd>
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
                                    <h4 class="app-note-title">Selecciona un producto de la lista</h4>
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
