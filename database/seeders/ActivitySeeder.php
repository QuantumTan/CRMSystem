<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\Lead;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $targetCount = 80;
        $currentCount = Activity::query()->count();

        if ($currentCount >= $targetCount) {
            return;
        }

        if (! Lead::query()->exists() && ! Customer::query()->exists()) {
            return;
        }

        Activity::factory()
            ->count($targetCount - $currentCount)
            ->create();
    }
}
