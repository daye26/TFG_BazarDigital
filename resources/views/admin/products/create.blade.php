<x-app-layout>
    <x-slot name="header">
        <x-admin.panel-header
            title="Nuevo producto"
            active="products"
            :back-href="route('admin.index')"
            back-label="Volver al panel"
        />
    </x-slot>

    <div class="app-page">
        <div class="app-shell-stack">
            <section class="app-surface">
                <div class="app-hero">
                    <p class="app-hero-kicker">Gestion de productos</p>
                    <h3 class="app-hero-title">Crear producto</h3>
                    <p class="app-hero-copy">
                        Completa la ficha, revisa la imagen y valida el precio antes de guardar el producto en el catalogo.
                    </p>
                </div>

                <div
                    x-data="productPricingForm({
                        costPrice: @js(old('cost_price', '')),
                        marginMultiplier: @js(old('margin_multiplier', '2.0000')),
                        salePrice: @js(old('sale_price', '')),
                        discountValue: @js(old('discount_value', '0')),
                        discountType: @js(old('discount_type', 'fixed')),
                        tax: @js(old('tax', '21')),
                        pricingMode: @js(old('pricing_mode', 'margin')),
                    })"
                    x-init="init()"
                    x-effect="refreshPreview()"
                    class="app-surface-body"
                >
                    @if ($errors->any())
                        <div class="app-alert-error">
                            Revisa los campos marcados. Hay datos que no se han podido guardar.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" @submit="applyPreview()" class="grid gap-8 lg:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
                        @csrf

                        <div class="space-y-8">
                            <section class="app-card-muted">
                                <h4 class="text-lg font-bold text-stone-900">Ficha del producto</h4>
                                <div class="mt-6 grid gap-6 md:grid-cols-2">
                                    <div>
                                        <x-input-label for="barcode">
                                            Codigo de barras <span class="text-red-600">*</span>
                                        </x-input-label>
                                        <x-text-input id="barcode" name="barcode" type="text" class="mt-2 block w-full" :value="old('barcode')" required />
                                        <x-input-error :messages="$errors->get('barcode')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="name">
                                            Nombre <span class="text-red-600">*</span>
                                        </x-input-label>
                                        <x-text-input id="name" name="name" type="text" class="mt-2 block w-full" :value="old('name')" required />
                                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                    </div>

                                    <div class="md:col-span-2">
                                        <x-input-label for="description" value="Descripcion" />
                                        <textarea id="description" name="description" rows="4" class="form-textarea mt-2">{{ old('description') }}</textarea>
                                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="category_id" value="Categoria" />
                                        <select id="category_id" name="category_id" class="form-select mt-2">
                                            <option value="">Sin categoria</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}" @selected((string) old('category_id') === (string) $category->id)>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="qty" value="Stock inicial" />
                                        <x-text-input id="qty" name="qty" type="number" min="0" step="1" class="mt-2 block w-full" :value="old('qty', 0)" />
                                        <x-input-error :messages="$errors->get('qty')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="image" value="Imagen del producto" />
                                        <x-text-input
                                            id="image"
                                            name="image"
                                            type="file"
                                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                            class="mt-2 block w-full"
                                            @change="handleImageSelection($event)"
                                        />
                                        <p class="app-helper-text">Formatos permitidos: JPG, PNG y WEBP. Maximo 2 MB.</p>
                                        <p x-cloak x-show="imageClientError" x-text="imageClientError" class="mt-2 text-sm text-rose-600"></p>
                                        <x-input-error :messages="$errors->get('image')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="url" value="Slug o URL amigable" />
                                        <x-text-input id="url" name="url" type="text" class="mt-2 block w-full" :value="old('url')" />
                                        <x-input-error :messages="$errors->get('url')" class="mt-2" />
                                    </div>

                                    <div class="md:col-span-2">
                                        <div class="app-image-preview-card">
                                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Vista previa de imagen</p>
                                            <div class="app-image-preview-media">
                                                <template x-if="imagePreviewUrl">
                                                    <img :src="imagePreviewUrl" :alt="imageFileName || 'Vista previa de imagen'" class="h-full max-h-72 w-full object-contain">
                                                </template>
                                                <template x-if="!imagePreviewUrl">
                                                    <span class="px-4 text-center text-sm font-medium text-stone-500">
                                                        La imagen se mostrara aqui en cuanto la selecciones.
                                                    </span>
                                                </template>
                                            </div>
                                            <p class="app-helper-text mt-3" x-show="imageFileName" x-text="imageFileName"></p>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="app-card">
                                <h4 class="text-lg font-bold text-stone-900">Precios e impuestos</h4>
                                <div class="mt-6 grid gap-6 md:grid-cols-2">
                                    <div>
                                        <x-input-label for="cost_price">
                                            Coste base <span class="text-red-600">*</span>
                                        </x-input-label>
                                        <x-text-input
                                            id="cost_price"
                                            name="cost_price"
                                            type="number"
                                            min="0.0001"
                                            step="0.0001"
                                            class="mt-2 block w-full"
                                            x-model="costPrice"
                                            @focus="pricingMode = 'margin'"
                                            @input="pricingMode = 'margin'"
                                            @blur="applyPreview()"
                                            required
                                        />
                                        <p class="app-helper-text">Se guarda con 4 decimales.</p>
                                        <x-input-error :messages="$errors->get('cost_price')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="tax">
                                            IVA (%) <span class="text-red-600">*</span>
                                        </x-input-label>
                                        <x-text-input
                                            id="tax"
                                            name="tax"
                                            type="number"
                                            min="0"
                                            step="1"
                                            class="mt-2 block w-full"
                                            x-model="tax"
                                            @blur="applyPreview()"
                                            required
                                        />
                                        <p class="app-helper-text">Por defecto 21.</p>
                                        <x-input-error :messages="$errors->get('tax')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="margin_multiplier">
                                            Multiplicador de margen <span class="text-red-600">*</span>
                                        </x-input-label>
                                        <x-text-input
                                            id="margin_multiplier"
                                            name="margin_multiplier"
                                            type="number"
                                            min="0.01"
                                            step="0.0001"
                                            class="mt-2 block w-full"
                                            x-model="marginMultiplier"
                                            @focus="pricingMode = 'margin'"
                                            @input="pricingMode = 'margin'"
                                            @blur="applyPreview()"
                                            required
                                        />
                                        <p class="app-helper-text">
                                            Valor inicial 2.0000. Vista previa del precio base:
                                            <span class="font-semibold text-stone-700" x-text="formatCurrency(previewSalePrice, 2)"></span>
                                        </p>
                                        <x-input-error :messages="$errors->get('margin_multiplier')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="sale_price">
                                            Precio de venta base <span class="text-red-600">*</span>
                                        </x-input-label>
                                        <x-text-input
                                            id="sale_price"
                                            name="sale_price"
                                            type="number"
                                            min="0.01"
                                            step="0.01"
                                            class="mt-2 block w-full"
                                            x-model="salePrice"
                                            @focus="pricingMode = 'sale_price'"
                                            @input="pricingMode = 'sale_price'"
                                            @blur="applyPreview()"
                                            required
                                        />
                                        <p class="app-helper-text">
                                            Incluye IVA. Vista previa del margen:
                                            <span class="font-semibold text-stone-700" x-text="formatNumber(previewMarginMultiplier, 4)"></span>
                                        </p>
                                        <x-input-error :messages="$errors->get('sale_price')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="discount_type" value="Tipo de descuento" />
                                        <select id="discount_type" name="discount_type" x-model="discountType" class="form-select mt-2" required>
                                            <option value="fixed" @selected(old('discount_type', 'fixed') === 'fixed')>Importe fijo</option>
                                            <option value="percentage" @selected(old('discount_type') === 'percentage')>Porcentaje</option>
                                        </select>
                                        <x-input-error :messages="$errors->get('discount_type')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="discount_value" value="Valor del descuento" />
                                        <x-text-input
                                            id="discount_value"
                                            name="discount_value"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            class="mt-2 block w-full"
                                            x-model="discountValue"
                                        />
                                        <p class="app-helper-text">
                                            Vista previa del precio con descuento:
                                            <span class="font-semibold text-stone-700" x-text="formatCurrency(displayDiscountedSalePriceValue(), 2)"></span>
                                        </p>
                                        <x-input-error :messages="$errors->get('discount_value')" class="mt-2" />
                                    </div>
                                </div>

                                <input type="hidden" name="pricing_mode" x-model="pricingMode">
                            </section>
                        </div>

                        <aside class="space-y-6">
                            <section class="app-note-card">
                                <p class="app-note-kicker">Resumen</p>
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
                                    Formula activa: coste x margen x (1 + IVA / 100). La vista previa se actualiza mientras escribes.
                                </p>
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
                                        <span class="block text-sm font-bold text-stone-900">Producto activo</span>
                                        <span class="mt-1 block text-xs leading-5 text-stone-500">Si lo desmarcas se crea oculto para la tienda publica.</span>
                                    </span>
                                </label>

                                <button type="submit" class="app-button-primary mt-6 w-full">
                                    Crear producto
                                </button>
                            </section>
                        </aside>
                    </form>
                </div>
            </section>
        </div>
    </div>

    @include('admin.products.partials.pricing-form-script')
</x-app-layout>
