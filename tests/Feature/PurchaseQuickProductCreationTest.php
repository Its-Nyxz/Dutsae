<?php

use App\Livewire\Purchases\Create;
use App\Models\Product;
use App\Models\Store;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can open quick product modal and create new product directly in incoming goods without leaving page', function () {
    $store = Store::create(['code' => 'ST01', 'name' => 'Toko Duta Sae']);
    $admin = User::create([
        'store_id' => $store->id,
        'name' => 'Admin Gudang',
        'email' => 'gudang@dutasae.test',
        'password' => 'secret',
        'role' => 'admin',
    ]);
    $unit = Unit::create(['code' => 'BTG', 'name' => 'Batang', 'symbol' => 'btg']);

    Livewire::actingAs($admin)
        ->test(Create::class)
        ->call('openQuickProductModal', 0)
        ->assertSet('showQuickProductModal', true)
        ->set('quickProductCode', 'PRD-BESI-10')
        ->set('quickProductName', 'Besi Beton Ulir 10mm SNI')
        ->set('quickProductUnitId', $unit->id)
        ->set('quickProductBuyPrice', 72000)
        ->set('quickProductSellPrice', 85000)
        ->call('saveQuickProduct')
        ->assertSet('showQuickProductModal', false)
        ->assertHasNoErrors();

    $product = Product::where('code', 'PRD-BESI-10')->first();
    expect($product)->not->toBeNull();
    expect($product->name)->toBe('Besi Beton Ulir 10mm SNI');

    $baseUnit = $product->baseUnit;
    expect($baseUnit)->not->toBeNull();
    expect((float) $baseUnit->selling_price)->toBe(85000.0);
});

test('user can create unit inline from within quick product modal in purchases', function () {
    $store = Store::create(['code' => 'ST01', 'name' => 'Toko Duta Sae']);
    $admin = User::create([
        'store_id' => $store->id,
        'name' => 'Admin Gudang',
        'email' => 'gudang2@dutasae.test',
        'password' => 'secret',
        'role' => 'admin',
    ]);

    Livewire::actingAs($admin)
        ->test(Create::class)
        ->call('openQuickProductModal', 0)
        ->set('newUnitCode', 'ROL')
        ->set('newUnitName', 'Gulungan Rol')
        ->set('newUnitSymbol', 'rol')
        ->call('createInlineUnit')
        ->assertHasNoErrors();

    $newUnit = Unit::where('code', 'ROL')->first();
    expect($newUnit)->not->toBeNull();
    expect($newUnit->name)->toBe('Gulungan Rol');
});
