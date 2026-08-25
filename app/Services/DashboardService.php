<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get Omzet Hari Ini (Total Grand Total of completed sales sold today).
     */
    public function getTodayTurnover(int $storeId): float
    {
        return (float) Sale::where('store_id', $storeId)
            ->where('status', 'completed')
            ->whereDate('sold_at', Carbon::today())
            ->sum('grand_total');
    }

    /**
     * Get Uang Masuk Hari Ini (Total actual cash/payments received today).
     */
    public function getTodayIncomingPayments(int $storeId): float
    {
        return (float) Payment::where('store_id', $storeId)
            ->whereDate('paid_at', Carbon::today())
            ->sum('amount');
    }

    /**
     * Get Today's Completed Transaction Count.
     */
    public function getTodayTransactionCount(int $storeId): int
    {
        return Sale::where('store_id', $storeId)
            ->where('status', 'completed')
            ->whereDate('sold_at', Carbon::today())
            ->count();
    }

    /**
     * Get Average Transaction Value Today.
     */
    public function getAverageTransactionValue(int $storeId): float
    {
        $count = $this->getTodayTransactionCount($storeId);
        if ($count === 0) {
            return 0.0;
        }

        return $this->getTodayTurnover($storeId) / $count;
    }

    /**
     * Get Low Stock Products (Base stock <= minimum_stock_base).
     */
    public function getLowStockProducts(int $storeId)
    {
        return Product::with(['baseUnit.unit', 'inventoryBalance'])
            ->where('products.store_id', $storeId)
            ->where('products.is_active', true)
            ->join('inventory_balances', 'products.id', '=', 'inventory_balances.product_id')
            ->whereColumn('inventory_balances.quantity_base', '<=', 'products.minimum_stock_base')
            ->select('products.*', 'inventory_balances.quantity_base as current_stock_base')
            ->get();
    }

    /**
     * Get Out of Stock Products (Base stock <= 0).
     */
    public function getOutOfStockProducts(int $storeId)
    {
        return Product::with(['baseUnit.unit', 'inventoryBalance'])
            ->where('products.store_id', $storeId)
            ->where('products.is_active', true)
            ->join('inventory_balances', 'products.id', '=', 'inventory_balances.product_id')
            ->where('inventory_balances.quantity_base', '<=', 0)
            ->select('products.*', 'inventory_balances.quantity_base as current_stock_base')
            ->get();
    }

    /**
     * Get Top Selling Products (by total quantity sold).
     */
    public function getTopSellingProducts(int $storeId, int $limit = 5)
    {
        return SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.store_id', $storeId)
            ->where('sales.status', 'completed')
            ->select(
                'sale_items.product_id',
                'sale_items.product_code_snapshot',
                'sale_items.product_name_snapshot',
                DB::raw('SUM(sale_items.quantity_base) as total_qty_base'),
                DB::raw('SUM(sale_items.subtotal) as total_sales_amount')
            )
            ->groupBy(
                'sale_items.product_id',
                'sale_items.product_code_snapshot',
                'sale_items.product_name_snapshot'
            )
            ->orderByDesc('total_qty_base')
            ->limit($limit)
            ->get();
    }
}
