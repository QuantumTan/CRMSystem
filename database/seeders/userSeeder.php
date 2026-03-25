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
        User::updateOrCreate([
            'email' => 'admin@comp.com',
        ], [
            'name' => 'Admin User',
            'password' => Hash::make('admin'),
            'role' => 'admin',
        ]);

        User::updateOrCreate([
            'email' => 'manager@comp.com',
        ], [
            'name' => 'Manager User',
            'password' => Hash::make('manager'),
            'role' => 'manager',
        ]);

        $salesAccounts = [
            ['email' => 'sales@comp.com', 'name' => 'Sales User 1', 'password' => 'sales'],
            ['email' => 'sales2@comp.com', 'name' => 'Sales User 2', 'password' => 'sales2'],
            ['email' => 'sales3@comp.com', 'name' => 'Sales User 3', 'password' => 'sales3'],
            ['email' => 'sales4@comp.com', 'name' => 'Sales User 4', 'password' => 'sales4'],
            ['email' => 'sales5@comp.com', 'name' => 'Sales User 5', 'password' => 'sales5'],
        ];

        foreach ($salesAccounts as $account) {
            User::updateOrCreate([
                'email' => $account['email'],
            ], [
                'name' => $account['name'],
                'password' => Hash::make($account['password']),
                'role' => 'sales',
            ]);
        }
    }
}
