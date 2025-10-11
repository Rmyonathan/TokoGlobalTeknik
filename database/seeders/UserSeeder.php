<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create admin user (keep original role designation)
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin Bos',
                'role' => 'admin',
                'password' => Hash::make('1234567890'),
            ]
        );
        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        // Create users with existing roles but assign them the new Spatie roles as well
        // First role user
        $sales = User::firstOrCreate(
            ['email' => 'sales@gmail.com'],
            [
                'name' => 'Sales Staff',
                'role' => 'first',
                'password' => Hash::make('1234567890'),
            ]
        );
        if (!$sales->hasRole('sales')) {
            $sales->assignRole('sales');
        }

        // Second role user
        $inventory = User::firstOrCreate(
            ['email' => 'inventory@gmail.com'],
            [
                'name' => 'Inventory Staff',
                'role' => 'second',
                'password' => Hash::make('1234567890'),
            ]
        );
        if (!$inventory->hasRole('inventory')) {
            $inventory->assignRole('inventory');
        }

        // Third role user
        $finance = User::firstOrCreate(
            ['email' => 'finance@gmail.com'],
            [
                'name' => 'Finance Staff',
                'role' => 'third',
                'password' => Hash::make('1234567890'),
            ]
        );
        if (!$finance->hasRole('finance')) {
            $finance->assignRole('finance');
        }
    }
}

