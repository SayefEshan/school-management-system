@extends('user::layouts.master')
@section('breadcrumb')
    <span class="breadcrumb-item active">View User</span>
@endsection

@section('content')
    @include('user::user.modals.user-view')
    

@endsection

@push('scripts')
@endpush
