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

    private const PRIORITY_OPTIONS = [
        'low',
        'medium',
        'high',
    ];

    /**
     * Lost categories for reporting.
     */
    private const LOST_CATEGORIES = [
        'budget'        => 'Budget too high',
        'competitor'    => 'Chose competitor',
        'timing'        => 'Wrong timing',
        'not_interested'=> 'Not interested',
        'no_decision'   => 'No decision maker',
        'other'         => 'Other',
    ];

    /**
     * Display the drag-and-drop Kanban board view for leads.
     */
    public function kanban(Request $request): View
    {
        $this->authorizeAccess($request, allowManager: true);

        $filters = $request->validate([
            'search'        => ['nullable', 'string', 'max:100'],
            'assigned_user' => ['nullable', 'exists:users,id'],
        ]);

        $leadQuery = Lead::query()->with(['assignedUser', 'convertedToCustomer']);

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
            'statuses'      => self::STATUS_OPTIONS,
            'leadsByStatus' => $leadsByStatus,
            'users'         => $this->assignableUsers(),
        ]);
    }

    /**
     * Display a paginated list of leads with advanced filtering options.
     */
    public function index(Request $request): View
    {
        $this->authorizeAccess($request, allowManager: true);

        $leads = Lead::with('assignedUser', 'convertedToCustomer')
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search}%")
                        ->orWhere('email', 'like', "%{$request->search}%")
                        ->orWhere('phone', 'like', "%{$request->search}%");
                });
            })
            ->when($request->status,        fn($q) => $q->where('status', $request->status))
            ->when($request->priority,      fn($q) => $q->where('priority', $request->priority))
            ->when($request->assigned_user, fn($q) => $q->where('assigned_user_id', $request->assigned_user))
            ->latest()
            ->paginate(15);

        return view('leads.index', [
            'leads'           => $leads,
            'statusOptions'   => self::STATUS_OPTIONS,
            'priorityOptions' => self::PRIORITY_OPTIONS,
            'assignableUsers' => $this->assignableUsers(),
            'users'           => User::orderBy('name')->get(),
        ]);
    }

    /**
     * Show the form for creating a new lead.
     */
    public function create(Request $request): View
    {
        $this->authorizeAccess($request);

        return view('leads.create', [
            'statusOptions'   => self::STATUS_OPTIONS,
            'priorityOptions' => self::PRIORITY_OPTIONS,
            'assignableUsers' => $this->assignableUsers(),
        ]);
    }

    /**
     * Store a newly created lead in the database.
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
     */
    public function show(Request $request, Lead $lead): View
    {
        $this->authorizeAccess($request, allowManager: true);

        $lead->load(['assignedUser', 'convertedToCustomer']);

        return view('leads.show', compact('lead'));
    }

    /**
     * Show the form for editing the specified lead.
     */
    public function edit(Request $request, Lead $lead): View
    {
        $this->authorizeAccess($request);

        return view('leads.edit', [
            'lead'            => $lead,
            'statusOptions'   => self::STATUS_OPTIONS,
            'priorityOptions' => self::PRIORITY_OPTIONS,
            'assignableUsers' => $this->assignableUsers(),
        ]);
    }

    /**
     * Update the specified lead in the database.
     */
    public function update(UpdateLeadRequest $request, Lead $lead): RedirectResponse
    {
        $this->authorizeAccess($request);

        $lead->update($request->validated());

        return redirect()->route('leads.index')->with('success', 'Lead updated successfully.');
    }

    /**
     * Update only the status of a specific lead.
     * Supports JSON (Kanban drag-and-drop) and standard HTTP form submissions.
     * NOTE: "lost" status is never sent here from the Kanban — the JS redirects
     * directly to the lost-form before making any AJAX call.
     */
    public function updateStatus(Request $request, Lead $lead): JsonResponse|RedirectResponse
    {
        $this->authorizeAccess($request, allowManager: true);

        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', self::STATUS_OPTIONS)],
        ]);

        $oldStatus = $lead->status;
        $newStatus = $data['status'];

        // Handle special case: marking as WON
        if ($newStatus === 'won' && $oldStatus !== 'won') {
            $lead->update(['status' => 'won']);

            if ($request->expectsJson()) {
                return response()->json([
                    'success'     => true,
                    'message'     => 'Lead marked as won! Ready to convert?',
                    'lead'        => $lead,
                    'old_status'  => $oldStatus,
                    'new_status'  => $newStatus,
                    'can_convert' => true,
                ]);
            }

            return redirect()->route('leads.show', $lead)
                ->with('success', 'Lead marked as won! Click "Convert to Customer" when ready.');
        }

        // Normal status update
        $lead->update(['status' => $newStatus]);

        if ($request->expectsJson()) {
            return response()->json([
                'success'    => true,
                'message'    => "Lead moved from {$oldStatus} to {$newStatus}",
                'lead'       => $lead,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);
        }

        return redirect()->route('leads.index')->with('success', 'Lead status updated.');
    }

    /**
     * Show form to record why lead was lost.
     */
    public function showLostForm(Lead $lead): View
    {
        return view('leads.lost-form', [
            'lead'           => $lead,
            'lostCategories' => self::LOST_CATEGORIES,
        ]);
    }

    /**
     * Mark lead as lost with reason.
     */
    public function markAsLost(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorizeAccess($request);

        $data = $request->validate([
            'lost_reason'    => ['required', 'string', 'min:3', 'max:500'],
            'lost_category'  => ['required', 'in:' . implode(',', array_keys(self::LOST_CATEGORIES))],
        ]);

        $lead->markAsLost($data['lost_reason'], $data['lost_category']);

        return redirect()->route('leads.index')
            ->with('success', 'Lead marked as lost.');
    }

    /**
     * Reopen a lost lead.
     */
    public function reopen(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorizeAccess($request);

        if (!$lead->isLost()) {
            return redirect()->route('leads.show', $lead)
                ->with('error', 'Only lost leads can be reopened.');
        }

        $lead->reopen('contacted');

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Lead has been reopened and is now active.');
    }

    /**
     * Update the user assigned to the specified lead.
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
     */
    public function setPriority(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorizeAccess($request);

        $data = $request->validate([
            'priority' => ['required', 'in:' . implode(',', self::PRIORITY_OPTIONS)],
        ]);

        $lead->update(['priority' => $data['priority']]);

        return redirect()->route('leads.index')->with('success', 'Lead priority updated.');
    }

    /**
     * Convert a successfully negotiated Lead into an active Customer record.
     */
    public function convert(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorizeAccess($request);

        if ($lead->isConverted()) {
            return redirect()->route('leads.show', $lead)
                ->with('error', 'This lead has already been converted to a customer.');
        }

        if (!$lead->isWon()) {
            return redirect()->route('leads.show', $lead)
                ->with('error', 'Only leads with "Won" status can be converted to customers.');
        }

        try {
            $customer = $lead->convertToCustomer();

            return redirect()->route('customers.show', $customer)
                ->with('success', "Lead successfully converted to customer: {$customer->name}");
        } catch (\Exception $e) {
            return redirect()->route('leads.show', $lead)
                ->with('error', 'Conversion failed: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified lead from the database.
     */
    public function destroy(Request $request, Lead $lead): RedirectResponse|JsonResponse
    {
        $this->authorizeAccess($request);

        if ($lead->isConverted()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete leads that have been converted to customers.',
                ], 422);
            }

            return redirect()->route('leads.index')
                ->with('error', 'Cannot delete leads that have been converted to customers.');
        }

        $lead->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Lead deleted successfully',
            ]);
        }

        return redirect()->route('leads.index')->with('success', 'Lead deleted successfully.');
    }

    /**
     * Centralized authorization check for lead actions.
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
     * Retrieve users eligible to be assigned to leads.
     */
    private function assignableUsers(): Collection
    {
        return User::query()
            ->whereIn('role', ['admin', 'manager', 'sales'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Escape special characters for SQL LIKE queries.
     */
    private function escapeLike(string $value): string
    {
        return addcslashes($value, '\\%_');
    }
}