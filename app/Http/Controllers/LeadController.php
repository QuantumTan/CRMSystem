<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;

class LeadController extends Controller
{
    public function index()
    {
        $leads = Lead::with('assignedUser')->latest()->paginate(10);
        return view('leads.index', compact('leads'));
    }

    public function create()
    {
        return view('leads.create');
    }

    public function store(StoreLeadRequest $request)
    {
        Lead::create($request->validated());
        return redirect()->route('leads.index')->with('success', 'Lead created successfully.');
    }

    public function show(Lead $lead)
    {
        return view('leads.show', compact('lead'));
    }

    public function edit(Lead $lead)
    {
        return view('leads.edit', compact('lead'));
    }

    public function update(UpdateLeadRequest $request, Lead $lead)
    {
        $lead->update($request->validated());
        return redirect()->route('leads.index')->with('success', 'Lead updated successfully.');
    }

    public function updateStatus(UpdateLeadRequest $request, Lead $lead)
    {
        $lead->update(['status' => $request->validated('status')]);
        return redirect()->route('leads.index')->with('success', 'Lead status updated.');
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();
        return redirect()->route('leads.index')->with('success', 'Lead deleted successfully.');
    }
}