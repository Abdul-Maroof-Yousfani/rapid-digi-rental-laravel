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
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->unsignedBigInteger('sale_people_id');
            $table->foreign('sale_person_id')->references('id')->on('sale_people')->onDelete('set null');
            $table->unsignedBigInteger('deposit_id');
            $table->foreign('deposit_id')->references('id')->on('deposits')->onDelete('set null');
            $table->string('agreement_no', 100)->nullable()->unique();
            $table->text('notes')->nullable();
            $table->integer('total_price')->nullable();
            $table->softDeletes();
            $table->timestamps();
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
