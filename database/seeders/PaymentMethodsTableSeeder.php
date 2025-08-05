<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('payment_methods')->insert([
            [ 'name' => 'Cash', 'created_at' => now(), 'updated_at' => now() ],
            [ 'name' => 'Credit Card', 'created_at' => now(), 'updated_at' => now() ],
            [ 'name' => 'Bank', 'created_at' => now(), 'updated_at' => now() ],
            [ 'name' => 'Others', 'created_at' => now(), 'updated_at' => now() ],
        ]);
    }
}
