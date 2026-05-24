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
        Schema::table('purchase_orders', function (Blueprint $table) {

            // ID supplier (quan trọng)
            $table->unsignedBigInteger('supplier_id')->nullable()->after('po_number');

            // Kho (nếu có nhiều warehouse)
            $table->unsignedBigInteger('warehouse_id')->nullable()->after('supplier_id');

            // Ngày dự kiến nhập
            $table->date('expected_date')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn([
                'supplier_id',
                'warehouse_id',
                'expected_date'
            ]);
        });
    }
};
