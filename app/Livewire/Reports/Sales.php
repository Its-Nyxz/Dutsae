<?php

namespace App\Livewire\Reports;

use App\Models\Sale;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Laporan Omzet & Penjualan')]
class Sales extends Component
{
    public string $startDate = '';

    public string $endDate = '';

    public string $search = '';

    public string $paymentMethod = '';

    public string $paymentStatus = '';

    // Detail Modal State
    public bool $showDetailModal = false;

    public ?Sale $selectedSale = null;

    public function mount(): void
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
    }

    public function setFilter(string $range): void
    {
        if ($range === 'today') {
            $this->startDate = Carbon::now()->format('Y-m-d');
            $this->endDate = Carbon::now()->format('Y-m-d');
        } elseif ($range === 'yesterday') {
            $this->startDate = Carbon::now()->subDay()->format('Y-m-d');
            $this->endDate = Carbon::now()->subDay()->format('Y-m-d');
        } elseif ($range === '7days') {
            $this->startDate = Carbon::now()->subDays(6)->format('Y-m-d');
            $this->endDate = Carbon::now()->format('Y-m-d');
        } elseif ($range === 'this_month') {
            $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
            $this->endDate = Carbon::now()->format('Y-m-d');
        } elseif ($range === 'last_month') {
            $this->startDate = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
            $this->endDate = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
        } elseif ($range === 'this_year') {
            $this->startDate = Carbon::now()->startOfYear()->format('Y-m-d');
            $this->endDate = Carbon::now()->format('Y-m-d');
        }
    }

    public function resetFilters(): void
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
        $this->search = '';
        $this->paymentMethod = '';
        $this->paymentStatus = '';
    }

    public function viewDetail(int $saleId): void
    {
        $sale = Sale::with(['items.product', 'items.unit', 'customer', 'cashier', 'payments'])->find($saleId);

        if ($sale) {
            $this->selectedSale = $sale;
            $this->showDetailModal = true;
        }
    }

    public function render()
    {
        $storeId = Auth::user()?->store_id ?? Store::first()?->id ?? 1;

        $query = Sale::with(['customer', 'cashier', 'payments', 'items.product', 'items.unit'])
            ->where('store_id', $storeId)
            ->where('status', 'completed');

        if ($this->startDate !== '') {
            $query->whereDate('sold_at', '>=', $this->startDate);
        }

        if ($this->endDate !== '') {
            $query->whereDate('sold_at', '<=', $this->endDate);
        }

        if ($this->paymentMethod !== '') {
            $pm = $this->paymentMethod;
            $query->whereHas('payments', fn ($p) => $p->where('payment_method', $pm));
        }

        if ($this->paymentStatus !== '') {
            if ($this->paymentStatus === 'paid') {
                $query->whereDoesntHave('payments', fn ($p) => $p->whereIn('payment_method', ['receivable', 'credit']));
            } elseif ($this->paymentStatus === 'receivable') {
                $query->whereHas('payments', fn ($p) => $p->whereIn('payment_method', ['receivable', 'credit']));
            }
        }

        if (trim($this->search) !== '') {
            $term = trim($this->search);
            $query->where(function ($q) use ($term) {
                $q->where('invoice_number', 'like', "%{$term}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('cashier', fn ($u) => $u->where('name', 'like', "%{$term}%"));
            });
        }

        $sales = $query->latest('sold_at')->get();

        // Calculate Metric Summaries
        $totalTurnover = (float) $sales->sum('grand_total');
        $totalPaid = (float) $sales->sum('paid_amount');
        $totalOutstanding = (float) $sales->sum('outstanding_amount');
        $totalInvoices = $sales->count();
        $totalDiscounts = (float) $sales->sum('discount_total');

        return view('livewire.reports.sales', [
            'sales' => $sales,
            'totalTurnover' => $totalTurnover,
            'totalPaid' => $totalPaid,
            'totalOutstanding' => $totalOutstanding,
            'totalInvoices' => $totalInvoices,
            'totalDiscounts' => $totalDiscounts,
        ]);
    }
}
