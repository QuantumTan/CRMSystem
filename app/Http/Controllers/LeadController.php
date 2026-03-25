<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeadController extends Controller
{
    private const STATUS_OPTIONS = [
        'new',
        'contacted',
        'qualified',
        'proposal_sent',
        'negotiation',
        'won',
        'lost',
    ];

    private const PRIORITY_OPTIONS = [
        'low',
        'medium',
        'high',
    ];

    public function index(Request $request): View
    {
        $leadQuery = Lead::query()->with(['assignedUser', 'customer'])->latest();

        if ($request->filled('search')) {
            $search = (string) $request->string('search');

            $leadQuery->where(function ($query) use ($search): void {
                $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('source', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $leadQuery->where('status', (string) $request->string('status'));
        }

        if ($request->filled('priority')) {
            $leadQuery->where('priority', (string) $request->string('priority'));
        }

        $leads = $leadQuery->paginate(10)->withQueryString();

        return view('leads.index', [
            'leads' => $leads,
            'statusOptions' => self::STATUS_OPTIONS,
            'priorityOptions' => self::PRIORITY_OPTIONS,
            'assignableUsers' => $this->assignableUsers(),
        ]);
    }

    public function create(): View
    {
        return view('leads.create', [
            'statusOptions' => self::STATUS_OPTIONS,
            'priorityOptions' => self::PRIORITY_OPTIONS,
            'assignableUsers' => $this->assignableUsers(),
            'customers' => Customer::query()->latest()->get(),
        ]);
    }

    public function store(StoreLeadRequest $request): RedirectResponse
    {
        $payload = $request->validated();

        if ($request->user()?->hasRole('sales') && empty($payload['assigned_user_id'])) {
            $payload['assigned_user_id'] = $request->user()?->id;
        }

        Lead::create($payload);

        return redirect()->route('leads.index')->with('success', 'Lead created successfully.');
    }

    public function show(Lead $lead): View
    {
        $lead->load(['assignedUser', 'customer']);

        return view('leads.show', compact('lead'));
    }

    public function edit(Lead $lead): View
    {
        return view('leads.edit', [
            'lead' => $lead,
            'statusOptions' => self::STATUS_OPTIONS,
            'priorityOptions' => self::PRIORITY_OPTIONS,
            'assignableUsers' => $this->assignableUsers(),
            'customers' => Customer::query()->latest()->get(),
        ]);
    }

    public function update(UpdateLeadRequest $request, Lead $lead): RedirectResponse
    {
        $lead->update($request->validated());

        return redirect()->route('leads.index')->with('success', 'Lead updated successfully.');
    }

    public function updateStatus(Request $request, Lead $lead): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', self::STATUS_OPTIONS)],
        ]);

        $lead->update([
            'status' => $data['status'],
        ]);

        return redirect()->route('leads.index')->with('success', 'Lead status updated.');
    }

    public function assign(Request $request, Lead $lead): RedirectResponse
    {
        $data = $request->validate([
            'assigned_user_id' => ['nullable', 'exists:users,id'],
        ]);

        $lead->update([
            'assigned_user_id' => $data['assigned_user_id'] ?? null,
        ]);

        return redirect()->route('leads.index')->with('success', 'Lead assignee updated.');
    }

    public function setPriority(Request $request, Lead $lead): RedirectResponse
    {
        $data = $request->validate([
            'priority' => ['required', 'in:'.implode(',', self::PRIORITY_OPTIONS)],
        ]);

        $lead->update([
            'priority' => $data['priority'],
        ]);

        return redirect()->route('leads.index')->with('success', 'Lead priority updated.');
    }

    public function convert(Lead $lead): RedirectResponse
    {
        if ($lead->customer_id !== null) {
            return redirect()->route('leads.show', $lead)->with('success', 'Lead is already linked to a customer.');
        }

        $nameParts = preg_split('/\s+/', trim($lead->name));
        $firstName = $nameParts[0] ?? 'Prospect';
        $lastName = trim(implode(' ', array_slice($nameParts, 1)));
        if ($lastName === '') {
            $lastName = '-';
        }

        $fallbackEmail = 'lead-'.$lead->id.'@nexlink.local';
        $email = $lead->email ?: $fallbackEmail;

        $customer = Customer::query()->firstOrCreate(
            ['email' => $email],
            [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $lead->phone ?: 'N/A',
                'status' => 'active',
                'assigned_user_id' => $lead->assigned_user_id,
            ]
        );

        $lead->update([
            'customer_id' => $customer->id,
            'status' => 'won',
        ]);

        return redirect()->route('leads.show', $lead)->with('success', 'Lead converted to customer successfully.');
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        $lead->delete();

        return redirect()->route('leads.index')->with('success', 'Lead deleted successfully.');
    }

    private function assignableUsers()
    {
        return User::query()->whereIn('role', ['admin', 'manager', 'sales'])->orderBy('name')->get();
    }
}
