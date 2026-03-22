<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class userSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@comp.com',
            'password' => Hash::make('admin'),
            'role' => 'admin',
        ]);
        User::create([
            'name' => 'Manager User',
            'email' => 'manager@comp.com',
            'password' => Hash::make('manager'),
            'role' => 'manager',
        ]);
        User::create([
            'name' => 'Sales User',
            'email' => 'sales@comp.com',
            'password' => Hash::make('sales'),
            'role' => 'sales',
        ]);

        // after creating the values register it to the databaseseeder
    }
}
