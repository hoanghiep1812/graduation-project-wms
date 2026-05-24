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
        Schema::table('slotting_recommendations', function (Blueprint $table) {
            $table->unsignedBigInteger('recommended_bin_id')->nullable()->after('suggested_zone_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slotting_recommendations', function (Blueprint $table) {
            $table->dropColumn('recommended_bin_id');
        });
    }
};
