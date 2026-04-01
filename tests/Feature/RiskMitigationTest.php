<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiskMitigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_routes_are_disabled(): void
    {
        $this->get('/register')->assertNotFound();

        $this->post('/register', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertNotFound();
    }

    public function test_lead_assignment_rejects_non_sales_user(): void
    {
        /** @var User $admin */
        $admin = User::factory()->admin()->create();
        /** @var User $manager */
        $manager = User::factory()->manager()->create();

        $this->actingAs($admin)
            ->post(route('leads.store'), [
                'name' => 'Acme Prospect',
                'email' => 'prospect@example.com',
                'phone' => '1234567890',
                'source' => 'Website',
                'status' => 'new',
                'priority' => 'medium',
                'expected_value' => 12000,
                'notes' => 'Initial contact',
                'assigned_user_id' => $manager->id,
            ])
            ->assertSessionHasErrors('assigned_user_id');

        $this->assertDatabaseMissing('leads', [
            'email' => 'prospect@example.com',
        ]);
    }

    public function test_follow_up_creation_requires_exactly_one_parent_target(): void
    {
        /** @var User $admin */
        $admin = User::factory()->admin()->create();
        /** @var User $sales */
        $sales = User::factory()->sales()->create();
        $customer = Customer::factory()->approved()->create([
            'assigned_user_id' => $sales->id,
        ]);
        $lead = Lead::factory()->create([
            'assigned_user_id' => $sales->id,
        ]);

        $this->actingAs($admin)
            ->post(route('follow-ups.store'), [
                'customer_id' => $customer->id,
                'lead_id' => $lead->id,
                'user_id' => $sales->id,
                'title' => 'Check in',
                'description' => 'Follow up on proposal',
                'due_date' => now()->addDay()->toDateString(),
                'status' => 'pending',
            ])
            ->assertSessionHasErrors(['customer_id', 'lead_id']);
    }

    public function test_activity_creation_requires_exactly_one_parent_target(): void
    {
        /** @var User $admin */
        $admin = User::factory()->admin()->create();
        /** @var User $sales */
        $sales = User::factory()->sales()->create();
        $customer = Customer::factory()->approved()->create([
            'assigned_user_id' => $sales->id,
        ]);
        $lead = Lead::factory()->create([
            'assigned_user_id' => $sales->id,
        ]);

        $this->actingAs($admin)
            ->post(route('activities.store'), [
                'customer_id' => $customer->id,
                'lead_id' => $lead->id,
                'activity_type' => 'call',
                'description' => 'Talked about timeline',
                'activity_date' => now()->toDateString(),
            ])
            ->assertSessionHasErrors(['customer_id', 'lead_id']);
    }

    public function test_manager_reports_are_scoped_to_sales_team_activity_data(): void
    {
        /** @var User $manager */
        $manager = User::factory()->manager()->create();
        /** @var User $admin */
        $admin = User::factory()->admin()->create();
        /** @var User $sales */
        $sales = User::factory()->sales()->create();

        Activity::query()->create([
            'user_id' => $admin->id,
            'activity_type' => 'note',
            'description' => 'Admin maintenance activity',
            'activity_date' => now()->toDateString(),
        ]);

        Activity::query()->create([
            'user_id' => $sales->id,
            'activity_type' => 'call',
            'description' => 'Sales follow-up call',
            'activity_date' => now()->toDateString(),
        ]);

        $this->actingAs($manager)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertViewHas('data', function (array $data): bool {
                $roles = collect($data['userActivity'])->pluck('role')->unique();

                return $roles->count() > 0 && $roles->every(fn (string $role): bool => $role === 'sales');
            });
    }
}
