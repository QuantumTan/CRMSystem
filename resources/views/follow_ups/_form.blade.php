@php
    $isEditing = isset($followUp);
    $submitLabel = $isEditing ? 'Save Changes' : 'Create Follow-up';
    $isLegacyCustomerOnlyFollowUp = $isEditing && ! $followUp->lead_id && $followUp->customer_id;
    $selectedStatus = old('status', $followUp->status ?? 'pending');
@endphp

@if ($errors->any())
    <div class="alert alert-danger crm-alert py-2 small mb-4">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ $isEditing ? route('follow-ups.update', $followUp) : route('follow-ups.store') }}" method="POST">
    @csrf
    @if ($isEditing)
        @method('PUT')
    @else
        <input type="hidden" name="status" value="pending">
    @endif

    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                        <div>
                            <h2 class="h5 mb-1">Follow-up Details</h2>
                            <p class="text-muted small mb-0">Capture the lead, schedule, and context for this reminder.</p>
                        </div>

                        @if (! $isEditing)
                            <span class="crm-section-tag">Default Status: Pending</span>
                        @endif
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label for="title" class="form-label fw-medium">Title <span class="text-danger">*</span></label>
                            <input
                                id="title"
                                type="text"
                                name="title"
                                class="form-control @error('title') is-invalid @enderror"
                                value="{{ old('title', $followUp->title ?? '') }}"
                                placeholder="Call lead about proposal feedback"
                                required
                            >
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="lead_id" class="form-label fw-medium">
                                Lead
                                @if (! $isLegacyCustomerOnlyFollowUp)
                                    <span class="text-danger">*</span>
                                @endif
                            </label>
                            <select id="lead_id" name="lead_id" class="form-select @error('lead_id') is-invalid @enderror" @required(! $isLegacyCustomerOnlyFollowUp)>
                                <option value="">
                                    {{ $isLegacyCustomerOnlyFollowUp ? 'Leave blank for converted customer follow-up' : 'Select lead' }}
                                </option>
                                @foreach ($leads as $lead)
                                    <option value="{{ $lead->id }}" @selected((string) old('lead_id', $followUp->lead_id ?? '') === (string) $lead->id)>
                                        {{ $lead->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">
                                New follow-ups should be attached to a lead. Customer-linked legacy follow-ups can still be edited safely.
                            </div>
                            @error('lead_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="due_date" class="form-label fw-medium">Due Date <span class="text-danger">*</span></label>
                            <input
                                id="due_date"
                                type="date"
                                name="due_date"
                                class="form-control @error('due_date') is-invalid @enderror"
                                value="{{ old('due_date', isset($followUp->due_date) ? $followUp->due_date->format('Y-m-d') : now()->toDateString()) }}"
                                required
                            >
                            @error('due_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label fw-medium">Description</label>
                            <textarea
                                id="description"
                                name="description"
                                rows="5"
                                class="form-control @error('description') is-invalid @enderror"
                                placeholder="Add notes, next step context, or what the assignee should prepare before reaching out."
                            >{{ old('description', $followUp->description ?? '') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h6 mb-3">Assignment</h2>

                    <div class="mb-3">
                        <label for="user_id" class="form-label fw-medium">Assigned User</label>
                        <select id="user_id" name="user_id" class="form-select @error('user_id') is-invalid @enderror">
                            @foreach ($assignableUsers as $assignableUser)
                                <option value="{{ $assignableUser->id }}" @selected((string) old('user_id', $followUp->user_id ?? auth()->id()) === (string) $assignableUser->id)>
                                    {{ $assignableUser->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if ($isEditing)
                        <div>
                            <label for="status" class="form-label fw-medium">Status</label>
                            <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                                @foreach (['pending', 'completed'] as $status)
                                    <option value="{{ $status }}" @selected($selectedStatus === $status)>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @else
                        <div class="crm-info-card">
                            <div class="crm-info-label">Status</div>
                            <div class="crm-info-value">Pending</div>
                            <div class="small text-muted mt-2">New follow-ups start as pending until they are completed.</div>
                        </div>
                    @endif
                </div>
            </div>

            @if ($isLegacyCustomerOnlyFollowUp)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h2 class="h6 mb-2">Converted Customer Follow-up</h2>
                        <p class="text-muted small mb-0">
                            This reminder is currently linked to
                            <strong>{{ $followUp->customer?->first_name }} {{ $followUp->customer?->last_name }}</strong>
                            after lead conversion. You can keep editing it without choosing a lead.
                        </p>
                    </div>
                </div>
            @endif

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">{{ $submitLabel }}</button>
                <a href="{{ route('follow-ups.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </div>
</form>
