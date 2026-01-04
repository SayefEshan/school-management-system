<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon"/>
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon"/>
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <title>{{ config('app.name') }}</title>

    @include('layouts.theme')
    @stack('styles')
    @stack('top_js')
</head>

<body class="navbar-top">

@include('layouts.partials.navbar')

<!-- Page content -->
<div class="page-content">

    @include('layouts.partials.sidebar')

    <!-- Main content -->
    <div class="content-wrapper">

        <div class="content">
            @include('layouts.partials.breadcrumb')
            {{ $slot }}
        </div>

    </div>

    @can('View User')
        <div id="user-modal" class="modal fade" tabindex="-1" style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-full modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">User Information</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="user-modal-data">
                    </div>
                </div>
            </div>
        </div>
    @endcan
</div>
<!-- /page content -->

@include('layouts.partials.footer')

@include('layouts.partials.notification')

@include('layouts.partials.right-sidebar')

@stack('modals')

@include('layouts.partials._message')
@include('layouts.partials._form_submit')

@stack('scripts')

@can('View User')
    <script>
        $(document).ready(function () {
            $(document).on('click', '.view-user', function () {
                $('#user-modal').modal('show');
                let userId = $(this).data('id');
                $.ajax({
                    url: '{{ route("admin.users.show", ":id") }}'.replace(':id', userId),
                    type: 'GET',
                    data: {isModal: true},
                    beforeSend: function () {
                        $('#user-modal-data').html('<div class="text-center"><div class="spinner-border text-primary" role="status"></div></div>');
                    },
                    success: function (response) {
                        $('#user-modal-data').html(response);
                    }
                });
            });
        });
    </script>
@endcan
</body>
</html>
