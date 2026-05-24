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
        Schema::table('inventories', function (Blueprint $table) {
            // 1. Xóa rào cản (Index) cũ đang bị sai
            $table->dropUnique('inventories_product_id_warehouse_id_bin_location_id_unique');

            // 2. Thêm rào cản (Index) mới: Bao gồm thêm cột batch_id
            // Đặt tên ngắn gọn 'inv_prod_wh_bin_batch_unq' để tránh lỗi tên quá dài của MySQL
            $table->unique(
                ['product_id', 'warehouse_id', 'bin_location_id', 'batch_id'],
                'inv_prod_wh_bin_batch_unq'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            // Hoàn tác lại nếu cần rollback
            $table->dropUnique('inv_prod_wh_bin_batch_unq');
            $table->unique(['product_id', 'warehouse_id', 'bin_location_id']);
        });
    }
};
