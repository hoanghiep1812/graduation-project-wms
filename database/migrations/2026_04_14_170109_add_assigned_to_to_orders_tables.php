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
            $table->unsignedBigInteger('assigned_to')->nullable()->after('status');
        });

        // Thêm cột cho Phiếu Xuất
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('assigned_to')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('assigned_to');
        });
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn('assigned_to');
        });
    }
};
