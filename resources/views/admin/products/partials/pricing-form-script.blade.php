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
