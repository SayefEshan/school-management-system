<!-- Breadcrumbs -->
{{--<div class="page-header page-header-light shadow mb-4">--}}
{{--    <div class="page-header-content d-lg-flex">--}}
{{--    </div>--}}
{{--</div>--}}
<!-- /breadcrumbs -->

<div class="card">
    <div class="card-body pt-0 pb-0">
        <div class="d-lg-flex">
            <div class="d-flex">
                <div class="breadcrumb py-2">
                    <a href="{{ route('admin.dashboard') }}" class="breadcrumb-item"><i class="ph-house"></i></a>
                    {{ $breadcrumbs }}
                </div>

                <a href="#breadcrumb_elements" class="btn btn-light align-self-center collapsed d-lg-none border-transparent rounded-pill p-0 ms-auto" data-bs-toggle="collapse">
                    <i class="ph-caret-down collapsible-indicator ph-sm m-1"></i>
                </a>
            </div>
            <div class="collapse d-lg-block ms-lg-auto" id="breadcrumb_elements">
                <div class="d-lg-flex mb-2 mb-lg-0">
                    <a href="#" class="d-flex align-items-center text-body py-2" data-bs-toggle="offcanvas" data-bs-target="#demo_config">
                        <i class="ph-gear me-2"></i>
                        <span class="flex-1">Theme Settings</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
