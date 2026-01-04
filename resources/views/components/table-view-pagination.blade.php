<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h4 class="card-title mb-0">
            {{ $title }}
            @if(isset($data) && $data->total() > 0)
                ({{ $data->total() }})
            @endif
        </h4>
        <div class="menus">
            @stack('actions')
        </div>
    </div>
    <div class="card-body">
        @if(isset($data) && $data->total() > 0)
            <div class="table-responsive custom-scrollbar">
                <table class="table table-bordered">
                    {{ $slot }}
                </table>
            </div>
            <div class="d-flex justify-content-end pt-2">
                {{ $data->withQueryString()->links() }}
            </div>
        @else
            <div class="text-center pt-2">
                <p>No data available...</p>
            </div>
        @endif
    </div>
</div>
