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
        User::factory()->create([
            'name' => 'Admin Bos',
            'email' => 'admin@gmail.com',
            'role' => 'admin', // Match existing role field value
            'password' => Hash::make('1234567890'),
        ])->assignRole('admin');

        // Create users with existing roles but assign them the new Spatie roles as well
        // Create manager user
        // First role user
        User::factory()->create([
            'name' => 'Sales Staff',
            'email' => 'sales@gmail.com',
            'role' => 'first', // This must match your DB enum
            'password' => Hash::make('1234567890'),
        ])->assignRole('sales'); // Assign Spatie role

        // Second role user
        User::factory()->create([
            'name' => 'Inventory Staff',
            'email' => 'inventory@gmail.com',
            'role' => 'second', // This must match your DB enum
            'password' => Hash::make('1234567890'),
        ])->assignRole('inventory'); // Assign Spatie role

        // Third role user
        User::factory()->create([
            'name' => 'Finance Staff',
            'email' => 'finance@gmail.com',
            'role' => 'third', // This must match your DB enum
            'password' => Hash::make('1234567890'),
        ])->assignRole('finance'); // Assign Spatie role

        
    }
}

