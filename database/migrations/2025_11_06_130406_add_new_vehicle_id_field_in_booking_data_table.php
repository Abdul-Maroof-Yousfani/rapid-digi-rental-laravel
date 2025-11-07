<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('booking_data', function (Blueprint $table) {
            $table->unsignedBigInteger('new_vehicle_id')->after('vehicle_id')->nullable();
            $table->foreign('new_vehicle_id')->references('id')->on('vehicles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_data', function (Blueprint $table) {
            $table->dropForeign(['new_vehicle_id']);
            $table->dropColumn('new_vehicle_id');
        });
    }
};
