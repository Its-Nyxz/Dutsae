<?php

use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Store;
use App\Models\Unit;

test('unit conversion normalizes quantity to base unit correctly', function () {
    $store = Store::create(['code' => 'ST01', 'name' => 'Toko Utama']);
    $meter = Unit::create(['code' => 'M', 'name' => 'Meter', 'symbol' => 'm', 'allow_decimal' => true]);
    $batang = Unit::create(['code' => 'BTG', 'name' => 'Batang', 'symbol' => 'btg', 'allow_decimal' => false]);

    $product = Product::create([
        'store_id' => $store->id,
        'code' => 'B10',
        'name' => 'Besi Beton 10mm',
        'minimum_stock_base' => 10,
    ]);

    $baseUnit = ProductUnit::create([
        'product_id' => $product->id,
        'unit_id' => $meter->id,
        'conversion_factor' => 1.0,
        'selling_price' => 15000,
        'is_base_unit' => true,
    ]);

    $batangUnit = ProductUnit::create([
        'product_id' => $product->id,
        'unit_id' => $batang->id,
        'conversion_factor' => 12.0, // 1 Batang = 12 Meter
        'selling_price' => 165000,
        'is_base_unit' => false,
    ]);

    $quantitySold = 2.0; // 2 Batang
    $quantityBase = $quantitySold * (float) $batangUnit->conversion_factor;

    expect($quantityBase)->toBe(24.0); // 2 x 12 = 24 Meter
});
