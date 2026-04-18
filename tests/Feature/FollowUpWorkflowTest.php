<?php

use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('defaults new follow ups to pending status', function () {
    $admin = User::factory()->admin()->create();
    $lead = Lead::factory()->create([
        'assigned_user_id' => $admin->id,
    ]);

    $this->actingAs($admin)
        ->post(route('follow-ups.store'), [
            'lead_id' => $lead->id,
            'title' => 'Follow up on pricing',
            'description' => 'Discuss the latest quote.',
            'due_date' => now()->addDay()->toDateString(),
        ])
        ->assertRedirect(route('follow-ups.index'));

    $followUp = FollowUp::query()->first();

    expect($followUp)->not->toBeNull();
    expect($followUp->status)->toBe('pending');
    expect($followUp->lead_id)->toBe($lead->id);
});

it('requires a lead when creating a new follow up', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->from(route('follow-ups.create'))
        ->post(route('follow-ups.store'), [
            'title' => 'Missing lead',
            'due_date' => now()->addDay()->toDateString(),
        ])
        ->assertRedirect(route('follow-ups.create'))
        ->assertSessionHasErrors('lead_id');
});

it('renders the create follow up form with pending as the add default', function () {
    $admin = User::factory()->admin()->create();
    $lead = Lead::factory()->create([
        'assigned_user_id' => $admin->id,
    ]);

    $this->actingAs($admin)
        ->get(route('follow-ups.create'))
        ->assertOk()
        ->assertSee('Default Status: Pending')
        ->assertSee('name="status" value="pending"', false)
        ->assertSee($lead->name);
});

it('allows editing legacy customer linked follow ups without forcing a lead', function () {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create();

    $followUp = FollowUp::query()->create([
        'customer_id' => $customer->id,
        'lead_id' => null,
        'user_id' => $admin->id,
        'title' => 'Legacy customer follow-up',
        'description' => 'Created before the lead-only workflow.',
        'due_date' => now()->addDays(2)->toDateString(),
        'status' => 'pending',
    ]);

    $this->actingAs($admin)
        ->put(route('follow-ups.update', $followUp), [
            'title' => 'Legacy customer follow-up updated',
            'description' => 'Still editable without a lead.',
            'due_date' => now()->addDays(4)->toDateString(),
            'status' => 'pending',
            'user_id' => $admin->id,
        ])
        ->assertRedirect(route('follow-ups.index'));

    expect($followUp->fresh()->title)->toBe('Legacy customer follow-up updated');
    expect($followUp->fresh()->customer_id)->toBe($customer->id);
    expect($followUp->fresh()->lead_id)->toBeNull();
});
