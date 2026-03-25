<?php

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

uses(RefreshDatabase::class);

test('users can add a customer with assignment', function () {
    /** @var User $admin */
    $admin = User::factory()->admin()->createOne();
    /** @var User $sales */
    $sales = User::factory()->sales()->createOne();

    actingAs($admin);

    post(route('customers.store'), [
        'first_name' => 'Jordan',
        'last_name' => 'Miles',
        'email' => 'jordan@example.com',
        'phone' => '09123456789',
        'company' => 'NexLink',
        'address' => 'Makati City',
        'status' => 'active',
        'assigned_user_id' => $sales->id,
    ])->assertRedirect(route('customers.index'));

    $customer = Customer::query()->first();

    expect($customer)->not->toBeNull()
        ->and($customer?->assigned_user_id)->toBe($sales->id)
        ->and($customer?->company)->toBe('NexLink');
});

test('users can edit a customer', function () {
    /** @var User $admin */
    $admin = User::factory()->admin()->createOne();
    /** @var User $sales */
    $sales = User::factory()->sales()->createOne();
    $customer = Customer::factory()->createOne([
        'first_name' => 'Taylor',
        'last_name' => 'Brooks',
        'assigned_user_id' => $sales->id,
    ]);

    actingAs($admin);

    put(route('customers.update', $customer), [
        'first_name' => 'Taylor Updated',
        'last_name' => 'Brooks',
        'email' => $customer->email,
        'phone' => $customer->phone,
        'company' => $customer->company,
        'address' => $customer->address,
        'status' => 'inactive',
        'assigned_user_id' => $sales->id,
    ])->assertRedirect(route('customers.index'));

    expect($customer->fresh()->first_name)->toBe('Taylor Updated')
        ->and($customer->fresh()->status)->toBe('inactive');
});

test('users can view customer details page', function () {
    /** @var User $manager */
    $manager = User::factory()->manager()->createOne();
    $customer = Customer::factory()->createOne();

    actingAs($manager);

    get(route('customers.show', $customer))
        ->assertOk()
        ->assertSee((string) $customer->id)
        ->assertSee($customer->first_name)
        ->assertSee($customer->last_name);
});

test('users can search and filter customers', function () {
    /** @var User $manager */
    $manager = User::factory()->manager()->createOne();
    /** @var User $sales */
    $sales = User::factory()->sales()->createOne();

    Customer::factory()->createOne([
        'first_name' => 'Searchable',
        'last_name' => 'Customer',
        'status' => 'active',
        'assigned_user_id' => $sales->id,
    ]);

    Customer::factory()->createOne([
        'first_name' => 'Hidden',
        'last_name' => 'Record',
        'status' => 'inactive',
        'assigned_user_id' => $sales->id,
    ]);

    actingAs($manager);

    get(route('customers.index', [
        'search' => 'Searchable',
        'status' => 'active',
        'assigned_user_id' => $sales->id,
    ]))
        ->assertOk()
        ->assertSee('Searchable')
        ->assertDontSee('Hidden');
});

test('users can delete customers', function () {
    /** @var User $admin */
    $admin = User::factory()->admin()->createOne();
    $customer = Customer::factory()->createOne();

    actingAs($admin);

    delete(route('customers.destroy', $customer))
        ->assertRedirect(route('customers.index'));

    expect(Customer::query()->find($customer->id))->toBeNull();
});

test('sales cannot delete customers', function () {
    /** @var User $sales */
    $sales = User::factory()->sales()->createOne();
    $customer = Customer::factory()->createOne([
        'assigned_user_id' => $sales->id,
    ]);

    actingAs($sales);

    delete(route('customers.destroy', $customer))
        ->assertForbidden();

    expect(Customer::query()->find($customer->id))->not->toBeNull();
});

test('manager cannot access customer create page', function () {
    /** @var User $manager */
    $manager = User::factory()->manager()->createOne();

    actingAs($manager);

    get(route('customers.create'))
        ->assertForbidden();
});

test('manager customer index does not show add customer button', function () {
    /** @var User $manager */
    $manager = User::factory()->manager()->createOne();

    actingAs($manager);

    get(route('customers.index'))
        ->assertOk()
        ->assertDontSee('Add Customer');
});

test('customer assignment must target sales staff only', function () {
    /** @var User $admin */
    $admin = User::factory()->admin()->createOne();
    /** @var User $manager */
    $manager = User::factory()->manager()->createOne();

    actingAs($admin);

    post(route('customers.store'), [
        'first_name' => 'Invalid',
        'last_name' => 'Assignment',
        'email' => 'invalid-assignment@example.com',
        'phone' => '09123456780',
        'status' => 'active',
        'assigned_user_id' => $manager->id,
    ])->assertSessionHasErrors('assigned_user_id');
});

test('sales customer creation is always assigned to current sales user', function () {
    /** @var User $sales */
    $sales = User::factory()->sales()->createOne();
    /** @var User $otherSales */
    $otherSales = User::factory()->sales()->createOne();

    actingAs($sales);

    post(route('customers.store'), [
        'first_name' => 'Owned',
        'last_name' => 'BySales',
        'email' => 'owned-by-sales@example.com',
        'phone' => '09123456711',
        'status' => 'active',
        'assigned_user_id' => $otherSales->id,
    ])->assertRedirect(route('customers.index'));

    $customer = Customer::query()->where('email', 'owned-by-sales@example.com')->first();

    expect($customer)->not->toBeNull()
        ->and($customer?->assigned_user_id)->toBe($sales->id);
});

test('sales only sees assigned customers in list', function () {
    /** @var User $sales */
    $sales = User::factory()->sales()->createOne();
    /** @var User $otherSales */
    $otherSales = User::factory()->sales()->createOne();

    Customer::factory()->createOne([
        'first_name' => 'Mine',
        'assigned_user_id' => $sales->id,
        'assignment_status' => 'approved',
    ]);

    Customer::factory()->createOne([
        'first_name' => 'NotMine',
        'assigned_user_id' => $otherSales->id,
        'assignment_status' => 'approved',
    ]);

    actingAs($sales);

    get(route('customers.index'))
        ->assertOk()
        ->assertSee('Mine')
        ->assertDontSee('NotMine');
});

test('sales does not see newly pending assigned customer until manager approval', function () {
    /** @var User $sales */
    $sales = User::factory()->sales()->createOne();

    $pendingCustomer = Customer::factory()->createOne([
        'first_name' => 'PendingCustomer',
        'assigned_user_id' => $sales->id,
        'assignment_status' => 'pending',
    ]);

    actingAs($sales);

    get(route('customers.index'))
        ->assertOk()
        ->assertDontSee('PendingCustomer');

    // Ensure record still exists and only visibility is restricted pending approval.
    expect(Customer::query()->find($pendingCustomer->id))->not->toBeNull();
});

test('sales cannot access customers assigned to other sales users', function () {
    /** @var User $sales */
    $sales = User::factory()->sales()->createOne();
    /** @var User $otherSales */
    $otherSales = User::factory()->sales()->createOne();

    $foreignCustomer = Customer::factory()->createOne([
        'assigned_user_id' => $otherSales->id,
    ]);

    actingAs($sales);

    get(route('customers.show', $foreignCustomer))->assertForbidden();
});

test('manager can reassign customer to another sales staff', function () {
    /** @var User $manager */
    $manager = User::factory()->manager()->createOne();
    /** @var User $salesA */
    $salesA = User::factory()->sales()->createOne();
    /** @var User $salesB */
    $salesB = User::factory()->sales()->createOne();

    $customer = Customer::factory()->createOne([
        'assigned_user_id' => $salesA->id,
        'assignment_status' => 'approved',
        'assignment_reviewed_by' => $manager->id,
        'assignment_reviewed_at' => now(),
    ]);

    actingAs($manager);

    patch(route('customers.reassign', $customer), [
        'assigned_user_id' => $salesB->id,
    ])->assertRedirect(route('customers.show', $customer));

    $customer->refresh();

    expect($customer->assigned_user_id)->toBe($salesB->id)
        ->and($customer->assignment_status)->toBe('pending')
        ->and($customer->assignment_reviewed_by)->toBeNull();
});
