<?php

use App\Models\InventoryBalance;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\PurchaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('supplier goods receiving increases stock ledger and inventory balance', function () {
    $store = Store::create(['code' => 'ST01', 'name' => 'Toko Utama']);
    $supplier = Supplier::create(['store_id' => $store->id, 'name' => 'PT Besi Jaya', 'phone' => '08123456789']);
    $user = User::create(['name' => 'Admin Gudang', 'email' => 'gudang@test.com', 'password' => 'secret', 'role' => 'admin']);

    $unitMeter = Unit::create(['code' => 'M', 'name' => 'Meter', 'symbol' => 'm']);
    $unitBatang = Unit::create(['code' => 'BTG', 'name' => 'Batang', 'symbol' => 'btg']);

    $product = Product::create([
        'store_id' => $store->id,
        'code' => 'B12',
        'name' => 'Besi 12mm',
    ]);

    ProductUnit::create([
        'product_id' => $product->id,
        'unit_id' => $unitMeter->id,
        'conversion_factor' => 1.0,
        'selling_price' => 20000,
        'is_base_unit' => true,
    ]);

    ProductUnit::create([
        'product_id' => $product->id,
        'unit_id' => $unitBatang->id,
        'conversion_factor' => 12.0, // 1 Batang = 12 Meter
        'selling_price' => 230000,
        'is_base_unit' => false,
    ]);

    $purchaseService = new PurchaseService(new InventoryService);

    // Receive 10 Batang from Supplier (10 x 12 = 120 Meter base units)
    $purchase = $purchaseService->recordPurchase([
        'store_id' => $store->id,
        'invoice_supplier_number' => 'SJ-998822',
        'supplier_id' => $supplier->id,
        'notes' => 'Penerimaan Barang Pabrik',
        'items' => [
            [
                'product_id' => $product->id,
                'unit_id' => $unitBatang->id,
                'quantity' => 10,
                'cost_price' => 210000,
            ],
        ],
    ], $user);

    expect($purchase)->not->toBeNull();
    expect($purchase->invoice_supplier_number)->toBe('SJ-998822');
    expect((float) $purchase->grand_total)->toBe(2100000.0);

    // Check Stock Movement
    $movement = StockMovement::where('product_id', $product->id)->first();
    expect($movement->type)->toBe('purchase');
    expect((float) $movement->quantity_base)->toBe(120.0); // 10 Batang x 12 Meter

    // Check Inventory Balance
    $balance = InventoryBalance::where('product_id', $product->id)->first();
    expect((float) $balance->quantity_base)->toBe(120.0);
});
