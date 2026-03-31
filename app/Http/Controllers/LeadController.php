<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
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
        'critical'
    ];

    /**
     * Display the Kanban board view
     */
    public function kanban(Request $request): View
    {
        $user = $request->user();
        if ($user && $user->hasRole('manager')) {
            // Managers can only view
        } elseif ($user && !$user->hasAnyRole('admin', 'sales')) {
            abort(403, 'Unauthorized.');
        }
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'assigned_user' => ['nullable', 'exists:users,id'],
        ]);

        $leadQuery = Lead::query()->with(['assignedUser', 'customer']);

        if (!empty($filters['search'])) {
            $search = $this->escapeLike((string) $filters['search']);

            $leadQuery->where(function ($query) use ($search): void {
                $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['assigned_user'])) {
            $leadQuery->where('assigned_user_id', $filters['assigned_user']);
        }

        $allLeads = $leadQuery->get();

        // Organize leads by status
        $leadsByStatus = [];
        foreach (self::STATUS_OPTIONS as $status) {
            $leadsByStatus[$status] = $allLeads->filter(function ($lead) use ($status) {
                return $lead->status === $status;
            })->values();
        }

        return view('leads.kanban', [
            'statuses' => self::STATUS_OPTIONS,
            'leadsByStatus' => $leadsByStatus,
            'users' => $this->assignableUsers(),
        ]);
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        if ($user && $user->hasRole('manager')) {
            // Managers can only view
        } elseif ($user && !$user->hasAnyRole('admin', 'sales')) {
            abort(403, 'Unauthorized.');
        }
        // $filters = $request->validate([
        //     'search' => ['nullable', 'string', 'max:100'],
        //     'status' => ['nullable', 'in:' . implode(',', self::STATUS_OPTIONS)],
        //     'priority' => ['nullable', 'in:' . implode(',', self::PRIORITY_OPTIONS)],
        // ]);

        // $leadQuery = Lead::query()->with(['assignedUser', 'customer'])->latest();

        // if (! empty($filters['search'])) {
        //     $search = $this->escapeLike((string) $filters['search']);

        //     $leadQuery->where(function ($query) use ($search): void {
        //         $query
        //             ->where('name', 'like', "%{$search}%")
        //             ->orWhere('email', 'like', "%{$search}%")
        //             ->orWhere('phone', 'like', "%{$search}%")
        //             ->orWhere('source', 'like', "%{$search}%");
        //     });
        // }

        // if (! empty($filters['status'])) {
        //     $leadQuery->where('status', (string) $filters['status']);
        // }

        // if (! empty($filters['priority'])) {
        //     $leadQuery->where('priority', (string) $filters['priority']);
        // }

        // $leads = $leadQuery->paginate(10)->withQueryString();

        $leads = Lead::with('assignedUser')
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($q) use ($request) {
                    $q->where('name',  'like', "%{$request->search}%")
                        ->orWhere('email', 'like', "%{$request->search}%")
                        ->orWhere('phone', 'like', "%{$request->search}%");
                });
            })
            ->when($request->status, fn($q) => $q->where('status',$request->status))
            ->when($request->priority,fn($q) => $q->where('priority', $request->priority))
            ->when($request->assigned_user, fn($q) => $q->where('assigned_user_id', $request->assigned_user))
            ->latest()
            ->paginate(15);

        return view('leads.index', [
            'leads' => $leads,
            'statusOptions' => self::STATUS_OPTIONS,
            'priorityOptions' => self::PRIORITY_OPTIONS,
            'assignableUsers' => $this->assignableUsers(),
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        if (!$user || !$user->hasAnyRole('admin', 'sales')) {
            abort(403, 'Unauthorized.');
        }
        return view('leads.create', [
            'statusOptions' => self::STATUS_OPTIONS,
            'priorityOptions' => self::PRIORITY_OPTIONS,
            'assignableUsers' => $this->assignableUsers(),
            'customers' => Customer::query()->latest()->get(),
        ]);
    }

    public function store(StoreLeadRequest $request): RedirectResponse
    {
        $user = $request->user();
        if (!$user || !$user->hasAnyRole('admin', 'sales')) {
            abort(403, 'Unauthorized.');
        }
        $payload = $request->validated();

        if ($request->user()?->hasRole('sales') && empty($payload['assigned_user_id'])) {
            $payload['assigned_user_id'] = $request->user()?->id;
        }

        Lead::create($payload);

        return redirect()->route('leads.index')->with('success', 'Lead created successfully.');
    }

    public function show(Request $request, Lead $lead): View
    {
        $user = $request->user();
        if ($user && $user->hasRole('manager')) {
            // Managers can only view
        } elseif ($user && !$user->hasAnyRole('admin', 'sales')) {
            abort(403, 'Unauthorized.');
        }
        $lead->load(['assignedUser', 'customer']);

        return view('leads.show', compact('lead'));
    }

    public function edit(Request $request, Lead $lead): View
    {
        $user = $request->user();
        if (!$user || !$user->hasAnyRole('admin', 'sales')) {
            abort(403, 'Unauthorized.');
        }
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
        $user = $request->user();
        if (!$user || !$user->hasAnyRole('admin', 'sales')) {
            abort(403, 'Unauthorized.');
        }
        $lead->update($request->validated());

        return redirect()->route('leads.index')->with('success', 'Lead updated successfully.');
    }

    /**
     * Update lead status (handles both AJAX and form submissions)
     */
    public function updateStatus(Request $request, Lead $lead): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        if (!$user || !$user->hasAnyRole('admin', 'sales')) {
            abort(403, 'Unauthorized.');
        }
        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', self::STATUS_OPTIONS)],
        ]);

        $oldStatus = $lead->status;
        $lead->update([
            'status' => $data['status'],
        ]);

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Lead moved from {$oldStatus} to {$data['status']}",
                'lead' => $lead,
                'old_status' => $oldStatus,
                'new_status' => $data['status']
            ]);
        }

        // Return redirect for form submissions
        return redirect()->route('leads.index')->with('success', 'Lead status updated.');
    }

    public function assign(Request $request, Lead $lead): RedirectResponse
    {
        $user = $request->user();
        if (!$user || !$user->hasAnyRole('admin', 'sales')) {
            abort(403, 'Unauthorized.');
        }
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
        $user = $request->user();
        if (!$user || !$user->hasAnyRole('admin', 'sales')) {
            abort(403, 'Unauthorized.');
        }
        $data = $request->validate([
            'priority' => ['required', 'in:' . implode(',', self::PRIORITY_OPTIONS)],
        ]);

        $lead->update([
            'priority' => $data['priority'],
        ]);

        return redirect()->route('leads.index')->with('success', 'Lead priority updated.');
    }

    public function convert(Request $request, Lead $lead): RedirectResponse
    {
        $user = $request->user();
        if (!$user || !$user->hasAnyRole('admin', 'sales')) {
            abort(403, 'Unauthorized.');
        }
        if ($lead->customer_id !== null) {
            return redirect()->route('leads.show', $lead)->with('success', 'Lead is already linked to a customer.');
        }

        $nameParts = preg_split('/\s+/', trim($lead->name));
        $firstName = $nameParts[0] ?? 'Prospect';
        $lastName = trim(implode(' ', array_slice($nameParts, 1)));
        if ($lastName === '') {
            $lastName = '-';
        }

        $fallbackEmail = 'lead-' . $lead->id . '@nexlink.local';
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

    public function destroy(Request $request, Lead $lead): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->hasAnyRole('admin', 'sales')) {
            abort(403, 'Unauthorized.');
        }
        $lead->delete();

        // Return JSON for AJAX requests
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Lead deleted successfully'
            ]);
        }

        // Return redirect for form submissions
        return redirect()->route('leads.index')->with('success', 'Lead deleted successfully.');
    }

    private function assignableUsers()
    {
        return User::query()->whereIn('role', ['admin', 'manager', 'sales'])->orderBy('name')->get();
    }

    private function escapeLike(string $value): string
    {
        return addcslashes($value, '\\%_');
    }
}
