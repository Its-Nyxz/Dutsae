<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'product_id',
        'product_code_snapshot',
        'product_name_snapshot',
        'unit_id',
        'unit_name_snapshot',
        'conversion_factor_snapshot',
        'quantity',
        'quantity_base',
        'cost_price',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'conversion_factor_snapshot' => 'decimal:4',
            'quantity' => 'decimal:4',
            'quantity_base' => 'decimal:4',
            'cost_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
