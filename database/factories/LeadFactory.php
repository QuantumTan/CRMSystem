<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\User;
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
        $status = $this->faker->randomElement($statuses);
        $isLost = $status === 'lost';
        $salesUserId = User::query()->where('role', 'sales')->inRandomOrder()->value('id')
            ?? User::factory()->sales()->create()->id;

        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'source' => $this->faker->randomElement(['Website', 'Referral', 'Event', 'Cold Call', 'Email', 'Other']),
            'status' => $status,
            'priority' => $this->faker->randomElement($priorities),
            'expected_value' => $this->faker->numberBetween(1000, 100000),
            'notes' => $this->faker->optional(0.8)->paragraph(),
            'assigned_user_id' => $salesUserId,
            'lost_reason' => $isLost ? $this->faker->sentence() : null,
            'lost_category' => $isLost ? $this->faker->randomElement(array_keys(Lead::getLostCategories())) : null,
            'lost_at' => $isLost ? $this->faker->dateTimeBetween('-3 months', 'now') : null,
        ];
    }

    public function assignedTo(User|int $user): static
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $this->state([
            'assigned_user_id' => $userId,
        ]);
    }

    public function won(): static
    {
        return $this->state([
            'status' => 'won',
            'lost_reason' => null,
            'lost_category' => null,
            'lost_at' => null,
        ]);
    }

    public function lost(): static
    {
        return $this->state([
            'status' => 'lost',
            'lost_reason' => $this->faker->sentence(),
            'lost_category' => $this->faker->randomElement(array_keys(Lead::getLostCategories())),
            'lost_at' => $this->faker->dateTimeBetween('-3 months', 'now'),
        ]);
    }
}
