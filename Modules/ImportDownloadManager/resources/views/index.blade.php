@extends('importdownloadmanager::layouts.master')

@section('breadcrumb')
    <span class="breadcrumb-item active">Import Download Manager</span>
@endsection

@section('content')
    <x-search-card>
        <div class="col-md-4 mb-3">
            <label class="form-label">Search (Title, Type)</label>
            {!! Form::text('search', request()->search, ['class' => 'form-control', 'placeholder' => 'Search...']) !!}
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Status</label>
            {!! Form::select(
                'status',
                [
                    'Pending' => 'Pending',
                    'Processing' => 'Processing',
                    'Completed' => 'Completed',
                    'Failed' => 'Failed',
                ],
                request()->status,
                [
                    'class' => 'form-control select',
                    'data-placeholder' => 'Select Status',
                    'placeholder' => 'Select Status',
                ],
            ) !!}
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Type</label>
            {!! Form::select(
                'type',
                [
                    'Download' => 'Download',
                    'Import' => 'Import',
                ],
                request()->type,
                [
                    'class' => 'form-control select',
                    'data-placeholder' => 'Select Type',
                    'placeholder' => 'Select Type',
                ],
            ) !!}
        </div>
    </x-search-card>

    <x-table-view-pagination title="Import Download Manager List" :data="$downloadImports">
        @push('actions')
            <a href="{{ route('download.import.manager.index') }}" class="btn btn-outline-primary w-sm" title="Refresh Page">
                <i class="fas fa-sync-alt me-2"></i> Refresh
            </a>
        @endpush

        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Title</th>
                <th>Status</th>
                <th>Type</th>
                <th>Remarks</th>
                @canany(['Import Manager Data Download', 'Import Manager Data Delete'])
                    <th class="text-end">Action</th>
                @endcanany
            </tr>
        </thead>
        <tbody>
            @php
                $i = $downloadImports->toArray()['from'] ?? 1;
            @endphp
            @foreach ($downloadImports as $item)
                @if (!in_array($item->status, ['Completed', 'Failed']))
                    <input type="hidden" name="ids[]" value="{{ $item->id }}">
                @endif
                <tr>
                    <td>{{ $i++ }}</td>
                    <td>{{ date('Y-m-d H:i A', strtotime($item->created_at)) }}</td>
                    <td>{{ $item->title }}</td>
                    <td>
                        <span id="status{{ $item->id }}"
                            class="@if ($item->status === 'Pending') badge bg-info
                          @elseif($item->status === 'Processing') badge bg-warning
                          @elseif($item->status === 'Failed') badge bg-danger
                          @elseif($item->status === 'Completed') badge bg-success @endif">
                            {{ $item->status }}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-primary">{{ $item->type }}</span>
                    </td>
                    <td>
                        <div id="remarks{{ $item->id }}">{!! $item->remarks !!}</div>
                    </td>
                    @canany(['Import Manager Data Download', 'Import Manager Data Delete'])
                        <td class="text-end">
                            <x-dropdown-menu>
                                @can('Import Manager Data Download')
                                    @if ($item->status === 'Completed' && $item->type === 'Download')
                                        <x-dropdown-link :url="route('download.import.manager.download', ['id' => $item->id])">
                                            <i class="fas fa-download me-2"></i> Download
                                        </x-dropdown-link>
                                    @endif
                                    @if ($item->type !== 'Download')
                                        <x-dropdown-link :url="route('download.import.manager.download', $item->id)">
                                            <i class="fas fa-download me-2"></i> Download Source File
                                        </x-dropdown-link>
                                    @endif
                                @endcan
                                @can('Import Manager Data Delete')
                                    @if ($item->status !== 'Pending' && $item->status !== 'Processing')
                                        <x-dropdown-link :url="route('download.import.manager.delete', $item->id)" data-text="Are you sure you want to delete this record?"
                                            class="swal-confirm">
                                            <i class="fas fa-trash me-2"></i> Delete
                                        </x-dropdown-link>
                                    @endif
                                @endcan
                            </x-dropdown-menu>
                        </td>
                    @endcanany
                </tr>
            @endforeach
        </tbody>
    </x-table-view-pagination>
@endsection

@push('top_js')
    <script src="{{ asset('assets/js/vendor/forms/selects/select2.min.js') }}"></script>
    <script src="{{ asset('assets/demo/pages/form_select2.js') }}"></script>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            setInterval(() => {
                getUpdateStatus();
            }, 5000);
        });

        function getUpdateStatus() {
            var statusClass = {
                'Pending': 'badge bg-info',
                'Processing': 'badge bg-warning',
                'Failed': 'badge bg-danger',
                'Completed': 'badge bg-success'
            };

            var itemIds = $('input[name="ids[]"]').map(function() {
                return $(this).val();
            }).get();

            if (itemIds.length > 0) {
                $.ajax({
                    url: "{{ route('download.import.get.update') }}",
                    data: {
                        ids: itemIds
                    },
                    success: function(results) {
                        if (results.length > 0) {
                            results.forEach(function(item) {
                                let id = item.id;
                                $('#remarks' + id).html(item.remarks);
                                let status = '#status' + id;
                                $(status).removeClass();
                                $(status).addClass(statusClass[item.status]);
                                $(status).text(item.status);
                            });
                        }
                    }
                });
            }
            return false;
        }
    </script>
@endpush
