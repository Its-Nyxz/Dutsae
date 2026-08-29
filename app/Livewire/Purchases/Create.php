<?php

namespace App\Livewire\Purchases;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\Unit;
use App\Services\PurchaseService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Input Barang Masuk Supplier')]
class Create extends Component
{
    public string $invoiceSupplierNumber = '';

    public ?int $supplierId = null;

    public string $notes = '';

    public array $items = [];

    // Inline Supplier Creation State
    public bool $showInlineSupplierForm = false;

    public string $newSupplierCode = '';

    public string $newSupplierName = '';

    public string $newSupplierPhone = '';

    public string $newSupplierAddress = '';

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    public function mount()
    {
        $this->addItemRow();

        $storeId = Auth::user()?->store_id ?? Store::first()?->id ?? 1;
        $firstSupplier = Supplier::where('store_id', $storeId)->first();
        if ($firstSupplier) {
            $this->supplierId = $firstSupplier->id;
        }
    }

    public function createInlineSupplier()
    {
        $this->errorMessage = null;

        if (trim($this->newSupplierName) === '') {
            $this->errorMessage = 'Nama supplier baru wajib diisi.';
            $this->dispatch('swal', title: 'Perhatian', text: $this->errorMessage, icon: 'warning');

            return;
        }

        $user = Auth::user();
        $storeId = $user->store_id ?? Store::first()?->id ?? 1;

        try {
            $supplier = Supplier::create([
                'store_id' => $storeId,
                'code' => trim($this->newSupplierCode) ?: ('SUP-'.rand(100, 999)),
                'name' => trim($this->newSupplierName),
                'phone' => trim($this->newSupplierPhone),
                'address' => trim($this->newSupplierAddress),
            ]);

            $this->supplierId = $supplier->id;
            $this->showInlineSupplierForm = false;
            $this->reset(['newSupplierCode', 'newSupplierName', 'newSupplierPhone', 'newSupplierAddress']);
            $msg = "Supplier baru '{$supplier->name}' berhasil dibuat dan terpilih!";
            $this->dispatch('swal-toast', message: $msg, icon: 'success');
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('swal', title: 'Gagal', text: $this->errorMessage, icon: 'error');
        }
    }

    public function addItemRow()
    {
        $storeId = Auth::user()?->store_id ?? Store::first()?->id ?? 1;
        $firstProduct = Product::where('store_id', $storeId)->first();
        $firstUnit = Unit::first();

        $this->items[] = [
            'product_id' => $firstProduct?->id,
            'unit_id' => $firstUnit?->id,
            'quantity' => 1.0,
            'cost_price' => 0.0,
        ];
    }

    public function removeItemRow(int $index)
    {
        array_splice($this->items, $index, 1);
        if (empty($this->items)) {
            $this->addItemRow();
        }
    }

    public function savePurchase(PurchaseService $purchaseService)
    {
        $this->errorMessage = null;
        $user = Auth::user();
        $storeId = $user->store_id ?? Store::first()?->id ?? 1;

        if (! $this->supplierId) {
            $this->errorMessage = 'Silakan pilih supplier pemasok.';
            $this->dispatch('swal', title: 'Perhatian', text: $this->errorMessage, icon: 'warning');

            return;
        }

        try {
            $purchase = $purchaseService->receivePurchaseOrder([
                'store_id' => $storeId,
                'supplier_id' => $this->supplierId,
                'invoice_supplier_number' => $this->invoiceSupplierNumber,
                'notes' => $this->notes,
                'items' => $this->items,
            ], $user);

            $this->items = [];
            $this->addItemRow();
            $this->invoiceSupplierNumber = '';
            $this->notes = '';
            $msg = "Penerimaan barang faktur {$purchase->invoice_supplier_number} berhasil disimpan!";
            $this->dispatch('swal', title: 'Sukses! 🎉', text: $msg, icon: 'success');
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('swal', title: 'Gagal Simpan Pembelian', text: $this->errorMessage, icon: 'error');
        }
    }

    // Detail Purchase Modal State
    public bool $showDetailModal = false;

    public ?Purchase $selectedPurchase = null;

    public function viewDetail(int $purchaseId)
    {
        $purchase = Purchase::with(['supplier', 'receiver', 'items.product', 'items.unit'])->find($purchaseId);

        if ($purchase) {
            $this->selectedPurchase = $purchase;
            $this->showDetailModal = true;
        }
    }

    public function render()
    {
        $storeId = Auth::user()?->store_id ?? Store::first()?->id ?? 1;
        $suppliers = Supplier::where('store_id', $storeId)->get();
        $products = Product::where('store_id', $storeId)->where('is_active', true)->get();
        $units = Unit::all();

        $recentPurchases = Purchase::with(['supplier', 'receiver', 'items.product', 'items.unit'])
            ->where('store_id', $storeId)
            ->latest('purchased_at')
            ->take(15)
            ->get();

        return view('livewire.purchases.create', [
            'suppliers' => $suppliers,
            'products' => $products,
            'units' => $units,
            'recentPurchases' => $recentPurchases,
        ]);
    }
}
