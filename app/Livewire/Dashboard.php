<?php

namespace App\Livewire;

use App\Models\Store;
use App\Services\DashboardService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Dashboard Operasional - Toko Besi')]
class Dashboard extends Component
{
    public float $todayTurnover = 0;

    public float $todayIncomingPayments = 0;

    public int $todayTransactionCount = 0;

    public float $averageTransactionValue = 0;

    public array $lowStockProducts = [];

    public array $outOfStockProducts = [];

    public array $topSellingProducts = [];

    public function mount(DashboardService $dashboardService)
    {
        $user = Auth::user();
        $storeId = $user->store_id ?? Store::first()?->id ?? 1;

        $this->todayTurnover = $dashboardService->getTodayTurnover($storeId);
        $this->todayIncomingPayments = $dashboardService->getTodayIncomingPayments($storeId);
        $this->todayTransactionCount = $dashboardService->getTodayTransactionCount($storeId);
        $this->averageTransactionValue = $dashboardService->getAverageTransactionValue($storeId);
        $this->lowStockProducts = $dashboardService->getLowStockProducts($storeId)->toArray();
        $this->outOfStockProducts = $dashboardService->getOutOfStockProducts($storeId)->toArray();
        $this->topSellingProducts = $dashboardService->getTopSellingProducts($storeId, 5)->toArray();
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
