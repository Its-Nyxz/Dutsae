<?php

use App\Livewire\Reports\Sales;
use App\Models\Sale;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

    Sale::create([
        'store_id' => $store->id,
        'cashier_id' => $admin->id,
        'invoice_number' => 'INV-2026-0001',
        'sold_at' => now(),
        'subtotal' => 500000,
        'discount_total' => 0,
        'grand_total' => 500000,
        'paid_amount' => 500000,
        'outstanding_amount' => 0,
        'payment_status' => 'paid',
    ]);

    Livewire::actingAs($admin)
        ->test(Sales::class)
        ->assertStatus(200)
        ->assertSee('INV-2026-0001')
        ->assertSee('Rp 500.000');
});
