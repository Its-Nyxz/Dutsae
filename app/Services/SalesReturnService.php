<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Sale;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SalesReturnService
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Process a sales return atomically:
     * 1. Validate returned quantities vs purchased quantities
     * 2. Restock products back to inventory
     * 3. Handle refund/receivable reduction
     * 4. Record sales return header & items
     */
    public function processReturn(array $data, User $handler): SalesReturn
    {
        return DB::transaction(function () use ($data, $handler) {
            $saleId = $data['sale_id'];
            $items = $data['items'];
            $refundMethod = $data['refund_method'] ?? 'deduct_receivable'; // deduct_receivable, cash, none
            $reason = $data['reason'] ?? null;

            $sale = Sale::with(['items', 'customer', 'payments'])->findOrFail($saleId);
            $storeId = $sale->store_id;

            if (empty($items)) {
                throw new InvalidArgumentException('Daftar barang yang diretur tidak boleh kosong.');
            }

            $totalReturnedAmount = 0.0;
            $itemsToProcess = [];

            // Calculate previous returns for this sale to ensure not over-returning
            $existingReturns = SalesReturn::with('items')->where('sale_id', $sale->id)->get();
            $previouslyReturnedQtyByProductId = [];
            foreach ($existingReturns as $prevRet) {
                foreach ($prevRet->items as $prevItem) {
                    $pid = $prevItem->product_id;
                    $previouslyReturnedQtyByProductId[$pid] = ($previouslyReturnedQtyByProductId[$pid] ?? 0) + (float) $prevItem->quantity_base;
                }
            }

            foreach ($items as $itemData) {
                $qtyToReturn = (float) ($itemData['quantity'] ?? 0);
                if ($qtyToReturn <= 0) {
                    continue;
                }

                $productId = (int) $itemData['product_id'];
                $unitId = (int) $itemData['unit_id'];

                $product = Product::findOrFail($productId);
                $unit = Unit::findOrFail($unitId);

                // Find original sale item
                $originalSaleItem = $sale->items->firstWhere('product_id', $productId);
                if (! $originalSaleItem) {
                    throw new InvalidArgumentException("Produk '{$product->name}' tidak ditemukan pada faktur penjualan {$sale->invoice_number}.");
                }

                $productUnit = ProductUnit::where('product_id', $productId)->where('unit_id', $unitId)->first();
                $conversionFactor = $productUnit ? (float) $productUnit->conversion_factor : 1.0;
                $qtyBaseToReturn = $qtyToReturn * $conversionFactor;

                // Validate against original sold quantity base
                $totalSoldQtyBase = (float) $originalSaleItem->quantity_base;
                $alreadyReturnedQtyBase = $previouslyReturnedQtyByProductId[$productId] ?? 0.0;
                $maxReturnableQtyBase = max(0.0, $totalSoldQtyBase - $alreadyReturnedQtyBase);

                if ($qtyBaseToReturn > ($maxReturnableQtyBase + 0.0001)) {
                    throw new InvalidArgumentException("Jumlah retur untuk '{$product->name}' melebihi sisa yang dapat diretur ({$maxReturnableQtyBase} unit dasar).");
                }

                $unitPrice = (float) ($itemData['unit_price'] ?? $originalSaleItem->unit_price);
                $subtotal = $qtyToReturn * $unitPrice;
                $totalReturnedAmount += $subtotal;

                $itemsToProcess[] = [
                    'product_id' => $product->id,
                    'product_code' => $product->code,
                    'product_name' => $product->name,
                    'unit_id' => $unit->id,
                    'unit_name' => $unit->name,
                    'quantity' => $qtyToReturn,
                    'conversion_factor' => $conversionFactor,
                    'quantity_base' => $qtyBaseToReturn,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ];
            }

            if (empty($itemsToProcess)) {
                throw new InvalidArgumentException('Kuantitas barang yang diretur harus lebih dari 0.');
            }

            // Generate Return Number
            $lastReturnId = (int) (SalesReturn::where('store_id', $storeId)->max('id') ?? 0);
            $nextNumber = str_pad((string) ($lastReturnId + 1), 4, '0', STR_PAD_LEFT);
            $returnNumber = 'RET-'.date('Ymd').'-'.$nextNumber;

            $salesReturn = SalesReturn::create([
                'store_id' => $storeId,
                'sale_id' => $sale->id,
                'return_number' => $returnNumber,
                'customer_id' => $sale->customer_id,
                'handled_by' => $handler->id,
                'total_returned_amount' => $totalReturnedAmount,
                'refund_method' => $refundMethod,
                'reason' => $reason,
                'returned_at' => now(),
            ]);

            // Create Return Items & Restock Inventory
            foreach ($itemsToProcess as $item) {
                SalesReturnItem::create([
                    'sales_return_id' => $salesReturn->id,
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'],
                    'quantity' => $item['quantity'],
                    'conversion_factor_snapshot' => $item['conversion_factor'],
                    'quantity_base' => $item['quantity_base'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                    'product_code_snapshot' => $item['product_code'],
                    'product_name_snapshot' => $item['product_name'],
                    'unit_name_snapshot' => $item['unit_name'],
                ]);

                // Restock back to inventory (+ quantity_base)
                $this->inventoryService->recordMovement(
                    storeId: $storeId,
                    productId: $item['product_id'],
                    type: 'sales_return',
                    quantityBase: $item['quantity_base'],
                    warehouseId: null,
                    locationId: null,
                    referenceType: SalesReturn::class,
                    referenceId: $salesReturn->id,
                    notes: "Retur Penjualan {$returnNumber} (Faktur: {$sale->invoice_number})",
                    createdBy: $handler->id
                );
            }

            // If cash refund, record refund payment log or adjust receivable
            if ($refundMethod === 'cash') {
                Payment::create([
                    'store_id' => $storeId,
                    'sale_id' => $sale->id,
                    'payment_method' => 'cash_refund',
                    'amount' => -$totalReturnedAmount,
                    'reference_number' => $returnNumber,
                    'paid_at' => now(),
                    'received_by' => $handler->id,
                    'notes' => "Pengembalian Kas Retur Barang #{$returnNumber}",
                ]);
            }

            return $salesReturn;
        });
    }
}
