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

    // Detail Modal State
    public bool $showDetailModal = false;

    public ?Sale $selectedSale = null;

    public function mount()
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
    }

    public function setFilter(string $range)
    {
        if ($range === 'today') {
            $this->startDate = Carbon::now()->format('Y-m-d');
            $this->endDate = Carbon::now()->format('Y-m-d');
        } elseif ($range === '7days') {
            $this->startDate = Carbon::now()->subDays(6)->format('Y-m-d');
            $this->endDate = Carbon::now()->format('Y-m-d');
        } elseif ($range === 'this_month') {
            $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
            $this->endDate = Carbon::now()->format('Y-m-d');
        }
    }

    public function viewDetail(int $saleId)
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

        $query = Sale::with(['customer', 'cashier', 'payments'])
            ->where('store_id', $storeId)
            ->whereDate('sold_at', '>=', $this->startDate)
            ->whereDate('sold_at', '<=', $this->endDate);

        if (trim($this->search) !== '') {
            $term = trim($this->search);
            $query->where(function ($q) use ($term) {
                $q->where('invoice_number', 'like', "%{$term}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$term}%"));
            });
        }

        $sales = $query->latest('sold_at')->get();

        // Calculate Metric Summaries
        $totalTurnover = $sales->sum('grand_total');
        $totalPaid = $sales->sum('paid_amount');
        $totalOutstanding = $sales->sum('outstanding_amount');
        $totalInvoices = $sales->count();

        return view('livewire.reports.sales', [
            'sales' => $sales,
            'totalTurnover' => $totalTurnover,
            'totalPaid' => $totalPaid,
            'totalOutstanding' => $totalOutstanding,
            'totalInvoices' => $totalInvoices,
        ]);
    }
}
