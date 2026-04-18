<?php

namespace Database\Factories;

use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FollowUp>
 */
class FollowUpFactory extends Factory
{
    protected $model = FollowUp::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $lead = Lead::query()->inRandomOrder()->first() ?? Lead::factory()->create();
        $status = $this->faker->randomElement(['pending', 'completed']);

        return [
            'customer_id' => null,
            'lead_id' => $lead->id,
            'user_id' => $lead->assigned_user_id
                ?? User::query()->where('role', 'sales')->inRandomOrder()->value('id')
                ?? User::factory()->sales()->create()->id,
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->optional(0.85)->paragraph(),
            'due_date' => $status === 'completed'
                ? $this->faker->dateTimeBetween('-30 days', 'yesterday')
                : $this->faker->dateTimeBetween('-5 days', '+21 days'),
            'status' => $status,
        ];
    }

    public function forLead(Lead $lead): static
    {
        return $this->state([
            'customer_id' => null,
            'lead_id' => $lead->id,
            'user_id' => $lead->assigned_user_id
                ?? User::query()->where('role', 'sales')->inRandomOrder()->value('id')
                ?? User::factory()->sales()->create()->id,
        ]);
    }

    public function pending(): static
    {
        return $this->state([
            'status' => 'pending',
            'due_date' => $this->faker->dateTimeBetween('today', '+21 days'),
        ]);
    }

    public function completed(): static
    {
        return $this->state([
            'status' => 'completed',
            'due_date' => $this->faker->dateTimeBetween('-30 days', 'yesterday'),
        ]);
    }
}
