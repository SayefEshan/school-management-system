@extends('notification::layouts.master')

@section('breadcrumb')
    <a href="{{ route('notification.index') }}" class="breadcrumb-item">Notifications</a>
    <span class="breadcrumb-item active">View Notification</span>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Notification Details</h5>
            <div>
                <a href="{{ route('notification.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to List
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-4">
                        <h6 class="fw-bold">ID</h6>
                        <p>{{ $notification->id }}</p>
                    </div>
                    <div class="mb-4">
                        <h6 class="fw-bold">Type</h6>
                        <p>{{ $notification->type }}</p>
                    </div>
                    <div class="mb-4">
                        <h6 class="fw-bold">Status</h6>
                        <p>
                            @if ($notification->read_at)
                                <span class="badge bg-success">Read</span>
                            @else
                                <span class="badge bg-warning">Unread</span>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-4">
                        <h6 class="fw-bold">Created</h6>
                        <p>{{ $notification->created_at->format('Y-m-d H:i:s') }}</p>
                    </div>
                    <div class="mb-4">
                        <h6 class="fw-bold">Read At</h6>
                        <p>{{ $notification->read_at ? $notification->read_at->format('Y-m-d H:i:s') : 'Not read yet' }}</p>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="fw-bold">Message</h6>
                    @if (is_array($notification->data) && isset($notification->data['message']))
                        <p>{{ $notification->data['message'] }}</p>
                    @endif
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <h6 class="fw-bold">Data</h6>
                    <pre class="border rounded p-3 bg-light">{{ json_encode($notification->data, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>

            <div class="mt-4">
                <div class="d-flex">
                    @if ($notification->read_at)
                        <form action="{{ route('notification.mark-as-unread', $notification->id) }}" method="POST"
                            class="me-2">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-envelope me-1"></i> Mark as Unread
                            </button>
                        </form>
                    @else
                        <form action="{{ route('notification.mark-as-read', $notification->id) }}" method="POST"
                            class="me-2">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-envelope-open me-1"></i> Mark as Read
                            </button>
                        </form>
                    @endif

                    <form action="{{ route('notification.destroy', $notification->id) }}" method="POST"
                        class="delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
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
