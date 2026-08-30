<?php

namespace App\Livewire\Returns;

use App\Models\Sale;
use App\Models\SalesReturn;
use App\Models\Store;
use App\Services\SalesReturnService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Retur Penjualan - Toko Duta Sae')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showCreateModal = false;

    public bool $showDetailModal = false;

    public ?int $selectedSaleId = null;

    public ?Sale $selectedSale = null;

    public array $returnItems = [];

    public string $refundMethod = 'deduct_receivable'; // deduct_receivable, cash, none

    public string $reason = '';

    public ?SalesReturn $detailReturn = null;

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    public function openCreateModal(): void
    {
        $this->reset(['selectedSaleId', 'selectedSale', 'returnItems', 'reason', 'errorMessage', 'successMessage']);
        $this->refundMethod = 'deduct_receivable';
        $this->showCreateModal = true;
    }

    public function selectSale(int $saleId): void
    {
        $this->selectedSaleId = $saleId;
        $sale = Sale::with(['items.product', 'items.unit', 'customer', 'payments'])->find($saleId);

        if ($sale) {
            $this->selectedSale = $sale;

            // Check existing returns for this sale to calculate max returnable qty
            $existingReturns = SalesReturn::with('items')->where('sale_id', $sale->id)->get();
            $alreadyReturnedByProduct = [];
            foreach ($existingReturns as $ret) {
                foreach ($ret->items as $rItem) {
                    $pid = $rItem->product_id;
                    $alreadyReturnedByProduct[$pid] = ($alreadyReturnedByProduct[$pid] ?? 0) + (float) $rItem->quantity;
                }
            }

            $this->returnItems = [];
            foreach ($sale->items as $item) {
                $alreadyReturned = (float) ($alreadyReturnedByProduct[$item->product_id] ?? 0);
                $maxReturnable = max(0.0, (float) $item->quantity - $alreadyReturned);

                $this->returnItems[] = [
                    'product_id' => $item->product_id,
                    'product_code' => $item->product?->code ?? '-',
                    'product_name' => $item->product?->name ?? 'Produk',
                    'unit_id' => $item->unit_id,
                    'unit_name' => $item->unit?->name ?? '-',
                    'sold_quantity' => (float) $item->quantity,
                    'already_returned' => $alreadyReturned,
                    'max_returnable' => $maxReturnable,
                    'unit_price' => (float) $item->unit_price,
                    'return_quantity' => 0.0,
                    'subtotal' => 0.0,
                ];
            }

            // Set smart default refund method
            if ($sale->outstanding_amount > 0) {
                $this->refundMethod = 'deduct_receivable';
            } else {
                $this->refundMethod = 'cash';
            }
        }
    }

    public function updateReturnQuantity(int $index, float|string|int|null $quantity = 0): void
    {
        if (isset($this->returnItems[$index])) {
            $parsedQty = is_numeric($quantity) ? (float) $quantity : 0.0;
            $max = (float) $this->returnItems[$index]['max_returnable'];
            $qty = max(0.0, min($max, $parsedQty));
            $this->returnItems[$index]['return_quantity'] = $qty;
            $this->returnItems[$index]['subtotal'] = $qty * (float) $this->returnItems[$index]['unit_price'];
        }
    }

    public function getTotalReturnAmountProperty(): float
    {
        return array_reduce($this->returnItems, fn ($carry, $item) => $carry + (float) ($item['subtotal'] ?? 0), 0.0);
    }

    public function saveReturn(SalesReturnService $returnService): void
    {
        $this->errorMessage = null;

        if (! $this->selectedSaleId) {
            $this->errorMessage = 'Silakan pilih faktur penjualan yang akan diretur.';
            $this->dispatch('swal', title: 'Perhatian', text: $this->errorMessage, icon: 'warning');

            return;
        }

        $itemsToReturn = array_filter($this->returnItems, fn ($item) => (float) ($item['return_quantity'] ?? 0) > 0);

        if (empty($itemsToReturn)) {
            $this->errorMessage = 'Masukkan minimal satu kuantitas barang yang diretur (lebih dari 0).';
            $this->dispatch('swal', title: 'Perhatian', text: $this->errorMessage, icon: 'warning');

            return;
        }

        $user = Auth::user();

        try {
            $salesReturn = $returnService->processReturn([
                'sale_id' => $this->selectedSaleId,
                'refund_method' => $this->refundMethod,
                'reason' => $this->reason,
                'items' => array_map(fn ($item) => [
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'],
                    'quantity' => $item['return_quantity'],
                    'unit_price' => $item['unit_price'],
                ], array_values($itemsToReturn)),
            ], $user);

            $this->showCreateModal = false;
            $formattedAmount = number_format($salesReturn->total_returned_amount, 0, ',', '.');
            $this->successMessage = "Retur Penjualan {$salesReturn->return_number} (Rp {$formattedAmount}) berhasil disimpan!";
            $this->dispatch('swal', title: 'Retur Sukses! 🎉', text: "Retur {$salesReturn->return_number} senilai Rp {$formattedAmount} berhasil dicatat dan stok gudang telah dikembalikan.", icon: 'success');
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('swal', title: 'Gagal Memproses Retur', text: $this->errorMessage, icon: 'error');
        }
    }

    public function viewReturnDetail(int $returnId): void
    {
        $this->detailReturn = SalesReturn::with(['sale', 'customer', 'handler', 'items.product', 'items.unit'])->find($returnId);
        if ($this->detailReturn) {
            $this->showDetailModal = true;
        }
    }

    public function render()
    {
        $storeId = Auth::user()?->store_id ?? Store::first()?->id ?? 1;

        $returns = SalesReturn::where('store_id', $storeId)
            ->with(['sale', 'customer', 'handler', 'items'])
            ->when($this->search, function ($q) {
                $q->where('return_number', 'like', '%'.$this->search.'%')
                    ->orWhereHas('sale', fn ($s) => $s->where('invoice_number', 'like', '%'.$this->search.'%'))
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', '%'.$this->search.'%'));
            })
            ->latest('id')
            ->paginate(15);

        $recentSales = Sale::where('store_id', $storeId)
            ->with(['customer'])
            ->latest('id')
            ->take(30)
            ->get();

        return view('livewire.returns.index', [
            'returns' => $returns,
            'recentSales' => $recentSales,
        ]);
    }
}
