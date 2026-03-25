<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Pest\ArchPresets\Custom;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $customers = Customer::query()
            ->with(['assignedUser', 'assignmentReviewer'])
            ->latest();

        if ($user?->hasRole('manager')) {
            $customers->whereNotNull('assigned_user_id');
        }

        if ($request->filled('assignment_status')) {
            $customers->where('assignment_status', $request->string('assignment_status')->toString());
        }

        // Customer count
        // This month
        $customerThisMonth = Customer::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Last month
        $customerLastMonth = Customer::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        // Specific month (e.g. January 2025)
        $customerSpecificMonth = Customer::whereMonth('created_at', 1)
            ->whereYear('created_at', 2025)
            ->count();

        // This year
        $customerThisYear = Customer::whereYear('created_at', now()->year)->count();

        // Active
        $customerIsActive = Customer::where('status', 'active')->count();
        // Inactive
        $customerIsInactive = Customer::where('status', 'inactive')->count();

        //all customers
        $totalCustomers = Customer::count();

        $editing  = $request->filled('edit')   ? Customer::find($request->edit)   : null;
        $deleting = $request->filled('delete') ? Customer::find($request->delete) : null;
        

        $customers = $customers->paginate(10)->withQueryString();

        return view('customers.index', compact('customers', 'customerThisMonth', 'customerLastMonth', 'customerThisYear', 'customerSpecificMonth', 'customerIsActive', 'customerIsInactive', 'totalCustomers', 'editing', 'deleting'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(StoreCustomerRequest $request)
    {
        Customer::create($request->validated());

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer)
    {
        $customer->load(['assignedUser', 'assignmentReviewer']);

        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }

    public function approveAssignment(Request $request, Customer $customer): RedirectResponse
    {
        $customer->update([
            'assignment_status' => 'approved',
            'assignment_reviewed_by' => $request->user()?->id,
            'assignment_reviewed_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Customer assignment approved.');
    }

    public function rejectAssignment(Request $request, Customer $customer): RedirectResponse
    {
        $customer->update([
            'assignment_status' => 'rejected',
            'assignment_reviewed_by' => $request->user()?->id,
            'assignment_reviewed_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Customer assignment rejected.');
    }
}
