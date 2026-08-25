<?php

use App\Livewire\Pos\Checkout;
use App\Livewire\Products\Index as ProductsIndex;
use App\Models\Location;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can create a new location inline directly inside products modal', function () {
    $store = Store::create(['code' => 'ST01', 'name' => 'Toko Utama']);
    $admin = User::create([
        'store_id' => $store->id,
        'name' => 'Admin Utama',
        'email' => 'admin@test.com',
        'password' => 'secret',
        'role' => 'admin',
    ]);

    Livewire::actingAs($admin)
        ->test(ProductsIndex::class)
        ->set('newLocationCode', 'RAK-D05')
        ->set('newLocationName', 'Rak D-05 (Cat & Kuas)')
        ->set('newLocationDescription', 'Samping Pintu Gudang A')
        ->call('createInlineLocation')
        ->assertHasNoErrors();

    $location = Location::where('code', 'RAK-D05')->first();
    expect($location)->not->toBeNull();
    expect($location->name)->toBe('Rak D-05 (Cat & Kuas)');
});

test('cashier can create a new location inline directly inside pos quick create modal', function () {
    $store = Store::create(['code' => 'ST01', 'name' => 'Toko Utama']);
    $kasir = User::create([
        'store_id' => $store->id,
        'name' => 'Budi Kasir',
        'email' => 'kasir@test.com',
        'password' => 'secret',
        'role' => 'kasir',
    ]);

    Livewire::actingAs($kasir)
        ->test(Checkout::class)
        ->set('newLocationCode', 'RAK-E01')
        ->set('newLocationName', 'Rak E-01 (Fitting & Pipa)')
        ->call('createInlineLocation')
        ->assertHasNoErrors();

    $location = Location::where('code', 'RAK-E01')->first();
    expect($location)->not->toBeNull();
    expect($location->name)->toBe('Rak E-01 (Fitting & Pipa)');
});
