<?php

use App\Livewire\Notifications\Dropdown;
use App\Livewire\Pos\Checkout;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Sale;
use App\Models\Store;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('pos checkout autofills due date from customer payment terms days when receivable is selected', function () {
    $store = Store::create(['code' => 'ST01', 'name' => 'Toko Duta Sae']);
    $cashier = User::create([
        'store_id' => $store->id,
        'name' => 'Kasir Duta',
        'email' => 'kasir@dutsae.com',
        'password' => 'secret',
        'role' => 'kasir',
    ]);

    $unit = Unit::create(['code' => 'PCS', 'name' => 'Pcs', 'symbol' => 'pcs']);
    $customer = Customer::create([
        'store_id' => $store->id,
        'code' => 'CUST01',
        'name' => 'Pak Haji Slamet',
        'payment_terms_days' => 21,
    ]);

    $product = Product::create([
        'store_id' => $store->id,
        'code' => 'B10',
        'name' => 'Besi 10mm',
        'base_unit_id' => $unit->id,
    ]);

    ProductUnit::create([
        'product_id' => $product->id,
        'unit_id' => $unit->id,
        'conversion_factor' => 1,
        'selling_price' => 50000,
    ]);

    // Select customer first, then switch paymentMethod to receivable
    Livewire::actingAs($cashier)
        ->test(Checkout::class)
        ->set('selectedCustomerId', $customer->id)
        ->call('setPaymentMethod', 'receivable')
        ->assertSet('dueDate', now()->addDays(21)->format('Y-m-d'));
});

test('notification dropdown displays due and overdue receivable alerts', function () {
    $store = Store::create(['code' => 'ST01', 'name' => 'Toko Duta Sae']);
    $admin = User::create([
        'store_id' => $store->id,
        'name' => 'Admin Duta',
        'email' => 'admin@dutsae.com',
        'password' => 'secret',
        'role' => 'admin',
    ]);

    $customer = Customer::create([
        'store_id' => $store->id,
        'code' => 'CUST02',
        'name' => 'PT Mandiri Jaya',
        'payment_terms_days' => 14,
    ]);

    $sale = Sale::create([
        'store_id' => $store->id,
        'cashier_id' => $admin->id,
        'customer_id' => $customer->id,
        'invoice_number' => 'INV-TEST-001',
        'status' => 'completed',
        'total_amount' => 1000000,
        'discount_amount' => 0,
        'grand_total' => 1000000,
        'due_date' => now()->subDay(), // Overdue
        'sold_at' => now()->subDays(15),
    ]);

    Payment::create([
        'store_id' => $store->id,
        'received_by' => $admin->id,
        'sale_id' => $sale->id,
        'payment_method' => 'receivable',
        'amount' => 1000000,
    ]);

    Livewire::actingAs($admin)
        ->test(Dropdown::class)
        ->assertSee('Bon Lewat Jatuh Tempo')
        ->assertSee('PT Mandiri Jaya')
        ->assertSee('INV-TEST-001');
});
