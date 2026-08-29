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
        'invoice_number' => 'INV-20260825-9999',
        'subtotal' => 150000,
        'discount_total' => 0,
        'grand_total' => 150000,
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
        'quantity' => 15,
        'quantity_base' => 15,
        'unit_price' => 10000,
        'subtotal' => 150000,
    ]);
});

it('renders printable sales report PDF document with metrics and invoice records', function () {
    $today = now()->format('Y-m-d');

    $this->actingAs($this->admin)
        ->get(route('print.reports.sales', ['start_date' => $today, 'end_date' => $today]))
        ->assertOk()
        ->assertSee('LAPORAN OMZET PENJUALAN')
        ->assertSee('INV-20260825-9999')
        ->assertSee('Toko Duta Sae')
        ->assertSee('150.000');
});
