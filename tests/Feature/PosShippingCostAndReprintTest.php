<?php

use App\Livewire\Pos\Checkout;
use App\Models\Customer;
use App\Models\InventoryBalance;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Sale;
use App\Models\Store;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('pos checkout calculates shipping cost and updates grand total accurately', function () {
    $store = Store::create(['code' => 'ST01', 'name' => 'Toko Duta Sae']);
    $user = User::create([
        'store_id' => $store->id,
        'name' => 'Kasir Toko',
        'email' => 'kasir@dutasae.test',
        'password' => 'secret',
        'role' => 'kasir',
    ]);

    $unit = Unit::create(['code' => 'BTG', 'name' => 'Batang', 'symbol' => 'btg']);
    $product = Product::create([
        'store_id' => $store->id,
        'code' => 'BESI-10',
        'name' => 'Besi Beton 10mm',
        'base_unit_id' => $unit->id,
    ]);

    ProductUnit::create([
        'product_id' => $product->id,
        'unit_id' => $unit->id,
        'conversion_factor' => 1.0,
        'selling_price' => 85000.0,
        'is_base_unit' => true,
    ]);

    InventoryBalance::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'quantity_base' => 100.0,
    ]);

    $customer = Customer::create([
        'store_id' => $store->id,
        'code' => 'CUST-01',
        'name' => 'Pak Haji Slamet',
        'phone' => '081234567890',
        'address' => 'Proyek Colomadu',
    ]);

    Livewire::actingAs($user)
        ->test(Checkout::class)
        ->call('selectProduct', $product->id)
        ->call('updateCartQuantity', 0, 10) // 10 x 85.000 = 850.000
        ->set('discountTotal', 50000) // Diskon 50.000
        ->set('shippingCost', 100000) // Ongkir Armada 100.000
        ->set('selectedCustomerId', $customer->id)
        ->set('amountPaid', 900000) // 850.000 - 50.000 + 100.000 = 900.000
        ->call('processCheckout')
        ->assertSet('showPrintModal', true)
        ->assertHasNoErrors();

    $sale = Sale::where('store_id', $store->id)->latest('id')->first();
    expect($sale)->not->toBeNull();
    expect((float) $sale->subtotal)->toBe(850000.0);
    expect((float) $sale->discount_total)->toBe(50000.0);
    expect((float) $sale->shipping_cost)->toBe(100000.0);
    expect((float) $sale->grand_total)->toBe(900000.0);
});

test('reprint last receipt fetches the latest transaction for cashier', function () {
    $store = Store::create(['code' => 'ST01', 'name' => 'Toko Duta Sae']);
    $user = User::create([
        'store_id' => $store->id,
        'name' => 'Kasir Toko',
        'email' => 'kasir@dutasae.test',
        'password' => 'secret',
        'role' => 'kasir',
    ]);

    $sale = Sale::create([
        'store_id' => $store->id,
        'invoice_number' => 'INV-2026-TEST-99',
        'cashier_id' => $user->id,
        'subtotal' => 100000,
        'grand_total' => 100000,
        'sold_at' => now(),
    ]);

    Livewire::actingAs($user)
        ->test(Checkout::class)
        ->call('reprintLastReceipt')
        ->assertSet('showPrintModal', true)
        ->assertSet('lastSale.id', $sale->id);
});
