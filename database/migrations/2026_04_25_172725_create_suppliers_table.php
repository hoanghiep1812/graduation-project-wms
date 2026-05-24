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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('Mã NCC (VD: NCC001)');
	        $table->string('name')->comment('Tên Nhà cung cấp');
	        $table->string('phone', 20)->nullable();
	        $table->string('email')->nullable();
	        $table->string('tax_code', 50)->nullable()->comment('Mã số thuế');
	        $table->text('address')->nullable();
	        $table->enum('status', ['active', 'inactive'])->default('active')->comment('Trạng thái giao dịch');
	        $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
