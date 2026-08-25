<?php

use App\Models\InventoryBalance;
use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\Unit;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('product code must be unique per store', function () {
    $store = Store::create(['code' => 'ST01', 'name' => 'Toko Utama']);
    $unit = Unit::create(['code' => 'PCS', 'name' => 'Pieces', 'symbol' => 'pcs']);

    $service = new ProductService(new InventoryService);

    $service->createProduct([
        'store_id' => $store->id,
        'code' => 'B10',
        'name' => 'Besi 10mm',
        'base_unit_id' => $unit->id,
        'base_selling_price' => 15000,
    ]);

    expect(fn () => $service->createProduct([
        'store_id' => $store->id,
        'code' => 'B10', // Duplicate code in same store
        'name' => 'Besi 10mm Lain',
        'base_unit_id' => $unit->id,
        'base_selling_price' => 16000,
    ]))->toThrow(InvalidArgumentException::class);
});

test('product creation creates base unit and initial stock movement when stock > 0', function () {
    $store = Store::create(['code' => 'ST01', 'name' => 'Toko Utama']);
    $unit = Unit::create(['code' => 'BTG', 'name' => 'Batang', 'symbol' => 'btg']);

    $service = new ProductService(new InventoryService);

    $product = $service->createProduct([
        'store_id' => $store->id,
        'code' => 'HS44',
        'name' => 'Hollow 4x4',
        'base_unit_id' => $unit->id,
        'base_selling_price' => 85000,
        'initial_stock' => 50,
    ]);

    expect($product->code)->toBe('HS44');
    expect(Product::count())->toBe(1);

    // Verify stock movement
    $movement = StockMovement::where('product_id', $product->id)->first();
    expect($movement)->not->toBeNull();
    expect($movement->type)->toBe('opening');
    expect((float) $movement->quantity_base)->toBe(50.0);

    // Verify inventory balance
    $balance = InventoryBalance::where('product_id', $product->id)->first();
    expect((float) $balance->quantity_base)->toBe(50.0);
});

test('updating product unit price logs price history', function () {
    $store = Store::create(['code' => 'ST01', 'name' => 'Toko Utama']);
    $unit = Unit::create(['code' => 'M', 'name' => 'Meter', 'symbol' => 'm']);
    $user = User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => 'secret', 'role' => 'admin']);

    $productService = new ProductService(new InventoryService);

    $product = $productService->createProduct([
        'store_id' => $store->id,
        'code' => 'PIPA34',
        'name' => 'Pipa 3/4',
        'base_unit_id' => $unit->id,
        'base_selling_price' => 20000,
    ]);

    $baseProductUnit = $product->baseUnit;

    $productService->updateUnitPrice($baseProductUnit, 22000, 'Kenaikan Bahan Baku', $user->id);

    $history = PriceHistory::where('product_id', $product->id)->first();
    expect($history)->not->toBeNull();
    expect((float) $history->old_price)->toBe(20000.0);
    expect((float) $history->new_price)->toBe(22000.0);
    expect($history->reason)->toBe('Kenaikan Bahan Baku');
});
