<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class VehicleTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('vehicletypes')->insert([
            [ 'name' => 'Cars' ],
            [ 'name' => 'Truck' ],
            [ 'name' => 'Coaster' ],
            [ 'name' => 'Bus' ],
            [ 'name' => 'Van' ],
        ]);
    }
}
