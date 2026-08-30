<?php

namespace App\Livewire\Inventory;

use App\Models\InventoryBalance;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Store;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Penyesuaian Stok (Stock Opname) - Toko Duta Sae')]
class Adjustment extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showAdjustmentModal = false;

    public ?int $selectedProductId = null;

    public ?int $selectedUnitId = null;

    public float $currentSystemStock = 0.0;

    public float|string $actualPhysicalStock = 0.0;

    public string $reason = 'Barang Rusak / Patah / Berkarat';

    public bool $isCustomReason = false;

    public string $customReasonInput = '';

    public string $notes = '';

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    public array $defaultReasons = [
        'Barang Rusak / Patah / Berkarat',
        'Semen Mengeras / Kantong Sobek',
        'Selisih Hitung / Hilang Saat Bongkar',
        'Koreksi Saldo Awal Gudang',
        'Bonus / Tambahan dari Supplier',
        'Kedaluwarsa / Expired (Cat/Kimia)',
        'Pemakaian Internal Operasional Toko',
    ];

    public function openAdjustmentModal(?int $productId = null): void
    {
        $this->reset(['errorMessage', 'successMessage', 'notes', 'actualPhysicalStock', 'customReasonInput', 'isCustomReason']);
        $this->reason = 'Barang Rusak / Patah / Berkarat';

        $storeId = Auth::user()?->store_id ?? Store::first()?->id ?? 1;

        if ($productId) {
            $this->selectProduct($productId);
        } else {
            $firstProduct = Product::where('store_id', $storeId)->first();
            if ($firstProduct) {
                $this->selectProduct($firstProduct->id);
            }
        }

        $this->showAdjustmentModal = true;
    }

    public function selectReason(string $selected): void
    {
        if ($selected === '__custom__') {
            $this->isCustomReason = true;
            $this->reason = $this->customReasonInput ?: 'Alasan Kustom';
        } else {
            $this->isCustomReason = false;
            $this->reason = $selected;
        }
    }

    public function selectProduct(int $productId): void
    {
        $this->selectedProductId = $productId;
        $product = Product::with(['baseUnit.unit', 'productUnits.unit'])->find($productId);

        if ($product) {
            $this->selectedUnitId = $product->base_unit_id;
            $storeId = Auth::user()?->store_id ?? Store::first()?->id ?? 1;
            $balance = InventoryBalance::where('store_id', $storeId)->where('product_id', $productId)->first();
            $this->currentSystemStock = $balance ? (float) $balance->quantity_base : 0.0;
            $this->actualPhysicalStock = $this->currentSystemStock;
        }
    }

    public function getDifferenceProperty(): float
    {
        $actual = is_numeric($this->actualPhysicalStock) ? (float) $this->actualPhysicalStock : 0.0;

        return $actual - $this->currentSystemStock;
    }

    public function saveAdjustment(InventoryService $inventoryService): void
    {
        $this->errorMessage = null;

        if (! $this->selectedProductId) {
            $this->errorMessage = 'Silakan pilih produk terlebih dahulu.';
            $this->dispatch('swal', title: 'Perhatian', text: $this->errorMessage, icon: 'warning');

            return;
        }

        $actual = is_numeric($this->actualPhysicalStock) ? (float) $this->actualPhysicalStock : 0.0;
        $qtyDiffBase = $actual - $this->currentSystemStock;

        if (abs($qtyDiffBase) < 0.0001) {
            $this->errorMessage = 'Stok fisik riil sama dengan stok sistem (tidak ada selisih yang disesuaikan).';
            $this->dispatch('swal', title: 'Tidak Ada Selisih', text: $this->errorMessage, icon: 'info');

            return;
        }

        $product = Product::findOrFail($this->selectedProductId);
        $user = Auth::user();
        $storeId = $user->store_id ?? Store::first()?->id ?? 1;

        try {
            $finalReason = $this->isCustomReason ? trim($this->customReasonInput) : trim($this->reason);
            if (empty($finalReason)) {
                $finalReason = 'Lainnya';
            }

            $fullNote = "[{$finalReason}] ".trim($this->notes);

            $inventoryService->recordMovement(
                storeId: $storeId,
                productId: $product->id,
                type: 'adjustment',
                quantityBase: $qtyDiffBase,
                notes: $fullNote,
                createdBy: $user->id
            );

            $this->showAdjustmentModal = false;
            $diffText = ($qtyDiffBase > 0 ? '+'.number_format($qtyDiffBase, 2) : number_format($qtyDiffBase, 2));
            $this->successMessage = "Penyesuaian stok '{$product->name}' berhasil disimpan ({$diffText} unit)!";
            $this->dispatch('swal-toast', message: $this->successMessage, icon: 'success');
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('swal', title: 'Gagal Menyesuaikan Stok', text: $this->errorMessage, icon: 'error');
        }
    }

    public function render()
    {
        $storeId = Auth::user()?->store_id ?? Store::first()?->id ?? 1;

        $products = Product::where('store_id', $storeId)
            ->with(['baseUnit.unit', 'location'])
            ->orderBy('name')
            ->get();

        $adjustments = StockMovement::where('store_id', $storeId)
            ->where('type', 'adjustment')
            ->with(['product.baseUnit.unit', 'createdBy'])
            ->when($this->search, function ($q) {
                $q->whereHas('product', function ($p) {
                    $p->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('code', 'like', '%'.$this->search.'%');
                })->orWhere('notes', 'like', '%'.$this->search.'%');
            })
            ->latest('id')
            ->paginate(15);

        // Discover historical reasons from previous stock movements
        $historicalNotes = StockMovement::where('store_id', $storeId)
            ->where('type', 'adjustment')
            ->whereNotNull('notes')
            ->pluck('notes');

        $discoveredReasons = [];
        foreach ($historicalNotes as $n) {
            if (preg_match('/^\[(.*?)\]/', (string) $n, $matches)) {
                $r = trim($matches[1]);
                if (! empty($r)) {
                    $discoveredReasons[] = $r;
                }
            }
        }

        $allReasons = array_values(array_unique(array_merge($this->defaultReasons, $discoveredReasons)));

        return view('livewire.inventory.adjustment', [
            'products' => $products,
            'adjustments' => $adjustments,
            'allReasons' => $allReasons,
        ]);
    }
}
