<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller for managing Leads within the CRM.
 * Handles the complete lifecycle of a lead from creation to customer conversion.
 */
class LeadController extends Controller
{
    /**
     * Standard status workflow for a lead.
     */
    private const STATUS_OPTIONS = [
        'new',
        'contacted',
        'qualified',
        'proposal_sent',
        'negotiation',
        'won',
        'lost',
    ];

    /**
     * Priority levels for lead triage.
     */
    private const PRIORITY_OPTIONS = [
        'low',
        'medium',
        'high',
        'critical'
    ];

    /**
     * Display the drag-and-drop Kanban board view for leads.
     * Groups leads by their current status for visual pipeline management.
     *
     * @param Request $request
     * @return View
     */
    public function kanban(Request $request): View
    {
        $this->authorizeAccess($request, allowManager: true);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'assigned_user' => ['nullable', 'exists:users,id'],
        ]);

        $leadQuery = Lead::query()->with(['assignedUser', 'customer']);

        if (!empty($filters['search'])) {
            $search = $this->escapeLike((string) $filters['search']);

            $leadQuery->where(function (Builder $query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['assigned_user'])) {
            $leadQuery->where('assigned_user_id', $filters['assigned_user']);
        }

        $allLeads = $leadQuery->get();

        $leadsByStatus = [];
        foreach (self::STATUS_OPTIONS as $status) {
            $leadsByStatus[$status] = $allLeads->where('status', $status)->values();
        }

        return view('leads.kanban', [
            'statuses' => self::STATUS_OPTIONS,
            'leadsByStatus' => $leadsByStatus,
            'users' => $this->assignableUsers(),
        ]);
    }

    /**
     * Display a paginated list of leads with advanced filtering options.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $this->authorizeAccess($request, allowManager: true);

        $leads = Lead::with('assignedUser')
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($q) use ($request) {
                    $q->where('name',  'like', "%{$request->search}%")
                        ->orWhere('email', 'like', "%{$request->search}%")
                        ->orWhere('phone', 'like', "%{$request->search}%");
                });
            })
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->priority, fn($q) => $q->where('priority', $request->priority))
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

    /**
     * Show the form for creating a new lead.
     *
     * @param Request $request
     * @return View
     */
    public function create(Request $request): View
    {
        $this->authorizeAccess($request);

        return view('leads.create', [
            'statusOptions' => self::STATUS_OPTIONS,
            'priorityOptions' => self::PRIORITY_OPTIONS,
            'assignableUsers' => $this->assignableUsers(),
            'customers' => Customer::query()->latest()->get(),
        ]);
    }

    /**
     * Store a newly created lead in the database.
     * Automatically assigns the lead to the authenticated user if they are in sales
     * and left the assignment blank.
     *
     * @param StoreLeadRequest $request
     * @return RedirectResponse
     */
    public function store(StoreLeadRequest $request): RedirectResponse
    {
        $this->authorizeAccess($request);

        $payload = $request->validated();

        if ($request->user()?->hasRole('sales') && empty($payload['assigned_user_id'])) {
            $payload['assigned_user_id'] = $request->user()?->id;
        }

        Lead::create($payload);

        return redirect()->route('leads.index')->with('success', 'Lead created successfully.');
    }

    /**
     * Display the detailed profile view of a specific lead.
     *
     * @param Request $request
     * @param Lead $lead
     * @return View
     */
    public function show(Request $request, Lead $lead): View
    {
        $this->authorizeAccess($request, allowManager: true);

        $lead->load(['assignedUser', 'customer']);

        return view('leads.show', compact('lead'));
    }

    /**
     * Show the form for editing the specified lead.
     *
     * @param Request $request
     * @param Lead $lead
     * @return View
     */
    public function edit(Request $request, Lead $lead): View
    {
        $this->authorizeAccess($request);

        return view('leads.edit', [
            'lead' => $lead,
            'statusOptions' => self::STATUS_OPTIONS,
            'priorityOptions' => self::PRIORITY_OPTIONS,
            'assignableUsers' => $this->assignableUsers(),
            'customers' => Customer::query()->latest()->get(),
        ]);
    }

    /**
     * Update the specified lead in the database.
     *
     * @param UpdateLeadRequest $request
     * @param Lead $lead
     * @return RedirectResponse
     */
    public function update(UpdateLeadRequest $request, Lead $lead): RedirectResponse
    {
        $this->authorizeAccess($request);

        $lead->update($request->validated());

        return redirect()->route('leads.index')->with('success', 'Lead updated successfully.');
    }

    /**
     * Update only the status of a specific lead.
     * Designed to support both asynchronous JSON requests (e.g., Kanban drag-and-drop) 
     * and standard HTTP form submissions.
     *
     * @param Request $request
     * @param Lead $lead
     * @return JsonResponse|RedirectResponse
     */
    public function updateStatus(Request $request, Lead $lead): JsonResponse|RedirectResponse
    {
        $this->authorizeAccess($request);

        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', self::STATUS_OPTIONS)],
        ]);

        $oldStatus = $lead->status;
        $lead->update([
            'status' => $data['status'],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Lead moved from {$oldStatus} to {$data['status']}",
                'lead' => $lead,
                'old_status' => $oldStatus,
                'new_status' => $data['status']
            ]);
        }

        return redirect()->route('leads.index')->with('success', 'Lead status updated.');
    }

    /**
     * Update the user assigned to the specified lead.
     *
     * @param Request $request
     * @param Lead $lead
     * @return RedirectResponse
     */
    public function assign(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorizeAccess($request);

        $data = $request->validate([
            'assigned_user_id' => ['nullable', 'exists:users,id'],
        ]);

        $lead->update([
            'assigned_user_id' => $data['assigned_user_id'] ?? null,
        ]);

        return redirect()->route('leads.index')->with('success', 'Lead assignee updated.');
    }

    /**
     * Update the priority level of the specified lead.
     *
     * @param Request $request
     * @param Lead $lead
     * @return RedirectResponse
     */
    public function setPriority(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorizeAccess($request);

        $data = $request->validate([
            'priority' => ['required', 'in:' . implode(',', self::PRIORITY_OPTIONS)],
        ]);

        $lead->update([
            'priority' => $data['priority'],
        ]);

        return redirect()->route('leads.index')->with('success', 'Lead priority updated.');
    }

    /**
     * Convert a successfully negotiated Lead into an active Customer record.
     * Generates fallback data for missing critical customer fields (like last name or email)
     * to ensure the database constraints are met.
     *
     * @param Request $request
     * @param Lead $lead
     * @return RedirectResponse
     */
    public function convert(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorizeAccess($request);

        if ($lead->customer_id !== null) {
            return redirect()->route('leads.show', $lead)->with('success', 'Lead is already linked to a customer.');
        }

        // Split full name into first and last name for the Customer model
        $nameParts = preg_split('/\s+/', trim($lead->name));
        $firstName = $nameParts[0] ?? 'Prospect';
        $lastName = trim(implode(' ', array_slice($nameParts, 1)));
        
        if ($lastName === '') {
            $lastName = '-';
        }

        $email = $lead->email ?: "lead-{$lead->id}@nexlink.local";

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

    /**
     * Remove the specified lead from the database.
     * Supports both JSON responses for AJAX deletion and standard HTTP redirects.
     *
     * @param Request $request
     * @param Lead $lead
     * @return RedirectResponse|JsonResponse
     */
    public function destroy(Request $request, Lead $lead): RedirectResponse|JsonResponse
    {
        $this->authorizeAccess($request);

        $lead->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Lead deleted successfully'
            ]);
        }

        return redirect()->route('leads.index')->with('success', 'Lead deleted successfully.');
    }

    /**
     * Centralized authorization check for lead actions.
     * Prevents unauthorized users from accessing or modifying lead data.
     *
     * @param Request $request
     * @param bool $allowManager Whether users with the 'manager' role bypass the check (read-only views)
     * @return void
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    private function authorizeAccess(Request $request, bool $allowManager = false): void
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Unauthorized.');
        }

        if ($allowManager && $user->hasRole('manager')) {
            return;
        }

        if (!$user->hasAnyRole('admin', 'sales')) {
            abort(403, 'Unauthorized.');
        }
    }

    /**
     * Retrieve a collection of users who are eligible to be assigned to leads.
     *
     * @return Collection
     */
    private function assignableUsers(): Collection
    {
        return User::query()
            ->whereIn('role', ['admin', 'manager', 'sales'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Escape special characters in a string to safely use it in an SQL LIKE query.
     *
     * @param string $value The raw search string
     * @return string The safely escaped string
     */
    private function escapeLike(string $value): string
    {
        return addcslashes($value, '\\%_');
    }
}