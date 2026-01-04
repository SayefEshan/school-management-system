@extends('user::layouts.master')
@section('breadcrumb')
    <span class="breadcrumb-item active">Create User</span>
@endsection
@section('content')
    <div class="card">
        <div class="card-body p-4">
            {{ Form::open(['route' => 'admin.users.store', 'method' => 'post', 'files' => true]) }}
            <div class="row">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            {!! Form::label('first_name', 'First Name ', ['class' => 'form-label']) !!}
                            {!! Form::text('first_name', null, ['class' => 'form-control', 'placeholder' => 'First Name']) !!}
                        </div>
                        <div class="col-md-4 mb-3">
                            {!! Form::label('last_name', 'Last Name ', ['class' => 'form-label required']) !!}
                            {!! Form::text('last_name', null, ['class' => 'form-control', 'placeholder' => 'Last Name', 'required']) !!}
                        </div>
                        <div class="col-md-4 mb-3">
                            {!! Form::label('email', 'Email ', ['class' => 'form-label']) !!}
                            {!! Form::email('email', null, ['class' => 'form-control', 'placeholder' => 'Email']) !!}
                        </div>
                        <div class="col-md-4 mb-3">
                            {!! Form::label('phone', 'Mobile No ', ['class' => 'form-label']) !!}
                            {!! Form::text('phone', null, ['class' => 'form-control', 'placeholder' => 'Mobile No']) !!}
                            <span class="form-text text-muted">Ex. 88017XXXXXXXX</span>
                        </div>
                        <div class="col-md-4 mb-3">
                            {!! Form::label('password', 'Password ', ['class' => 'form-label required']) !!}
                            {!! Form::password('password', [
                                'class' => 'form-control',
                                'placeholder' => 'Password',
                                'id' => 'password',
                                'required',
                            ]) !!}
                        </div>
                        <div class="col-md-4 mb-3">
                            {!! Form::label('password_confirmation', 'Confirm Password ', ['class' => 'form-label required']) !!}
                            <span id="msg"></span>
                            {!! Form::password('password_confirmation', [
                                'class' => 'form-control',
                                'placeholder' => 'Password',
                                'id' => 'password_confirmation',
                                'required',
                            ]) !!}
                        </div>
                        <div class="col-md-4 mb-3">
                            {!! Form::label('image', 'Image', ['class' => 'form-label']) !!}
                            {!! Form::file('image', ['class' => 'form-control']) !!}
                            <span class="form-text text-muted">Accepted formats: jpeg, png. Max file size 2Mb</span>
                        </div>
                        <div class="col-md-4 mb-3">
                            {!! Form::label('gender', 'Gender ', ['class' => 'form-label required']) !!}
                            {!! Form::select('gender', ['male' => 'Male', 'female' => 'Female', 'other' => 'Other'], null, [
                                'class' => 'form-control select',
                                'data-placeholder' => 'Select Gender',
                                'placeholder' => 'Select Gender',
                                'required',
                            ]) !!}
                        </div>
                        <div class="col-md-4 mb-3">
                            {!! Form::label('Roles', 'Roles ', ['class' => 'form-label required']) !!}
                            {!! Form::select('roles[]', $roles, null, [
                                'class' => 'form-control select',
                                'multiple',
                                'data-placeholder' => 'Select a Role...',
                                'required',
                                'id' => 'roles',
                            ]) !!}
                        </div>
                        <div class="col-md-4 mb-3">
                            {!! Form::label('is_active', 'Status ', ['class' => 'form-label required']) !!}
                            {!! Form::select('is_active', [true => 'Active', false => 'Inactive'], null, [
                                'class' => 'form-control select',
                                'data-placeholder' => 'Select Status',
                                'placeholder' => 'Select Status',
                                'required',
                            ]) !!}
                        </div>
                    </div>

                    <!-- end row -->
                </div>
                <!-- end col -->
                <div class="col-lg-12">
                    <div class="row">
                        <!-- end col -->
                        <div class="col-lg-12 text-end">
                            <x-secondary-button type="reset">Reset</x-secondary-button>
                            <x-primary-button type="submit">Submit</x-primary-button>
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
        $("#password").keyup(function() {
            var password_confirmation = $("#password_confirmation").val();
            if (password_confirmation !== '') {
                if ($("#password").val() !== password_confirmation) {
                    $("#msg").html("<i class='fa-x bx bx-window-close'></i>").css("color", "red");
                } else {
                    $("#msg").html("<i class='fa-x bx bx-check-square'></i>").css("color", "green");
                }
            } else {
                $("#msg").empty();
            }
        });
        $("#password_confirmation").keyup(function() {
            var password = $("#password").val();
            if (password !== '') {
                if (password !== $("#password_confirmation").val()) {
                    $("#msg").html("<i class='fa-x bx bx-window-close'></i>").css("color", "red");
                } else {
                    $("#msg").html("<i class='fa-x bx bx-check-square'></i>").css("color", "green");
                }
            } else {
                $("#msg").empty();
            }
        });
    </script>
@endpush
