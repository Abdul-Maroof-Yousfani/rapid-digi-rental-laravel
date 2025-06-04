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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('sale_people_id');
            $table->unsignedBigInteger('deposit_id');
            $table->string('agreement_no', 100)->nullable()->unique();
            $table->text('notes')->nullable();
            $table->enum('status', ['paid', 'pending'])->nullable();
            $table->enum('booking_status', ['closed', 'overdue'])->nullable();
            $table->enum('booking_cancel', [0, 1])->nullable();
            $table->integer('total_price')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('sale_person_id')->references('id')->on('sale_people')->onDelete('set null');
            $table->foreign('deposit_id')->references('id')->on('deposits')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
