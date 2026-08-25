<?php

use App\Models\Payment;
use App\Models\Sale;
use App\Models\Store;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('dashboard distinguishes turnover vs incoming payments', function () {
    $store = Store::create(['code' => 'ST01', 'name' => 'Toko Utama']);
    $cashier = User::create(['name' => 'Kasir 1', 'email' => 'kasir@test.com', 'password' => 'secret', 'role' => 'kasir']);

    // Sale Today: Total Rp 1.000.000 (Payment split: Cash Rp 400.000, Piutang Rp 600.000)
    $saleToday = Sale::create([
        'store_id' => $store->id,
        'invoice_number' => 'INV-001',
        'cashier_id' => $cashier->id,
        'status' => 'completed',
        'subtotal' => 1000000,
        'grand_total' => 1000000,
        'sold_at' => now(),
    ]);

    // Payment Today for Sale Today (Cash Rp 400.000)
    Payment::create([
        'store_id' => $store->id,
        'sale_id' => $saleToday->id,
        'payment_method' => 'cash',
        'amount' => 400000,
        'paid_at' => now(),
        'received_by' => $cashier->id,
    ]);

    // Payment Today for Yesterday's Receivable (Cash Rp 300.000)
    $saleYesterday = Sale::create([
        'store_id' => $store->id,
        'invoice_number' => 'INV-000',
        'cashier_id' => $cashier->id,
        'status' => 'completed',
        'subtotal' => 500000,
        'grand_total' => 500000,
        'sold_at' => now()->subDay(),
    ]);

    Payment::create([
        'store_id' => $store->id,
        'sale_id' => $saleYesterday->id,
        'payment_method' => 'cash',
        'amount' => 300000,
        'paid_at' => now(), // Received today!
        'received_by' => $cashier->id,
    ]);

    $dashboardService = new DashboardService;

    $omzetToday = $dashboardService->getTodayTurnover($store->id);
    $uangMasukToday = $dashboardService->getTodayIncomingPayments($store->id);

    // Omzet Hari Ini = Rp 1.000.000 (Today's Sale Grand Total)
    expect($omzetToday)->toBe(1000000.0);

    // Uang Masuk Hari Ini = Rp 400.000 (cash from today sale) + Rp 300.000 (old receivable paid today) = Rp 700.000
    expect($uangMasukToday)->toBe(700000.0);
});
