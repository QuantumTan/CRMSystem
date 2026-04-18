@php
    $isEditing = isset($activity) && $activity;
    $isEmbedded = ! $isEditing && (! empty($lead) || ! empty($customer));
    $formAction = $isEditing ? route('activities.update', $activity) : route('activities.store');
    $relatedLead = $lead ?? $activity?->lead;
    $relatedCustomer = $customer ?? $activity?->customer;
@endphp

@if ($errors->any() && ! $isEmbedded)
    <div class="alert alert-danger crm-alert mb-4">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ $formAction }}" method="POST" class="crm-form-shell row g-4">
    @csrf
    @if ($isEditing)
        @method('PATCH')
    @endif

    @if (! empty($relatedLead))
        <input type="hidden" name="lead_id" value="{{ $relatedLead->id }}">
    @endif

    @if (! empty($relatedCustomer))
        <input type="hidden" name="customer_id" value="{{ $relatedCustomer->id }}">
    @endif

    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm crm-form-card mb-4">
            <div class="card-body">
                <div class="crm-form-section-head">
                    <h2 class="crm-form-section-title">{{ $isEditing ? 'Activity Details' : 'New Activity' }}</h2>
                    <p class="crm-form-section-copy">Capture what happened, when it happened, and the related record for the team timeline.</p>
                </div>

                <div class="row g-3">
                    @if (empty($relatedLead) && empty($relatedCustomer) && ! $isEditing)
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-medium">Customer (optional)</label>
                            <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror">
                                <option value="">Select customer</option>
                                @foreach (($customers ?? collect()) as $customerOption)
                                    <option value="{{ $customerOption->id }}" @selected(old('customer_id') == $customerOption->id)>
                                        {{ $customerOption->first_name }} {{ $customerOption->last_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-medium">Lead (optional)</label>
                            <select name="lead_id" class="form-select @error('lead_id') is-invalid @enderror">
                                <option value="">Select lead</option>
                                @foreach (($leads ?? collect()) as $leadOption)
                                    <option value="{{ $leadOption->id }}" @selected(old('lead_id') == $leadOption->id)>
                                        {{ $leadOption->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('lead_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    <div class="col-12">
                        <label class="form-label fw-medium">Activity Type <span class="text-danger">*</span></label>
                        <div class="crm-choice-grid">
                            @foreach ([
                                'call' => ['bi-telephone-fill', 'Call'],
                                'email' => ['bi-envelope-fill', 'Email'],
                                'meeting' => ['bi-calendar-event-fill', 'Meeting'],
                                'note' => ['bi-sticky-fill', 'Note'],
                            ] as $type => [$icon, $label])
                                <div>
                                    <input type="radio" class="btn-check" name="activity_type"
                                        id="{{ $isEditing ? 'edit_' : '' }}type_{{ $type }}"
                                        value="{{ $type }}"
                                        @checked(old('activity_type', $activity->activity_type ?? 'note') === $type)>
                                    <label class="btn btn-outline-secondary d-flex align-items-center gap-2 px-3"
                                        for="{{ $isEditing ? 'edit_' : '' }}type_{{ $type }}">
                                        <i class="bi {{ $icon }}"></i> {{ $label }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('activity_type')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-medium">Description <span class="text-danger">*</span></label>
                        <textarea name="description" rows="5"
                            class="form-control @error('description') is-invalid @enderror"
                            placeholder="Describe the conversation, outcome, or next step...">{{ old('description', $activity->description ?? '') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="card border-0 shadow-sm crm-form-card mb-4">
            <div class="card-body">
                <div class="crm-form-section-head">
                    <h2 class="crm-form-section-title">Schedule</h2>
                    <p class="crm-form-section-copy">Set the date and keep the activity attached to the correct record.</p>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-medium">Activity Date <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="activity_date"
                            class="form-control @error('activity_date') is-invalid @enderror"
                            value="{{ old('activity_date', isset($activity->activity_date) ? $activity->activity_date->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}">
                        @error('activity_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        @if (! empty($relatedLead) || ! empty($relatedCustomer))
            <div class="crm-note-box mb-4">
                @if (! empty($relatedLead))
                    This activity will be saved under lead <strong>{{ $relatedLead->name }}</strong>.
                @else
                    This activity will be saved under customer <strong>{{ $relatedCustomer->first_name }} {{ $relatedCustomer->last_name }}</strong>.
                @endif
            </div>
        @endif

        <div class="crm-form-actions">
            <button type="submit" class="btn btn-primary flex-grow-1">{{ $isEditing ? 'Save Changes' : 'Log Activity' }}</button>
            @if (! $isEmbedded)
                <a href="{{ route('activities.index') }}" class="btn btn-outline-secondary">Cancel</a>
            @endif
        </div>
    </div>
</form>
