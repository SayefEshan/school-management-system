@php
    $otp_whitelist_links = ['otp-whitelist.create', 'otp-whitelist.index', 'otp-whitelist.show', 'otp-whitelist.edit'];
    $otp_menu = $otp_whitelist_links;
@endphp

@if (auth()->user()->hasAnyPermission([
            'View OTP Whitelist',
            'Create OTP Whitelist',
            'Edit OTP Whitelist',
            'Delete OTP Whitelist',
        ]))
    <li class="nav-item nav-item-submenu">
        <a href="#" class="nav-link">
            <i class="ph-password"></i>
            <span>OTP Management</span>
        </a>
        <ul class="nav-group-sub @if (!in_array($current_route, $otp_menu, true)) collapse @endif" data-submenu-title="OTP Management">
            @canany(['View OTP Whitelist', 'Create OTP Whitelist', 'Edit OTP Whitelist', 'Delete OTP Whitelist'])
                <li class="nav-item">
                    <a href="{{ route('otp-whitelist.index') }}"
                        class="nav-link @if (in_array($current_route, $otp_whitelist_links, true)) active @endif">
                        OTP Whitelist
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endif
