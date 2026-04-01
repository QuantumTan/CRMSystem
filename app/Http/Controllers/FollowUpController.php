<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFollowUpRequest;
use App\Http\Requests\UpdateFollowUpRequest;
use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FollowUpController extends Controller
{
    public function index(Request $request): Response|StreamedResponse
    {
        $this->authorize('viewAny', FollowUp::class);

        $user = Auth::user();
        $query = FollowUp::with(['customer', 'lead', 'user'])->latest('due_date');

        if ($user->role === 'sales') {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->string('search').'%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->input('export') === 'csv') {
            $fileName = 'follow-ups-'.now()->format('Ymd-His').'.csv';

            return response()->streamDownload(function () use ($query) {
                $handle = fopen('php://output', 'w');

                fputcsv($handle, ['Title', 'Description', 'Due Date', 'Status', 'Customer', 'Lead', 'Assigned To']);

                (clone $query)->each(function (FollowUp $followUp) use ($handle) {
                    fputcsv($handle, [
                        $followUp->title,
                        $followUp->description,
                        optional($followUp->due_date)->format('Y-m-d'),
                        $followUp->status,
                        $followUp->customer ? ($followUp->customer->first_name.' '.$followUp->customer->last_name) : '',
                        $followUp->lead?->name,
                        $followUp->user?->name,
                    ]);
                });

                fclose($handle);
            }, $fileName, ['Content-Type' => 'text/csv']);
        }

        $followUps = $query->paginate(10)->withQueryString();
        $statusOptions = ['pending', 'completed'];

        return response()->view('follow_ups.index', compact('followUps', 'statusOptions'));
    }

    public function create(): Response
    {
        $this->authorize('create', FollowUp::class);

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

        $assignableUsers = $user->role === 'sales'
            ? User::whereKey($user->id)->get()
            : User::whereIn('role', ['admin', 'manager', 'sales'])->orderBy('name')->get();

        return response()->view('follow_ups.create', compact('customers', 'leads', 'assignableUsers'));
    }

    public function store(StoreFollowUpRequest $request): Response|RedirectResponse
    {
        $this->authorize('create', FollowUp::class);

        $data = $request->validated();
        $data['user_id'] = $data['user_id'] ?? Auth::id();

        if (Auth::user()->role === 'sales') {
            $data['user_id'] = Auth::id();

            if (! empty($data['customer_id'])) {
                $isAssignedCustomer = Customer::whereKey($data['customer_id'])
                    ->where('assigned_user_id', Auth::id())
                    ->exists();

                if (! $isAssignedCustomer) {
                    throw ValidationException::withMessages([
                        'customer_id' => 'You can only create follow-ups for customers assigned to you.',
                    ]);
                }
            }

            if (! empty($data['lead_id'])) {
                $isAssignedLead = Lead::whereKey($data['lead_id'])
                    ->where('assigned_user_id', Auth::id())
                    ->exists();

                if (! $isAssignedLead) {
                    throw ValidationException::withMessages([
                        'lead_id' => 'You can only create follow-ups for leads assigned to you.',
                    ]);
                }
            }
        }

        FollowUp::create($data);

        return redirect()->route('follow-ups.index')->with('success', 'Follow-up created successfully.');
    }

    public function edit(FollowUp $followUp): Response|RedirectResponse
    {
        $this->authorize('update', $followUp);

        if ($followUp->status === 'completed') {
            return redirect()->route('follow-ups.index')->with('error', 'Completed follow-ups are locked for editing.');
        }

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

        $assignableUsers = $user->role === 'sales'
            ? User::whereKey($user->id)->get()
            : User::whereIn('role', ['admin', 'manager', 'sales'])->orderBy('name')->get();

        return response()->view('follow_ups.edit', compact('followUp', 'customers', 'leads', 'assignableUsers'));
    }

    public function update(UpdateFollowUpRequest $request, FollowUp $followUp): Response|RedirectResponse
    {
        $this->authorize('update', $followUp);

        if ($followUp->status === 'completed') {
            return redirect()->route('follow-ups.index')->with('error', 'Completed follow-ups are locked for editing.');
        }

        $validated = $request->validated();

        if (Auth::user()->role === 'sales') {
            $validated['user_id'] = Auth::id();

            if (! empty($validated['customer_id'])) {
                $isAssignedCustomer = Customer::whereKey($validated['customer_id'])
                    ->where('assigned_user_id', Auth::id())
                    ->exists();

                if (! $isAssignedCustomer) {
                    throw ValidationException::withMessages([
                        'customer_id' => 'You can only update follow-ups for customers assigned to you.',
                    ]);
                }
            }

            if (! empty($validated['lead_id'])) {
                $isAssignedLead = Lead::whereKey($validated['lead_id'])
                    ->where('assigned_user_id', Auth::id())
                    ->exists();

                if (! $isAssignedLead) {
                    throw ValidationException::withMessages([
                        'lead_id' => 'You can only update follow-ups for leads assigned to you.',
                    ]);
                }
            }
        }

        $followUp->update($validated);

        return redirect()->route('follow-ups.index')->with('success', 'Follow-up updated successfully.');
    }

    public function markComplete(FollowUp $followUp): RedirectResponse
    {
        $this->authorize('update', $followUp);

        $followUp->update(['status' => 'completed']);

        return redirect()->route('follow-ups.index')->with('success', 'Follow-up marked as completed.');
    }

    public function reopen(FollowUp $followUp): RedirectResponse
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        $followUp->update(['status' => 'pending']);

        return redirect()->route('follow-ups.index')->with('success', 'Follow-up reopened successfully.');
    }
}
