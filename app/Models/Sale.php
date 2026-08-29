<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'invoice_number',
        'customer_id',
        'cashier_id',
        'status',
        'subtotal',
        'discount_total',
        'grand_total',
        'due_date',
        'notes',
        'sold_at',
    ];

    protected $appends = [
        'paid_amount',
        'outstanding_amount',
        'payment_status',
        'total_amount',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'due_date' => 'date',
            'sold_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getPaidAmountAttribute(): float
    {
        if ($this->relationLoaded('payments')) {
            return (float) $this->payments
                ->whereNotIn('payment_method', ['receivable', 'credit'])
                ->sum('amount');
        }

        return (float) $this->payments()
            ->whereNotIn('payment_method', ['receivable', 'credit'])
            ->sum('amount');
    }

    public function getOutstandingAmountAttribute(): float
    {
        return max(0.0, (float) $this->grand_total - $this->paid_amount);
    }

    public function getPaymentStatusAttribute(): string
    {
        $grand = (float) $this->grand_total;
        $paid = $this->paid_amount;

        if ($grand <= 0 || $paid >= $grand) {
            return 'paid';
        }

        if ($paid > 0) {
            return 'partial';
        }

        return 'unpaid';
    }

    public function getTotalAmountAttribute(): float
    {
        return (float) $this->subtotal;
    }
}
