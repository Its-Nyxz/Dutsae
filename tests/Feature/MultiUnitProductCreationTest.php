<?php

use App\Livewire\Products\Index;
use App\Models\Product;
use App\Models\Store;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can create a new product with multiple conversion units', function () {
    $store = Store::create(['code' => 'ST01', 'name' => 'Toko Utama']);
    $admin = User::create([
        'store_id' => $store->id,
        'name' => 'Admin Utama',
        'email' => 'admin@test.com',
        'password' => 'secret',
        'role' => 'admin',
    ]);

    $unitMeter = Unit::create(['code' => 'M', 'name' => 'Meter', 'symbol' => 'm', 'allow_decimal' => true]);
    $unitBatang = Unit::create(['code' => 'BTG', 'name' => 'Batang', 'symbol' => 'btg', 'allow_decimal' => false]);

    Livewire::actingAs($admin)
        ->test(Index::class)
        ->set('code', 'B16')
        ->set('name', 'Besi Beton 16mm SNI')
        ->set('baseUnitId', $unitMeter->id)
        ->set('baseSellingPrice', 25000)
        ->set('initialStock', 120)
        ->set('minStock', 12)
        ->set('additionalUnits', [
            [
                'unit_id' => $unitBatang->id,
                'conversion_factor' => 12.0,
                'selling_price' => 280000,
            ],
        ])
        ->call('saveProduct')
        ->assertHasNoErrors();

    $product = Product::where('code', 'B16')->first();
    expect($product)->not->toBeNull();
    expect($product->productUnits)->toHaveCount(2); // Base Unit (Meter) + Additional Unit (Batang)
});
