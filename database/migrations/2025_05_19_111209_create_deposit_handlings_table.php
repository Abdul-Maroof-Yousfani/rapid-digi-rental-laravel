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
        Schema::create('deposit_handlings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_data_id');
            $table->foreign('payment_data_id')->references('id')->on('payment_data')->onDelete('restrict');
            $table->unsignedBigInteger('booking_id');
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('restrict');
            $table->integer('deduct_deposit')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposit_handlings');
    }
};
