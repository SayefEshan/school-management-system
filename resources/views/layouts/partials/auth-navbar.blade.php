<!-- Main navbar -->
<div class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm py-2">
    <div class="container-fluid">
        <!-- Brand -->
        <a href="{{ route('admin.dashboard') }}" class="navbar-brand d-flex align-items-center">
            <img src="{{ config('settings.app_logo.value') }}" class="h-32px" alt="{{ config('app.name') }}">
            <span class="d-none d-sm-inline-block ms-2 fw-bold text-dark fs-5">{{ config('app.name') }}</span>
        </a>

        <!-- Right Side -->
        <div class="d-flex align-items-center ms-auto gap-2">
            <!-- Support -->
            <a href="#" class="btn btn-link text-decoration-none text-muted d-none d-md-flex align-items-center gap-2">
                <i class="ph-lifebuoy fs-5"></i>
                <span class="fw-medium">Support</span>
            </a>

            <!-- Auth Actions -->
            <div class="d-flex align-items-center gap-2">
                 @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="btn btn-light btn-sm fw-medium d-flex align-items-center gap-2">
                        <i class="ph-sign-in"></i>
                        <span class="d-none d-sm-inline">{{ __('Login') }}</span>
                    </a>
                 @endif

                 @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm fw-medium d-flex align-items-center gap-2 shadow-sm">
                        <i class="ph-user-circle-plus"></i>
                        <span class="d-none d-sm-inline">{{ __('Register') }}</span>
                    </a>
                 @endif
            </div>
        </div>
    </div>
</div>
<!-- /main navbar -->
