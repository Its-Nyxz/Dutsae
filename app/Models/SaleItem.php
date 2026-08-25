<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'product_code_snapshot',
        'product_name_snapshot',
        'unit_id',
        'unit_name_snapshot',
        'conversion_factor_snapshot',
        'quantity',
        'quantity_base',
        'unit_price',
        'discount_amount',
        'subtotal',
        'cost_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'conversion_factor_snapshot' => 'decimal:4',
            'quantity' => 'decimal:4',
            'quantity_base' => 'decimal:4',
            'unit_price' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'cost_snapshot' => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
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
