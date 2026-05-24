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
            // Thêm cột batch_id
            $table->foreignId('batch_id')->nullable()->constrained('batches')->onDelete('cascade');

            // XÓA Unique Index cũ (Bạn cần check lại xem index cũ tên là gì trong DB, 
            // thông thường Laravel tự đặt là: bảng_cột1_cột2_unique. 
            // Nếu bạn chưa từng set unique cho inventory, có thể bỏ qua dòng dropUnique này)
            // $table->dropUnique(['product_id', 'warehouse_id', 'bin_location_id']);

            // TẠO Unique Index mới bao gồm cả batch_id. 
            // Đặt tên ngắn 'inv_pwd_bin_batch_unique' để không bị lỗi độ dài của MySQL
            $table->unique(
                ['product_id', 'warehouse_id', 'bin_location_id', 'batch_id'],
                'inv_pwd_bin_batch_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropUnique('inv_pwd_bin_batch_unique');
            $table->dropForeign(['batch_id']);
            $table->dropColumn('batch_id');
        });
    }
};
