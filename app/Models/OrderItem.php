<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'quantity',
        'tax',
        'unit_price',
        'discount_type',
        'discount_value',
        'unit_final_price',
        'line_total',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'tax' => 'integer',
            'unit_price' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'unit_final_price' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function hasDiscount(): bool
    {
        return $this->discountCents() > 0;
    }

    public function baseLineTotalAmount(): float
    {
        return $this->baseLineTotalCents() / 100;
    }

    protected function baseLineTotalCents(): int
    {
        return $this->amountToCents($this->unit_price) * $this->quantity;
    }

    protected function lineTotalCents(): int
    {
        return $this->amountToCents($this->line_total);
    }

    protected function discountCents(): int
    {
        return max($this->baseLineTotalCents() - $this->lineTotalCents(), 0);
    }

    protected function amountToCents(float|int|string|null $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }
}
