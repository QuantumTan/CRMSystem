<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $assignmentStatus = $this->faker->randomElement(['pending', 'approved', 'rejected']);
        $isReviewed = in_array($assignmentStatus, ['approved', 'rejected']);
        $salesAssigneeId = User::query()->where('role', 'sales')->inRandomOrder()->value('id')
            ?? User::factory()->sales()->create()->id;

        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'company' => $this->faker->optional()->company(),
            'address' => $this->faker->optional()->address(),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'assigned_user_id' => $salesAssigneeId,
            'assignment_status' => $assignmentStatus,
            'assignment_reviewed_by' => $isReviewed
                ? User::query()->whereIn('role', ['admin', 'manager'])
                    ->inRandomOrder()
                    ->value('id')
                : null,
            'assignment_reviewed_at' => $isReviewed ? $this->faker->dateTimeBetween('-6 months', 'now') : null,
        ];
    }

    // states
    public function pending(): static
    {
        return $this->state([
            'assignment_status' => 'pending',
            'assignment_reviewed_by' => null,
            'assignment_reviewed_at' => null,
        ]);
    }

    public function approved(): static
    {
        return $this->state(function () {
            return [
                'assignment_status' => 'approved',
                'assignment_reviewed_by' => User::query()->whereIn('role', ['admin', 'manager'])
                    ->inRandomOrder()
                    ->value('id'),
                'assignment_reviewed_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            ];
        });
    }

    public function rejected(): static
    {
        return $this->state(function () {
            return [
                'assignment_status' => 'rejected',
                'assignment_reviewed_by' => User::query()->whereIn('role', ['admin', 'manager'])
                    ->inRandomOrder()
                    ->value('id'),
                'assignment_reviewed_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            ];
        });
    }

    public function unassigned(): static
    {
        return $this->state([
            'assigned_user_id' => null,
            'assignment_status' => 'pending',
            'assignment_reviewed_by' => null,
            'assignment_reviewed_at' => null,
        ]);
    }
}
