@extends('user::layouts.master')
@section('breadcrumb')
    <span class="breadcrumb-item active">Create Push Notification</span>
@endsection
@section('content')
    <div class="card">
        <div class="card-body p-4">
            {{ Form::open(['route' => 'push.notification.store', 'method' => 'post', 'files' => true ]) }}
            <div class="row">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            {!! Form::label('title', 'Title',['class' => 'form-label required']) !!}
                            {!! Form::text('title', null,['class' => 'form-control','placeholder' => 'Title','required']) !!}
                        </div>
                        <div class="col-md-4 mb-3">
                            {!! Form::label('body', 'Body',['class' => 'form-label required']) !!}
                            {!! Form::text('body', null,['class' => 'form-control','placeholder' => 'Body','required']) !!}
                        </div>
                        <div class="col-md-4 mb-3">
                            {!! Form::label('image', 'Image',['class' => 'form-label']) !!}
                            {!! Form::file('image', ['class' => 'form-control']) !!}
                        </div>
                    </div>
                </div>
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
    <script src="{{asset('assets/js/vendor/forms/selects/select2.min.js')}}"></script>
    <script src="{{asset('assets/demo/pages/form_select2.js')}}"></script>
@endpush

@push('scripts')
@endpush
