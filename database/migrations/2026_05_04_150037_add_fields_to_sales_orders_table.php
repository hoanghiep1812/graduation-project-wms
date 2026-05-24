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
        Schema::table('sales_orders', function (Blueprint $table) {

            if (!Schema::hasColumn('sales_orders', 'partner_id')) {
                $table->unsignedBigInteger('partner_id')->after('so_number');
            }

            if (!Schema::hasColumn('sales_orders', 'warehouse_id')) {
                $table->unsignedBigInteger('warehouse_id')->default(4)->after('partner_id');
            }

            if (!Schema::hasColumn('sales_orders', 'assigned_to')) {
                $table->unsignedBigInteger('assigned_to')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn([
                'partner_id',
                'warehouse_id',
                'assigned_to'
            ]);
        });
    }
};
