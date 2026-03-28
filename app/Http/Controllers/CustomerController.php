<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'assignment_status' => ['nullable', 'in:pending,approved,rejected'],
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,inactive'],
            'assigned_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'delete' => ['nullable', 'integer', 'exists:customers,id'],
        ]);

        $customersQuery = $this->applyVisibilityScope(
            Customer::query()->with(['assignedUser', 'assignmentReviewer']),
            $request
        )
            ->with(['assignedUser', 'assignmentReviewer'])
            ->latest();

        if (! empty($filters['assignment_status'])) {
            $customersQuery->where('assignment_status', (string) $filters['assignment_status']);
        }

        if (! empty($filters['search'])) {
            $search = $this->escapeLike((string) $filters['search']);

            $customersQuery->where(function ($query) use ($search): void {
                $query
                    ->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status'])) {
            $customersQuery->where('status', (string) $filters['status']);
        }

        if (! empty($filters['assigned_user_id'])) {
            $customersQuery->where('assigned_user_id', (int) $filters['assigned_user_id']);
        }

        $baseStatsQuery = $this->applyVisibilityScope(Customer::query(), $request);

        $customerThisMonth = (clone $baseStatsQuery)->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $customerLastMonth = (clone $baseStatsQuery)->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        $customerSpecificMonth = (clone $baseStatsQuery)->whereMonth('created_at', 1)
            ->whereYear('created_at', 2025)
            ->count();

        $customerThisYear = (clone $baseStatsQuery)->whereYear('created_at', now()->year)->count();

        $customerIsActive = (clone $baseStatsQuery)->where('status', 'active')->count();
        $customerIsInactive = (clone $baseStatsQuery)->where('status', 'inactive')->count();

        $totalCustomers = (clone $baseStatsQuery)->count();

        $customers = $customersQuery->paginate(10)->withQueryString();
        $deleting = isset($filters['delete']) ? Customer::find($filters['delete']) : null;
        $assignmentStatuses = ['pending', 'approved', 'rejected'];
        return view('customers.index', [
            'customers' => $customers,
            'customerThisMonth' => $customerThisMonth,
            'customerLastMonth' => $customerLastMonth,
            'customerThisYear' => $customerThisYear,
            'customerSpecificMonth' => $customerSpecificMonth,
            'customerIsActive' => $customerIsActive,
            'customerIsInactive' => $customerIsInactive,
            'totalCustomers' => $totalCustomers,
            'assignableUsers' => $this->assignableUsers(),
            'deleting' => $deleting,
            'assignmentStatuses' => $assignmentStatuses,
        ]);
    }

    public function create(): View
    {
        return view('customers.create', [
            'assignableUsers' => $this->assignableUsers(),
        ]);
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $payload = $request->validated();
        $user = $request->user();

        if ($user?->hasRole('sales')) {
            $payload['assigned_user_id'] = $user->id;
        }

        if (! empty($payload['assigned_user_id'])) {
            $payload['assignment_status'] = 'pending';
            $payload['assignment_reviewed_by'] = null;
            $payload['assignment_reviewed_at'] = null;
        }

        Customer::create($payload);

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer): View
    {
        $this->ensureCustomerAccessible(request(), $customer);

        $customer->load(['assignedUser', 'assignmentReviewer']);

        return view('customers.show', [
            'customer' => $customer,
            'assignableUsers' => $this->assignableUsers(),
        ]);
    }

    public function edit(Customer $customer): View
    {
        $this->ensureCustomerAccessible(request(), $customer);

        return view('customers.edit', [
            'customer' => $customer,
            'assignableUsers' => $this->assignableUsers(),
        ]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $this->ensureCustomerAccessible($request, $customer);

        $payload = $request->validated();
        $user = $request->user();

        if ($user?->hasRole('sales')) {
            $payload['assigned_user_id'] = $user->id;
        }

        if (array_key_exists('assigned_user_id', $payload) && (int) ($payload['assigned_user_id'] ?? 0) !== (int) ($customer->assigned_user_id ?? 0)) {
            $payload['assignment_status'] = 'pending';
            $payload['assignment_reviewed_by'] = null;
            $payload['assignment_reviewed_at'] = null;
        }

        $customer->update($payload);

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $this->ensureCustomerAccessible(request(), $customer);

        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }

    public function reassign(Request $request, Customer $customer): RedirectResponse
    {
        $data = $request->validate([
            'assigned_user_id' => ['required', 'exists:users,id'],
        ]);

        $salesUser = User::query()
            ->where('id', (int) $data['assigned_user_id'])
            ->where('role', 'sales')
            ->exists();

        if (! $salesUser) {
            return redirect()->back()->with('error', 'Assigned user must be a Sales Staff account.');
        }

        $customer->update([
            'assigned_user_id' => (int) $data['assigned_user_id'],
            'assignment_status' => 'pending',
            'assignment_reviewed_by' => null,
            'assignment_reviewed_at' => null,
        ]);

        return redirect()->route('customers.show', $customer)->with('success', 'Customer reassigned successfully.');
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

    private function assignableUsers()
    {
        return User::query()
            ->where('role', 'sales')
            ->orderBy('name')
            ->get();
    }

    private function applyVisibilityScope(Builder $query, Request $request): Builder
    {
        $user = $request->user();

        if ($user?->hasRole('sales')) {
            $query
                ->where('assigned_user_id', $user->id)
                ->where('assignment_status', 'approved');
        }

        return $query;
    }

    private function ensureCustomerAccessible(Request $request, Customer $customer): void
    {
        $user = $request->user();

        if ($user?->hasRole('sales') && (int) $customer->assigned_user_id !== (int) $user->id) {
            abort(403, 'Unauthorized. You can only access customers assigned to you.');
        }
    }

    private function escapeLike(string $value): string
    {
        return addcslashes($value, '\\%_');
    }
}
