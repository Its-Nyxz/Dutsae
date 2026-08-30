<?php

use App\Livewire\Inventory\Adjustment;
use App\Models\InventoryBalance;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\Unit;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can record stock adjustment when defect or lost items occur', function () {
    $store = Store::create(['code' => 'ST01', 'name' => 'Toko Duta Sae']);
    $admin = User::create([
        'store_id' => $store->id,
        'name' => 'Admin Gudang',
        'email' => 'admin@dutasae.test',
        'password' => 'secret',
        'role' => 'admin',
    ]);

    $unit = Unit::create(['code' => 'SAK', 'name' => 'Sak Semen', 'symbol' => 'sak']);
    $product = Product::create([
        'store_id' => $store->id,
        'code' => 'SMN-01',
        'name' => 'Semen Gresik 40kg',
        'base_unit_id' => $unit->id,
    ]);

    ProductUnit::create([
        'product_id' => $product->id,
        'unit_id' => $unit->id,
        'conversion_factor' => 1.0,
        'selling_price' => 55000.0,
        'is_base_unit' => true,
    ]);

    InventoryBalance::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'quantity_base' => 100.0,
    ]);

    Livewire::actingAs($admin)
        ->test(Adjustment::class)
        ->call('openAdjustmentModal', $product->id)
        ->assertSet('currentSystemStock', 100.0)
        ->set('actualPhysicalStock', 95.0) // 5 sak semen mengeras/sobek
        ->set('reason', 'Semen Mengeras / Kantong Sobek')
        ->set('notes', 'Ditemukan 5 sak mengeras kena air hujan')
        ->call('saveAdjustment', app(InventoryService::class))
        ->assertSet('showAdjustmentModal', false)
        ->assertHasNoErrors();

    $balance = InventoryBalance::where('product_id', $product->id)->first();
    expect((float) $balance->quantity_base)->toBe(95.0);

    $movement = StockMovement::where('product_id', $product->id)->where('type', 'adjustment')->first();
    expect($movement)->not->toBeNull();
    expect((float) $movement->quantity_base)->toBe(-5.0);
    expect((float) $movement->balance_before)->toBe(100.0);
    expect((float) $movement->balance_after)->toBe(95.0);
    expect($movement->notes)->toContain('Semen Mengeras / Kantong Sobek');
});

test('user can input custom dynamic reason during stock adjustment', function () {
    $store = Store::create(['code' => 'ST01', 'name' => 'Toko Duta Sae']);
    $admin = User::create([
        'store_id' => $store->id,
        'name' => 'Admin Gudang',
        'email' => 'admin@dutasae.test',
        'password' => 'secret',
        'role' => 'admin',
    ]);

    $unit = Unit::create(['code' => 'BTG', 'name' => 'Batang', 'symbol' => 'btg']);
    $product = Product::create([
        'store_id' => $store->id,
        'code' => 'BESI-8',
        'name' => 'Besi Beton 8mm',
        'base_unit_id' => $unit->id,
    ]);

    ProductUnit::create([
        'product_id' => $product->id,
        'unit_id' => $unit->id,
        'conversion_factor' => 1.0,
        'selling_price' => 45000.0,
        'is_base_unit' => true,
    ]);

    InventoryBalance::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'quantity_base' => 50.0,
    ]);

    Livewire::actingAs($admin)
        ->test(Adjustment::class)
        ->call('openAdjustmentModal', $product->id)
        ->set('actualPhysicalStock', 48.0)
        ->call('selectReason', '__custom__')
        ->set('customReasonInput', 'Hancur Tertimpa Truk Pasir')
        ->set('notes', 'Kejadian saat bongkar muatan sore hari')
        ->call('saveAdjustment', app(InventoryService::class))
        ->assertSet('showAdjustmentModal', false)
        ->assertHasNoErrors();

    $movement = StockMovement::where('product_id', $product->id)->where('type', 'adjustment')->first();
    expect($movement)->not->toBeNull();
    expect($movement->notes)->toContain('[Hancur Tertimpa Truk Pasir]');
});
