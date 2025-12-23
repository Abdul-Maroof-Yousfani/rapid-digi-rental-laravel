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
        Schema::table('deposits', function (Blueprint $table) {
            $table->boolean('is_transferred')->default(false);

            $table->unsignedBigInteger('transferred_booking_id')->nullable();
            $table->foreign('transferred_booking_id')->references('id')->on('bookings')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->dropForeign(['transferred_booking_id']);
            $table->dropColumn('transferred_booking_id');
            $table->dropColumn('is_transferred');
        });
    }
};
