<?php

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('kasir can access pos selling screen but is blocked from admin routes', function () {
    $store = Store::create(['code' => 'ST01', 'name' => 'Toko Utama']);
    $kasir = User::create([
        'store_id' => $store->id,
        'name' => 'Budi Kasir',
        'email' => 'kasir@test.com',
        'password' => 'secret',
        'role' => 'kasir',
    ]);

    // Kasir can access POS
    $responsePos = $this->actingAs($kasir)->get(route('pos'));
    $responsePos->assertStatus(200);

    // Kasir is redirected from Admin routes
    $adminRoutes = [
        route('dashboard'),
        route('purchases.create'),
        route('products.index'),
        route('units.index'),
        route('suppliers.index'),
        route('customers.index'),
    ];

    foreach ($adminRoutes as $adminRoute) {
        $response = $this->actingAs($kasir)->get($adminRoute);
        $response->assertRedirect(route('pos'));
    }
});

test('admin can access all system routes including pos and master data', function () {
    $store = Store::create(['code' => 'ST01', 'name' => 'Toko Utama']);
    $admin = User::create([
        'store_id' => $store->id,
        'name' => 'Admin Utama',
        'email' => 'admin@test.com',
        'password' => 'secret',
        'role' => 'admin',
    ]);

    $allRoutes = [
        route('pos'),
        route('dashboard'),
        route('purchases.create'),
        route('products.index'),
        route('units.index'),
        route('suppliers.index'),
        route('customers.index'),
    ];

    foreach ($allRoutes as $r) {
        $response = $this->actingAs($admin)->get($r);
        $response->assertStatus(200);
    }
});
