<?php

namespace App\Services;

use App\Models\InventoryBalance;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Record a stock movement and update inventory balance atomically.
     */
    public function recordMovement(
        int $storeId,
        int $productId,
        string $type,
        float $quantityBase,
        ?int $warehouseId = null,
        ?int $locationId = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null,
        ?int $createdBy = null
    ): StockMovement {
        return DB::transaction(function () use (
            $storeId,
            $productId,
            $type,
            $quantityBase,
            $warehouseId,
            $locationId,
            $referenceType,
            $referenceId,
            $notes,
            $createdBy
        ) {
            // Find or lock existing balance
            $balanceModel = InventoryBalance::where('store_id', $storeId)
                ->where('warehouse_id', $warehouseId)
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->first();

            $balanceBefore = $balanceModel ? (float) $balanceModel->quantity_base : 0.0;
            $balanceAfter = $balanceBefore + $quantityBase;

            if ($balanceModel) {
                $balanceModel->update([
                    'quantity_base' => $balanceAfter,
                ]);
            } else {
                InventoryBalance::create([
                    'store_id' => $storeId,
                    'warehouse_id' => $warehouseId,
                    'product_id' => $productId,
                    'quantity_base' => $balanceAfter,
                ]);
            }

            return StockMovement::create([
                'store_id' => $storeId,
                'warehouse_id' => $warehouseId,
                'location_id' => $locationId,
                'product_id' => $productId,
                'type' => $type,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'quantity_base' => $quantityBase,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'notes' => $notes,
                'created_by' => $createdBy,
            ]);
        });
    }

    /**
     * Get current available base quantity for a product.
     */
    public function getAvailableStock(Product $product, int $storeId, ?int $warehouseId = null): float
    {
        $query = InventoryBalance::where('store_id', $storeId)
            ->where('product_id', $product->id);

        if ($warehouseId !== null) {
            $query->where('warehouse_id', $warehouseId);
        }

        return (float) $query->sum('quantity_base');
    }
}
