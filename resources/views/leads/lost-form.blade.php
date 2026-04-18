@extends('layouts.app')

@section('title', 'Mark Lead as Lost')

@section('content')
    @php
        $currencyCode = $systemConfiguration?->currency_code ?? 'PHP';
    @endphp

    <div class="container-fluid px-3 px-md-4 py-4">
        <div class="crm-page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <div class="crm-eyebrow mb-2">Lead Status</div>
                <h1 class="h3 mb-1 fw-semibold">Mark Lead as Lost</h1>
                <p class="text-muted mb-0 small">Document why this opportunity was lost and keep the pipeline history clear.</p>
            </div>
            <a href="{{ route('leads.show', $lead) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Lead
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger crm-alert mb-4">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('leads.mark-lost', $lead) }}" method="POST" class="crm-form-shell row g-4">
            @csrf

            <div class="col-12 col-xl-8">
                <div class="card border-0 shadow-sm crm-form-card mb-4">
                    <div class="card-body">
                        <div class="crm-form-section-head">
                            <h2 class="crm-form-section-title">Loss Details</h2>
                            <p class="crm-form-section-copy">Choose the closest category and explain what caused the lead to be lost.</p>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-medium">Reason Category <span class="text-danger">*</span></label>
                                <div class="crm-choice-grid">
                                    @foreach ($lostCategories as $value => $label)
                                        <div>
                                            <input type="radio" class="btn-check" name="lost_category" id="cat_{{ $value }}" value="{{ $value }}" {{ old('lost_category') === $value ? 'checked' : '' }} required>
                                            <label class="btn btn-outline-danger" for="cat_{{ $value }}">{{ $label }}</label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('lost_category')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="lost_reason" class="form-label fw-medium">Additional Details <span class="text-danger">*</span></label>
                                <textarea id="lost_reason" name="lost_reason" rows="5" class="form-control @error('lost_reason') is-invalid @enderror" placeholder="Describe why this lead was lost..." minlength="3" maxlength="500" required>{{ old('lost_reason') }}</textarea>
                                @error('lost_reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="text-end mt-1">
                                    <small class="text-muted" id="charCount">{{ strlen(old('lost_reason', '')) }} / 500</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card border-0 shadow-sm crm-detail-card mb-4">
                    <div class="card-body">
                        <div class="crm-form-section-head">
                            <h2 class="crm-form-section-title">Lead Snapshot</h2>
                            <p class="crm-form-section-copy">Reference the key details before you finalize the lost status.</p>
                        </div>

                        <div class="crm-detail-grid">
                            <div class="crm-detail-item">
                                <span class="crm-detail-label">Lead</span>
                                <div class="crm-detail-value">{{ $lead->name }}</div>
                            </div>
                            <div class="crm-detail-item">
                                <span class="crm-detail-label">Lead ID</span>
                                <div class="crm-detail-value">{{ $lead->lead_id }}</div>
                            </div>
                            <div class="crm-detail-item">
                                <span class="crm-detail-label">Contact</span>
                                <div class="crm-detail-value">{{ $lead->email ?: ($lead->phone ?: 'N/A') }}</div>
                            </div>
                            <div class="crm-detail-item">
                                <span class="crm-detail-label">Expected Value</span>
                                <div class="crm-detail-value">
                                    {{ $lead->expected_value ? $currencyCode.' '.number_format($lead->expected_value, 2) : 'N/A' }}
                                </div>
                            </div>
                            <div class="crm-detail-item">
                                <span class="crm-detail-label">Assigned User</span>
                                <div class="crm-detail-value">{{ $lead->assignedUser?->name ?: 'Unassigned' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="crm-form-actions">
                    <button type="submit" class="btn btn-danger flex-grow-1">Confirm Lost</button>
                    <a href="{{ route('leads.show', $lead) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>

    <script>
        const textarea = document.getElementById('lost_reason');
        const charCount = document.getElementById('charCount');

        if (textarea && charCount) {
            textarea.addEventListener('input', function() {
                charCount.textContent = `${this.value.length} / 500`;
            });
        }
    </script>
@endsection
