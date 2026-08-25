<?php

namespace App\Services;

use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ProductService
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Create product with units and optional initial stock.
     */
    public function createProduct(array $data, ?int $createdBy = null): Product
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $storeId = $data['store_id'];
            $code = trim($data['code']);

            // Validate manual product code uniqueness per store
            $exists = Product::where('store_id', $storeId)
                ->where('code', $code)
                ->exists();

            if ($exists) {
                throw new InvalidArgumentException("Kode barang '{$code}' sudah digunakan di toko ini.");
            }

            $product = Product::create([
                'store_id' => $storeId,
                'category_id' => $data['category_id'] ?? null,
                'location_id' => $data['location_id'] ?? null,
                'code' => $code,
                'name' => trim($data['name']),
                'description' => $data['description'] ?? null,
                'minimum_stock_base' => $data['minimum_stock_base'] ?? 0,
                'is_active' => $data['is_active'] ?? true,
            ]);

            // Create Base Unit
            $baseUnitId = $data['base_unit_id'];
            $basePrice = $data['base_selling_price'] ?? 0;

            $baseProductUnit = ProductUnit::create([
                'product_id' => $product->id,
                'unit_id' => $baseUnitId,
                'conversion_factor' => 1.0,
                'selling_price' => $basePrice,
                'barcode' => $data['barcode'] ?? null,
                'is_base_unit' => true,
            ]);

            // Additional Units (if provided)
            if (! empty($data['additional_units']) && is_array($data['additional_units'])) {
                foreach ($data['additional_units'] as $unitData) {
                    if (! empty($unitData['unit_id'])) {
                        ProductUnit::create([
                            'product_id' => $product->id,
                            'unit_id' => $unitData['unit_id'],
                            'conversion_factor' => $unitData['conversion_factor'],
                            'selling_price' => $unitData['selling_price'],
                            'barcode' => $unitData['barcode'] ?? null,
                            'is_base_unit' => false,
                        ]);
                    }
                }
            }

            // Initial Stock
            $initialStock = (float) ($data['initial_stock'] ?? 0);
            if ($initialStock > 0) {
                $this->inventoryService->recordMovement(
                    storeId: $storeId,
                    productId: $product->id,
                    type: 'opening',
                    quantityBase: $initialStock,
                    warehouseId: $data['warehouse_id'] ?? null,
                    locationId: $product->location_id,
                    notes: 'Stok Awal Produk Baru',
                    createdBy: $createdBy
                );
            }

            return $product;
        });
    }

    /**
     * Update unit selling price and log to price history.
     */
    public function updateUnitPrice(ProductUnit $productUnit, float $newPrice, string $reason, int $changedBy): void
    {
        DB::transaction(function () use ($productUnit, $newPrice, $reason, $changedBy) {
            $oldPrice = (float) $productUnit->selling_price;

            if ($oldPrice === $newPrice) {
                return;
            }

            $productUnit->update(['selling_price' => $newPrice]);

            PriceHistory::create([
                'product_id' => $productUnit->product_id,
                'unit_id' => $productUnit->unit_id,
                'old_price' => $oldPrice,
                'new_price' => $newPrice,
                'type' => 'selling_price',
                'reason' => $reason,
                'changed_by' => $changedBy,
            ]);
        });
    }
}
