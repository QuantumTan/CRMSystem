<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class userSeeder extends Seeder
{
    private function upsertUser(string $email, string $name, string $password, string $role): void
    {
        $user = User::withTrashed()->firstOrNew([
            'email' => $email,
        ]);

        $user->fill([
            'name' => $name,
            'password' => Hash::make($password),
            'role' => $role,
        ]);

        $user->deleted_at = null;
        $user->save();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->upsertUser('admin@comp.com', 'Admin User', 'admin', 'admin');
        $this->upsertUser('manager@comp.com', 'Manager User', 'manager', 'manager');

        $salesAccounts = [
            ['email' => 'sales@comp.com', 'name' => 'Sales User 1', 'password' => 'sales'],
            ['email' => 'sales2@comp.com', 'name' => 'Sales User 2', 'password' => 'sales2'],
            ['email' => 'sales3@comp.com', 'name' => 'Sales User 3', 'password' => 'sales3'],
            ['email' => 'sales4@comp.com', 'name' => 'Sales User 4', 'password' => 'sales4'],
            ['email' => 'sales5@comp.com', 'name' => 'Sales User 5', 'password' => 'sales5'],
        ];

        foreach ($salesAccounts as $account) {
            $this->upsertUser($account['email'], $account['name'], $account['password'], 'sales');
        }
    }
}
