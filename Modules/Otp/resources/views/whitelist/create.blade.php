@extends('otp::layouts.master')

@section('breadcrumb')
    <span class="breadcrumb-item active">Create OTP Whitelist Entry</span>
@endsection

@section('content')
    <div class="card">
        <div class="card-body p-4">
            {{ Form::open(['route' => 'otp-whitelist.store', 'method' => 'post']) }}
            <div class="row">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            {!! Form::label('recipient_type', 'Recipient Type', ['class' => 'form-label required']) !!}
                            {!! Form::select('recipient_type', ['email' => 'Email', 'phone' => 'Phone'], null, [
                                'class' => 'form-control select',
                                'data-placeholder' => 'Select Type',
                                'placeholder' => 'Select Type',
                                'required',
                            ]) !!}
                        </div>

                        <div class="col-md-6 mb-3">
                            {!! Form::label('recipient', 'Recipient', ['class' => 'form-label required']) !!}
                            {!! Form::text('recipient', null, [
                                'class' => 'form-control',
                                'placeholder' => 'Email address or phone number',
                                'required',
                            ]) !!}
                        </div>

                        <div class="col-md-6 mb-3">
                            {!! Form::label('fixed_otp', 'Fixed OTP', ['class' => 'form-label required']) !!}
                            {!! Form::text('fixed_otp', null, [
                                'class' => 'form-control',
                                'placeholder' => '6-digit OTP',
                                'maxlength' => '6',
                                'required',
                                'id' => 'fixed_otp',
                            ]) !!}
                            <span class="form-text text-muted">Must be exactly 6 digits (0-9)</span>
                        </div>

                        <div class="col-md-6 mb-3">
                            {!! Form::label('is_active', 'Status', ['class' => 'form-label required']) !!}
                            {!! Form::select('is_active', [true => 'Active', false => 'Inactive'], true, [
                                'class' => 'form-control select',
                                'data-placeholder' => 'Select Status',
                                'required',
                            ]) !!}
                        </div>

                        <div class="col-md-12 mb-3">
                            {!! Form::label('description', 'Description', ['class' => 'form-label']) !!}
                            {!! Form::textarea('description', null, [
                                'class' => 'form-control',
                                'placeholder' => 'Description (optional)',
                                'rows' => 3,
                            ]) !!}
                        </div>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-12 text-end">
                            <x-secondary-button :url="route('otp-whitelist.index')">Cancel</x-secondary-button>
                            <x-primary-button type="submit">Save Entry</x-primary-button>
                        </div>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
@endsection

@push('top_js')
    <script src="{{ asset('assets/js/vendor/forms/selects/select2.min.js') }}"></script>
    <script src="{{ asset('assets/demo/pages/form_select2.js') }}"></script>
@endpush

@push('scripts')
    <script>
        $(function() {
            // Add input validation for fixed OTP
            $('#fixed_otp').on('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '').substring(0, 6);
            });
        });
    </script>
@endpush
