<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->string('return_number', 100)->unique();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('handled_by')->constrained('users')->cascadeOnDelete();
            $table->decimal('total_returned_amount', 18, 2)->default(0);
            $table->string('refund_method', 50)->default('deduct_receivable'); // deduct_receivable, cash, none
            $table->text('reason')->nullable();
            $table->timestamp('returned_at')->useCurrent();
            $table->timestamps();

            $table->index(['store_id', 'returned_at']);
        });

        Schema::create('sales_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_return_id')->constrained('sales_returns')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->decimal('quantity', 18, 4);
            $table->decimal('conversion_factor_snapshot', 18, 4)->default(1);
            $table->decimal('quantity_base', 18, 4);
            $table->decimal('unit_price', 18, 2);
            $table->decimal('subtotal', 18, 2);
            $table->string('product_code_snapshot', 100)->nullable();
            $table->string('product_name_snapshot', 255)->nullable();
            $table->string('unit_name_snapshot', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_return_items');
        Schema::dropIfExists('sales_returns');
    }
};
