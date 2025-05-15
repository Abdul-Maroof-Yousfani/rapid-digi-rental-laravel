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
            $table->longText('description')->nullable();
            $table->decimal('rate', 10, 2)->nullable();
            $table->integer('quantity')->nullable();
            $table->integer('tax_percent')->nullable();
            $table->decimal('item_total', 12, 2)->nullable();
            $table->string('tax_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_data', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'rate',
                'quantity',
                'tax_percent',
                'item_total',
                'tax_name',
            ]);
        });
    }
};
