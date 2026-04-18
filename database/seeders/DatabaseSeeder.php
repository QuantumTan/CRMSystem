<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            userSeeder::class,
            SystemConfigurationSeeder::class,
            NormalizeCustomerAssignmentsSeeder::class,
            CustomerSeeder::class,
            LeadSeeder::class,
            ActivitySeeder::class,
            FollowUpSeeder::class,
        ]);
    }
}
