<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PurchaseService
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    public function receivePurchaseOrder(array $data, User $receiver): Purchase
    {
        return $this->recordPurchase($data, $receiver);
    }

    /**
     * Record incoming goods from supplier and increment stock.
     */
    public function recordPurchase(array $data, User $receiver): Purchase
    {
        return DB::transaction(function () use ($data, $receiver) {
            $storeId = $data['store_id'];
            $items = $data['items'];

            if (empty($items)) {
                throw new InvalidArgumentException('Transaksi barang masuk harus memiliki minimal 1 barang.');
            }

            $grandTotal = 0;
            foreach ($items as $item) {
                $grandTotal += (float) ($item['subtotal'] ?? ($item['quantity'] * $item['cost_price']));
            }

            $invNumber = trim($data['invoice_supplier_number'] ?? '');
            if ($invNumber === '') {
                $invNumber = 'INV-SUP-'.date('Ymd').'-'.rand(1000, 9999);
            }

            $purchase = Purchase::create([
                'store_id' => $storeId,
                'invoice_supplier_number' => $invNumber,
                'supplier_id' => $data['supplier_id'],
                'received_by' => $receiver->id,
                'status' => 'completed',
                'grand_total' => $grandTotal,
                'notes' => $data['notes'] ?? null,
                'purchased_at' => $data['purchased_at'] ?? now(),
            ]);

            foreach ($items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $unit = Unit::findOrFail($itemData['unit_id']);

                $productUnit = ProductUnit::where('product_id', $product->id)
                    ->where('unit_id', $unit->id)
                    ->first();

                $conversionFactor = $productUnit ? (float) $productUnit->conversion_factor : 1.0;
                $qty = (float) $itemData['quantity'];
                $qtyBase = $qty * $conversionFactor;
                $costPrice = (float) $itemData['cost_price'];
                $subtotal = (float) ($itemData['subtotal'] ?? ($qty * $costPrice));

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'product_code_snapshot' => $product->code,
                    'product_name_snapshot' => $product->name,
                    'unit_id' => $unit->id,
                    'unit_name_snapshot' => $unit->name,
                    'conversion_factor_snapshot' => $conversionFactor,
                    'quantity' => $qty,
                    'quantity_base' => $qtyBase,
                    'cost_price' => $costPrice,
                    'subtotal' => $subtotal,
                ]);

                // Record stock movement type 'purchase'
                $this->inventoryService->recordMovement(
                    storeId: $storeId,
                    productId: $product->id,
                    type: 'purchase',
                    quantityBase: $qtyBase,
                    warehouseId: $data['warehouse_id'] ?? null,
                    referenceType: Purchase::class,
                    referenceId: $purchase->id,
                    notes: 'Barang Masuk Supplier Faktur: '.$purchase->invoice_supplier_number,
                    createdBy: $receiver->id
                );
            }

            return $purchase;
        });
    }
}
