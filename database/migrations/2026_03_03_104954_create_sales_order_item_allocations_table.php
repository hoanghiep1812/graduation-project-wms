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
        Schema::create('sales_order_item_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_item_id')
                ->constrained('sales_order_items')
                ->onDelete('cascade');

            $table->foreignId('inventory_id')
                ->constrained('inventories');

            $table->unsignedInteger('allocated_quantity');

            $table->timestamps();

            $table->unique(
                ['sales_order_item_id', 'inventory_id'],
                'so_item_inv_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_order_item_allocations');
    }
};
