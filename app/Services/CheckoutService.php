<?php

namespace App\Services;

use App\Events\LowStockDetectedEvent;
use App\Events\SaleCompletedEvent;
use App\Models\Customer;
use App\Models\InventoryBalance;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CheckoutService
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Process atomic checkout transaction.
     */
    public function processCheckout(array $data, User $cashier): Sale
    {
        return DB::transaction(function () use ($data, $cashier) {
            $storeId = $data['store_id'];
            $items = $data['items'];
            $paymentsData = $data['payments'];
            $customerId = $data['customer_id'] ?? null;
            $status = $data['status'] ?? 'completed'; // completed, pending_hold

            if (empty($items)) {
                throw new InvalidArgumentException('Keranjang penjualan tidak boleh kosong.');
            }

            // 1. Calculate Totals & Validate Stock (with lockForUpdate)
            $subtotal = 0;
            $discountTotal = (float) ($data['discount_total'] ?? 0);
            $itemsToProcess = [];

            foreach ($items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $unit = Unit::findOrFail($itemData['unit_id']);

                $productUnit = ProductUnit::where('product_id', $product->id)
                    ->where('unit_id', $unit->id)
                    ->first();

                if (! $productUnit) {
                    throw new InvalidArgumentException("Satuan '{$unit->name}' tidak valid untuk produk '{$product->name}'.");
                }

                $conversionFactor = (float) $productUnit->conversion_factor;
                $qty = (float) $itemData['quantity'];
                $qtyBase = $qty * $conversionFactor;
                $unitPrice = (float) ($itemData['unit_price'] ?? $productUnit->selling_price);
                $discountAmount = (float) ($itemData['discount_amount'] ?? 0);
                $itemSubtotal = ($qty * $unitPrice) - $discountAmount;

                $subtotal += $itemSubtotal;

                // Concurrency Safety: Lock Inventory row
                $inventoryBalance = InventoryBalance::where('store_id', $storeId)
                    ->where('product_id', $product->id)
                    ->lockForUpdate()
                    ->first();

                $currentAvailable = $inventoryBalance ? (float) $inventoryBalance->quantity_base : 0.0;

                if ($status === 'completed' && $currentAvailable < $qtyBase) {
                    throw new InvalidArgumentException("Stok tidak mencukupi untuk '{$product->name}'. Stok tersedia: {$currentAvailable}, dibutuhkan: {$qtyBase}.");
                }

                $itemsToProcess[] = [
                    'product' => $product,
                    'unit' => $unit,
                    'conversion_factor' => $conversionFactor,
                    'quantity' => $qty,
                    'quantity_base' => $qtyBase,
                    'unit_price' => $unitPrice,
                    'discount_amount' => $discountAmount,
                    'subtotal' => $itemSubtotal,
                    'current_available' => $currentAvailable,
                ];
            }

            $grandTotal = max(0, $subtotal - $discountTotal);

            // 2. Customer validation if paying via receivable/credit
            if ($customerId) {
                $customer = Customer::find($customerId);
            }

            // 3. Generate Invoice Number
            $invoiceNumber = $data['invoice_number'] ?? ('INV-'.date('Ymd').'-'.str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT));

            // 4. Create Sale Record
            $sale = Sale::create([
                'store_id' => $storeId,
                'invoice_number' => $invoiceNumber,
                'customer_id' => $customerId,
                'cashier_id' => $cashier->id,
                'status' => $status,
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'grand_total' => $grandTotal,
                'due_date' => $data['due_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'sold_at' => now(),
            ]);

            // 5. Create Sale Items & Deduct Stock if Completed
            $lowStockProducts = [];

            foreach ($itemsToProcess as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product']->id,
                    'product_code_snapshot' => $item['product']->code,
                    'product_name_snapshot' => $item['product']->name,
                    'unit_id' => $item['unit']->id,
                    'unit_name_snapshot' => $item['unit']->name,
                    'conversion_factor_snapshot' => $item['conversion_factor'],
                    'quantity' => $item['quantity'],
                    'quantity_base' => $item['quantity_base'],
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => $item['discount_amount'],
                    'subtotal' => $item['subtotal'],
                    'cost_snapshot' => 0, // Option to snapshot buy price
                ]);

                if ($status === 'completed') {
                    // Record negative stock movement for sale
                    $movement = $this->inventoryService->recordMovement(
                        storeId: $storeId,
                        productId: $item['product']->id,
                        type: 'sale',
                        quantityBase: -$item['quantity_base'],
                        warehouseId: $data['warehouse_id'] ?? null,
                        referenceType: Sale::class,
                        referenceId: $sale->id,
                        notes: 'Penjualan POS Invoice: '.$sale->invoice_number,
                        createdBy: $cashier->id
                    );

                    // Check low stock threshold
                    $remainingBase = $movement->balance_after;
                    $minStock = (float) $item['product']->minimum_stock_base;

                    if ($remainingBase <= $minStock) {
                        $lowStockProducts[] = [
                            'product' => $item['product'],
                            'remaining_base' => $remainingBase,
                        ];
                    }
                }
            }

            // 6. Create Payments
            if ($status === 'completed' && ! empty($paymentsData)) {
                foreach ($paymentsData as $paymentItem) {
                    Payment::create([
                        'store_id' => $storeId,
                        'sale_id' => $sale->id,
                        'payment_method' => $paymentItem['payment_method'],
                        'amount' => (float) $paymentItem['amount'],
                        'reference_number' => $paymentItem['reference_number'] ?? null,
                        'paid_at' => now(),
                        'received_by' => $cashier->id,
                        'notes' => $paymentItem['notes'] ?? null,
                    ]);
                }
            }

            // 7. Dispatch Events for Notifications (if Laravel Events exist)
            if ($status === 'completed') {
                event(new SaleCompletedEvent($sale));

                foreach ($lowStockProducts as $lsp) {
                    event(new LowStockDetectedEvent($lsp['product'], $lsp['remaining_base']));
                }
            }

            return $sale;
        });
    }
}
