<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    protected $model = Activity::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hasLeads = Lead::query()->exists();
        $hasCustomers = Customer::query()->exists();
        $attachToLead = $hasLeads && (! $hasCustomers || $this->faker->boolean(65));

        $lead = $attachToLead ? Lead::query()->inRandomOrder()->first() : null;
        $customer = $attachToLead ? null : Customer::query()->inRandomOrder()->first();

        if (! $lead && ! $customer) {
            $lead = Lead::factory()->create();
        }

        $userId = $lead?->assigned_user_id
            ?? $customer?->assigned_user_id
            ?? User::query()->where('role', 'sales')->inRandomOrder()->value('id')
            ?? User::factory()->sales()->create()->id;

        return [
            'customer_id' => $customer?->id,
            'lead_id' => $lead?->id,
            'user_id' => $userId,
            'activity_type' => $this->faker->randomElement(['call', 'email', 'meeting', 'note']),
            'description' => $this->faker->paragraphs($this->faker->numberBetween(1, 2), true),
            'activity_date' => $this->faker->dateTimeBetween('-90 days', 'now'),
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

    public function forCustomer(Customer $customer): static
    {
        return $this->state([
            'customer_id' => $customer->id,
            'lead_id' => null,
            'user_id' => $customer->assigned_user_id
                ?? User::query()->where('role', 'sales')->inRandomOrder()->value('id')
                ?? User::factory()->sales()->create()->id,
        ]);
    }
}
