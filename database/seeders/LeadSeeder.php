<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Seeder;

class LeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $targetCount = 60;
        $currentCount = Lead::query()->count();

        if ($currentCount >= $targetCount) {
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

        $statusCycle = [
            'new',
            'contacted',
            'qualified',
            'proposal_sent',
            'negotiation',
            'won',
            'lost',
        ];

        $shortfall = $targetCount - $currentCount;
        $salesUserCount = $salesUsers->count();
        $statusCount = count($statusCycle);

        for ($index = 0; $index < $shortfall; $index++) {
            $salesUser = $salesUsers[$index % $salesUserCount];
            $status = $statusCycle[$index % $statusCount];

            $factory = Lead::factory()->assignedTo($salesUser);

            $factory = match ($status) {
                'won' => $factory->won(),
                'lost' => $factory->lost(),
                default => $factory->state([
                        'status' => $status,
                        'lost_reason' => null,
                        'lost_category' => null,
                        'lost_at' => null,
                    ]),
            };

            $factory->create();
        }
    }
}
