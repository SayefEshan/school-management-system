@php
    $user_links = ['admin.users.create', 'admin.users.index', 'admin.users.show', 'admin.users.edit'];
    $push_notification_links = ['push.notification.create', 'push.notification.index'];
    $user_menu = array_merge($user_links, $push_notification_links);
@endphp

@if (auth()->user()->hasAnyPermission($all_permission->where('module_name', 'User')->pluck('name')))
    <li class="nav-item nav-item-submenu">
        <a href="#" class="nav-link">
            <i class="ph-users-four"></i>
            <span>Users</span>
        </a>
        <ul class="nav-group-sub @if (!in_array($current_route, $user_menu, true)) collapse @endif" data-submenu-title="Users">
            @can('View User')
                <li class="nav-item">
                    <a href="{{ route('admin.users.index') }}"
                        class="nav-link @if (in_array($current_route, ['admin.users.index', 'admin.users.show'], true)) active @endif">
                        <i class="ph-list me-1"></i>
                        Users List
                    </a>
                </li>
            @endcan
            @canany(['Create Push Notification', 'View Push Notification'])
                <li class="nav-item">
                    <a href="{{ route('push.notification.index') }}"
                        class="nav-link @if (in_array($current_route, $push_notification_links, true)) active @endif">
                        <i class="ph-bell me-1"></i>
                        Push Notifications
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endif
