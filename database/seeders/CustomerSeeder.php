<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $salesUsers = User::query()
            ->where('role', 'sales')
            ->orderBy('id')
            ->get();

        if ($salesUsers->isEmpty()) {
            return;
        }

        foreach ($salesUsers as $salesUser) {
            // Pending customers assigned to each sales user.
            Customer::factory(8)
                ->pending()
                ->state([
                    'assigned_user_id' => $salesUser->id,
                ])
                ->create();

            // Reviewed assignment samples per sales user.
            Customer::factory(3)
                ->approved()
                ->state([
                    'assigned_user_id' => $salesUser->id,
                ])
                ->create();

            Customer::factory(2)
                ->rejected()
                ->state([
                    'assigned_user_id' => $salesUser->id,
                ])
                ->create();
        }
    }
}
