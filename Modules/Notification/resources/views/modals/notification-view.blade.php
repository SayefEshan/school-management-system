<div class="modal-header">
    <h5 class="modal-title">Notification Details</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <div class="mb-3">
        <h6 class="fw-bold">Type</h6>
        <p>{{ $notification->type }}</p>
    </div>

    <div class="mb-3">
        <h6 class="fw-bold">Status</h6>
        <p>
            @if ($notification->read_at)
                <span class="badge bg-success">Read</span>
            @else
                <span class="badge bg-warning">Unread</span>
            @endif
        </p>
    </div>

    <div class="mb-3">
        <h6 class="fw-bold">Date</h6>
        <p>{{ $notification->created_at->format('Y-m-d H:i:s') }} ({{ $notification->created_at->diffForHumans() }})</p>
    </div>

    @if (isset($notification->data['message']))
        <div class="mb-3">
            <h6 class="fw-bold">Message</h6>
            <p>{{ $notification->data['message'] }}</p>
        </div>
    @endif

    <div class="mb-3">
        <h6 class="fw-bold">Data</h6>
        <pre class="border rounded p-3 bg-light" style="max-height: 200px; overflow-y: auto;">{{ json_encode($notification->data, JSON_PRETTY_PRINT) }}</pre>
    </div>
</div>
<div class="modal-footer">
    <div class="d-flex w-100 justify-content-between">
        <div>
            @if ($notification->read_at)
                <form action="{{ route('notification.mark-as-unread', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-warning btn-sm">
                        <i class="fas fa-envelope me-1"></i> Mark as Unread
                    </button>
                </form>
            @else
                <form action="{{ route('notification.mark-as-read', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fas fa-envelope-open me-1"></i> Mark as Read
                    </button>
                </form>
            @endif
        </div>
        <div>
            <form action="{{ route('notification.destroy', $notification->id) }}" method="POST"
                class="d-inline delete-form">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="fas fa-trash me-1"></i> Delete
                </button>
            </form>
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        </div>
    </div>
</div>

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
