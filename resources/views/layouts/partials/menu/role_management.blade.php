@php
    $role_menu = [
        'role.index',
        'role.create',
        'role.show',
        'role.edit',
        'role.assign.permission.get',
        'role.clone',
        'permissions.manage',
        'permission.matrix',
        'permission.sync',
        'permission.delete',
        'permission.bulk-delete',
        'permission.store',
        'permission.roles',
        'permission.matrix.update',
    ];
@endphp

@if (auth()->user()->hasAnyPermission(['Create Role', 'View Role', 'Edit Role', 'Delete Role', 'Assign Permission']))
    <li class="nav-item nav-item-submenu {{ in_array($current_route, $role_menu, true) ? 'nav-item-open' : '' }}">
        <a href="#" class="nav-link">
            <i class="ph-shield"></i>
            <span>Role & Permissions</span>
        </a>
        <ul class="nav-group-sub collapse {{ in_array($current_route, $role_menu, true) ? 'show' : '' }}">
            <li class="nav-item">
                <a href="{{ route('role.index') }}" class="nav-link {{ $current_route == 'role.index' ? 'active' : '' }}">
                    <i class="ph-users-three"></i>
                    <span>Manage Roles</span>
                </a>
            </li>
            @can('Assign Permission')
                <li class="nav-item">
                    <a href="{{ route('permissions.manage') }}"
                        class="nav-link {{ $current_route == 'permissions.manage' ? 'active' : '' }}">
                        <i class="ph-lock-key"></i>
                        <span>Manage Permissions</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('permission.matrix') }}"
                        class="nav-link {{ $current_route == 'permission.matrix' ? 'active' : '' }}">
                        <i class="ph-table"></i>
                        <span>Permission Matrix</span>
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endif
