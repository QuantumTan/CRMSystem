@php
    $isEditing = isset($followUp);
    $submitLabel = $isEditing ? 'Update Follow-up' : 'Create Follow-up';
@endphp

@if ($errors->any())
    <div class="alert alert-danger py-2 small">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card border-0 shadow-sm" style="max-width: 760px;">
    <div class="card-body p-4">
        <form action="{{ $isEditing ? route('follow-ups.update', $followUp) : route('follow-ups.store') }}" method="POST">
            @csrf
            @if ($isEditing)
                @method('PUT')
            @endif

            <div class="row g-3">
                <div class="col-12 col-md-8">
                    <label for="title" class="form-label small fw-medium">Title</label>
                    <input id="title" type="text" name="title"
                        class="form-control form-control-sm @error('title') is-invalid @enderror"
                        value="{{ old('title', $followUp->title ?? '') }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-4">
                    <label for="status" class="form-label small fw-medium">Status</label>
                    <select id="status" name="status" class="form-select form-select-sm @error('status') is-invalid @enderror"
                        required>
                        @foreach (['pending', 'completed'] as $status)
                            <option value="{{ $status }}" @selected(old('status', $followUp->status ?? 'pending') === $status)>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="customer_id" class="form-label small fw-medium">Customer (optional)</label>
                    <select id="customer_id" name="customer_id"
                        class="form-select form-select-sm @error('customer_id') is-invalid @enderror">
                        <option value="">Select customer</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}" @selected((string) old('customer_id', $followUp->customer_id ?? '') === (string) $customer->id)>
                                {{ $customer->first_name }} {{ $customer->last_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('customer_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="lead_id" class="form-label small fw-medium">Lead (optional)</label>
                    <select id="lead_id" name="lead_id" class="form-select form-select-sm @error('lead_id') is-invalid @enderror">
                        <option value="">Select lead</option>
                        @foreach ($leads as $lead)
                            <option value="{{ $lead->id }}" @selected((string) old('lead_id', $followUp->lead_id ?? '') === (string) $lead->id)>
                                {{ $lead->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('lead_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="user_id" class="form-label small fw-medium">Assigned User</label>
                    <select id="user_id" name="user_id" class="form-select form-select-sm @error('user_id') is-invalid @enderror">
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

                <div class="col-12 col-md-6">
                    <label for="due_date" class="form-label small fw-medium">Due Date</label>
                    <input id="due_date" type="date" name="due_date"
                        class="form-control form-control-sm @error('due_date') is-invalid @enderror"
                        value="{{ old('due_date', isset($followUp->due_date) ? $followUp->due_date->format('Y-m-d') : now()->toDateString()) }}"
                        required>
                    @error('due_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="description" class="form-label small fw-medium">Description</label>
                    <textarea id="description" name="description" rows="4"
                        class="form-control form-control-sm @error('description') is-invalid @enderror"
                        placeholder="Add notes about this follow-up...">{{ old('description', $followUp->description ?? '') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-dark btn-sm px-4">{{ $submitLabel }}</button>
                <a href="{{ route('follow-ups.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
            </div>
        </form>
    </div>
</div>
