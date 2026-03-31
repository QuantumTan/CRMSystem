@extends('layouts.app')

@section('title', 'Mark Lead as Lost')

@section('content')
    <div class="container py-5" style="max-width: 640px;">

        {{-- Back Button --}}
        <div class="mb-4">
            <a href="{{ route('leads.show', $lead) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Lead
            </a>
        </div>

        {{-- Header --}}
        <div class="mb-4">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                    style="width:48px;height:48px;background:#fff1f1;flex-shrink:0;">
                    <i class="bi bi-x-circle-fill text-danger" style="font-size:1.5rem;"></i>
                </div>
                <div>
                    <h4 class="mb-0 fw-semibold">Mark Lead as Lost</h4>
                    <p class="text-muted mb-0 small">
                        Lead: <strong>{{ $lead->name }}</strong>
                        @if ($lead->lead_id)
                            <span class="ms-1 text-secondary">({{ $lead->lead_id }})</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- Form Card --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">

                @if ($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('leads.mark-lost', $lead) }}" method="POST">
                    @csrf

                    {{-- Lost Category --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            Reason Category <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($lostCategories as $value => $label)
                                <div>
                                    <input type="radio" class="btn-check" name="lost_category"
                                        id="cat_{{ $value }}" value="{{ $value }}"
                                        {{ old('lost_category') === $value ? 'checked' : '' }} required>
                                    <label class="btn btn-sm btn-outline-danger" for="cat_{{ $value }}">
                                        {{ $label }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('lost_category')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Lost Reason --}}
                    <div class="mb-4">
                        <label for="lost_reason" class="form-label fw-semibold">
                            Additional Details <span class="text-danger">*</span>
                        </label>
                        <textarea id="lost_reason" name="lost_reason" rows="4"
                            class="form-control @error('lost_reason') is-invalid @enderror" placeholder="Describe why this lead was lost..."
                            minlength="3" maxlength="500" required>{{ old('lost_reason') }}</textarea>
                        @error('lost_reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="text-end mt-1">
                            <small class="text-muted" id="charCount">0 / 500</small>
                        </div>
                    </div>

                    {{-- Lead Summary (read-only info) --}}
                    <div class="rounded p-3 mb-4" style="background:#f8f9fa;border:1px solid #e9ecef;">
                        <p class="text-muted small mb-2 fw-semibold text-uppercase" style="letter-spacing:.05em;">Lead
                            Summary</p>
                        <div class="row g-2 small">
                            @if ($lead->email)
                                <div class="col-sm-6 text-muted">
                                    <i class="bi bi-envelope me-1"></i>{{ $lead->email }}
                                </div>
                            @endif
                            @if ($lead->phone)
                                <div class="col-sm-6 text-muted">
                                    <i class="bi bi-telephone me-1"></i>{{ $lead->phone }}
                                </div>
                            @endif
                            @if ($lead->expected_value)
                                <div class="col-sm-6 text-muted">
                                    <i class="bi bi-cash-coin me-1"></i>PHP {{ number_format($lead->expected_value, 2) }}
                                </div>
                            @endif
                            @if ($lead->assignedUser)
                                <div class="col-sm-6 text-muted">
                                    <i class="bi bi-person me-1"></i>{{ $lead->assignedUser->name }}
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('leads.show', $lead) }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="bi bi-x-circle me-1"></i> Confirm Lost
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script>
        const textarea = document.getElementById('lost_reason');
        const charCount = document.getElementById('charCount');

        textarea.addEventListener('input', function() {
            charCount.textContent = `${this.value.length} / 500`;
        });
    </script>
@endsection
