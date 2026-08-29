<?php

namespace App\Livewire\Notifications;

use App\Models\Sale;
use App\Services\DashboardService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dropdown extends Component
{
    public array $notifications = [];

    public int $unreadCount = 0;

    public function mount(DashboardService $dashboardService)
    {
        $this->loadNotifications($dashboardService);
    }

    public function loadNotifications(DashboardService $dashboardService)
    {
        $user = Auth::user();
        $storeId = $user->store_id ?? 1;

        $notifications = [];

        // 1. Piutang / Bon Jatuh Tempo & Segera Jatuh Tempo (H-3)
        $dueSales = Sale::where('store_id', $storeId)
            ->where('status', 'completed')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<=', now()->addDays(3))
            ->whereHas('payments', fn ($q) => $q->whereIn('payment_method', ['receivable', 'credit']))
            ->with(['customer', 'payments'])
            ->get()
            ->filter(fn ($s) => $s->outstanding_amount > 0);

        foreach ($dueSales as $sale) {
            $isOverdue = $sale->due_date->isPast();
            $customerName = $sale->customer?->name ?? 'Pelanggan';
            $notifications[] = [
                'type' => 'due_receivable',
                'title' => $isOverdue ? '🚨 Bon Lewat Jatuh Tempo' : '⚠️ Bon Mendekati Jatuh Tempo',
                'message' => "Bon {$customerName} (#{$sale->invoice_number}) sisa Rp ".number_format($sale->outstanding_amount, 0, ',', '.')." (Jatuh Tempo: {$sale->due_date->format('d/m/Y')})",
                'created_at' => $sale->due_date->diffForHumans(),
                'url' => route('receivables.index'),
            ];
        }

        // 2. Low stock notifications
        $lowStock = $dashboardService->getLowStockProducts($storeId);
        foreach ($lowStock as $p) {
            $notifications[] = [
                'type' => 'low_stock',
                'title' => 'Stok Menipis',
                'message' => "{$p->name} tersisa ".number_format($p->current_stock_base, 2, ',', '.')." {$p->baseUnit?->unit?->name}",
                'created_at' => now()->diffForHumans(),
                'url' => route('products.index'),
            ];
        }

        // 3. Recent sales notifications for Admin
        if ($user && $user->isAdmin()) {
            $recentSales = Sale::where('store_id', $storeId)
                ->where('status', 'completed')
                ->latest('sold_at')
                ->limit(5)
                ->get();

            foreach ($recentSales as $sale) {
                $notifications[] = [
                    'type' => 'new_sale',
                    'title' => 'Penjualan Baru',
                    'message' => "Invoice #{$sale->invoice_number} senilai Rp ".number_format($sale->grand_total, 0, ',', '.'),
                    'created_at' => $sale->sold_at->diffForHumans(),
                    'url' => route('pos'),
                ];
            }
        }

        $this->notifications = $notifications;
        $this->unreadCount = count($notifications);
    }

    public function render()
    {
        return view('livewire.notifications.dropdown');
    }
}
