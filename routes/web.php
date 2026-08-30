<?php

use App\Http\Controllers\ExportController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\PrintReportController;
use App\Livewire\Customers\Index as CustomerIndex;
use App\Livewire\Dashboard;
use App\Livewire\Inventory\Adjustment as InventoryAdjustment;
use App\Livewire\Pos\Checkout;
use App\Livewire\Products\Index as ProductIndex;
use App\Livewire\Purchases\Create as PurchaseCreate;
use App\Livewire\Receivables\Index as ReceivableIndex;
use App\Livewire\Reports\Sales as SalesReport;
use App\Livewire\Returns\Index as SalesReturnIndex;
use App\Livewire\Suppliers\Index as SupplierIndex;
use App\Livewire\Units\Index as UnitIndex;
use App\Livewire\Users\Index as UserIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('pos');
})->name('home');

// Routes accessible to both Admin & Kasir
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/pos', Checkout::class)->name('pos');
    Route::get('/returns', SalesReturnIndex::class)->name('returns.index');
    Route::get('/receivables', ReceivableIndex::class)->name('receivables.index');
    Route::get('/print/receipt/{sale}', [PrintController::class, 'receipt'])->name('print.receipt');
    Route::get('/print/surat-jalan/{sale}', [PrintController::class, 'suratJalan'])->name('print.surat-jalan');
    Route::get('/print/receivables/{payment}', [PrintReportController::class, 'receivableReceipt'])->name('print.receivables.receipt');
    Route::get('/exports/sales', [ExportController::class, 'salesReport'])->name('exports.sales');
    Route::get('/exports/products', [ExportController::class, 'products'])->name('exports.products');
    Route::get('/exports/receivables', [ExportController::class, 'receivables'])->name('exports.receivables');
});

// Admin-Only Routes
Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/users', UserIndex::class)->name('users.index');
    Route::get('/inventory/adjustment', InventoryAdjustment::class)->name('inventory.adjustment');
    Route::get('/purchases/create', PurchaseCreate::class)->name('purchases.create');
    Route::get('/reports/sales', SalesReport::class)->name('reports.sales');
    Route::get('/print/reports/sales', [PrintReportController::class, 'salesReport'])->name('print.reports.sales');
    Route::get('/products', ProductIndex::class)->name('products.index');
    Route::get('/units', UnitIndex::class)->name('units.index');
    Route::get('/suppliers', SupplierIndex::class)->name('suppliers.index');
    Route::get('/customers', CustomerIndex::class)->name('customers.index');
});

require __DIR__.'/settings.php';
