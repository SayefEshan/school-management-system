@extends('user::layouts.master')

@section('breadcrumb')
    <span class="breadcrumb-item active">Update User</span>
@endsection

@section('content')
    <div class="card">
        <div class="card-body p-4">
            {{ Form::model($user, ['route' => ['admin.users.update', $user->id], 'method' => 'put', 'files' => true]) }}
            <div class="row">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            {!! Form::label('first_name', 'First Name ', ['class' => 'form-label']) !!}
                            {!! Form::text('first_name', $user->first_name, [
                                'class' => 'form-control',
                                'placeholder' => 'First Name',
                            ]) !!}
                        </div>
                        <div class="col-md-4 mb-3">
                            {!! Form::label('last_name', 'Last Name ', ['class' => 'form-label required']) !!}
                            {!! Form::text('last_name', $user->last_name, [
                                'class' => 'form-control',
                                'placeholder' => 'Last Name',
                                'required',
                            ]) !!}
                        </div>
                        <div class="col-md-4 mb-3">
                            {!! Form::label('email', 'Email ', ['class' => 'form-label']) !!}
                            {!! Form::email('email', null, ['class' => 'form-control', 'placeholder' => 'Email']) !!}
                        </div>
                        <div class="col-md-4 mb-3">
                            {!! Form::label('phone', 'Mobile No ', ['class' => 'form-label']) !!}
                            {!! Form::text('phone', null, ['class' => 'form-control', 'placeholder' => 'Mobile No']) !!}
                        </div>
                        <div class="col-md-4 mb-3">
                            {!! Form::label('image', 'Image', ['class' => 'form-label me-2']) !!}
                            <div class="d-flex justify-content-between">
                                <div>
                                    {!! Form::file('image', ['class' => 'form-control']) !!}
                                    <span class="form-text text-muted">Accepted formats: jpeg, png. Max file size 2Mb</span>
                                </div>
                                <a href="{{ $user->image }}" target="_blank" title="View Image">
                                    <img src="{{ $user->image }}" class="rounded-circle w-56px" alt="Image">
                                </a>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            {!! Form::label('gender', 'Gender ', ['class' => 'form-label required']) !!}
                            {!! Form::select(
                                'gender',
                                ['male' => 'Male', 'female' => 'Female', 'other' => 'Other'],
                                $user->gender,
                                [
                                    'class' => 'form-control select',
                                    'data-placeholder' => 'Select Gender',
                                    'placeholder' => 'Select Gender',
                                    'required',
                                ],
                            ) !!}
                        </div>
                        <div class="col-md-4 mb-3">
                            {!! Form::label('Roles', 'Roles ', ['class' => 'form-label required']) !!}
                            {!! Form::select('roles[]', $roles, $user->roles->pluck('id')->toArray(), [
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


                    <div class="col-lg-12">
                        <div class="row">
                            <!-- end col -->
                            <div class="col-lg-12 text-end">
                                <a href="{{ route('admin.users.index') }}"
                                    class="btn btn-outline-primary w-sm me-2">Back</a>
                                <button type="submit" id="main-submit-button" class="btn btn-primary w-sm">Submit
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
@endsection

@push('top_js')
    <script src="{{ asset('assets/js/vendor/forms/selects/select2.min.js') }}"></script>
    <script src="{{ asset('assets/demo/pages/form_select2.js') }}"></script>
@endpush

@push('scripts')
@endpush
