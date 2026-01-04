@extends('user::layouts.master')
@section('breadcrumb')
    <span class="breadcrumb-item active">Push Notification List</span>
@endsection

@section('content')
    <x-search-card/>
    <x-table-view-pagination title="Push Notification List" :data="$notifications">
        @push('actions')
            @can('Create Push Notification')
                <a href="{{ route('push.notification.create') }}" class="btn btn-primary">Create</a>
            @endcan
        @endpush
        <thead>
        <tr>
            <th>#</th>
            <th>Title</th>
            <th>Body</th>
            <th>Image</th>
            <th>Result</th>
            <th class="text-end">Action</th>
        </tr>
        </thead>
        <tbody>
        @foreach($notifications as $user)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $user->title }}</td>
                <td>{{ $user->body }}</td>
                <td>
                    <x-image :src="$user->image" alt="Image" maxWidth="40"/>
                </td>
                <td>

                </td>
                <td class="text-end"></td>
            </tr>
        @endforeach
        </tbody>
    </x-table-view-pagination>
@endsection

@push('top_js')
    <script src="{{asset('assets/js/vendor/forms/selects/select2.min.js')}}"></script>
    <script src="{{asset('assets/demo/pages/form_select2.js')}}"></script>
@endpush

@push('scripts')
@endpush
