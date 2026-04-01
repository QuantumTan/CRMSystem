{{-- resources/views/activities/_timeline.blade.php --}}
{{-- Usage: @include('activities._timeline', ['activities' => $lead->activities]) --}}

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">

        <h6 class="fw-semibold mb-4">
            <i class="bi bi-clock-history me-2 text-primary"></i>Activity History
        </h6>

        @if ($activities->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox display-6 opacity-25"></i>
                <p class="mt-3 small mb-0">No activities logged yet.</p>
            </div>
        @else
            <div class="activity-timeline">
                @foreach ($activities as $activity)
                    <div class="timeline-item d-flex gap-3 pb-4">

                        <div class="timeline-icon flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle text-white"
                             style="width: 36px; height: 36px; background-color: {{ $activity->type_color }}; margin-top: 2px;">
                            <i class="bi {{ $activity->type_icon }}" style="font-size: 0.8rem;"></i>
                        </div>

                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-1">
                                <div>
                                    <span class="badge rounded-pill fw-medium px-2 py-1"
                                          style="background-color: {{ $activity->type_color }}; color: white; font-size: 0.75rem;">
                                        {{ $activity->type_label }}
                                    </span>
                                    <span class="small text-muted ms-2">
                                        by {{ $activity->user->name ?? 'Unknown' }}
                                    </span>
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    <small class="text-muted"
                                           title="{{ $activity->activity_date->format('M d, Y h:i A') }}">
                                        {{ $activity->activity_date->diffForHumans() }}
                                    </small>

                                    <a href="{{ route('activities.edit', $activity) }}"
                                       class="btn btn-sm btn-outline-secondary py-0 px-2" title="Edit">
                                        <i class="bi bi-pencil" style="font-size: 0.7rem;"></i>
                                    </a>

                                    <form action="{{ route('activities.destroy', $activity) }}" method="POST"
                                          onsubmit="return confirm('Delete this activity?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-sm btn-outline-danger py-0 px-2" title="Delete">
                                            <i class="bi bi-trash" style="font-size: 0.7rem;"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <p class="mb-1 small text-dark" style="white-space: pre-line;">{{ $activity->description }}</p>

                            @if ($activity->customer)
                                <small class="text-muted">
                                    <i class="bi bi-person me-1"></i>
                                    <a href="{{ route('customers.show', $activity->customer) }}"
                                       class="text-muted text-decoration-none">
                                        {{ $activity->customer->first_name }} {{ $activity->customer->last_name }}
                                    </a>
                                </small>
                            @endif
                            @if ($activity->lead)
                                <small class="text-muted">
                                    <i class="bi bi-funnel me-1"></i>
                                    <a href="{{ route('leads.show', $activity->lead) }}"
                                       class="text-muted text-decoration-none">
                                        {{ $activity->lead->name }}
                                    </a>
                                </small>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</div>

<style>
    .activity-timeline { position: relative; }
    .activity-timeline::before {
        content: '';
        position: absolute;
        left: 17px; top: 0; bottom: 0;
        width: 2px;
        background: #f1f3f5;
    }
    .timeline-item:last-child { padding-bottom: 0 !important; }
</style>