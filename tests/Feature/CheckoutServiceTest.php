<?php

use App\Models\Customer;
use App\Models\InventoryBalance;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\SaleItem;
use App\Models\Store;
use App\Models\Unit;
use App\Models\User;
use App\Services\CheckoutService;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('pos atomic checkout reduces stock and preserves historical sale item snapshot', function () {
    $store = Store::create(['code' => 'ST01', 'name' => 'Toko Utama']);
    $cashier = User::create(['name' => 'Kasir 1', 'email' => 'kasir@test.com', 'password' => 'secret', 'role' => 'kasir']);

    $unitMeter = Unit::create(['code' => 'M', 'name' => 'Meter', 'symbol' => 'm']);
    $unitBatang = Unit::create(['code' => 'BTG', 'name' => 'Batang', 'symbol' => 'btg']);

    $product = Product::create([
        'store_id' => $store->id,
        'code' => 'B10',
        'name' => 'Besi 10mm',
        'minimum_stock_base' => 5,
    ]);

    ProductUnit::create([
        'product_id' => $product->id,
        'unit_id' => $unitMeter->id,
        'conversion_factor' => 1.0,
        'selling_price' => 15000,
        'is_base_unit' => true,
    ]);

    $batangUnit = ProductUnit::create([
        'product_id' => $product->id,
        'unit_id' => $unitBatang->id,
        'conversion_factor' => 12.0, // 1 Batang = 12 Meter
        'selling_price' => 165000,
        'is_base_unit' => false,
    ]);

    // Initial stock: 100 Meter
    $inventoryService = new InventoryService;
    $inventoryService->recordMovement(
        storeId: $store->id,
        productId: $product->id,
        type: 'opening',
        quantityBase: 100.0
    );

    $checkoutService = new CheckoutService($inventoryService);

    // Sell 2 Batang (24 Meter base units) @ Rp 165.000 = Total Rp 330.000
    $sale = $checkoutService->processCheckout([
        'store_id' => $store->id,
        'items' => [
            [
                'product_id' => $product->id,
                'unit_id' => $unitBatang->id,
                'quantity' => 2,
                'unit_price' => 165000,
            ],
        ],
        'payments' => [
            [
                'payment_method' => 'cash',
                'amount' => 350000,
            ],
        ],
    ], $cashier);

    expect($sale)->not->toBeNull();
    expect($sale->status)->toBe('completed');
    expect((float) $sale->grand_total)->toBe(330000.0);

    // Check SaleItem historical snapshot
    $saleItem = SaleItem::where('sale_id', $sale->id)->first();
    expect($saleItem->product_code_snapshot)->toBe('B10');
    expect($saleItem->product_name_snapshot)->toBe('Besi 10mm');
    expect($saleItem->unit_name_snapshot)->toBe('Batang');
    expect((float) $saleItem->conversion_factor_snapshot)->toBe(12.0);
    expect((float) $saleItem->quantity_base)->toBe(24.0); // 2 x 12

    // Check remaining inventory balance: 100 - 24 = 76 Meter
    $balance = InventoryBalance::where('product_id', $product->id)->first();
    expect((float) $balance->quantity_base)->toBe(76.0);

    // Check Payment
    $payment = Payment::where('sale_id', $sale->id)->first();
    expect($payment->payment_method)->toBe('cash');
    expect((float) $payment->amount)->toBe(350000.0);
});

test('checkout fails when stock is insufficient', function () {
    $store = Store::create(['code' => 'ST01', 'name' => 'Toko Utama']);
    $cashier = User::create(['name' => 'Kasir 1', 'email' => 'kasir2@test.com', 'password' => 'secret', 'role' => 'kasir']);

    $unitPcs = Unit::create(['code' => 'PCS', 'name' => 'Pieces', 'symbol' => 'pcs']);

    $product = Product::create([
        'store_id' => $store->id,
        'code' => 'P1',
        'name' => 'Paku 5cm',
    ]);

    ProductUnit::create([
        'product_id' => $product->id,
        'unit_id' => $unitPcs->id,
        'conversion_factor' => 1.0,
        'selling_price' => 500,
        'is_base_unit' => true,
    ]);

    // Initial stock: 10 Pcs
    $inventoryService = new InventoryService;
    $inventoryService->recordMovement(
        storeId: $store->id,
        productId: $product->id,
        type: 'opening',
        quantityBase: 10.0
    );

    $checkoutService = new CheckoutService($inventoryService);

    // Try selling 15 Pcs (exceeds stock)
    expect(fn () => $checkoutService->processCheckout([
        'store_id' => $store->id,
        'items' => [
            [
                'product_id' => $product->id,
                'unit_id' => $unitPcs->id,
                'quantity' => 15,
                'unit_price' => 500,
            ],
        ],
        'payments' => [
            ['payment_method' => 'cash', 'amount' => 7500],
        ],
    ], $cashier))->toThrow(InvalidArgumentException::class);
});

test('checkout succeeds with receivable payment method for customer', function () {
    $store = Store::create(['code' => 'ST01', 'name' => 'Toko Utama']);
    $cashier = User::create(['name' => 'Kasir 1', 'email' => 'kasir3@test.com', 'password' => 'secret', 'role' => 'kasir']);
    $customer = Customer::create([
        'store_id' => $store->id,
        'name' => 'Toko Bangunan Sukses',
        'credit_limit' => 500000,
    ]);

    $unitPcs = Unit::create(['code' => 'PCS', 'name' => 'Pieces', 'symbol' => 'pcs']);
    $product = Product::create(['store_id' => $store->id, 'code' => 'S1', 'name' => 'Semen']);
    ProductUnit::create(['product_id' => $product->id, 'unit_id' => $unitPcs->id, 'conversion_factor' => 1, 'selling_price' => 70000, 'is_base_unit' => true]);

    $inventoryService = new InventoryService;
    $inventoryService->recordMovement($store->id, $product->id, 'opening', 100.0);

    $checkoutService = new CheckoutService($inventoryService);

    // Buy 10 bags @ 70,000 = 700,000 via receivable
    $sale = $checkoutService->processCheckout([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'due_date' => now()->addDays(14)->format('Y-m-d'),
        'items' => [
            ['product_id' => $product->id, 'unit_id' => $unitPcs->id, 'quantity' => 10, 'unit_price' => 70000],
        ],
        'payments' => [
            ['payment_method' => 'receivable', 'amount' => 700000],
        ],
    ], $cashier);

    expect($sale)->not->toBeNull();
    expect($sale->status)->toBe('completed');
    expect((float) $sale->grand_total)->toBe(700000.0);
    expect($sale->customer_id)->toBe($customer->id);
    expect($sale->due_date->format('Y-m-d'))->toBe(now()->addDays(14)->format('Y-m-d'));
});
