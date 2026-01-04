@extends('otp::layouts.master')

@section('breadcrumb')
    <span class="breadcrumb-item active">OTP Whitelist List</span>
@endsection

@section('content')
    <x-table-view-pagination title="OTP Whitelist List" :data="$whitelists">
        @push('actions')
            @can('Create OTP Whitelist')
                <a href="{{ route('otp-whitelist.create') }}" type="submit" class="btn btn-primary w-sm">
                    <i class="fas fa-plus me-2"></i> Add New Entry
                </a>
            @endcan
        @endpush

        <thead>
            <tr>
                <th width="5%">ID</th>
                <th>Type</th>
                <th>Recipient</th>
                <th>Fixed OTP</th>
                <th>Status</th>
                <th>Description</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($whitelists as $whitelist)
                <tr>
                    <td>{{ $whitelist->id }}</td>
                    <td>{{ ucfirst($whitelist->recipient_type) }}</td>
                    <td>{{ $whitelist->recipient }}</td>
                    <td>{{ $whitelist->fixed_otp }}</td>
                    <td>
                        @if ($whitelist->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </td>
                    <td>{{ $whitelist->description }}</td>
                    <td class="text-end">
                        <x-dropdown-menu>
                            @can('View OTP Whitelist')
                                <x-dropdown-link :url="route('otp-whitelist.show', $whitelist->id)">
                                    <i class="fas fa-eye me-2"></i> View Entry
                                </x-dropdown-link>
                            @endcan

                            @can('Edit OTP Whitelist')
                                <x-dropdown-link :url="route('otp-whitelist.edit', $whitelist->id)">
                                    <i class="fas fa-edit me-2"></i> Edit Entry
                                </x-dropdown-link>
                            @endcan

                            @can('Delete OTP Whitelist')
                                <x-dropdown-link :url="route('otp-whitelist.destroy', $whitelist->id)"
                                    data-text="Are you sure you want to delete this whitelist entry?"
                                    class="swal-delete" data-method="DELETE">
                                    <i class="fas fa-trash me-2"></i> Delete Entry
                                </x-dropdown-link>
                            @endcan
                        </x-dropdown-menu>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No whitelist entries found</td>
                </tr>
            @endforelse
        </tbody>
    </x-table-view-pagination>
@endsection
