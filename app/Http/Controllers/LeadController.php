<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Models\Lead;
use App\Services\LeadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeadController extends Controller
{
    public function __construct(private LeadService $service) {}

    public function kanban(Request $request): View
    {
        $this->authorize('viewAny', Lead::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'assigned_user' => ['nullable', 'exists:users,id'],
            'status' => ['nullable', 'string'],
            'priority' => ['nullable', 'string'],
        ]);

        $isSales = $request->user()?->hasRole('sales');
        $allLeads = $this->service->getFilteredQuery($filters, $isSales)->get();

        return view('leads.kanban', [
            'statuses' => LeadService::STATUS_OPTIONS,
            'leadsByStatus' => $this->service->groupByStatus($allLeads),
            'users' => $this->service->assignableUsers($request->user()),
        ]);
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Lead::class);

        $filters = $request->only(['search', 'status', 'priority', 'assigned_user']);
        $isSales = $request->user()?->hasRole('sales');

        $leads = $this->service->getFilteredQuery($filters, $isSales)->paginate(15);

        return view('leads.index', [
            'leads' => $leads,
            'statusOptions' => LeadService::STATUS_OPTIONS,
            'priorityOptions' => LeadService::PRIORITY_OPTIONS,
            'assignableUsers' => $this->service->assignableUsers($request->user()),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Lead::class);

        return view('leads.create', [
            'statusOptions' => LeadService::STATUS_OPTIONS,
            'priorityOptions' => LeadService::PRIORITY_OPTIONS,
            'assignableUsers' => $this->service->assignableUsers($request->user()),
        ]);
    }

    public function store(StoreLeadRequest $request): RedirectResponse
    {
        $this->authorize('create', Lead::class);

        $payload = $request->validated();

        if ($request->user()?->hasRole('sales')) {
            $payload['assigned_user_id'] = $request->user()->id;
        }

        Lead::create($payload);

        return redirect()->route('leads.index')->with('success', 'Lead created successfully.');
    }

    public function show(Lead $lead): View
    {
        $this->authorize('view', $lead);

        $lead->load(['assignedUser', 'convertedToCustomer']);

        $activities = $lead->activities()->with('user')->latest('activity_date')->get();

        return view('leads.show', compact('lead', 'activities'));
    }

    public function edit(Request $request, Lead $lead): View
    {
        $this->authorize('update', $lead);

        return view('leads.edit', [
            'lead' => $lead,
            'statusOptions' => LeadService::STATUS_OPTIONS,
            'priorityOptions' => LeadService::PRIORITY_OPTIONS,
            'assignableUsers' => $this->service->assignableUsers($request->user()),
        ]);
    }

    public function update(UpdateLeadRequest $request, Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);

        $payload = $request->validated();

        if ($request->user()?->hasRole('sales')) {
            $payload['assigned_user_id'] = $request->user()->id;
        }

        $lead->update($payload);

        return redirect()->route('leads.index')->with('success', 'Lead updated successfully.');
    }

    public function updateStatus(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('updateStatus', $lead);

        $data = $request->validate(['status' => ['required', 'in:'.implode(',', LeadService::STATUS_OPTIONS)]]);
        $oldStatus = $lead->status;
        $newStatus = $data['status'];

        $lead->update(['status' => $newStatus]);

        if ($newStatus === 'won' && $oldStatus !== 'won') {
            return redirect()->route('leads.show', $lead)->with('success', 'Lead marked as won! Click "Convert to Customer" when ready.');
        }

        return redirect()->back()->with('success', "Lead moved from {$oldStatus} to {$newStatus}.");
    }

    public function showLostForm(Lead $lead): View
    {
        $this->authorize('update', $lead);

        return view('leads.lost-form', [
            'lead' => $lead,
            'lostCategories' => LeadService::LOST_CATEGORIES,
        ]);
    }

    public function markAsLost(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);

        $data = $request->validate([
            'lost_reason' => ['required', 'string', 'min:3', 'max:500'],
            'lost_category' => ['required', 'in:'.implode(',', array_keys(LeadService::LOST_CATEGORIES))],
        ]);

        $lead->markAsLost($data['lost_reason'], $data['lost_category']);

        return redirect()->route('leads.index')->with('success', 'Lead marked as lost.');
    }

    public function reopen(Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);

        if (! $lead->isLost()) {
            return redirect()->route('leads.show', $lead)->with('error', 'Only lost leads can be reopened.');
        }

        $lead->reopen('contacted');

        return redirect()->route('leads.show', $lead)->with('success', 'Lead has been reopened and is now active.');
    }

    public function assign(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('assign', $lead);

        $data = $request->validate([
            'assigned_user_id' => ['nullable', 'exists:users,id'],
        ]);

        $lead->update(['assigned_user_id' => $data['assigned_user_id'] ?? null]);

        return redirect()->back()->with('success', 'Lead assignee updated.');
    }

    public function setPriority(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);

        $data = $request->validate([
            'priority' => ['required', 'in:'.implode(',', LeadService::PRIORITY_OPTIONS)],
        ]);

        $lead->update(['priority' => $data['priority']]);

        return redirect()->back()->with('success', 'Lead priority updated.');
    }

    public function convert(Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);

        if ($lead->isConverted()) {
            return redirect()->route('leads.show', $lead)->with('error', 'This lead has already been converted to a customer.');
        }

        if (! $lead->isWon()) {
            return redirect()->route('leads.show', $lead)->with('error', 'Only leads with "Won" status can be converted to customers.');
        }

        try {
            $customer = $lead->convertToCustomer();
            $customerName = trim($customer->first_name.' '.$customer->last_name);

            return redirect()->route('customers.show', $customer)->with('success', "Lead successfully converted to customer: {$customerName}");
        } catch (\Exception $e) {
            return redirect()->route('leads.show', $lead)->with('error', 'Conversion failed: '.$e->getMessage());
        }
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        $this->authorize('delete', $lead);

        if ($lead->isConverted()) {
            return redirect()->back()->with('error', 'Cannot delete leads that have been converted to customers.');
        }

        $lead->delete();

        return redirect()->back()->with('success', 'Lead deleted successfully.');
    }
}
