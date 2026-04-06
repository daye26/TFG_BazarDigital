<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'barcode',
        'name',
        'description',
        'tax',
        'cost_price',
        'sale_price',
        'margin_multiplier',
        'discount_value',
        'discount_type',
        'qty',
        'image',
        'url',
        'category_id',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tax' => 'integer',
            'cost_price' => 'decimal:4',
            'sale_price' => 'decimal:2',
            'margin_multiplier' => 'decimal:4',
            'discount_value' => 'decimal:2',
            'qty' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function getDiscountedPriceAttribute(): string
    {
        $salePrice = (float) $this->sale_price;
        $discountValue = (float) $this->discount_value;

        if ($discountValue <= 0) {
            return number_format($salePrice, 2, '.', '');
        }

        $discountedPrice = match ($this->discount_type) {
            'percentage' => $salePrice - ($salePrice * $discountValue / 100),
            'fixed' => $salePrice - $discountValue,
            default => throw new InvalidArgumentException('Unsupported discount type.'),
        };

        return number_format(max($discountedPrice, 0), 2, '.', '');
    }

    public function getHasDiscountAttribute(): bool
    {
        return (float) $this->discount_value > 0;
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return Storage::disk('public')->url($this->image);
    }

    public function getIsNewAttribute(): bool
    {
        if (! $this->created_at) {
            return false;
        }

        return $this->created_at->greaterThanOrEqualTo(now()->subDays(14));
    }
}
