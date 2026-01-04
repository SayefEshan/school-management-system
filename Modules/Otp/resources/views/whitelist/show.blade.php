@extends('otp::layouts.master')

@section('breadcrumb')
    <span class="breadcrumb-item active">View OTP Whitelist Entry</span>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">OTP Whitelist Entry #{{ $whitelist->id }}</h5>
            <div>
                @can('Edit OTP Whitelist')
                    <a href="{{ route('otp-whitelist.edit', $whitelist->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i> Edit
                    </a>
                @endcan

                @can('Delete OTP Whitelist')
                    <a href="{{ route('otp-whitelist.destroy', $whitelist->id) }}" class="btn btn-danger swal-delete"
                        data-text="Are you sure you want to delete this whitelist entry?" data-method="DELETE">
                        <i class="fas fa-trash me-2"></i> Delete
                    </a>
                @endcan
            </div>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="fw-semibold">ID</h6>
                    <p>{{ $whitelist->id }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="fw-semibold">Created At</h6>
                    <p>{{ $whitelist->created_at->format('Y-m-d H:i:s') }}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="fw-semibold">Recipient Type</h6>
                    <p>{{ ucfirst($whitelist->recipient_type) }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="fw-semibold">Recipient</h6>
                    <p>{{ $whitelist->recipient }}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="fw-semibold">Fixed OTP</h6>
                    <p>{{ $whitelist->fixed_otp }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="fw-semibold">Status</h6>
                    <p>
                        @if ($whitelist->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-3">
                    <h6 class="fw-semibold">Description</h6>
                    <p>{{ $whitelist->description ?: 'No description provided' }}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="fw-semibold">Last Updated</h6>
                    <p>{{ $whitelist->updated_at->format('Y-m-d H:i:s') }}</p>
                </div>
            </div>
        </div>

        <div class="card-footer text-end">
            <a href="{{ route('otp-whitelist.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Back to List
            </a>
        </div>
    </div>
@endsection
