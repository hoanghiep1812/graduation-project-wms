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
        Schema::create('inventory_audit_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_audit_id')->constrained()->onDelete('cascade');
            $table->foreignId('inventory_id')->constrained(); // Lô hàng/Kệ đang kiểm
            $table->integer('system_quantity'); // Số lượng lúc chốt sổ trên hệ thống
            $table->integer('actual_quantity')->nullable(); // Số lượng đếm thực tế bằng tay
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_audit_items');
    }
};
