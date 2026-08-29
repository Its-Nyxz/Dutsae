<?php

namespace App\Livewire\Purchases;

use App\Models\Location;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\Unit;
use App\Services\ProductService;
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

    // Quick Product Creation Modal State
    public bool $showQuickProductModal = false;

    public ?int $targetItemIndex = null;

    public string $quickProductCode = '';

    public string $quickProductName = '';

    public ?int $quickProductUnitId = null;

    public ?int $quickProductLocationId = null;

    public float $quickProductBuyPrice = 0;

    public float $quickProductSellPrice = 0;

    public float $quickProductMinStock = 5;

    // Inline Create Location Form State
    public bool $showInlineLocationForm = false;

    public string $newLocationCode = '';

    public string $newLocationName = '';

    public string $newLocationDescription = '';

    // Inline Create Unit Form State
    public bool $showInlineUnitForm = false;

    public string $newUnitCode = '';

    public string $newUnitName = '';

    public string $newUnitSymbol = '';

    public bool $newUnitAllowDecimal = false;

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    public function mount(): void
    {
        $this->addItemRow();

        $storeId = Auth::user()?->store_id ?? Store::first()?->id ?? 1;
        $firstSupplier = Supplier::where('store_id', $storeId)->first();
        if ($firstSupplier) {
            $this->supplierId = $firstSupplier->id;
        }

        $firstUnit = Unit::first();
        if ($firstUnit) {
            $this->quickProductUnitId = $firstUnit->id;
        }

        $firstLocation = Location::first();
        if ($firstLocation) {
            $this->quickProductLocationId = $firstLocation->id;
        }
    }

    public function createInlineSupplier(): void
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

    public function openQuickProductModal(?int $itemIndex = null): void
    {
        $storeId = Auth::user()?->store_id ?? Store::first()?->id ?? 1;
        $lastId = (int) (Product::where('store_id', $storeId)->max('id') ?? 0);
        $nextNumber = str_pad((string) ($lastId + 1), 4, '0', STR_PAD_LEFT);

        $this->targetItemIndex = $itemIndex;
        $this->quickProductCode = 'PRD-'.$nextNumber;
        $this->quickProductName = '';
        $this->quickProductBuyPrice = 0;
        $this->quickProductSellPrice = 0;
        $this->quickProductMinStock = 5;

        $firstUnit = Unit::first();
        if ($firstUnit) {
            $this->quickProductUnitId = $firstUnit->id;
        }

        $firstLocation = Location::first();
        if ($firstLocation) {
            $this->quickProductLocationId = $firstLocation->id;
        }

        $this->showInlineUnitForm = false;
        $this->showInlineLocationForm = false;
        $this->showQuickProductModal = true;
    }

    public function createInlineLocation(): void
    {
        $this->errorMessage = null;

        if (trim($this->newLocationName) === '') {
            $this->errorMessage = 'Nama lokasi rak baru wajib diisi.';
            $this->dispatch('swal', title: 'Perhatian', text: $this->errorMessage, icon: 'warning');

            return;
        }

        $user = Auth::user();
        $storeId = $user->store_id ?? Store::first()?->id ?? 1;

        try {
            $location = Location::create([
                'store_id' => $storeId,
                'code' => trim($this->newLocationCode) ?: ('LOC-'.rand(100, 999)),
                'name' => trim($this->newLocationName),
                'description' => trim($this->newLocationDescription),
            ]);

            $this->quickProductLocationId = $location->id;
            $this->showInlineLocationForm = false;
            $this->reset(['newLocationCode', 'newLocationName', 'newLocationDescription']);
            $this->dispatch('swal-toast', message: "Lokasi rak '{$location->name}' berhasil dibuat dan terpilih!", icon: 'success');
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('swal', title: 'Gagal', text: $this->errorMessage, icon: 'error');
        }
    }

    public function createInlineUnit(): void
    {
        $this->errorMessage = null;

        if (trim($this->newUnitCode) === '' || trim($this->newUnitName) === '') {
            $this->errorMessage = 'Kode dan nama satuan baru wajib diisi.';
            $this->dispatch('swal', title: 'Perhatian', text: $this->errorMessage, icon: 'warning');

            return;
        }

        try {
            $storeId = Auth::user()?->store_id ?? Store::first()?->id ?? 1;
            $unit = Unit::create([
                'store_id' => $storeId,
                'code' => strtoupper(trim($this->newUnitCode)),
                'name' => trim($this->newUnitName),
                'symbol' => strtolower(trim($this->newUnitSymbol ?: $this->newUnitCode)),
                'allow_decimal' => $this->newUnitAllowDecimal,
            ]);

            $this->quickProductUnitId = $unit->id;
            $this->showInlineUnitForm = false;
            $this->reset(['newUnitCode', 'newUnitName', 'newUnitSymbol', 'newUnitAllowDecimal']);
            $this->dispatch('swal-toast', message: "Satuan baru '{$unit->name}' berhasil dibuat dan terpilih!", icon: 'success');
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('swal', title: 'Gagal', text: $this->errorMessage, icon: 'error');
        }
    }

    public function saveQuickProduct(ProductService $productService): void
    {
        $this->errorMessage = null;
        $user = Auth::user();
        $storeId = $user->store_id ?? Store::first()?->id ?? 1;

        if (trim($this->quickProductCode) === '' || trim($this->quickProductName) === '') {
            $this->errorMessage = 'Kode barang dan Nama produk wajib diisi.';
            $this->dispatch('swal', title: 'Perhatian', text: $this->errorMessage, icon: 'warning');

            return;
        }

        if (! $this->quickProductUnitId) {
            $this->errorMessage = 'Satuan dasar produk wajib dipilih.';
            $this->dispatch('swal', title: 'Perhatian', text: $this->errorMessage, icon: 'warning');

            return;
        }

        try {
            $product = $productService->createProduct([
                'store_id' => $storeId,
                'location_id' => $this->quickProductLocationId,
                'code' => trim($this->quickProductCode),
                'name' => trim($this->quickProductName),
                'base_unit_id' => $this->quickProductUnitId,
                'base_selling_price' => (float) $this->quickProductSellPrice,
                'minimum_stock_base' => (float) $this->quickProductMinStock,
                'initial_stock' => 0, // Stock will be added by receiving this purchase order
            ], $user->id);

            // Assign created product to target item row or new row
            if ($this->targetItemIndex !== null && isset($this->items[$this->targetItemIndex])) {
                $this->items[$this->targetItemIndex]['product_id'] = $product->id;
                $this->items[$this->targetItemIndex]['unit_id'] = $this->quickProductUnitId;
                $this->items[$this->targetItemIndex]['cost_price'] = (float) $this->quickProductBuyPrice;
            } else {
                // If no specific row targeted, check if empty first row exists or add new
                if (count($this->items) === 1 && empty($this->items[0]['product_id'])) {
                    $this->items[0]['product_id'] = $product->id;
                    $this->items[0]['unit_id'] = $this->quickProductUnitId;
                    $this->items[0]['cost_price'] = (float) $this->quickProductBuyPrice;
                } else {
                    $this->items[] = [
                        'product_id' => $product->id,
                        'unit_id' => $this->quickProductUnitId,
                        'quantity' => 1.0,
                        'cost_price' => (float) $this->quickProductBuyPrice,
                    ];
                }
            }

            $this->showQuickProductModal = false;
            $msg = "Produk '{$product->name}' berhasil dibuat dan dimasukkan ke daftar barang masuk!";
            $this->dispatch('swal-toast', message: $msg, icon: 'success');
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('swal', title: 'Gagal Membuat Produk', text: $this->errorMessage, icon: 'error');
        }
    }

    public function addItemRow(): void
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

    public function removeItemRow(int $index): void
    {
        array_splice($this->items, $index, 1);
        if (empty($this->items)) {
            $this->addItemRow();
        }
    }

    public function savePurchase(PurchaseService $purchaseService): void
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

    public function viewDetail(int $purchaseId): void
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
        $locations = Location::where('store_id', $storeId)->get();
        $units = Unit::all();

        $recentPurchases = Purchase::with(['supplier', 'receiver', 'items.product', 'items.unit'])
            ->where('store_id', $storeId)
            ->latest('purchased_at')
            ->take(15)
            ->get();

        return view('livewire.purchases.create', [
            'suppliers' => $suppliers,
            'products' => $products,
            'locations' => $locations,
            'units' => $units,
            'recentPurchases' => $recentPurchases,
        ]);
    }
}
