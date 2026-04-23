<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Models\User;
use App\Services\CustomerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(private CustomerService $service) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'assignment_status' => ['nullable', 'in:pending,approved,rejected'],
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,inactive'],
            'assigned_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $customers = $this->service->getFilteredQuery($filters)->paginate(10)->withQueryString();

        return view('customers.index', array_merge($this->service->getStats(), [
            'customers' => $customers,
            'assignableUsers' => $this->service->assignableUsers(),
            'assignmentStatuses' => ['pending', 'approved', 'rejected'],
        ]));
    }

    public function create(): View
    {
        return view('customers.create', [
            'assignableUsers' => $this->service->assignableUsers(),
        ]);
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $payload = $this->service->prepareAssignmentPayload($request->validated(), $request->user());

        Customer::create($payload);

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer): View
    {
        $this->authorize('view', $customer);

        $customer->load(['assignedUser', 'assignmentReviewer']);

        return view('customers.show', [
            'customer' => $customer,
            'assignableUsers' => $this->service->assignableUsers(),
            'activities' => $customer->activities()->with('user')->latest('activity_date')->get(),
        ]);
    }

    public function edit(Customer $customer): View
    {
        $this->authorize('view', $customer);

        return view('customers.edit', [
            'customer' => $customer,
            'assignableUsers' => $this->service->assignableUsers(),
        ]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $this->authorize('update', $customer);

        $payload = $request->validated();
        $user = $request->user();

        if ($user?->hasRole('sales')) {
            $payload['assigned_user_id'] = $user->id;
        }

        if ($this->service->hasAssignmentChanged($payload, $customer)) {
            $payload = array_merge($payload, [
                'assignment_status' => 'pending',
                'assignment_reviewed_by' => null,
                'assignment_reviewed_at' => null,
            ]);
        }

        $customer->update($payload);

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $this->authorize('delete', $customer);

        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }

    public function reassign(Request $request, Customer $customer): RedirectResponse
    {
        $data = $request->validate([
            'assigned_user_id' => ['required', 'exists:users,id'],
        ]);

        $isSales = User::where('id', (int) $data['assigned_user_id'])->where('role', 'sales')->exists();

        if (! $isSales) {
            return redirect()->back()->with('error', 'Assigned user must be a Sales Staff account.');
        }

        $customer->reassignTo((int) $data['assigned_user_id']);

        return redirect()->route('customers.show', $customer)->with('success', 'Customer reassigned successfully.');
    }

    public function approveAssignment(Request $request, Customer $customer): RedirectResponse
    {
        $customer->approve($request->user());

        return redirect()->back()->with('success', 'Customer assignment approved.');
    }

    public function rejectAssignment(Request $request, Customer $customer): RedirectResponse
    {
        $customer->reject($request->user());

        return redirect()->back()->with('success', 'Customer assignment rejected.');
    }
}
