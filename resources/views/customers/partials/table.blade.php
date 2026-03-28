@include('customers.partials._modal-delete', ['deleting' => $deleting ?? null])

<div class="card border-0 shadow-sm">
    @php
        $isAdmin = auth()->user()?->role === 'admin';
        $isSales = auth()->user()?->role === 'sales';
    @endphp

    <div class="card-header bg-white border-bottom p-3">
        <form action="{{ route('customers.index') }}" method="GET"
            class="d-flex flex-column flex-lg-row align-items-lg-center gap-3">
            <div class="position-relative" style="max-width: 320px; flex: 1;">
                <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                <input type="text" name="search" class="form-control form-control-sm ps-5"
                    placeholder="Search name, email, phone, company..." value="{{ request('search') }}">
            </div>

            <div class="d-flex flex-wrap   gap-2">
                <select name="status" class="form-select form-select-sm w-auto" style="min-width: 130px;">
                    <option value="">All Status</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
                @if ($isAdmin || $isManager)
                    <select name="assigned_user_id" class="form-select form-select-sm w-auto" style="min-width: 170px;">
                        <option value="">All Assignees</option>
                        @foreach ($assignableUsers as $assignableUser)
                            <option value="{{ $assignableUser->id }}" @selected((string) request('assigned_user_id') === (string) $assignableUser->id)>
                                {{ $assignableUser->name }}
                            </option>
                        @endforeach
                    </select>
                @endif
                @if ($isAdmin || $isManager)
                    <select name="assignment_status" class="form-select form-select-sm w-auto">
                        <option value="">All Assignment Status</option>
                        @foreach ($assignmentStatuses as $assignmentStatus)
                            <option value="{{$assignmentStatus}}" @@selected((string) request('assignment_status') === $assignmentStatus)>
                                {{ucfirst($assignmentStatus)}}
                            </option>
                        @endforeach
                    </select>
                @endif


                <button type="submit" class="btn btn-primary btn-sm px-3">Filter</button>
                <a href="{{ route('customers.index') }}" class="btn btn-outline-primary btn-sm">Reset</a>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="small text-muted py-3">Customer ID</th>
                    <th class="small text-muted py-3">First Name</th>
                    <th class="small text-muted py-3">Last Name</th>
                    <th class="small text-muted py-3">Email</th>
                    <th class="small text-muted py-3">Phone Number</th>
                    <th class="small text-muted py-3">Company Name</th>
                    <th class="small text-muted py-3">Address</th>
                    <th class="small text-muted py-3">Status</th>
                    <th class="small text-muted py-3">Assignment Status</th>
                    <th class="small text-muted py-3">Assigned User</th>
                    <th class="small text-muted py-3">Created At</th>
                    <th class="small text-muted py-3 text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customers as $customer)
                    <tr>
                        <td class="small text-muted py-3">#{{ $customer->id }}</td>
                        <td class="small text-muted py-3">{{ $customer->first_name }}</td>
                        <td class="small text-muted py-3">{{ $customer->last_name }}</td>
                        <td class="small text-muted  py-3">{{ $customer->email }}</td>
                        <td class="small text-muted py-3">{{ $customer->phone }}</td>
                        <td class="small text-muted py-3">{{ $customer->company ?: 'N/A' }}</td>
                        <td class="small text-muted py-3">{{ $customer->address ?: 'N/A' }}</td>
                        <td class="py-3">
                            <x-status-badge :status="$customer->status" />
                        </td>
                        <td class="py-3">
                            @php
                                $assignmentStatus = $customer->assignment_status ?? 'pending';
                                $badgeClass = match ($assignmentStatus) {
                                    'approved' => 'badge bg-success-subtle text-success border border-success-subtle',
                                    'rejected' => 'badge bg-danger-subtle text-danger border border-danger-subtle',
                                    'pending' => 'badge bg-warning-subtle text-warning border border-warning-subtle',
                                    default
                                        => 'badge bg-secondary-subtle text-secondary border border-secondary-subtle',
                                };
                            @endphp
                            <span class="{{ $badgeClass }}">{{ ucfirst($assignmentStatus) }}</span>
                        </td>
                        <td class="small text-muted py-3">{{ $customer->assignedUser?->name ?: 'Unassigned' }}</td>
                        <td class="small text-muted py-3">{{ $customer->created_at->format('M d, Y') }}</td>
                        <td class="py-3">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('customers.show', $customer) }}"
                                    class="btn btn-sm btn-light border text-primary">View</a>
                                @if ($isAdmin || $isSales)
                                    <a href="{{ route('customers.edit', $customer) }}"
                                        class="btn btn-sm btn-light border text-dark">Edit</a>
                                @endif
                                @if ($isAdmin)
                                    <a href="{{ route('customers.index', ['delete' => $customer->id]) }}"
                                        class="btn btn-sm btn-light border text-danger">Delete</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center text-muted py-5">No customers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-white border-top py-3">
        {{ $customers->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
</div>
