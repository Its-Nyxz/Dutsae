<?php

use App\Livewire\Receivables\Index;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Store;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->store = Store::create([
        'code' => 'ST01',
        'name' => 'Toko Duta Sae',
        'address' => 'Jl. Pemuda No. 1',
        'phone' => '02717685127',
    ]);

    $this->admin = User::create([
        'store_id' => $this->store->id,
        'name' => 'Admin Test',
        'email' => 'admin@tokobesi.com',
        'password' => Hash::make('password'),
        'role' => 'admin',
    ]);

    $this->customer = Customer::create([
        'store_id' => $this->store->id,
        'code' => 'CUST-01',
        'name' => 'Kontraktor Budi',
        'phone' => '08123456789',
        'address' => 'Jl. Proyek No. 5',
        'credit_limit' => 10000000,
        'payment_terms_days' => 14,
    ]);

    $this->unit = Unit::create(['code' => 'SAK', 'name' => 'Sak', 'symbol' => 'sak']);

    $this->product = Product::create([
        'store_id' => $this->store->id,
        'code' => 'SEM01',
        'name' => 'Semen Gresik 50kg',
        'base_unit_id' => $this->unit->id,
        'base_selling_price' => 65000,
        'minimum_stock_base' => 10,
    ]);

    $this->sale = Sale::create([
        'store_id' => $this->store->id,
        'customer_id' => $this->customer->id,
        'cashier_id' => $this->admin->id,
        'invoice_number' => 'INV-20260825-7777',
        'subtotal' => 650000,
        'discount_total' => 0,
        'grand_total' => 650000,
        'status' => 'completed',
        'sold_at' => now(),
    ]);

    $this->sale->payments()->create([
        'store_id' => $this->store->id,
        'payment_method' => 'receivable',
        'amount' => 650000,
        'paid_at' => now(),
        'received_by' => $this->admin->id,
    ]);
});

it('renders receivables ledger page with customer outstanding credit balance', function () {
    $this->actingAs($this->admin)
        ->get(route('receivables.index'))
        ->assertOk()
        ->assertSee('Buku Piutang & Pelunasan Bon Pelanggan')
        ->assertSee('Kontraktor Budi')
        ->assertSee('650.000');
});

it('can process customer credit settlement payment via livewire', function () {
    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->call('openPaymentModal', $this->customer->id)
        ->set('amount', 500000)
        ->set('paymentMethod', 'cash')
        ->call('savePayment')
        ->assertHasNoErrors();

    expect(CustomerPayment::count())->toBe(1);
    expect($this->customer->fresh()->outstanding_receivable)->toBe(150000.0);
});

it('exports sales report, product catalog, and receivables data to Excel format', function () {
    $today = now()->format('Y-m-d');

    $this->actingAs($this->admin)
        ->get(route('exports.sales', ['start_date' => $today, 'end_date' => $today]))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/vnd.ms-excel');

    $this->actingAs($this->admin)
        ->get(route('exports.products'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/vnd.ms-excel');

    $this->actingAs($this->admin)
        ->get(route('exports.receivables'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/vnd.ms-excel');
});
