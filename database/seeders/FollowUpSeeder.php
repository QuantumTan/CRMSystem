<?php

namespace Database\Seeders;

use App\Models\FollowUp;
use App\Models\Lead;
use Illuminate\Database\Seeder;

class FollowUpSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $targetCount = 40;
        $currentCount = FollowUp::query()->count();

        if ($currentCount >= $targetCount) {
            return;
        }

        if (! Lead::query()->exists()) {
            return;
        }

        FollowUp::factory()
            ->count($targetCount - $currentCount)
            ->create();
    }
}
