{{-- resources/views/activities/_form.blade.php --}}
{{-- Usage: @include('activities._form', ['lead' => $lead, 'customer' => null]) --}}

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">

        <h6 class="fw-semibold mb-3">
            <i class="bi bi-plus-circle me-2 text-primary"></i>Log Activity
        </h6>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show py-2 small" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('activities.store') }}" method="POST">
            @csrf

            @if (!empty($lead))
                <input type="hidden" name="lead_id" value="{{ $lead->id }}">
            @endif
            @if (!empty($customer))
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">
            @endif

            <div class="d-flex gap-2 mb-3 flex-wrap">
                @foreach ([
                    'call'    => ['bi-telephone-fill',     'success', 'Call'],
                    'email'   => ['bi-envelope-fill',       'primary', 'Email'],
                    'meeting' => ['bi-calendar-event-fill', 'warning', 'Meeting'],
                    'note'    => ['bi-sticky-fill',         'purple',  'Note'],
                ] as $type => [$icon, $color, $label])
                    <div>
                        <input type="radio" class="btn-check" name="activity_type"
                               id="type_{{ $type }}" value="{{ $type }}"
                               {{ old('activity_type', 'note') === $type ? 'checked' : '' }}>
                        <label class="btn btn-outline-{{ $color }} btn-sm d-flex align-items-center gap-1 px-3"
                               for="type_{{ $type }}">
                            <i class="bi {{ $icon }}"></i> {{ $label }}
                        </label>
                    </div>
                @endforeach
            </div>
            @error('activity_type')
                <div class="text-danger small mb-2">{{ $message }}</div>
            @enderror

            <div class="mb-3">
                <textarea name="description" rows="3"
                          class="form-control form-control-sm @error('description') is-invalid @enderror"
                          placeholder="Describe the activity...">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex align-items-center gap-3">
                <div style="max-width: 220px;">
                    <input type="datetime-local" name="activity_date"
                           class="form-control form-control-sm @error('activity_date') is-invalid @enderror"
                           value="{{ old('activity_date', now()->format('Y-m-d\TH:i')) }}">
                    @error('activity_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-dark btn-sm px-4">
                    <i class="bi bi-check2 me-1"></i> Log Activity
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .btn-outline-purple { color: #8b5cf6; border-color: #8b5cf6; }
    .btn-outline-purple:hover,
    .btn-check:checked + .btn-outline-purple {
        background-color: #8b5cf6; border-color: #8b5cf6; color: white;
    }
</style>