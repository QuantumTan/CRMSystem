<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure there are users and customers to relate to
        if (\App\Models\User::count() === 0) {
            \App\Models\User::factory()->count(3)->create();
        }
        if (\App\Models\Customer::count() === 0) {
            \App\Models\Customer::factory()->count(3)->create();
        }
        \App\Models\Lead::factory()->count(200)->create();
    }
}
