@php
    $system_setting_links = [
        'system_settings.index',
        'special_settings.privacy_policy',
        'special_settings.sms_gateways',
        'special_settings.email_mailers',
    ];
    $manage_setting_links = [
        'system_settings.manage',
        'system_settings.create',
        'system_settings.store_new',
        'system_settings.edit',
        'system_settings.update',
        'system_settings.destroy',
        'system_settings.export',
        'system_settings.import_form',
    ];
    $settings_menu = array_merge($system_setting_links, $manage_setting_links);
@endphp

@if (auth()->user()->hasAnyPermission($all_permission->where('module_name', 'Settings')->pluck('name')))
    <li class="nav-item nav-item-submenu">
        <a href="#" class="nav-link">
            <i class="ph-gear"></i>
            <span>Setting</span>
        </a>
        <ul class="nav-group-sub @if (!in_array($current_route, $settings_menu, true)) collapse @endif" data-submenu-title="Settings">
            @if (auth()->user()->hasAnyPermission(['Edit System Setting']))
                <li class="nav-item">
                    <a href="{{ route('system_settings.index') }}"
                        class="nav-link @if ($current_route === 'system_settings.index') active @endif">
                        <i class="ph-sliders me-1"></i>
                        General Settings
                    </a>
                </li>
            @endif

            @if (auth()->user()->hasAnyPermission(['Edit Special Setting']))
                <li class="nav-item">
                    <a href="{{ route('special_settings.privacy_policy') }}"
                        class="nav-link @if ($current_route === 'special_settings.privacy_policy') active @endif">
                        <i class="ph-file-text me-1"></i>
                        Privacy Policy
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('special_settings.sms_gateways') }}"
                        class="nav-link @if ($current_route === 'special_settings.sms_gateways') active @endif">
                        <i class="ph-chat-text me-1"></i>
                        SMS Gateways
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('special_settings.email_mailers') }}"
                        class="nav-link @if ($current_route === 'special_settings.email_mailers') active @endif">
                        <i class="ph-envelope me-1"></i>
                        Email Mailers
                    </a>
                </li>
            @endif

            @if (auth()->user()->hasAnyPermission(['Developer Setting']))
                <li class="nav-item">
                    <a href="{{ route('system_settings.manage') }}"
                        class="nav-link @if (in_array($current_route, $manage_setting_links, true)) active @endif">
                        <i class="ph-gear-six me-1"></i>
                        Manage Settings
                    </a>
                </li>
            @endif
        </ul>
    </li>
@endif
