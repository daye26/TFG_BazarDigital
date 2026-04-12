<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function discountedPriceAmount(): float
    {
        $salePrice = (float) $this->sale_price;
        $discountValue = (float) $this->discount_value;

        if ($discountValue <= 0) {
            return $salePrice;
        }

        $discountedPrice = match ($this->discount_type) {
            'percentage' => $salePrice - ($salePrice * $discountValue / 100),
            'fixed' => $salePrice - $discountValue,
            default => throw new InvalidArgumentException('Unsupported discount type.'),
        };

        return max($discountedPrice, 0);
    }

    public function getDiscountedPriceAttribute(): string
    {
        return number_format($this->discountedPriceAmount(), 2, '.', '');
    }

    public function getHasDiscountAttribute(): bool
    {
        return (float) $this->discount_value > 0;
    }

    public function scopeOrderByDiscountedPrice(Builder $query, string $direction = 'asc'): Builder
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return $query->orderByRaw(
            self::discountedPriceSortExpression().' '.$direction,
            ['percentage', 'fixed']
        );
    }

    public static function discountedPriceSortExpression(): string
    {
        return <<<'SQL'
CASE
    WHEN discount_value <= 0 THEN sale_price
    WHEN discount_type = ? THEN
        CASE
            WHEN sale_price - (sale_price * discount_value / 100) < 0 THEN 0
            ELSE sale_price - (sale_price * discount_value / 100)
        END
    WHEN discount_type = ? THEN
        CASE
            WHEN sale_price - discount_value < 0 THEN 0
            ELSE sale_price - discount_value
        END
    ELSE sale_price
END
SQL;
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
