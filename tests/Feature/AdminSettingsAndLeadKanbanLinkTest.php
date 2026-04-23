<?php

use App\Models\Lead;
use App\Models\SystemConfiguration;
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

it('renders lead status badges as links to the matching kanban card', function () {
    $admin = User::factory()->admin()->create();

    $lead = Lead::factory()->create([
        'status' => 'proposal_sent',
        'assigned_user_id' => $admin->id,
    ]);

    $expectedLink = route('leads.kanban').'#lead-kanban-card-'.$lead->id;

    $this->actingAs($admin)
        ->get(route('leads.index'))
        ->assertOk()
        ->assertSee($expectedLink, false)
        ->assertSee('Proposal sent', false);
});

it('lets admins update system configuration values', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->put(route('settings.update'), [
            'app_name' => 'Atlas CRM',
            'company_email' => 'support@atlas.test',
            'company_phone' => '+63 912 000 1111',
            'company_address' => 'Makati City',
            'default_lead_status' => 'qualified',
            'default_lead_priority' => 'high',
            'currency_code' => 'USD',
            'password_reset_expire_minutes' => 120,
        ])
        ->assertRedirect(route('settings.index'));

    $configuration = SystemConfiguration::query()->first();

    expect($configuration)->not->toBeNull();
    expect($configuration->app_name)->toBe('Atlas CRM');
    expect($configuration->default_lead_status)->toBe('qualified');
    expect($configuration->default_lead_priority)->toBe('high');
    expect($configuration->currency_code)->toBe('USD');
    expect($configuration->password_reset_expire_minutes)->toBe(120);
});

it('uses configured lead defaults on the create lead page', function () {
    $admin = User::factory()->admin()->create();

    SystemConfiguration::query()->create([
        'app_name' => 'Atlas CRM',
        'default_lead_status' => 'qualified',
        'default_lead_priority' => 'high',
        'currency_code' => 'USD',
        'password_reset_expire_minutes' => 90,
    ] + SystemConfiguration::defaults());

    $this->actingAs($admin)
        ->get(route('leads.create'))
        ->assertOk()
        ->assertSee('value="qualified" selected', false)
        ->assertSee('value="high" selected', false)
        ->assertSee('<span class="input-group-text">USD</span>', false);
});

it('shows the configured password reset expiration on the forgot password page', function () {
    SystemConfiguration::query()->create([
        'app_name' => 'Atlas CRM',
        'password_reset_expire_minutes' => 180,
    ] + SystemConfiguration::defaults());

    $this->get(route('password.request'))
        ->assertOk()
        ->assertSee('Reset links expire in', false)
        ->assertSee('180 minutes', false);
});
