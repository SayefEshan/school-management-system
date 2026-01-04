@extends('activitylog::layouts.master')
@section('breadcrumb')
    <span class="breadcrumb-item active">Activity Logs</span>
@endsection

@section('content')
    <x-search-card>
        <div class="col-md-3 mb-3">
            <label class="form-label">Search (Values, IP, URL)</label>
            {!! Form::text('search', request()->search, ['class' => 'form-control', 'placeholder' => 'Search...']) !!}
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Date From</label>
            {!! Form::date('date_from', request()->date_from, ['class' => 'form-control']) !!}
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Date To</label>
            {!! Form::date('date_to', request()->date_to, ['class' => 'form-control']) !!}
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Event Type</label>
            {!! Form::select(
                'event',
                ['' => 'All Events'] + array_combine($eventTypes, array_map('ucfirst', $eventTypes)),
                request()->event,
                [
                    'class' => 'form-control select',
                    'data-placeholder' => 'Select Event Type',
                ],
            ) !!}
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Entity Type</label>
            {!! Form::select(
                'auditable_type',
                ['' => 'All Entities'] +
                    array_combine(
                        $auditableTypes,
                        array_map(function ($type) {
                            return \Modules\ActivityLog\Helpers\ActivityLogHelper::getModelName($type);
                        }, $auditableTypes),
                    ),
                request()->auditable_type,
                [
                    'class' => 'form-control select',
                    'data-placeholder' => 'Select Entity Type',
                ],
            ) !!}
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">User ID</label>
            {!! Form::text('user_id', request()->user_id, ['class' => 'form-control', 'placeholder' => 'Filter by user ID']) !!}
        </div>
    </x-search-card>

    <x-table-view-pagination title="Activity Logs" :data="$audits">
        @push('actions')
            @can('Export Activity Log')
                <a href="{{ route('activity-logs.export') }}?{{ request()->getQueryString() }}"
                    class="btn btn-success w-sm swal-confirm" data-bs-toggle="tooltip"
                    title="Exports will be processed in the background and you'll be notified when ready"
                    data-text="Do you want to export activity logs? Large exports may take some time to process.">
                    <i class="fas fa-file-excel me-2"></i> Export Logs
                </a>
            @endcan
        @endpush

        <thead>
            <tr>
                <th>Id</th>
                <th>Action</th>
                <th>Description</th>
                <th>Action by</th>
                <th>IP Address</th>
                <th>Action URL</th>
                <th class="text-end">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($audits as $audit)
                @php
                    $auditMetaData = $audit->getMetadata();
                    $modifiedData = $audit->getModified();
                    $model = \Modules\ActivityLog\Helpers\ActivityLogHelper::getModelName($audit->auditable_type);
                @endphp
                <tr>
                    <td>{{ $audit->id }}</td>
                    <td>
                        <span
                            class="badge bg-{{ $audit->event == 'created' ? 'success' : ($audit->event == 'updated' ? 'primary' : 'danger') }}">
                            {{ ucfirst($audit->event) }}
                        </span>
                        <strong>{{ $model }}</strong>
                        @if ($model == 'Setting')
                            ({{ $audit->auditable->key }})
                        @endif
                    </td>
                    <td>
                        <small>
                            @foreach ($modifiedData as $key => $value)
                                <span class="d-block text-truncate" style="max-width: 300px;">
                                    <strong>{{ \Modules\ActivityLog\Helpers\ActivityLogHelper::titleCase($key) }}</strong>:
                                    @if (is_array($value))
                                        @if (isset($value['old']) && isset($value['new']))
                                            {{ is_array($value['old']) ? json_encode($value['old']) : $value['old'] }}
                                            →
                                            {{ is_array($value['new']) ? json_encode($value['new']) : $value['new'] }}
                                        @else
                                            {{ json_encode($value) }}
                                        @endif
                                    @else
                                        {{ $value }}
                                    @endif
                                </span>
                            @endforeach
                        </small>
                    </td>
                    <td>
                        @if ($audit->user_id !== null)
                            <a href="#" class="view-user" data-id="{{ $audit->user_id }}">
                                @if ($audit->user)
                                    {{ $audit->user->name }}
                                @else
                                    User #{{ $audit->user_id }}
                                @endif
                            </a>
                        @else
                            System
                        @endif
                    </td>
                    <td>
                        <a href="#" class="badge bg-primary track-ip">{{ $auditMetaData['audit_ip_address'] }}</a>
                    </td>
                    <td>
                        <x-truncated-text :text="$auditMetaData['audit_url']" :limit="20" />
                    </td>
                    <td class="text-end">
                        <x-dropdown-menu>
                            @can('View Activity Log')
                                <x-dropdown-link :url="route('activity-logs.show', $audit->id)">
                                    <i class="fas fa-eye me-2"></i> View Content History
                                </x-dropdown-link>
                            @endcan
                            @can('Delete Activity Log')
                                <form action="{{ route('activity-logs.destroy', $audit->id) }}" method="POST"
                                    onsubmit="return confirm('Are you sure you want to delete this log?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-trash me-2"></i> Delete Log
                                    </button>
                                </form>
                            @endcan
                        </x-dropdown-menu>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </x-table-view-pagination>

    <x-modal id="track-ip-modal" title="IP Information">
        <div class="row">
            <div class="col-md-12" id="ip-details">
            </div>
        </div>
    </x-modal>
@endsection

@push('top_js')
    <script src="{{ asset('assets/js/vendor/forms/selects/select2.min.js') }}"></script>
    <script src="{{ asset('assets/demo/pages/form_select2.js') }}"></script>
    <script src="{{ asset('assets/js/vendor/pickers/datepicker.min.js') }}"></script>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            // Track IP modal
            $('.track-ip').on('click', function(e) {
                e.preventDefault();
                const ip = $(this).text();
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

        });
    </script>
@endpush
