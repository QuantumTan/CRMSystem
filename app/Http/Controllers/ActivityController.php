<?php

namespace App\Http\Controllers;

use App\Filters\ActivityFilter;
use App\Http\Requests\StoreActivityRequest;
use App\Models\Activity;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ActivityController extends Controller
{
    // Create
    public function create()
    {
        $this->authorize('create', Activity::class);

        $user = Auth::user();

        $customers = Customer::query()
            ->when(
                $user->role === 'sales',
                fn ($query) => $query->where('assigned_user_id', $user->id)
            )
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $leads = Lead::query()
            ->when(
                $user->role === 'sales',
                fn ($query) => $query->where('assigned_user_id', $user->id)
            )
            ->orderBy('name')
            ->get();

        return view('activities.create', compact('customers', 'leads'));
    }

    // Index
    public function index(ActivityFilter $filter)
    {
        $this->authorize('viewAny', Activity::class);

        $user = Auth::user();

        $activities = Activity::query()
            ->with(['user', 'customer', 'lead'])
            ->visibleTo($user)
            ->latest('activity_date')
            ->filter($filter)
            ->paginate(20)
            ->withQueryString();

        $users = $user->role === 'sales'
            ? collect([$user])
            : User::query()->orderBy('name')->get();

        return view('activities.index', compact('activities', 'users'));
    }

    // Store
    public function store(StoreActivityRequest $request)
    {
        $validated = $request->validated();
        $user = Auth::user();

        if ($user->role === 'sales') {
            if (! empty($validated['customer_id'])) {
                $isAssignedCustomer = Customer::query()
                    ->whereKey($validated['customer_id'])
                    ->where('assigned_user_id', $user->id)
                    ->exists();

                if (! $isAssignedCustomer) {
                    throw ValidationException::withMessages([
                        'customer_id' => 'You can only log activities for customers assigned to you.',
                    ]);
                }
            }

            if (! empty($validated['lead_id'])) {
                $isAssignedLead = Lead::query()
                    ->whereKey($validated['lead_id'])
                    ->where('assigned_user_id', $user->id)
                    ->exists();

                if (! $isAssignedLead) {
                    throw ValidationException::withMessages([
                        'lead_id' => 'You can only log activities for leads assigned to you.',
                    ]);
                }
            }
        }

        Activity::create([
            ...$validated,
            'user_id' => $user->id,
        ]);

        $previous = url()->previous();
        $isStandalone = str_contains($previous, route('activities.create'));

        return $isStandalone
            ? redirect()->route('activities.index')->with('success', 'Activity logged successfully.')
            : back()->with('success', 'Activity logged successfully.');
    }

    // Edit
    public function edit(Activity $activity)
    {
        $this->authorize('update', $activity);

        return view('activities.edit', compact('activity'));
    }

    // Update
    public function update(\Illuminate\Http\Request $request, Activity $activity)
    {
        $this->authorize('update', $activity);

        $validated = $request->validate([
            'activity_type' => 'required|in:call,email,meeting,note',
            'description' => 'required|string|max:2000',
            'activity_date' => 'required|date',
        ]);

        $activity->update($validated);

        return redirect()->back()->with('success', 'Activity updated successfully.');
    }

    // Destroy
    public function destroy(Activity $activity)
    {
        $this->authorize('delete', $activity);

        $activity->delete();

        return back()->with('success', 'Activity deleted.');
    }
}