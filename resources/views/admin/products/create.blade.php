<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Nuevo producto
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
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-amber-200">Gestion de productos</p>
                    <h3 class="mt-2 text-3xl font-black tracking-tight">Crear producto</h3>
                    <p class="mt-4 max-w-3xl text-sm leading-6 text-stone-200">
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
                    class="p-6 lg:p-8"
                >
                    @if ($errors->any())
                        <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800">
                            Revisa los campos marcados. Hay datos que no se han podido guardar.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" @submit="applyPreview()" class="grid gap-8 lg:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
                        @csrf

                        <div class="space-y-8">
                            <section class="rounded-3xl border border-stone-200 bg-stone-50 p-6">
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
                                        <textarea id="description" name="description" rows="4" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="category_id" value="Categoria" />
                                        <select id="category_id" name="category_id" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
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
                                        <p class="mt-2 text-xs text-stone-500">Formatos permitidos: JPG, PNG y WEBP. Maximo 2 MB.</p>
                                        <p x-cloak x-show="imageClientError" x-text="imageClientError" class="mt-2 text-sm text-rose-600"></p>
                                        <x-input-error :messages="$errors->get('image')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="url" value="Slug o URL amigable" />
                                        <x-text-input id="url" name="url" type="text" class="mt-2 block w-full" :value="old('url')" />
                                        <x-input-error :messages="$errors->get('url')" class="mt-2" />
                                    </div>

                                    <div class="md:col-span-2">
                                        <div class="rounded-2xl border border-dashed border-stone-300 bg-white p-4">
                                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Vista previa de imagen</p>
                                            <div class="mt-4 flex min-h-56 items-center justify-center overflow-hidden rounded-2xl bg-stone-100">
                                                <template x-if="imagePreviewUrl">
                                                    <img :src="imagePreviewUrl" :alt="imageFileName || 'Vista previa de imagen'" class="h-full max-h-72 w-full object-contain">
                                                </template>
                                                <template x-if="!imagePreviewUrl">
                                                    <span class="px-4 text-center text-sm font-medium text-stone-500">
                                                        La imagen se mostrara aqui en cuanto la selecciones.
                                                    </span>
                                                </template>
                                            </div>
                                            <p class="mt-3 text-xs text-stone-500" x-show="imageFileName" x-text="imageFileName"></p>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm">
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
                                        <p class="mt-2 text-xs text-stone-500">Se guarda con 4 decimales.</p>
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
                                        <p class="mt-2 text-xs text-stone-500">Por defecto 21.</p>
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
                                        <p class="mt-2 text-xs text-stone-500">
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
                                        <p class="mt-2 text-xs text-stone-500">
                                            Incluye IVA. Vista previa del margen:
                                            <span class="font-semibold text-stone-700" x-text="formatNumber(previewMarginMultiplier, 4)"></span>
                                        </p>
                                        <x-input-error :messages="$errors->get('sale_price')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="discount_type" value="Tipo de descuento" />
                                        <select id="discount_type" name="discount_type" x-model="discountType" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
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
                                        <p class="mt-2 text-xs text-stone-500">
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
                            <section class="rounded-3xl border border-stone-200 bg-amber-50 p-6 shadow-sm">
                                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-amber-700">Resumen</p>
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
                                        <span class="block text-sm font-bold text-stone-900">Producto activo</span>
                                        <span class="mt-1 block text-xs leading-5 text-stone-500">Si lo desmarcas se crea oculto para la tienda publica.</span>
                                    </span>
                                </label>

                                <button type="submit" class="mt-6 inline-flex w-full items-center justify-center rounded-full bg-stone-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-stone-700">
                                    Crear producto
                                </button>
                            </section>
                        </aside>
                    </form>
                </div>
            </section>
        </div>
    </div>

    <script>
        function productPricingForm(initialState) {
            return {
                costPrice: initialState.costPrice || '',
                marginMultiplier: initialState.marginMultiplier || '2.0000',
                salePrice: initialState.salePrice || '',
                discountValue: initialState.discountValue || '0',
                discountType: initialState.discountType || 'fixed',
                tax: initialState.tax || '21',
                pricingMode: initialState.pricingMode || 'margin',
                imagePreviewUrl: initialState.imagePreviewUrl || null,
                imageFileName: initialState.imageFileName || '',
                imageClientError: initialState.imageClientError || '',
                previewSalePrice: '0.00',
                previewMarginMultiplier: initialState.marginMultiplier || '2.0000',
                init() {
                    if (this.marginMultiplier === '') {
                        this.marginMultiplier = '2.0000';
                    }

                    if (this.tax === '') {
                        this.tax = '21';
                    }

                    this.refreshPreview();
                },
                async handleImageSelection(event) {
                    const file = event.target.files?.[0];

                    this.resetImagePreview();

                    if (! file) {
                        return;
                    }

                    const validationError = await this.validateSelectedImage(file);

                    if (validationError) {
                        this.imageClientError = validationError;
                        event.target.value = '';

                        return;
                    }

                    this.imagePreviewUrl = URL.createObjectURL(file);
                    this.imageFileName = file.name;
                },
                resetImagePreview() {
                    if (this.imagePreviewUrl) {
                        URL.revokeObjectURL(this.imagePreviewUrl);
                    }

                    this.imagePreviewUrl = null;
                    this.imageFileName = '';
                    this.imageClientError = '';
                },
                async validateSelectedImage(file) {
                    const allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
                    const allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                    const extension = file.name.split('.').pop()?.toLowerCase() || '';
                    const maxSizeInBytes = 2 * 1024 * 1024;

                    if (! allowedExtensions.includes(extension) || ! allowedMimeTypes.includes(file.type)) {
                        return 'La imagen no es valida. Solo se permiten archivos JPG, PNG o WEBP.';
                    }

                    if (file.size > maxSizeInBytes) {
                        return 'La imagen supera el tamano maximo permitido de 2 MB.';
                    }

                    return await this.validateImageDimensions(file);
                },
                validateImageDimensions(file) {
                    return new Promise((resolve) => {
                        const probeUrl = URL.createObjectURL(file);
                        const image = new Image();

                        image.onload = () => {
                            const isValid = image.naturalWidth <= 3000 && image.naturalHeight <= 3000;
                            URL.revokeObjectURL(probeUrl);

                            resolve(isValid ? '' : 'La imagen supera el maximo permitido de 3000 x 3000 px.');
                        };

                        image.onerror = () => {
                            URL.revokeObjectURL(probeUrl);
                            resolve('No se ha podido procesar el archivo como una imagen valida.');
                        };

                        image.src = probeUrl;
                    });
                },
                parseNumber(value) {
                    const parsed = Number.parseFloat(value);

                    return Number.isFinite(parsed) ? parsed : 0;
                },
                normalize(value, decimals) {
                    return this.parseNumber(value).toFixed(decimals);
                },
                calculateSalePrice() {
                    const cost = this.parseNumber(this.costPrice);
                    const margin = this.parseNumber(this.marginMultiplier);
                    const tax = this.parseNumber(this.tax);

                    return cost * margin * (1 + (tax / 100));
                },
                calculateMarginMultiplier() {
                    const cost = this.parseNumber(this.costPrice);
                    const salePrice = this.parseNumber(this.salePrice);
                    const tax = this.parseNumber(this.tax);
                    const divisor = cost * (1 + (tax / 100));

                    return divisor > 0 ? salePrice / divisor : 0;
                },
                refreshPreview() {
                    if (this.pricingMode === 'sale_price') {
                        this.previewSalePrice = this.normalize(this.salePrice || 0, 2);
                        this.previewMarginMultiplier = this.normalize(this.calculateMarginMultiplier(), 4);

                        return;
                    }

                    this.previewSalePrice = this.normalize(this.calculateSalePrice(), 2);
                    this.previewMarginMultiplier = this.normalize(this.marginMultiplier || 0, 4);
                },
                applyPreview() {
                    if (this.pricingMode === 'sale_price') {
                        this.salePrice = this.normalize(this.salePrice || 0, 2);
                        this.marginMultiplier = this.previewMarginMultiplier;

                        return;
                    }

                    this.marginMultiplier = this.normalize(this.marginMultiplier || 0, 4);
                    this.salePrice = this.previewSalePrice;
                },
                displayMarginValue() {
                    return this.pricingMode === 'sale_price'
                        ? this.previewMarginMultiplier
                        : (this.marginMultiplier || 0);
                },
                displaySalePriceValue() {
                    return this.pricingMode === 'sale_price'
                        ? (this.salePrice || 0)
                        : this.previewSalePrice;
                },
                calculateDiscountedSalePrice(basePrice) {
                    const salePrice = this.parseNumber(basePrice);
                    const discountValue = this.parseNumber(this.discountValue);

                    if (discountValue <= 0) {
                        return salePrice;
                    }

                    if (this.discountType === 'percentage') {
                        return Math.max(salePrice - (salePrice * discountValue / 100), 0);
                    }

                    return Math.max(salePrice - discountValue, 0);
                },
                displayDiscountedSalePriceValue() {
                    return this.normalize(this.calculateDiscountedSalePrice(this.displaySalePriceValue()), 2);
                },
                formatCurrency(value, decimals) {
                    return `${this.formatNumber(value, decimals)} EUR`;
                },
                formatNumber(value, decimals) {
                    return this.parseNumber(value).toFixed(decimals);
                },
                formatTax(value) {
                    return `${Math.round(this.parseNumber(value))}%`;
                },
            };
        }
    </script>
</x-app-layout>
