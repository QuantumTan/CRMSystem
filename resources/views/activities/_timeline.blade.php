{{-- resources/views/activities/_timeline.blade.php --}}
{{-- Usage: @include('activities._timeline', ['activities' => $lead->activities]) --}}

<div class="card border-0 shadow-sm rounded-4 crm-detail-card">
    <div class="card-body p-4">

        <h6 class="fw-semibold mb-4 d-flex align-items-center gap-2">
            <span class="crm-icon-chip">
                <i class="bi bi-clock-history fs-6"></i>
            </span>
            Activity History
        </h6>

        @if ($activities->isEmpty())
            <div class="text-center py-5 text-muted crm-empty-state">
                <i class="bi bi-inbox display-6 opacity-25"></i>
                <p class="mt-3 small mb-0">No activities logged yet.</p>
            </div>
        @else
            <div class="position-relative">
                {{-- Timeline container --}}
                <div class="timeline-container">
                    @foreach ($activities as $activity)
                        <div class="d-flex gap-3 pb-4 position-relative timeline-item">
                            {{-- Icon with background --}}
                            <div class="timeline-icon timeline-icon-{{ strtolower((string) $activity->activity_type) }} flex-shrink-0 d-flex align-items-center justify-content-center shadow-sm">
                                <i class="bi {{ $activity->type_icon }} fs-6"></i>
                            </div>

                            {{-- Content --}}
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                                    <div>
                                        @php
                                            $activityTypeClass = match (strtolower((string) $activity->activity_type)) {
                                                'call' => 'crm-table-status crm-table-status-info',
                                                'email' => 'crm-table-status crm-table-status-primary',
                                                'meeting' => 'crm-table-status crm-table-status-warning',
                                                'note' => 'crm-table-status crm-table-status-muted',
                                                default => 'crm-table-status crm-table-status-muted',
                                            };
                                        @endphp
                                        <span class="{{ $activityTypeClass }} px-2 py-1">
                                            {{ $activity->type_label }}
                                        </span>
                                        <span class="small text-muted ms-2">
                                            <i class="bi bi-person-circle me-1"></i>
                                            {{ $activity->user->name ?? 'Unknown' }}
                                        </span>
                                    </div>

                                    <div class="d-flex align-items-center gap-2">
                                        <small class="text-muted" data-bs-toggle="tooltip"
                                            title="{{ $activity->activity_date->format('M d, Y h:i A') }}">
                                            <i class="bi bi-calendar3 me-1"></i>
                                            {{ $activity->activity_date->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>

                                <p class="mb-2 text-dark small" style="white-space: pre-line;">
                                    {{ $activity->description }}
                                </p>

                                {{-- Linked records --}}
                                <div class="d-flex flex-wrap gap-3 mt-1">
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
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</div>

