<?php

namespace App\Http\Controllers;

use App\Models\FollowUp;
use App\Http\Requests\StoreFollowUpRequest;
use App\Http\Requests\UpdateFollowUpRequest;
use Illuminate\Support\Facades\Auth;

class FollowUpController extends Controller
{
    public function index()
    {
        $followUps = FollowUp::with(['customer', 'lead', 'user'])->latest()->paginate(10);
        return view('follow_ups.index', compact('followUps'));
    }

    public function create()
    {
        return view('follow_ups.create');
    }

    public function store(StoreFollowUpRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();

        FollowUp::create($data);
        return redirect()->route('follow-ups.index')->with('success', 'Follow-up created successfully.');
    }

    public function edit(FollowUp $followUp)
    {
        return view('follow_ups.edit', compact('followUp'));
    }

    public function update(UpdateFollowUpRequest $request, FollowUp $followUp)
    {
        $followUp->update($request->validated());
        return redirect()->route('follow-ups.index')->with('success', 'Follow-up updated successfully.');
    }

    public function markComplete(FollowUp $followUp)
    {
        $followUp->update(['status' => 'completed']);
        return redirect()->route('follow-ups.index')->with('success', 'Follow-up marked as completed.');
    }
}