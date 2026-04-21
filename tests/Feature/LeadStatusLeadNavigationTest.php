<?php

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('links lead statuses in the table view to the lead kanban card anchor', function () {
    $admin = User::factory()->admin()->create();
    $lead = Lead::factory()->create([
        'status' => 'contacted',
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('leads.index'));

    $response
        ->assertOk()
        ->assertSee($lead->name)
        ->assertSee(route('leads.kanban').'#lead-kanban-card-'.$lead->id, false);
});

it('renders a stable anchor id for each kanban lead card', function () {
    $admin = User::factory()->admin()->create();
    $lead = Lead::factory()->create([
        'status' => 'qualified',
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('leads.kanban'));

    $response
        ->assertOk()
        ->assertSee('id="lead-kanban-card-'.$lead->id.'"', false);
});
