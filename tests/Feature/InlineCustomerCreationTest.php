<?php

use App\Livewire\Pos\Checkout;
use App\Models\Customer;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('cashier can create a new customer inline directly inside checkout screen', function () {
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
        ->set('newCustomerCode', 'CUST-99')
        ->set('newCustomerName', 'CV Bangun Jaya Utama')
        ->set('newCustomerPhone', '0812999000')
        ->set('newCustomerTermsDays', 14)
        ->call('createInlineCustomer')
        ->assertHasNoErrors();

    $customer = Customer::where('name', 'CV Bangun Jaya Utama')->first();
    expect($customer)->not->toBeNull();
    expect($customer->payment_terms_days)->toBe(14);
});
