{{-- resources/views/activities/edit.blade.php --}}

@extends('layouts.app')

@section('title', 'Edit Activity')

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">

    <div class="mb-4">
        <h4 class="fw-semibold mb-0">Edit Activity</h4>
        <p class="text-muted small mb-0">Update the activity details below.</p>
    </div>

    <div class="card border-0 shadow-sm" style="max-width: 640px;">
        <div class="card-body p-4">

            @if ($errors->any())
                <div class="alert alert-danger py-2 small">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('activities.update', $activity) }}" method="POST">
                @csrf
                @method('PATCH')

                {{-- Type --}}
                <div class="mb-3">
                    <label class="form-label small fw-medium">Activity Type</label>
                    <div class="d-flex gap-2 flex-wrap">
                        @foreach ([
                            'call'    => ['bi-telephone-fill',     'success', 'Call'],
                            'email'   => ['bi-envelope-fill',       'primary', 'Email'],
                            'meeting' => ['bi-calendar-event-fill', 'warning', 'Meeting'],
                            'note'    => ['bi-sticky-fill',         'purple',  'Note'],
                        ] as $type => [$icon, $color, $label])
                            <div>
                                <input type="radio" class="btn-check" name="activity_type"
                                       id="edit_type_{{ $type }}" value="{{ $type }}"
                                       {{ old('activity_type', $activity->activity_type) === $type ? 'checked' : '' }}>
                                <label class="btn btn-outline-{{ $color }} btn-sm d-flex align-items-center gap-1 px-3"
                                       for="edit_type_{{ $type }}">
                                    <i class="bi {{ $icon }}"></i> {{ $label }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Description --}}
                <div class="mb-3">
                    <label class="form-label small fw-medium">Description</label>
                    <textarea name="description" rows="4"
                              class="form-control form-control-sm @error('description') is-invalid @enderror"
                              placeholder="Describe the activity...">{{ old('description', $activity->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Date --}}
                <div class="mb-4">
                    <label class="form-label small fw-medium">Activity Date</label>
                    <input type="datetime-local" name="activity_date"
                           class="form-control form-control-sm @error('activity_date') is-invalid @enderror"
                           style="max-width: 220px;"
                           value="{{ old('activity_date', $activity->activity_date->format('Y-m-d\TH:i')) }}">
                    @error('activity_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-dark btn-sm px-4">Save Changes</button>
                    <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .btn-outline-purple { color: #8b5cf6; border-color: #8b5cf6; }
    .btn-outline-purple:hover,
    .btn-check:checked + .btn-outline-purple {
        background-color: #8b5cf6; border-color: #8b5cf6; color: white;
    }
</style>
@endsection