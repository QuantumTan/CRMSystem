<?php

// app/Http/Controllers/ActivityController.php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ActivityController extends Controller
{
    // ── Create ─────────────────────────────────────────────────────────────────
    public function create()
    {
        $this->authorize('create', Activity::class);

        $user = Auth::user();
        $customers = Customer::query()
            ->when($user->role === 'sales', function ($query) use ($user) {
                $query->where('assigned_user_id', $user->id);
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $leads = Lead::query()
            ->when($user->role === 'sales', function ($query) use ($user) {
                $query->where('assigned_user_id', $user->id);
            })
            ->orderBy('name')
            ->get();

        return view('activities.create', compact('customers', 'leads'));
    }

    // ── Index ─────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $this->authorize('viewAny', Activity::class);

        $user = Auth::user();
        $query = Activity::with(['user', 'customer', 'lead'])->latest('activity_date');

        // Sales only see activities for leads/customers assigned to them
        if ($user->role === 'sales') {
            $leadIds = Lead::where('assigned_user_id', $user->id)->pluck('id');
            $customerIds = Customer::where('assigned_user_id', $user->id)->pluck('id');

            $query->where(function ($q) use ($leadIds, $customerIds) {
                $q->whereIn('lead_id', $leadIds)
                    ->orWhereIn('customer_id', $customerIds);
            });
        }

        // Filters
        if ($request->filled('type')) {
            $query->where('activity_type', $request->type);
        }
        if ($request->filled('search')) {
            $query->where('description', 'like', '%'.$request->search.'%');
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $activities = $query->paginate(20)->withQueryString();

        // Users for filter dropdown
        if ($user->role === 'sales') {
            $users = collect([$user]);
        } else {
            $users = User::orderBy('name')->get();
        }

        return view('activities.index', compact('activities', 'users'));
    }

    // ── Store ─────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $this->authorize('create', Activity::class);

        $validated = $request->validate([
            'activity_type' => 'required|in:call,email,meeting,note',
            'description' => 'required|string|max:2000',
            'activity_date' => 'required|date',
            'customer_id' => 'nullable|required_without:lead_id|exists:customers,id',
            'lead_id' => 'nullable|required_without:customer_id|exists:leads,id',
        ]);

        if (! empty($validated['customer_id']) && ! empty($validated['lead_id'])) {
            throw ValidationException::withMessages([
                'customer_id' => 'Select either a customer or a lead, not both.',
                'lead_id' => 'Select either a lead or a customer, not both.',
            ]);
        }

        $user = Auth::user();

        if ($user->role === 'sales') {
            if (! empty($validated['customer_id'])) {
                $isAssignedCustomer = Customer::whereKey($validated['customer_id'])
                    ->where('assigned_user_id', $user->id)
                    ->exists();

                if (! $isAssignedCustomer) {
                    throw ValidationException::withMessages([
                        'customer_id' => 'You can only log activities for customers assigned to you.',
                    ]);
                }
            }

            if (! empty($validated['lead_id'])) {
                $isAssignedLead = Lead::whereKey($validated['lead_id'])
                    ->where('assigned_user_id', $user->id)
                    ->exists();

                if (! $isAssignedLead) {
                    throw ValidationException::withMessages([
                        'lead_id' => 'You can only log activities for leads assigned to you.',
                    ]);
                }
            }
        }

        Activity::create([...$validated, 'user_id' => Auth::id()]);

        // If the form was submitted from a lead/customer page, back() returns there.
        // If submitted from the standalone create page, redirect to the index.
        $previous = url()->previous();
        $isStandalone = str_contains($previous, route('activities.create'));

        return $isStandalone
            ? redirect()->route('activities.index')->with('success', 'Activity logged successfully.')
            : back()->with('success', 'Activity logged successfully.');
    }

    // ── Edit ──────────────────────────────────────────────────────────────────
    public function edit(Activity $activity)
    {
        $this->authorize('update', $activity);

        return view('activities.edit', compact('activity'));
    }

    // ── Update ────────────────────────────────────────────────────────────────
    public function update(Request $request, Activity $activity)
    {

        $this->authorize('update', $activity);
        $validated = $request->validate([
            'activity_type' => 'required|in:call,email,meeting,note',
            'description' => 'required|string|max:2000',
            'activity_date' => 'required|date',
        ]);

        $activity->update($validated);

        // Redirect back to wherever the user came from before opening the edit page.
        // The edit form's cancel button also uses history.back() for the same reason.
        return redirect()->back()->with('success', 'Activity updated successfully.');
    }

    // ── Destroy ───────────────────────────────────────────────────────────────
    public function destroy(Activity $activity)
    {
        $this->authorize('delete', $activity);
        $activity->delete();

        return back()->with('success', 'Activity deleted.');
    }
}
