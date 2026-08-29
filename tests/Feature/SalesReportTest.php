<?php

use App\Livewire\Reports\Sales;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Store;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('admin can access sales report page and view turnover summary', function () {
    $store = Store::create(['code' => 'ST01', 'name' => 'Toko Utama']);
    $admin = User::create([
        'store_id' => $store->id,
        'name' => 'Admin Boss',
        'email' => 'admin@test.com',
        'password' => 'secret',
        'role' => 'admin',
    ]);

    $sale = Sale::create([
        'store_id' => $store->id,
        'cashier_id' => $admin->id,
        'invoice_number' => 'INV-2026-0001',
        'status' => 'completed',
        'sold_at' => now(),
        'subtotal' => 500000,
        'discount_total' => 0,
        'grand_total' => 500000,
    ]);

    Payment::create([
        'sale_id' => $sale->id,
        'store_id' => $store->id,
        'received_by' => $admin->id,
        'payment_method' => 'cash',
        'amount' => 500000,
        'paid_at' => now(),
    ]);

    Livewire::actingAs($admin)
        ->test(Sales::class)
        ->assertStatus(200)
        ->assertSee('INV-2026-0001')
        ->assertSee('Rp 500.000');
});

test('sales report filters by payment method and status', function () {
    $store = Store::create(['code' => 'ST01', 'name' => 'Toko Utama']);
    $admin = User::create([
        'store_id' => $store->id,
        'name' => 'Admin Boss',
        'email' => 'admin2@test.com',
        'password' => 'secret',
        'role' => 'admin',
    ]);
    $cust = Customer::create(['store_id' => $store->id, 'code' => 'C01', 'name' => 'Pak Joko', 'payment_terms_days' => 14]);

    $saleCash = Sale::create([
        'store_id' => $store->id,
        'customer_id' => $cust->id,
        'cashier_id' => $admin->id,
        'invoice_number' => 'INV-CASH-01',
        'status' => 'completed',
        'sold_at' => now(),
        'subtotal' => 200000,
        'discount_total' => 0,
        'grand_total' => 200000,
    ]);
    Payment::create([
        'sale_id' => $saleCash->id,
        'store_id' => $store->id,
        'received_by' => $admin->id,
        'payment_method' => 'cash',
        'amount' => 200000,
        'paid_at' => now(),
    ]);

    $saleBon = Sale::create([
        'store_id' => $store->id,
        'customer_id' => $cust->id,
        'cashier_id' => $admin->id,
        'invoice_number' => 'INV-BON-01',
        'status' => 'completed',
        'sold_at' => now(),
        'subtotal' => 750000,
        'discount_total' => 0,
        'grand_total' => 750000,
    ]);
    Payment::create([
        'sale_id' => $saleBon->id,
        'store_id' => $store->id,
        'received_by' => $admin->id,
        'payment_method' => 'receivable',
        'amount' => 750000,
        'paid_at' => now(),
    ]);

    // Test filter by receivable
    Livewire::actingAs($admin)
        ->test(Sales::class)
        ->set('paymentMethod', 'receivable')
        ->assertSee('INV-BON-01')
        ->assertDontSee('INV-CASH-01')
        ->set('paymentMethod', 'cash')
        ->assertSee('INV-CASH-01')
        ->assertDontSee('INV-BON-01');
});

test('user can view detail of a sale in modal with items snapshot', function () {
    $store = Store::create(['code' => 'ST01', 'name' => 'Toko Utama']);
    $admin = User::create([
        'store_id' => $store->id,
        'name' => 'Admin Boss',
        'email' => 'admin3@test.com',
        'password' => 'secret',
        'role' => 'admin',
    ]);
    $unit = Unit::create(['store_id' => $store->id, 'code' => 'BKS', 'name' => 'Bungkus', 'symbol' => 'bks']);
    $product = Product::create([
        'store_id' => $store->id,
        'code' => 'PRD-99',
        'name' => 'Paku Beton Super',
        'base_unit_id' => $unit->id,
        'buy_price' => 10000,
        'sell_price' => 15000,
    ]);

    $sale = Sale::create([
        'store_id' => $store->id,
        'cashier_id' => $admin->id,
        'invoice_number' => 'INV-DETAIL-01',
        'status' => 'completed',
        'sold_at' => now(),
        'subtotal' => 30000,
        'discount_total' => 0,
        'grand_total' => 30000,
    ]);

    SaleItem::create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'unit_id' => $unit->id,
        'product_code_snapshot' => 'PRD-99',
        'product_name_snapshot' => 'Paku Beton Super',
        'unit_name_snapshot' => 'Bungkus',
        'conversion_factor_snapshot' => 1,
        'quantity' => 2,
        'quantity_base' => 2,
        'unit_price' => 15000,
        'subtotal' => 30000,
    ]);

    Livewire::actingAs($admin)
        ->test(Sales::class)
        ->call('viewDetail', $sale->id)
        ->assertSet('showDetailModal', true)
        ->assertSee('Paku Beton Super')
        ->assertSee('PRD-99')
        ->assertSee('Bungkus');
});
