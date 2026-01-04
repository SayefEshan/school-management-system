@php
    $activity_log = ['activity-logs.index', 'activity-logs.show', 'activity-logs.dashboard', 'track-ip'];
    $analytics = ['analytics.index'];
    $activityLogMenu = array_merge($activity_log, $analytics);
@endphp

@if (auth()->user()->hasAnyPermission($all_permission->where('module_name', 'Activity Log')->pluck('name')))
    <li class="nav-item nav-item-submenu">
        <a href="#" class="nav-link">
            <i class="ph-activity"></i>
            <span>Activity & Logs</span>
        </a>
        <ul class="nav-group-sub @if (!in_array($current_route, $activityLogMenu, true)) collapse @endif" data-submenu-title="activity-log">
            @if (auth()->user()->hasAnyPermission(['View Activity Log', 'Delete Activity Log']))
                <li class="nav-item">
                    <a href="{{ route('activity-logs.index') }}"
                        class="nav-link @if (in_array($current_route, ['activity-logs.index', 'activity-logs.show', 'track-ip'], true)) active @endif">
                        <i class="ph-list me-1"></i>
                        Activity Logs
                    </a>
                </li>
            @endcan

            @if (auth()->user()->hasAnyPermission(['View Logs']))
                <li class="nav-item">
                    <a href="{{ url('log-viewer') }}" class="nav-link" target="_blank">
                        <i class="ph-terminal me-1"></i>
                        System Logs
                    </a>
                </li>
            @endcan
</ul>
</li>
@endif
