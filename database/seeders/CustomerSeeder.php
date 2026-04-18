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
        if (Customer::query()->exists()) {
            return;
        }

        $salesUsers = User::query()
            ->where('role', 'sales')
            ->orderBy('id')
            ->get();

        if ($salesUsers->isEmpty()) {
            $salesUsers = User::factory()
                ->count(3)
                ->sales()
                ->create();
        }

        foreach ($salesUsers as $salesUser) {
            // Pending customers assigned to each sales user.
            Customer::factory(5)
                ->pending()
                ->state([
                    'assigned_user_id' => $salesUser->id,
                ])
                ->create();

            // Reviewed assignment samples per sales user.
            Customer::factory(2)
                ->approved()
                ->state([
                    'assigned_user_id' => $salesUser->id,
                ])
                ->create();

            Customer::factory(1)
                ->rejected()
                ->state([
                    'assigned_user_id' => $salesUser->id,
                ])
                ->create();
        }
    }
}
