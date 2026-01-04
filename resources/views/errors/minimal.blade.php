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
</head>

<body>
<!-- Page content -->
<div class="page-content">
    <!-- Main content -->
    <div class="content-wrapper">
        <div class="content-inner">
            <div class="content d-flex justify-content-center align-items-center">
                <!-- Container -->
                <div class="flex-fill">
                    <!-- Error title -->
                    <div class="text-center mb-4">
                        <img src="{{asset('assets/images/error_bg.svg')}}" class="img-fluid mb-3" height="230" alt="">
                        <h1 class="display-3 fw-semibold lh-1 mb-3">
                            @yield('code', '404')
                        </h1>
                        <h6 class="w-md-25 mx-md-auto">
                            @yield('message', __('Service Unavailable'))
                        </h6>
                    </div>
                    <!-- /error title -->


                    <!-- Error content -->
                    <div class="text-center">
                        <a href="/" class="btn btn-primary">
                            <i class="ph-house me-2"></i>
                            Return Home
                        </a>
                    </div>
                    <!-- /error wrapper -->
                </div>
                <!-- /container -->
            </div>
        </div>
    </div>
    <!-- /main content -->
</div>
<!-- /page content -->
</body>
</html>
