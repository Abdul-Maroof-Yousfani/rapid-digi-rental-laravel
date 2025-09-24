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
        Schema::table('sale_people', function (Blueprint $table) {
            $table->string('zoho_salesperson_id')->nullable()->unique()->after('id');
            $table->string('email')->nullable()->unique()->after('name');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_people', function (Blueprint $table) {
            $table->dropColumn('zoho_salesperson_id');
            $table->dropColumn('email');
        });
    }
};
