<?php

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders customer table delete actions as modal triggers for admins', function () {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create();

    $response = $this
        ->actingAs($admin)
        ->get(route('customers.index'));

    $response
        ->assertOk()
        ->assertSee('data-delete-modal-target="#deleteModal"', false)
        ->assertSee('data-delete-action="'.route('customers.destroy', $customer).'"', false)
        ->assertSee('deleteModal', false)
        ->assertDontSee('href="'.route('customers.index', ['delete' => $customer->id]).'"', false)
        ->assertDontSee('modal fade show', false)
        ->assertDontSee('<form method="POST" action="'.route('customers.destroy', $customer).'"', false);
});

it('renders the customer delete modal shell once on the table page', function () {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create();

    $response = $this
        ->actingAs($admin)
        ->get(route('customers.index'));

    $response
        ->assertOk()
        ->assertSee('id="deleteModal"', false)
        ->assertSee('data-delete-modal-form', false)
        ->assertSee('data-delete-modal-name', false)
        ->assertSee('name="_method" value="DELETE"', false)
        ->assertSee('Yes, Delete', false)
        ->assertDontSee('<form method="POST" action="'.route('customers.destroy', $customer).'"', false);
});

it('uses the same customer delete modal on the detail page', function () {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create();

    $this
        ->actingAs($admin)
        ->get(route('customers.show', $customer))
        ->assertOk()
        ->assertSee('data-delete-modal-target="#deleteModal"', false)
        ->assertSee('data-delete-action="'.route('customers.destroy', $customer).'"', false)
        ->assertSee('data-delete-modal-form', false)
        ->assertSee('name="_method" value="DELETE"', false)
        ->assertDontSee('href="'.route('customers.show', ['customer' => $customer, 'delete' => $customer->id]).'"', false)
        ->assertDontSee('modal fade show', false);
});

it('allows admins to delete customers', function () {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create();

    $this
        ->actingAs($admin)
        ->delete(route('customers.destroy', $customer))
        ->assertRedirect(route('customers.index'));

    $this->assertSoftDeleted('customers', [
        'id' => $customer->id,
    ]);
});

it('does not expose customer delete actions to sales users', function () {
    $sales = User::factory()->sales()->create();
    $customer = Customer::factory()
        ->approved()
        ->create(['assigned_user_id' => $sales->id]);

    $this
        ->actingAs($sales)
        ->get(route('customers.show', $customer))
        ->assertOk()
        ->assertDontSee('modal fade show', false)
        ->assertDontSee('data-delete-action="'.route('customers.destroy', $customer).'"', false);

    $this
        ->actingAs($sales)
        ->delete(route('customers.destroy', $customer))
        ->assertForbidden();
});
