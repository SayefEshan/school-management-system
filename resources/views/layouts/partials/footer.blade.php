<div class="navbar navbar-sm navbar-footer border-top bg-white">
    <div class="container-fluid d-flex flex-column flex-md-row align-items-center justify-content-between py-0">
        <!-- Left: Copyright & Brand -->
        <span class="d-flex align-items-center mb-2 mb-md-0">
            <span class="text-muted">&copy; {{ date('Y') }}</span>
            <a href="{{ config('app.url') }}" class="text-body fw-semibold text-decoration-none ms-2" target="_blank">
                {{ config('app.name') }}
            </a>
            <span class="text-muted mx-2">&bull;</span>
            <span class="text-muted small">All rights reserved.</span>
        </span>

        <!-- Right: Support & Links -->
        <ul class="nav">
            <li class="nav-item">
                <a href="mailto:{{ config('app.email') }}" class="navbar-nav-link navbar-nav-link-icon rounded text-muted" target="_blank">
                    <div class="d-flex align-items-center">
                        <i class="ph-lifebuoy ph-lg me-2 text-primary"></i>
                        <span class="d-none d-md-inline-block fw-medium">Support</span>
                    </div>
                </a>
            </li>
        </ul>
    </div>
</div>
