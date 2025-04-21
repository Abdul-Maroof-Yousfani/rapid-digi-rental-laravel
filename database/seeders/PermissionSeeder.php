<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin related permissions
        Permission::create(['name' => 'view admin dashboard']);
        Permission::create(['name' => 'manage customers']);
        Permission::create(['name' => 'manage investors']);
        Permission::create(['name' => 'manage bookers']);
        Permission::create(['name' => 'manage vehicles']);
        Permission::create(['name' => 'import vehicles CSV']);
        Permission::create(['name' => 'manage vehicle types']);

        // Booker related permissions
        Permission::create(['name' => 'view booker dashboard']);
        Permission::create(['name' => 'edit booker details']);
        Permission::create(['name' => 'create booking']);

        // Investor related permissions
        Permission::create(['name' => 'view investor dashboard']);
        Permission::create(['name' => 'edit investor details']);
    }
}