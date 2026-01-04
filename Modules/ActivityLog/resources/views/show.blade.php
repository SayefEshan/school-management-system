@extends('activitylog::layouts.master')
@section('breadcrumb')
    <span class="breadcrumb-item active">Content History</span>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Content History</h5>
            <a href="{{ route('activity-logs.index') }}" class="btn btn-outline-primary w-sm">
                <i class="fas fa-arrow-left me-2"></i> Back to Activity Logs
            </a>
        </div>
    </div>

    <div class="timeline timeline-center">
        <div class="timeline-container">
            @forelse ($audits as $audit)
                <div class="timeline-row {{ $loop->even ? 'timeline-row-end' : 'timeline-row-start' }}">
                    <div class="timeline-icon">
                        @if ($audit->event == 'created')
                            <div class="bg-success text-white">
                                <i class="fas fa-plus"></i>
                            </div>
                        @elseif($audit->event == 'updated')
                            <div class="bg-primary text-white">
                                <i class="fas fa-edit"></i>
                            </div>
                        @elseif($audit->event == 'deleted')
                            <div class="bg-danger text-white">
                                <i class="fas fa-trash"></i>
                            </div>
                        @elseif($audit->event == 'restored')
                            <div class="bg-warning text-white">
                                <i class="fas fa-undo"></i>
                            </div>
                        @endif
                    </div>

                    <div class="timeline-time">
                        @php
                            $model = \Modules\ActivityLog\Helpers\ActivityLogHelper::getModelName(
                                $audit->auditable_type,
                            );
                        @endphp
                        <div class="timeline-action">
                            <a href="#" class="view-user user-link" data-id="{{ $audit->user_id }}">
                                {{ $audit->user ? $audit->user->name : 'System' }}
                            </a>
                            <span class="action-text">{{ $audit->event }}</span>
                            <span class="model-name">{{ $model }}</span>
                            @if ($model == 'Setting')
                                <span class="auditable-key">{{ $audit->auditable->key }}</span>
                            @endif
                        </div>

                        @if ($audit->url)
                            <div class="timeline-url">
                                <i class="fas fa-link me-1"></i>
                                <a href="{{ $audit->url }}" class="url-link" target="_blank" rel="noopener">
                                    {{ Str::limit($audit->url, 50) }}
                                </a>
                            </div>
                        @endif

                        @if ($audit->ip_address)
                            <div class="timeline-meta">
                                <i class="fas fa-globe me-1"></i>
                                <a href="#" class="track-ip ip-link" data-ip="{{ $audit->ip_address }}">
                                    {{ $audit->ip_address }}
                                </a>
                            </div>
                        @endif

                        <div class="timeline-timestamp">
                            <i class="fas fa-clock me-1"></i>
                            <span class="text-muted">{{ $audit->created_at->diffForHumans() }}</span>
                            <span class="text-muted ms-2">•</span>
                            <span class="text-muted">{{ $audit->created_at->format('M d, Y H:i') }}</span>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <span
                                    class="badge bg-{{ $audit->event == 'created' ? 'success' : ($audit->event == 'updated' ? 'primary' : ($audit->event == 'deleted' ? 'danger' : 'warning')) }} me-2">
                                    {{ ucfirst($audit->event) }}
                                </span>
                                Change Details
                            </h6>
                        </div>

                        <div class="card-body">
                            @if ($audit->event === 'updated')
                                <div class="changes-list">
                                    @foreach ($audit->old_values as $attribute => $oldValue)
                                        <div class="change-item mb-3">
                                            <div
                                                class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-2">
                                                <span
                                                    class="field-label">{{ \Modules\ActivityLog\Helpers\ActivityLogHelper::titleCase($attribute) }}</span>
                                                <span class="old-value flex-shrink-0">{{ is_array($oldValue) ? json_encode($oldValue) : ($oldValue ?? 'N/A') }}</span>
                                                <i class="fas fa-arrow-right text-muted d-none d-md-inline"></i>
                                                <i class="fas fa-arrow-down text-muted d-md-none"></i>
                                                <span
                                                    class="new-value flex-shrink-0">{{ is_array($audit->new_values[$attribute] ?? null) ? json_encode($audit->new_values[$attribute]) : ($audit->new_values[$attribute] ?? 'N/A') }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif($audit->event === 'created')
                                <div class="changes-list">
                                    @foreach ($audit->new_values as $attribute => $newValue)
                                        <div class="change-item mb-3">
                                            <div
                                                class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-2">
                                                <span
                                                    class="field-label">{{ \Modules\ActivityLog\Helpers\ActivityLogHelper::titleCase($attribute) }}</span>
                                                <span
                                                    class="new-value flex-shrink-0">{{ is_array($newValue) ? json_encode($newValue) : $newValue }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif($audit->event === 'deleted')
                                <div class="changes-list">
                                    @foreach ($audit->old_values as $attribute => $oldValue)
                                        <div class="change-item mb-3">
                                            <div
                                                class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-2">
                                                <span
                                                    class="field-label">{{ \Modules\ActivityLog\Helpers\ActivityLogHelper::titleCase($attribute) }}</span>
                                                <span
                                                    class="old-value flex-shrink-0">{{ is_array($oldValue) ? json_encode($oldValue) : $oldValue }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif($audit->event === 'restored')
                                <div class="changes-list">
                                    @foreach ($audit->new_values as $attribute => $newValue)
                                        <div class="change-item mb-3">
                                            <div
                                                class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-2">
                                                <span
                                                    class="field-label">{{ \Modules\ActivityLog\Helpers\ActivityLogHelper::titleCase($attribute) }}</span>
                                                <span
                                                    class="new-value flex-shrink-0">{{ is_array($newValue) ? json_encode($newValue) : $newValue }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="timeline-row timeline-row-full">
                    <div class="timeline-icon">
                        <div class="bg-secondary text-white">
                            <i class="fas fa-history"></i>
                        </div>
                    </div>
                    <div class="timeline-time">
                        No audit history available
                        <div class="text-muted">There are no audit records for this item.</div>
                    </div>
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No History Available</h5>
                            <p class="text-muted">There are no audit records for this item.</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <x-modal id="track-ip-modal" title="IP Information">
        <div class="row">
            <div class="col-md-12" id="ip-details">
            </div>
        </div>
    </x-modal>
@endsection

@push('top_js')
    <script src="{{ asset('assets/demo/pages/timelines.js') }}"></script>
    <script src="{{ asset('assets/demo/charts/echarts/bars/tornado_negative_stack.js') }}"></script>
@endpush

@push('styles')
    <style>
        /* Activity History - Theme Timeline Integration */
        .card-header h5 {
            color: #1a1a1a;
            font-weight: 600;
        }

        /* Timeline Icon Styling */
        .timeline-icon .bg-success,
        .timeline-icon .bg-primary,
        .timeline-icon .bg-danger,
        .timeline-icon .bg-warning,
        .timeline-icon .bg-secondary {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 600;
        }

        /* Change Values Styling - Simplified */
        .old-value {
            background: rgba(239, 68, 68, 0.05);
            color: #dc2626;
            padding: 0.2rem 0.4rem;
            border-radius: 3px;
            font-size: 0.8rem;
            font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, 'Courier New', monospace;
            border: none;
            word-break: break-word;
            overflow-wrap: break-word;
            max-width: 100%;
            display: inline-block;
            font-weight: 500;
        }

        .new-value {
            background: rgba(34, 197, 94, 0.05);
            color: #16a34a;
            padding: 0.2rem 0.4rem;
            border-radius: 3px;
            font-size: 0.8rem;
            font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, 'Courier New', monospace;
            border: none;
            word-break: break-word;
            overflow-wrap: break-word;
            max-width: 100%;
            display: inline-block;
            font-weight: 500;
        }

        /* Card Body Responsive */
        .card-body {
            overflow-x: auto;
            word-wrap: break-word;
        }

        /* Change Item Responsive */
        .change-item {
            min-width: 0;
        }

        .change-item .row {
            margin: 0;
        }

        .change-item .col-12 {
            padding: 0;
        }

        /* Field Label Styling - Simplified */
        .field-label {
            color: #6c757d;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 0.25rem;
        }

        /* Auditable Key Styling */
        .auditable-key {
            background: rgba(108, 117, 125, 0.1);
            color: #495057;
            padding: 0.2rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
            font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, 'Courier New', monospace;
            border: 1px solid rgba(108, 117, 125, 0.2);
            margin-left: 0.5rem;
            display: inline-block;
        }

        /* Timeline Time Enhancements */
        .timeline-action {
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }

        .user-link {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .user-link:hover {
            color: #2563eb;
            text-decoration: underline;
        }

        .action-text {
            color: #6c757d;
            font-weight: 500;
            margin: 0 0.25rem;
        }

        .model-name {
            color: #495057;
            font-weight: 500;
        }

        .timeline-url {
            margin-bottom: 0.25rem;
            font-size: 0.85rem;
        }

        .url-link {
            color: #6c757d;
            text-decoration: none;
            font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, 'Courier New', monospace;
            font-size: 0.8rem;
        }

        .url-link:hover {
            color: #3b82f6;
            text-decoration: underline;
        }

        .timeline-meta {
            margin-bottom: 0.25rem;
            font-size: 0.85rem;
        }

        .ip-link {
            color: #6c757d;
            text-decoration: none;
            font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, 'Courier New', monospace;
            font-size: 0.8rem;
        }

        .ip-link:hover {
            color: #3b82f6;
            text-decoration: underline;
        }

        .timeline-timestamp {
            font-size: 0.8rem;
            color: #6c757d;
        }

        /* Track IP Link */
        .track-ip {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
        }

        .track-ip:hover {
            color: #2563eb;
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 768px) {

            .timeline-icon .bg-success,
            .timeline-icon .bg-primary,
            .timeline-icon .bg-danger,
            .timeline-icon .bg-warning,
            .timeline-icon .bg-secondary {
                width: 32px;
                height: 32px;
                font-size: 14px;
            }

            .change-item .row {
                flex-direction: column;
            }

            .change-item .col-12 {
                width: 100%;
                margin-bottom: 0.5rem;
            }

            .field-label {
                text-align: center;
                margin-bottom: 0.5rem;
            }

            .old-value,
            .new-value {
                font-size: 0.75rem;
                padding: 0.2rem 0.4rem;
            }

            .auditable-key {
                font-size: 0.7rem;
                padding: 0.15rem 0.4rem;
                margin-left: 0.25rem;
            }

            .timeline-action {
                margin-bottom: 0.75rem;
            }

            .timeline-url,
            .timeline-meta {
                margin-bottom: 0.5rem;
            }

            .timeline-timestamp {
                font-size: 0.75rem;
            }
        }

        @media (max-width: 576px) {
            .card-body {
                padding: 0.75rem;
            }

            .change-item {
                margin-bottom: 1rem;
            }

            .old-value,
            .new-value {
                font-size: 0.7rem;
                padding: 0.15rem 0.3rem;
                line-height: 1.3;
            }

            .auditable-key {
                font-size: 0.65rem;
                padding: 0.1rem 0.3rem;
                margin-left: 0.2rem;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            // Track IP modal
            $('.track-ip, .ip-link').on('click', function(e) {
                e.preventDefault();
                const ip = $(this).data('ip');
                $('#track-ip-modal').modal('show');
                $('#ip-details').html(
                    '<div class="text-center"><div class="spinner-border" role="status"></div><p>Loading IP information...</p></div>'
                );

                $.ajax({
                    url: "{{ route('track-ip') }}",
                    type: 'GET',
                    data: {
                        ip: ip
                    },
                    success: function(data) {
                        $('#ip-details').html(data);
                    },
                    error: function() {
                        $('#ip-details').html(
                            '<div class="alert alert-danger">Failed to load IP information.</div>'
                        );
                    }
                });
            });

            // User profile viewing
            $('.view-user, .user-link').on('click', function(e) {
                e.preventDefault();
                const userId = $(this).data('id');

                if (userId && userId !== 'null' && userId !== '') {
                    // You can implement user profile modal or redirect here
                    // For now, show user ID and suggest implementation
                    alert('User ID: ' + userId +
                        '\n\nThis would typically open a user profile modal or redirect to user details page.\n\nYou can implement this by:\n1. Creating a user profile modal\n2. Redirecting to user edit page\n3. Making an AJAX call to fetch user details'
                    );
                } else {
                    alert('This action was performed by the system.');
                }
            });

            // URL link handling - let default behavior handle opening URLs
            $('.url-link').on('click', function(e) {
                // The target="_blank" and rel="noopener" attributes are already set
                // This ensures the link opens in a new tab safely
            });
        });
    </script>
@endpush
