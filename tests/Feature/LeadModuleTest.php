<?php

use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

test('sales users can add a lead with expected value', function () {
    $user = User::factory()->sales()->createOne();

    actingAs($user);

    post(route('leads.store'), [
        'name' => 'Cameron Blake',
        'email' => 'cameron@example.com',
        'phone' => '0999999999',
        'source' => 'Website',
        'status' => 'new',
        'priority' => 'high',
        'expected_value' => 180000.50,
        'notes' => 'Strong fit for premium package.',
        'assigned_user_id' => $user->id,
    ])->assertRedirect(route('leads.index'));

    $lead = Lead::query()->first();

    expect($lead)->not->toBeNull()
        ->and($lead?->name)->toBe('Cameron Blake')
        ->and((float) $lead?->expected_value)->toBe(180000.50);
});

test('users can update lead status', function () {
    $user = User::factory()->sales()->createOne();

    $lead = Lead::query()->create([
        'name' => 'Taylor Rivers',
        'status' => 'new',
        'priority' => 'medium',
        'assigned_user_id' => $user->id,
    ]);

    actingAs($user);

    patch(route('leads.update-status', $lead), [
        'status' => 'qualified',
    ])->assertRedirect(route('leads.index'));

    expect($lead->fresh()->status)->toBe('qualified');
});

test('users can assign and prioritize leads', function () {
    $admin = User::factory()->admin()->createOne();
    $sales = User::factory()->sales()->createOne();

    $lead = Lead::query()->create([
        'name' => 'Jordan Miles',
        'status' => 'contacted',
        'priority' => 'low',
    ]);

    actingAs($admin);

    patch(route('leads.assign', $lead), [
        'assigned_user_id' => $sales->id,
    ])->assertRedirect(route('leads.index'));

    patch(route('leads.set-priority', $lead), [
        'priority' => 'high',
    ])->assertRedirect(route('leads.index'));

    $lead->refresh();

    expect($lead->assigned_user_id)->toBe($sales->id)
        ->and($lead->priority)->toBe('high');
});

test('users can convert a lead to customer', function () {
    $user = User::factory()->manager()->createOne();

    $lead = Lead::query()->create([
        'name' => 'Morgan Lee',
        'email' => 'morgan@example.com',
        'phone' => '0911222333',
        'status' => 'negotiation',
        'priority' => 'high',
        'assigned_user_id' => $user->id,
    ]);

    actingAs($user);

    patch(route('leads.convert', $lead))
        ->assertRedirect(route('leads.show', $lead));

    $lead->refresh();

    $customer = Customer::query()->find($lead->customer_id);

    expect($lead->customer_id)->not->toBeNull()
        ->and($lead->status)->toBe('won')
        ->and($customer)->not->toBeNull()
        ->and($customer?->email)->toBe('morgan@example.com');
});
