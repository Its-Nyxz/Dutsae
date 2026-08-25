<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'code',
        'name',
        'phone',
        'address',
        'credit_limit',
        'payment_terms_days',
    ];

    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:2',
            'payment_terms_days' => 'integer',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function customerPayments(): HasMany
    {
        return $this->hasMany(CustomerPayment::class);
    }

    public function getOutstandingReceivableAttribute(): float
    {
        $creditSalesTotal = (float) $this->sales()
            ->whereHas('payments', function ($q) {
                $q->whereIn('payment_method', ['receivable', 'credit']);
            })
            ->sum('grand_total');

        $paymentsTotal = (float) $this->customerPayments()->sum('amount');

        return max(0.0, $creditSalesTotal - $paymentsTotal);
    }
}
