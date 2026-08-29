<?php

use App\Livewire\Pos\Checkout;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Store;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('pos checkout component can hold cart, open hold modal, and restore held transaction', function () {
    $store = Store::create(['code' => 'ST01', 'name' => 'Toko Duta Sae']);
    $user = User::create([
        'store_id' => $store->id,
        'name' => 'Kasir 1',
        'email' => 'kasir1@dutsae.com',
        'password' => 'secret',
        'role' => 'kasir',
    ]);

    $unit = Unit::create(['code' => 'PCS', 'name' => 'Pcs', 'symbol' => 'pcs']);
    $customer = Customer::create([
        'store_id' => $store->id,
        'code' => 'CUST01',
        'name' => 'Pak Budi',
        'credit_limit' => 5000000,
        'payment_terms_days' => 14,
    ]);

    $product = Product::create([
        'store_id' => $store->id,
        'code' => 'BESI10',
        'name' => 'Besi Beton 10mm',
        'base_unit_id' => $unit->id,
        'minimum_stock_base' => 5,
    ]);

    ProductUnit::create([
        'product_id' => $product->id,
        'unit_id' => $unit->id,
        'conversion_factor' => 1,
        'selling_price' => 75000,
    ]);

    $test = Livewire::actingAs($user)
        ->test(Checkout::class)
        ->call('selectProduct', $product->id)
        ->set('selectedCustomerId', $customer->id)
        ->assertCount('cart', 1);

    // Hold the cart
    $test->call('holdCurrentCart')
        ->assertCount('cart', 0)
        ->assertCount('holdCarts', 1)
        ->assertSee('1 Transaksi Ditahan (Pending)');

    // Open hold modal
    $test->set('showHoldModal', true)
        ->assertSee('Transaksi Ditahan (Hold)')
        ->assertSee('Pak Budi');

    // Restore the held cart
    $test->call('restoreHoldCart', 0)
        ->assertCount('cart', 1)
        ->assertCount('holdCarts', 0)
        ->assertSet('showHoldModal', false);
});
