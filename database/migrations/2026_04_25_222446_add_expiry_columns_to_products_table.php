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
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('has_expiry')->default(false)->after('is_active')->comment('Sản phẩm có theo dõi HSD không?');
            $table->integer('expiry_duration')->nullable()->after('has_expiry')->comment('Hạn sử dụng mặc định (Tính theo THÁNG)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['has_expiry', 'expiry_duration']);
        });
    }
};
