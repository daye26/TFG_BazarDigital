<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'order_number',
        'user_id',
        'source',
        'pickup_name',
        'status',
        'cancel_reason',
        'payment_method',
        'payment_status',
        'paid_at',
        'payment_reference',
        'notes',
        'subtotal',
        'discount_total',
        'tax_total',
        'total',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_method' => PaymentMethod::class,
            'payment_status' => PaymentStatus::class,
            'paid_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            OrderStatus::PENDING,
            OrderStatus::READY,
        ], true);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === PaymentStatus::PAID;
    }

    public function usesOnlinePayment(): bool
    {
        return $this->payment_method === PaymentMethod::ONLINE;
    }

    public function usesStorePayment(): bool
    {
        return $this->payment_method === PaymentMethod::STORE;
    }

    public function canRetryOnlinePayment(): bool
    {
        return $this->usesOnlinePayment()
            && in_array($this->payment_status, [
                PaymentStatus::PENDING,
                PaymentStatus::FAILED,
            ], true)
            && ! in_array($this->status, [
                OrderStatus::COMPLETED,
                OrderStatus::CANCELLED,
            ], true);
    }

    public function canSwitchToStorePayment(): bool
    {
        return $this->canRetryOnlinePayment();
    }

    public function canBePrepared(): bool
    {
        if ($this->status === OrderStatus::COMPLETED || $this->status === OrderStatus::CANCELLED) {
            return false;
        }

        return $this->usesStorePayment() || $this->isPaid();
    }
}
