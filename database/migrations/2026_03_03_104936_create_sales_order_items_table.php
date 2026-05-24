<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')
                ->constrained('sales_orders')
                ->onDelete('cascade');

            $table->foreignId('product_id')
                ->constrained('products');

            $table->unsignedInteger('quantity');
            $table->unsignedInteger('shipped_quantity')->default(0);

            $table->timestamps();

            // tránh trùng sản phẩm trong cùng 1 đơn
            $table->unique(['sales_order_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_order_items');
    }
};
