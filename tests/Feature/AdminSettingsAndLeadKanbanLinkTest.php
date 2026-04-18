<?php

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows only admins to open the system configuration page', function () {
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->manager()->create();
    $sales = User::factory()->sales()->create();

    $this->actingAs($admin)
        ->get(route('settings.index'))
        ->assertOk()
        ->assertSee('System Configuration');

    $this->actingAs($manager)
        ->get(route('settings.index'))
        ->assertForbidden();

    $this->actingAs($sales)
        ->get(route('settings.index'))
        ->assertForbidden();
});

it('renders lead status badges as links to the matching kanban column', function () {
    $admin = User::factory()->admin()->create();

    $lead = Lead::factory()->create([
        'status' => 'proposal_sent',
        'assigned_user_id' => $admin->id,
    ]);

    $expectedLink = route('leads.kanban', ['status' => $lead->status]).'#status-proposal-sent';

    $this->actingAs($admin)
        ->get(route('leads.index'))
        ->assertOk()
        ->assertSee($expectedLink, false)
        ->assertSee('Proposal sent', false);
});
