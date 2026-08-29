<?php

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Store;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

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

    $this->unit = Unit::create(['code' => 'M', 'name' => 'Meter', 'symbol' => 'm']);

    $this->product = Product::create([
        'store_id' => $this->store->id,
        'code' => 'B12',
        'name' => 'Besi 12mm',
        'base_unit_id' => $this->unit->id,
        'base_selling_price' => 10000,
        'minimum_stock_base' => 5,
    ]);

    $this->sale = Sale::create([
        'store_id' => $this->store->id,
        'cashier_id' => $this->admin->id,
        'invoice_number' => 'INV-20260825-0001',
        'subtotal' => 50000,
        'discount_total' => 0,
        'grand_total' => 50000,
        'status' => 'completed',
        'sold_at' => now(),
    ]);

    SaleItem::create([
        'sale_id' => $this->sale->id,
        'product_id' => $this->product->id,
        'product_code_snapshot' => $this->product->code,
        'product_name_snapshot' => $this->product->name,
        'unit_id' => $this->unit->id,
        'unit_name_snapshot' => $this->unit->name,
        'conversion_factor_snapshot' => 1.0,
        'quantity' => 5,
        'quantity_base' => 5,
        'unit_price' => 10000,
        'subtotal' => 50000,
    ]);
});

it('renders printable thermal receipt page with invoice and store details', function () {
    $this->actingAs($this->admin)
        ->get(route('print.receipt', $this->sale->id))
        ->assertOk()
        ->assertSee('INV-20260825-0001')
        ->assertSee('Toko Duta Sae')
        ->assertSee('Besi 12mm');
});

it('renders printable surat jalan document page with delivery details', function () {
    $this->actingAs($this->admin)
        ->get(route('print.surat-jalan', $this->sale->id))
        ->assertOk()
        ->assertSee('SURAT JALAN')
        ->assertSee('SJ-INV-20260825-0001')
        ->assertSee('Besi 12mm');
});
