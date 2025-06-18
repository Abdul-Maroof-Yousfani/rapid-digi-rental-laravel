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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_name')->nullable();
            $table->string('temp_vehicle_detail')->nullable();
            $table->unsignedBigInteger('vehicletypes');
            $table->foreign('vehicletypes')->references('id')->on('vehicletypes');
            $table->unsignedBigInteger('investor_id');
            $table->foreign('investor_id')->references('id')->on('investors');
            $table->string('car_make')->nullable();
            $table->string('year')->nullable();
            $table->string('number_plate')->unique();
            $table->enum('status', [1, 0])->nullable()->default(1);
            $table->unsignedBigInteger('vehicle_status_id')->nullable();
            $table->foreign('vehicle_status_id')->references('id')->on('vehicle_statuses')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
