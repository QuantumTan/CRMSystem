@include('customers.partials._modal-delete')

<div class="card border-0 shadow-sm crm-toolkit">
    @php
        $isAdmin = auth()->user()?->role === 'admin';
        $isSales = auth()->user()?->role === 'sales';
    @endphp

    <div class="card-header bg-white border-bottom">
        <div class="card-body p-1">
            <form action="{{ route('customers.index') }}" method="GET"
                class="d-flex flex-column flex-lg-row align-items-lg-center gap-3">
                <div class="position-relative" style="max-width: 320px; flex: 1;">
                    <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" name="search" class="form-control form-control-sm ps-5"
                        placeholder="Search name, email, phone, company..." value="{{ request('search') }}">
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <select name="status" class="form-select form-select-sm w-auto" style="min-width: 130px;">
                        <option value="">All Status</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                    </select>
                    @if ($isAdmin || $isManager)
                        <select name="assigned_user_id" class="form-select form-select-sm w-auto"
                            style="min-width: 170px;">
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
                                <option value="{{ $assignmentStatus }}" @selected((string) request('assignment_status') === $assignmentStatus)>
                                    {{ ucfirst($assignmentStatus) }}
                                </option>
                            @endforeach
                        </select>
                    @endif


                    <button type="submit" class="btn btn-primary btn-sm px-3">Filter</button>
                    <a href="{{ route('customers.index') }}" class="btn btn-outline-primary btn-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table crm-table table-hover align-middle mb-0 crm-data-table crm-table-fixed crm-customer-table">
            <colgroup>
                <col class="crm-col-customer-id">
                <col class="crm-col-customer-name">
                <col class="crm-col-customer-name">
                <col class="crm-col-customer-email">
                <col class="crm-col-customer-phone">
                <col class="crm-col-customer-company">
                <col class="crm-col-customer-address">
                <col class="crm-col-customer-status">
                <col class="crm-col-customer-assignment">
                <col class="crm-col-customer-assignee">
                <col class="crm-col-customer-date">
                <col class="crm-col-customer-actions">
            </colgroup>
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
                    @php
                        $customerAddress = $customer->address
                            ? preg_replace('/\s+/', ' ', trim((string) $customer->address))
                            : 'N/A';
                        $customerCompany = $customer->company ?: 'N/A';
                        $customerAssignee = $customer->assignedUser?->name ?: 'Unassigned';
                        $customerCreatedAt = $customer->created_at->format('M d, Y');
                    @endphp
                    <tr>
                        <td class="small text-muted py-3 crm-table-cell-truncate" title="#{{ $customer->id }}">
                            #{{ $customer->id }}</td>
                        <td class="small text-muted py-3 crm-table-cell-truncate" title="{{ $customer->first_name }}">
                            {{ $customer->first_name }}</td>
                        <td class="small text-muted py-3 crm-table-cell-truncate" title="{{ $customer->last_name }}">
                            {{ $customer->last_name }}</td>
                        <td class="small text-muted py-3 crm-table-cell-truncate" title="{{ $customer->email }}">
                            {{ $customer->email }}</td>
                        <td class="small text-muted py-3 crm-table-cell-truncate" title="{{ $customer->phone }}">
                            {{ $customer->phone }}</td>
                        <td class="small text-muted py-3 crm-table-cell-truncate" title="{{ $customerCompany }}">
                            {{ $customerCompany }}</td>
                        <td class="small text-muted py-3 crm-table-cell-truncate" title="{{ $customerAddress }}">
                            {{ $customerAddress }}</td>
                        <td class="py-3">
                            @php
                                $customerStatus = strtolower((string) $customer->status);
                                $customerStatusClass = match ($customerStatus) {
                                    'active' => 'crm-table-status crm-table-status-success',
                                    'inactive' => 'crm-table-status crm-table-status-muted',
                                    default => 'crm-table-status crm-table-status-primary',
                                };
                            @endphp
                            <span
                                class="{{ $customerStatusClass }}">{{ ucfirst($customerStatus ?: 'unknown') }}</span>
                        </td>
                        <td class="py-3">
                            @php
                                $assignmentStatus = $customer->assignment_status ?? 'pending';
                                $assignmentStatusClass = match ($assignmentStatus) {
                                    'approved' => 'crm-table-status crm-table-status-success',
                                    'rejected' => 'crm-table-status crm-table-status-danger',
                                    'pending' => 'crm-table-status crm-table-status-warning',
                                    default => 'crm-table-status crm-table-status-muted',
                                };
                            @endphp
                            <span class="{{ $assignmentStatusClass }}">{{ ucfirst($assignmentStatus) }}</span>
                        </td>
                        <td class="small text-muted py-3 crm-table-cell-truncate" title="{{ $customerAssignee }}">
                            {{ $customerAssignee }}</td>
                        <td class="small text-muted py-3 crm-table-cell-truncate" title="{{ $customerCreatedAt }}">
                            {{ $customerCreatedAt }}</td>
                        <td class="py-3 crm-table-actions-cell">
                            <div class="crm-table-actions">
                                <a href="{{ route('customers.show', $customer) }}"
                                    class="btn btn-sm btn-light border text-primary">View</a>
                                @if ($isAdmin || $isSales)
                                    <a href="{{ route('customers.edit', $customer) }}"
                                        class="btn btn-sm btn-light border text-dark">Edit</a>
                                @endif
                                @include('customers.partials._delete-form', [
                                    'customer' => $customer,
                                    'buttonClass' =>
                                        'btn btn-sm btn-light border text-danger d-inline-flex align-items-center gap-1',
                                    'buttonLabel' => 'Delete',
                                ])
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
