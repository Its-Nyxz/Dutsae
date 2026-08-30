<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_return_id',
        'product_id',
        'unit_id',
        'quantity',
        'conversion_factor_snapshot',
        'quantity_base',
        'unit_price',
        'subtotal',
        'product_code_snapshot',
        'product_name_snapshot',
        'unit_name_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'conversion_factor_snapshot' => 'decimal:4',
            'quantity_base' => 'decimal:4',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function salesReturn(): BelongsTo
    {
        return $this->belongsTo(SalesReturn::class);
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
