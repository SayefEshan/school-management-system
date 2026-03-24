@extends('notification::layouts.master')

@section('breadcrumb')
    <span class="breadcrumb-item active">Notifications</span>
@endsection

@section('content')
    <x-search-card>
        <div class="col-md-4 mb-3">
            <label class="form-label">Notification Type</label>
            {!! Form::select(
                'type',
                [
                    '' => 'All Types',
                    'info' => 'Information',
                    'warning' => 'Warning',
                    'success' => 'Success',
                    'error' => 'Error',
                ],
                request()->type,
                [
                    'class' => 'form-control select',
                    'data-placeholder' => 'Select Type',
                ],
            ) !!}
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Status</label>
            {!! Form::select(
                'read',
                [
                    '' => 'All Notifications',
                    'true' => 'Read',
                    'false' => 'Unread',
                ],
                request()->read,
                [
                    'class' => 'form-control select',
                    'data-placeholder' => 'Select Status',
                ],
            ) !!}
        </div>
    </x-search-card>

    <x-table-view-pagination title="Notifications" :data="$notifications">
        @push('actions')
            <form action="{{ route('notification.mark-all-as-read') }}" method="POST" class="d-inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-primary w-sm swal-confirm"
                    data-text="Do you want to mark all notifications as read?">
                    <i class="fas fa-check-double me-2"></i> Mark All as Read
                </button>
            </form>
        @endpush

        <tr>
            <th width="5%">ID</th>
            <th width="15%">Type</th>
            <th width="40%">Message</th>
            <th width="15%">Status</th>
            <th width="15%">Date</th>
            <th width="10%" class="text-center">Actions</th>
        </tr>

        @foreach($notifications as $notification)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $notification->type }}</td>
                <td>
                    @if (is_array($notification->data) && isset($notification->data['message']))
                        {{ Str::limit($notification->data['message'], 100) }}
                    @else
                        {{ Str::limit(json_encode($notification->data), 100) }}
                    @endif
                </td>
                <td>
                    @if ($notification->read_at)
                        <span class="badge bg-success">Read</span>
                    @else
                        <span class="badge bg-warning">Unread</span>
                    @endif
                </td>
                <td>{{ $notification->created_at->diffForHumans() }}</td>
                <td class="text-center">
                    <div class="d-inline-flex">
                        <a href="{{ route('notification.show', $notification->id) }}" class="btn btn-sm btn-info me-1">
                            <i class="fas fa-eye"></i>
                        </a>

                        @if ($notification->read_at)
                            <form action="{{ route('notification.mark-as-unread', $notification->id) }}" method="POST"
                                class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-warning me-1">
                                    <i class="fas fa-envelope"></i>
                                </button>
                            </form>
                        @else
                            <form action="{{ route('notification.mark-as-read', $notification->id) }}" method="POST"
                                class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-success me-1">
                                    <i class="fas fa-envelope-open"></i>
                                </button>
                            </form>
                        @endif

                        <form action="{{ route('notification.destroy', $notification->id) }}" method="POST"
                            class="d-inline delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-table-view-pagination>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('.delete-form').on('submit', function(e) {
                e.preventDefault();

                if (confirm('Are you sure you want to delete this notification?')) {
                    this.submit();
                }
            });
        });
    </script>
@endpush
