<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Customer::factory(50)->create();
        Customer::factory(30)->approved()->create();
        Customer::factory(10)->rejected()->create();
        Customer::factory(5)->unassigned()->create();
    }
}
