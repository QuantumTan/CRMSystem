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
        $assignmentStatus = fake()->randomElement(['pending', 'approved', 'rejected']);
        $isReviewed = in_array($assignmentStatus, ['approved', 'rejected']);

        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'company' => fake()->optional()->company(),
            'address' => fake()->optional()->address(),
            'status' => fake()->randomElement(['active', 'inactive']),
            'assigned_user_id' => User::query()->inRandomOrder()->value('id'),
            'assignment_status' => $assignmentStatus,
            'assignment_reviewed_by' => $isReviewed
                ? User::query()->whereIn('role', ['admin', 'manager'])
                    ->inRandomOrder()
                    ->value('id')
                : null,
            'assignment_reviewed_at' => $isReviewed ? fake()->dateTimeBetween('-6 months', 'now') : null,
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
                'assignment_reviewed_at' => fake()->dateTimeBetween('-6 months', 'now'),
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
                'assignment_reviewed_at' => fake()->dateTimeBetween('-6 months', 'now'),
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
