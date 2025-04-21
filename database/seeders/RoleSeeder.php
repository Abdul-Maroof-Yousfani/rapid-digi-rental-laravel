<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
                // Fetch roles from the database
                $adminRole = Role::findByName('admin');
                $bookerRole = Role::findByName('booker');
                $investorRole = Role::findByName('investor');
        
                // Assign permissions to Admin role
                $adminRole->givePermissionTo([
                    'manage customers',
                    'manage investors',
                    'manage bookers',
                    'manage vehicles',
                    'import vehicles CSV',
                    'manage vehicle types',
                ]);

                // Assign permissions to Booker role
                $bookerRole->givePermissionTo([
                    'view booker dashboard',
                    'edit booker details',
                    'manage booking',
                ]);
        
                // Assign permissions to Investor role
                $investorRole->givePermissionTo([
                    'view investor dashboard',
                    'edit investor details',
                ]);
    }
}
