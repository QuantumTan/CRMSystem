<?php
// app/Http/Controllers/ActivityController.php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller
{
    // ── Create ─────────────────────────────────────────────────────────────────
    public function create()
    {
        return view('activities.create');
    }
    // ── Index ─────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Activity::with(['user', 'customer', 'lead'])->latest('activity_date');

        if ($request->filled('type')) {
            $query->where('activity_type', $request->type);
        }

        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $activities = $query->paginate(20)->withQueryString();
        $users = User::orderBy('name')->get();

        return view('activities.index', compact('activities', 'users'));
    }

    // ── Store ─────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'activity_type' => 'required|in:call,email,meeting,note',
            'description' => 'required|string|max:2000',
            'activity_date' => 'required|date',
            'customer_id' => 'nullable|exists:customers,id',
            'lead_id' => 'nullable|exists:leads,id',
        ]);

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
        return view('activities.edit', compact('activity'));
    }

    // ── Update ────────────────────────────────────────────────────────────────
    public function update(Request $request, Activity $activity)
    {
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
        $activity->delete();

        return back()->with('success', 'Activity deleted.');
    }
}
