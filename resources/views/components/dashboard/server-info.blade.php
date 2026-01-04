<!-- Basic sparklines -->
<div class="card">
    <div class="card-body">
        <div class="d-flex">
            <h4 class="mb-0 cpu-usage">
                0.00%
            </h4>
        </div>

        <div>
            Server load Avg. in last minute
            <div class="text-muted fs-sm background-worker-status">
                Queue connection is set to {{ config('queue.default') }}
                {{ $workerCount > 0 ? 'with ' . $workerCount . ' worker' . ($workerCount > 1 ? 's.' : '.') : 'without any worker.' }}
                Scheduler is {{ $schedulerRunning ? 'running.' : 'not running.' }}
            </div>
        </div>
    </div>

    <div class="rounded-bottom overflow-hidden" id="sparklines_basic"></div>
</div>
<!-- /basic sparklines -->

@push('scripts')
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                _sparklinesWidget("#sparklines_basic", "area", 30, 50, "basis", 750, 2000, "#66BB6A");

                function updateServerInfo() {
                    $.ajax({
                        url: '/server-info',
                        method: 'GET',
                        dataType: 'json',
                        success: function (response) {
                            $('.cpu-usage').text(response.cpu_usage + '%');
                        }
                    });
                }

                // Initial call to update server info
                updateServerInfo();

                // Update server info every 10 seconds
                setInterval(updateServerInfo, 10000);
            });
        </script>
    @endpush
@endpush
