<?php

namespace App\Livewire\Receivables;

use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Sale;
use App\Models\Store;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Buku Piutang & Pelunasan Bon Pelanggan')]
class Index extends Component
{
    public string $search = '';

    // Payment Modal State
    public bool $showPaymentModal = false;

    public ?int $selectedCustomerId = null;

    public $amount = 0;

    public string $paymentMethod = 'cash';

    public string $referenceNumber = '';

    public string $notes = '';

    // Customer Ledger Modal State (Kartu Piutang)
    public bool $showLedgerModal = false;

    public ?Customer $ledgerCustomer = null;

    public array $ledgerHistory = [];

    public ?string $errorMessage = null;

    public function openPaymentModal(int $customerId)
    {
        $customer = Customer::find($customerId);

        if ($customer) {
            $this->selectedCustomerId = $customer->id;
            $this->amount = $customer->outstanding_receivable;
            $this->paymentMethod = 'cash';
            $this->referenceNumber = '';
            $this->notes = '';
            $this->errorMessage = null;
            $this->showPaymentModal = true;
        }
    }

    public function closeModal()
    {
        $this->showPaymentModal = false;
        $this->showLedgerModal = false;
        $this->selectedCustomerId = null;
        $this->ledgerCustomer = null;
        $this->ledgerHistory = [];
        $this->errorMessage = null;
    }

    public function savePayment()
    {
        $this->errorMessage = null;
        $user = Auth::user();
        $storeId = $user->store_id ?? Store::first()?->id ?? 1;

        if (! $this->selectedCustomerId) {
            return;
        }

        $paidAmount = is_numeric($this->amount) ? (float) $this->amount : 0.0;

        if ($paidAmount <= 0) {
            $this->errorMessage = 'Nominal pembayaran pelunasan piutang harus lebih dari Rp 0.';
            $this->dispatch('swal', title: 'Perhatian', text: $this->errorMessage, icon: 'warning');

            return;
        }

        $customer = Customer::findOrFail($this->selectedCustomerId);

        try {
            $paymentNumber = 'PAY-'.date('Ymd').'-'.rand(1000, 9999);

            $payment = CustomerPayment::create([
                'store_id' => $storeId,
                'customer_id' => $customer->id,
                'payment_number' => $paymentNumber,
                'amount' => $paidAmount,
                'payment_method' => $this->paymentMethod,
                'reference_number' => trim($this->referenceNumber),
                'notes' => trim($this->notes),
                'paid_at' => now(),
                'received_by' => $user->id,
            ]);

            $this->closeModal();
            $msg = "Pembayaran pelunasan piutang '{$customer->name}' sebesar Rp ".number_format($paidAmount, 0, ',', '.').' berhasil disimpan!';
            $this->dispatch('swal-toast', message: $msg, icon: 'success');
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('swal', title: 'Gagal Simpan Pembayaran', text: $this->errorMessage, icon: 'error');
        }
    }

    public function viewLedger(int $customerId)
    {
        $customer = Customer::with(['sales.payments', 'customerPayments.receiver'])->find($customerId);

        if (! $customer) {
            return;
        }

        $this->ledgerCustomer = $customer;

        // Build Chronological Ledger History (Sales + Payments)
        $history = [];

        // Credit Sales
        $creditSales = Sale::where('customer_id', $customer->id)
            ->whereHas('payments', fn ($q) => $q->whereIn('payment_method', ['receivable', 'credit']))
            ->get();

        foreach ($creditSales as $s) {
            $history[] = [
                'type' => 'sale',
                'date' => $s->sold_at ?? $s->created_at,
                'ref' => $s->invoice_number,
                'description' => 'Faktur Bon / Piutang Penjualan',
                'debit' => (float) $s->grand_total,
                'credit' => 0.0,
            ];
        }

        // Payments
        foreach ($customer->customerPayments as $p) {
            $history[] = [
                'type' => 'payment',
                'date' => $p->paid_at ?? $p->created_at,
                'ref' => $p->payment_number,
                'description' => 'Pelunasan / Angsuran Bon ('.strtoupper($p->payment_method).')',
                'debit' => 0.0,
                'credit' => (float) $p->amount,
            ];
        }

        // Sort by Date ascending
        usort($history, fn ($a, $b) => $a['date'] <=> $b['date']);

        // Calculate running balance
        $running = 0.0;
        foreach ($history as &$h) {
            $running += ($h['debit'] - $h['credit']);
            $h['balance'] = max(0.0, $running);
        }

        $this->ledgerHistory = $history;
        $this->showLedgerModal = true;
    }

    public function render()
    {
        $storeId = Auth::user()?->store_id ?? Store::first()?->id ?? 1;

        $customers = Customer::where('store_id', $storeId)
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('code', 'like', "%{$this->search}%");
            })
            ->get()
            ->sortByDesc(fn ($c) => $c->outstanding_receivable);

        $totalReceivableAll = $customers->sum(fn ($c) => $c->outstanding_receivable);
        $totalCustomersWithCredit = $customers->filter(fn ($c) => $c->outstanding_receivable > 0)->count();

        $thisMonthPaid = CustomerPayment::where('store_id', $storeId)
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        return view('livewire.receivables.index', [
            'customers' => $customers,
            'totalReceivableAll' => $totalReceivableAll,
            'totalCustomersWithCredit' => $totalCustomersWithCredit,
            'thisMonthPaid' => $thisMonthPaid,
        ]);
    }
}
