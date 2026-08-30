<?php

use App\Livewire\Returns\Index;
use App\Models\Customer;
use App\Models\InventoryBalance;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesReturn;
use App\Models\Store;
use App\Models\Unit;
use App\Models\User;
use App\Services\SalesReturnService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can record sales return and restock inventory balance atomically', function () {
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
        'code' => 'BESI-12',
        'name' => 'Besi Beton 12mm',
        'base_unit_id' => $unit->id,
    ]);

    ProductUnit::create([
        'product_id' => $product->id,
        'unit_id' => $unit->id,
        'conversion_factor' => 1.0,
        'selling_price' => 120000.0,
        'is_base_unit' => true,
    ]);

    // Initial stock was 50, then sold 20 -> remaining 30
    InventoryBalance::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'quantity_base' => 30.0,
    ]);

    $customer = Customer::create([
        'store_id' => $store->id,
        'code' => 'CUST-02',
        'name' => 'Kontraktor Jaya',
        'phone' => '081987654321',
    ]);

    $sale = Sale::create([
        'store_id' => $store->id,
        'invoice_number' => 'INV-2026-RET-01',
        'customer_id' => $customer->id,
        'cashier_id' => $user->id,
        'status' => 'completed',
        'subtotal' => 2400000.0, // 20 batang x 120.000
        'grand_total' => 2400000.0,
        'sold_at' => now(),
    ]);

    SaleItem::create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'product_code_snapshot' => $product->code,
        'product_name_snapshot' => $product->name,
        'unit_id' => $unit->id,
        'unit_name_snapshot' => $unit->name,
        'conversion_factor_snapshot' => 1.0,
        'quantity' => 20.0,
        'quantity_base' => 20.0,
        'unit_price' => 120000.0,
        'subtotal' => 2400000.0,
    ]);

    // Payment was bon / receivable
    Payment::create([
        'store_id' => $store->id,
        'sale_id' => $sale->id,
        'payment_method' => 'receivable',
        'amount' => 2400000.0,
        'paid_at' => now(),
        'received_by' => $user->id,
    ]);

    // Return 5 batang (sisa proyek) -> 5 x 120.000 = 600.000
    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('openCreateModal')
        ->call('selectSale', $sale->id)
        ->call('updateReturnQuantity', 0, '5') // String passed from browser DOM event
        ->set('refundMethod', 'deduct_receivable')
        ->set('reason', 'Sisa material dari proyek renovasi')
        ->call('saveReturn', app(SalesReturnService::class))
        ->assertSet('showCreateModal', false)
        ->assertHasNoErrors();

    $salesReturn = SalesReturn::where('sale_id', $sale->id)->first();
    expect($salesReturn)->not->toBeNull();
    expect((float) $salesReturn->total_returned_amount)->toBe(600000.0);
    expect($salesReturn->refund_method)->toBe('deduct_receivable');

    // Check inventory balance restocked from 30 -> 35
    $balance = InventoryBalance::where('product_id', $product->id)->first();
    expect((float) $balance->quantity_base)->toBe(35.0);
});
