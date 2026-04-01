<?php

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['new', 'contacted', 'qualified', 'proposal_sent', 'negotiation', 'won', 'lost'];
        $priorities = ['low', 'medium', 'high', 'critical'];
        $userId = \App\Models\User::inRandomOrder()->value('id') ?? \App\Models\User::factory()->create()->id;
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'source' => $this->faker->randomElement(['Website', 'Referral', 'Event', 'Cold Call', 'Email', 'Other']),
            'status' => $this->faker->randomElement($statuses),
            'priority' => $this->faker->randomElement($priorities),
            'expected_value' => $this->faker->numberBetween(1000, 100000),
            'notes' => $this->faker->optional()->sentence(10),
            'assigned_user_id' => $userId,
        ];
    }
}
