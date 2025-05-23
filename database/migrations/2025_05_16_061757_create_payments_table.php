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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('restrict');
            $table->unsignedBigInteger('payment_method');
            $table->foreign('payment_method')->references('id')->on('payment_methods')->onDelete('set null');
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->foreign('bank_id')->references('id')->on('banks')->onDelete('restrict');
            $table->string('receipt')->nullable();
            $table->decimal('booking_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2);
            $table->decimal('pending_amount', 10, 2)->nullable();
            $table->enum('payment_status', ['paid', 'pending'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
