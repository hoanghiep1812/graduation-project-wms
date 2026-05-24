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
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('batch_number');
            $table->date('manufactured_date')->nullable();
            $table->date('expiry_date')->nullable(); // Dùng cho FIFO/FEFO
            $table->timestamps();

            // Đảm bảo mã lô không trùng lặp cho cùng 1 sản phẩm
            $table->unique(['product_id', 'batch_number'], 'product_batch_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
