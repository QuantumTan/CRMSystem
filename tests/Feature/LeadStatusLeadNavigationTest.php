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

it('renders table delete actions as modal triggers', function () {
    $admin = User::factory()->admin()->create();
    $lead = Lead::factory()->create();

    $response = $this
        ->actingAs($admin)
        ->get(route('leads.index'));

    $response
        ->assertOk()
        ->assertSee('data-delete-modal-target="#deleteLeadModal"', false)
        ->assertSee('data-delete-action="'.route('leads.destroy', $lead).'"', false)
        ->assertSee('deleteLeadModal', false)
        ->assertDontSee('href="'.route('leads.index', ['delete' => $lead->id]).'"', false)
        ->assertDontSee('modal fade show', false)
        ->assertDontSee('<form method="POST" action="'.route('leads.destroy', $lead).'"', false)
        ->assertDontSee('data-delete-lead', false);
});

it('renders the lead delete modal shell on the table page', function () {
    $admin = User::factory()->admin()->create();
    $lead = Lead::factory()->create();

    $response = $this
        ->actingAs($admin)
        ->get(route('leads.index'));

    $response
        ->assertOk()
        ->assertSee('id="deleteLeadModal"', false)
        ->assertSee('data-delete-modal-form', false)
        ->assertSee('data-delete-modal-name', false)
        ->assertSee('name="_method" value="DELETE"', false)
        ->assertSee('Yes, Delete', false)
        ->assertDontSee('<form method="POST" action="'.route('leads.destroy', $lead).'"', false);
});

it('uses the same lead delete modal on kanban and detail pages', function () {
    $admin = User::factory()->admin()->create();
    $lead = Lead::factory()->create([
        'status' => 'qualified',
    ]);

    $this
        ->actingAs($admin)
        ->get(route('leads.kanban'))
        ->assertOk()
        ->assertSee('data-delete-modal-target="#deleteLeadModal"', false)
        ->assertSee('data-delete-action="'.route('leads.destroy', $lead).'"', false)
        ->assertSee('data-delete-modal-form', false)
        ->assertSee('name="_method" value="DELETE"', false);

    $this
        ->actingAs($admin)
        ->get(route('leads.show', $lead))
        ->assertOk()
        ->assertSee('data-delete-modal-target="#deleteLeadModal"', false)
        ->assertSee('data-delete-action="'.route('leads.destroy', $lead).'"', false)
        ->assertSee('data-delete-modal-form', false)
        ->assertSee('name="_method" value="DELETE"', false);
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
