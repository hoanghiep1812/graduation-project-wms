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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained()->onDelete('cascade');
            $table->string('transaction_type'); // inbound, outbound, adjustment, transfer
            $table->integer('quantity_change'); // Số lượng thay đổi (có thể âm)
            $table->integer('balance_after');   // Tồn thực tế sau giao dịch

            // Đa hình: Liên kết tới PO, SO, hoặc Adjustment
            $table->nullableMorphs('reference');

            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
