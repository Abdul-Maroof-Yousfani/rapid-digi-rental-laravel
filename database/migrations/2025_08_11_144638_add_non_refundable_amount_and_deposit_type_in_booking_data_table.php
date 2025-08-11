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
        Schema::table('booking_data', function (Blueprint $table) {
            $table->decimal('non_refundable_amount', 10, 2)->nullable();
            $table->unsignedTinyInteger('deposit_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_data', function (Blueprint $table) {
            $table->dropColumn('non_refundable_amount');
            $table->dropColumn('deposit_type');
        });
    }
};
