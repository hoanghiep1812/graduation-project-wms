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
        Schema::create('product_demand_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            // Lịch sử bán hàng
            $table->integer('sales_30_days')->default(0);
            $table->integer('sales_90_days')->default(0);

            // Phân loại ABC
            $table->enum('velocity_category', ['FAST_MOVING', 'MEDIUM_MOVING', 'SLOW_MOVING'])->default('SLOW_MOVING');

            $table->timestamp('last_calculated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_demand_metrics');
    }
};
