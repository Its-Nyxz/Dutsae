<?php

use App\Livewire\Purchases\Create;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can create a new supplier inline directly inside incoming goods form', function () {
    $store = Store::create(['code' => 'ST01', 'name' => 'Toko Utama']);
    $admin = User::create([
        'store_id' => $store->id,
        'name' => 'Admin Utama',
        'email' => 'admin@test.com',
        'password' => 'secret',
        'role' => 'admin',
    ]);

    Livewire::actingAs($admin)
        ->test(Create::class)
        ->set('newSupplierCode', 'SUP-99')
        ->set('newSupplierName', 'PT Hanwa Steel Indonesia')
        ->set('newSupplierPhone', '021-999888')
        ->set('newSupplierAddress', 'Kawasan Industri Jababeka')
        ->call('createInlineSupplier')
        ->assertHasNoErrors();

    $supplier = Supplier::where('name', 'PT Hanwa Steel Indonesia')->first();
    expect($supplier)->not->toBeNull();
    expect($supplier->code)->toBe('SUP-99');
});
