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
        Schema::table('payment_data', function (Blueprint $table) {
            $table->string('reference_invoice_number')->nullable()->after('payment_id');
            $table->string('remarks')->nullable()->after('reference_invoice_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_data', function (Blueprint $table) {
            $table->dropColumn('reference_invoice_number');
            $table->dropColumn('remarks');
        });
    }
};
