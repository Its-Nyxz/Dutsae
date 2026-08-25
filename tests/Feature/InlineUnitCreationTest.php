<?php

use App\Livewire\Pos\Checkout;
use App\Models\Store;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('cashier can create a new unit inline directly inside checkout quick create modal', function () {
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
        ->set('newUnitCode', 'DUS')
        ->set('newUnitName', 'Dus / Karton')
        ->set('newUnitSymbol', 'dus')
        ->set('newUnitAllowDecimal', false)
        ->call('createInlineUnit')
        ->assertHasNoErrors();

    $unit = Unit::where('code', 'DUS')->first();
    expect($unit)->not->toBeNull();
    expect($unit->name)->toBe('Dus / Karton');
});
