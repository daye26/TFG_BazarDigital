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

    $activeProductEditorTab = collect($pricingFields)->contains(fn ($field) => $errors->has($field))
        ? 'price'
        : (request()->string('tab')->toString() === 'price' ? 'price' : 'general');
@endphp

<div
    x-data="{ editorOpen: @js(request()->boolean('edit') || $errors->any()), activeTab: @js($activeProductEditorTab) }"
    class="mt-8 space-y-4"
>
    <div class="store-detail-card store-product-purchase-card">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="store-kicker">Modo admin</p>

            <button type="button" @click="editorOpen = !editorOpen" class="store-button-primary">
                <span x-show="! editorOpen" x-cloak>Editar producto</span>
                <span x-show="editorOpen" x-cloak>Ocultar editor</span>
            </button>
        </div>
    </div>

    <section x-show="editorOpen" x-cloak class="store-detail-card space-y-6">
        @if (session('status'))
            <div class="app-alert-success mb-0">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="app-alert-error mb-0">
                Revisa los campos marcados. Hay cambios que no se han podido guardar.
            </div>
        @endif

        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="store-kicker">Editor rapido</p>
                <h2 class="mt-2 text-2xl font-black tracking-tight text-stone-950">Actualizar producto</h2>
            </div>

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
        </div>

        <section x-show="activeTab === 'general'" x-cloak>
            <form method="POST" action="{{ route('admin.products.update.details', $product) }}" enctype="multipart/form-data" class="grid gap-6">
                @csrf
                @method('PATCH')
                <input type="hidden" name="return_context" value="show">
                <input type="hidden" name="return_tab" value="general">

                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <x-input-label for="show-barcode">Codigo de barras <span class="text-red-600">*</span></x-input-label>
                        <x-text-input id="show-barcode" name="barcode" type="text" class="mt-2 block w-full" :value="old('barcode', $product->barcode)" required />
                        <x-input-error :messages="$errors->get('barcode')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="show-name">Nombre <span class="text-red-600">*</span></x-input-label>
                        <x-text-input id="show-name" name="name" type="text" class="mt-2 block w-full" :value="old('name', $product->name)" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="md:col-span-2">
                        <x-input-label for="show-description" value="Descripcion" />
                        <textarea id="show-description" name="description" rows="4" class="form-textarea mt-2">{{ old('description', $product->description) }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="show-category_id" value="Categoria" />
                        <select id="show-category_id" name="category_id" class="form-select mt-2">
                            <option value="">Sin categoria</option>
                            @foreach ($adminCategories as $category)
                                <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id) === (string) $category->id)>
                                    {{ $category->name }}{{ $category->is_active ? '' : ' (inactiva)' }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="show-qty" value="Stock disponible" />
                        <x-text-input id="show-qty" name="qty" type="number" min="0" step="1" class="mt-2 block w-full" :value="old('qty', $product->qty)" />
                        <x-input-error :messages="$errors->get('qty')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="show-image" value="Sustituir imagen" />
                        <x-text-input id="show-image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="mt-2 block w-full" />
                        <p class="app-helper-text">Sube una nueva imagen solo si quieres reemplazar la actual.</p>
                        <x-input-error :messages="$errors->get('image')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="show-url" value="Slug o URL amigable" />
                        <x-text-input id="show-url" name="url" type="text" class="mt-2 block w-full" :value="old('url', $product->url)" />
                        <x-input-error :messages="$errors->get('url')" class="mt-2" />
                    </div>
                </div>

                <div class="grid gap-4 rounded-[1.5rem] border border-stone-200 bg-stone-50 p-4 sm:grid-cols-2">
                    <label class="app-form-option-card">
                        <input type="hidden" name="remove_image" value="0">
                        <input type="checkbox" name="remove_image" value="1" class="app-checkbox" @checked(old('remove_image') === '1')>
                        <span>
                            <span class="app-form-option-title">Eliminar imagen actual</span>
                            <span class="app-form-option-copy">La imagen visible en la ficha se borrara al guardar.</span>
                        </span>
                    </label>

                    <label class="app-form-option-card">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" class="app-checkbox" @checked((string) old('is_active', $product->is_active ? '1' : '0') === '1')>
                        <span>
                            <span class="app-form-option-title">Producto activo</span>
                            <span class="app-form-option-copy">Si lo desmarcas dejara de mostrarse para clientes.</span>
                        </span>
                    </label>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="store-button-primary px-5 py-3">Guardar cambios</button>
                </div>
            </form>
        </section>

        <section
            x-show="activeTab === 'price'"
            x-cloak
            x-data="productPricingForm({
                costPrice: @js(old('cost_price', number_format((float) $product->cost_price, 4, '.', ''))),
                marginMultiplier: @js(old('margin_multiplier', number_format((float) $product->margin_multiplier, 4, '.', ''))),
                salePrice: @js(old('sale_price', number_format((float) $product->sale_price, 2, '.', ''))),
                discountValue: @js(old('discount_value', number_format((float) $product->discount_value, 2, '.', ''))),
                discountType: @js(old('discount_type', $product->discount_type)),
                tax: @js(old('tax', (string) $product->tax)),
                pricingMode: @js(old('pricing_mode', 'margin')),
            })"
            x-init="init()"
            x-effect="refreshPreview()"
        >
            <form method="POST" action="{{ route('admin.products.update.pricing', $product) }}" @submit="applyPreview()" class="app-pricing-layout">
                @csrf
                @method('PATCH')
                <input type="hidden" name="return_context" value="show">
                <input type="hidden" name="return_tab" value="price">

                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <x-input-label for="show-cost_price">Coste base <span class="text-red-600">*</span></x-input-label>
                        <x-text-input id="show-cost_price" name="cost_price" type="number" min="0.0001" step="0.0001" class="mt-2 block w-full" x-model="costPrice" @focus="pricingMode = 'margin'" @input="pricingMode = 'margin'" @blur="applyPreview()" required />
                        <p class="app-helper-text">Se guarda con 4 decimales.</p>
                        <x-input-error :messages="$errors->get('cost_price')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="show-tax">IVA (%) <span class="text-red-600">*</span></x-input-label>
                        <x-text-input id="show-tax" name="tax" type="number" min="0" step="1" class="mt-2 block w-full" x-model="tax" @blur="applyPreview()" required />
                        <x-input-error :messages="$errors->get('tax')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="show-margin_multiplier">Multiplicador de margen <span class="text-red-600">*</span></x-input-label>
                        <x-text-input id="show-margin_multiplier" name="margin_multiplier" type="number" min="0.01" step="0.0001" class="mt-2 block w-full" x-model="marginMultiplier" @focus="pricingMode = 'margin'" @input="pricingMode = 'margin'" @blur="applyPreview()" required />
                        <p class="app-helper-text">Vista previa del precio base: <span class="font-semibold text-stone-700" x-text="formatCurrency(previewSalePrice, 2)"></span></p>
                        <x-input-error :messages="$errors->get('margin_multiplier')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="show-sale_price">Precio de venta base <span class="text-red-600">*</span></x-input-label>
                        <x-text-input id="show-sale_price" name="sale_price" type="number" min="0.01" step="0.01" class="mt-2 block w-full" x-model="salePrice" @focus="pricingMode = 'sale_price'" @input="pricingMode = 'sale_price'" @blur="applyPreview()" required />
                        <p class="app-helper-text">Vista previa del margen: <span class="font-semibold text-stone-700" x-text="formatNumber(previewMarginMultiplier, 4)"></span></p>
                        <x-input-error :messages="$errors->get('sale_price')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="show-discount_type" value="Tipo de descuento" />
                        <select id="show-discount_type" name="discount_type" x-model="discountType" class="form-select mt-2" required>
                            <option value="fixed" @selected(old('discount_type', $product->discount_type) === 'fixed')>Importe fijo</option>
                            <option value="percentage" @selected(old('discount_type', $product->discount_type) === 'percentage')>Porcentaje</option>
                        </select>
                        <x-input-error :messages="$errors->get('discount_type')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="show-discount_value" value="Valor del descuento" />
                        <x-text-input id="show-discount_value" name="discount_value" type="number" min="0" step="0.01" class="mt-2 block w-full" x-model="discountValue" />
                        <p class="app-helper-text">Precio final con descuento: <span class="font-semibold text-stone-700" x-text="formatCurrency(displayDiscountedSalePriceValue(), 2)"></span></p>
                        <x-input-error :messages="$errors->get('discount_value')" class="mt-2" />
                    </div>
                </div>

                <aside class="rounded-[1.5rem] border border-stone-200 bg-stone-50 p-5">
                    <p class="store-kicker">Resumen</p>
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
                        <div class="border-t border-stone-200 pt-4">
                            <dt class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Precio base</dt>
                            <dd class="mt-2 text-3xl font-black tracking-tight text-stone-950" x-text="formatCurrency(displaySalePriceValue(), 2)"></dd>
                        </div>
                        <div class="pt-2">
                            <dt class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Precio con descuento</dt>
                            <dd class="mt-2 text-xl font-black tracking-tight text-emerald-700" x-text="formatCurrency(displayDiscountedSalePriceValue(), 2)"></dd>
                        </div>
                    </dl>
                </aside>

                <input type="hidden" name="pricing_mode" x-model="pricingMode">

                <div class="xl:col-span-2 flex justify-end">
                    <button type="submit" class="store-button-primary px-5 py-3">Guardar precio</button>
                </div>
            </form>
        </section>
    </section>
</div>
