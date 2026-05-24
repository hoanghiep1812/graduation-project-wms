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
        Schema::create('slotting_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('current_bin_id')->constrained('bin_locations')->onDelete('cascade');
            $table->foreignId('suggested_zone_id')->constrained('zones')->onDelete('cascade');

            $table->text('reason'); // Lý do đề xuất (để hiển thị cho quản lý duyệt)
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slotting_recommendations');
    }
};
