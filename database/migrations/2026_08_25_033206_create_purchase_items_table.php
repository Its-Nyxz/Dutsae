<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('product_code_snapshot', 50);
            $table->string('product_name_snapshot', 150);
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->string('unit_name_snapshot', 50);
            $table->decimal('conversion_factor_snapshot', 18, 4);
            $table->decimal('quantity', 18, 4);
            $table->decimal('quantity_base', 18, 4);
            $table->decimal('cost_price', 18, 2);
            $table->decimal('subtotal', 18, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};
