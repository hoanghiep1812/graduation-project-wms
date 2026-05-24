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
        Schema::table('bin_locations', function (Blueprint $table) {
            // Thêm Zone (nullable để không báo lỗi với các bin đã có sẵn trong DB của bạn)
            $table->foreignId('zone_id')->nullable()->constrained()->onDelete('set null');

            $table->integer('current_capacity')->default(0); // Sức chứa đang bị chiếm dụng
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bin_locations', function (Blueprint $table) {
            $table->dropForeign(['zone_id']);
            $table->dropColumn(['zone_id', 'current_capacity']);
        });
    }
};
