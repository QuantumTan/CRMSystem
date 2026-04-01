<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can access user management screens', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('users.index'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('users.create'))
        ->assertOk();
});

test('manager is blocked from all user management actions', function () {
    $manager = User::factory()->manager()->create();
    $targetUser = User::factory()->sales()->create();

    $this->actingAs($manager)
        ->get(route('users.index'))
        ->assertForbidden();

    $this->actingAs($manager)
        ->get(route('users.create'))
        ->assertForbidden();

    $this->actingAs($manager)
        ->post(route('users.store'), [
            'name' => 'Blocked Manager Create',
            'email' => 'manager.create@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'sales',
        ])
        ->assertForbidden();

    $this->actingAs($manager)
        ->get(route('users.edit', $targetUser))
        ->assertForbidden();

    $this->actingAs($manager)
        ->put(route('users.update', $targetUser), [
            'name' => 'Blocked Update',
            'email' => 'blocked.update@example.com',
            'role' => 'sales',
        ])
        ->assertForbidden();

    $this->actingAs($manager)
        ->delete(route('users.destroy', $targetUser))
        ->assertForbidden();
});

test('sales is blocked from all user management actions', function () {
    $sales = User::factory()->sales()->create();
    $targetUser = User::factory()->manager()->create();

    $this->actingAs($sales)
        ->get(route('users.index'))
        ->assertForbidden();

    $this->actingAs($sales)
        ->get(route('users.create'))
        ->assertForbidden();

    $this->actingAs($sales)
        ->post(route('users.store'), [
            'name' => 'Blocked Sales Create',
            'email' => 'sales.create@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'manager',
        ])
        ->assertForbidden();

    $this->actingAs($sales)
        ->get(route('users.edit', $targetUser))
        ->assertForbidden();

    $this->actingAs($sales)
        ->put(route('users.update', $targetUser), [
            'name' => 'Blocked Sales Update',
            'email' => 'sales.blocked.update@example.com',
            'role' => 'manager',
        ])
        ->assertForbidden();

    $this->actingAs($sales)
        ->delete(route('users.destroy', $targetUser))
        ->assertForbidden();
});
