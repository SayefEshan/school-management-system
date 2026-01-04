@php
    $idm = ['download.import.manager.index', 'download.import.get.update'];
@endphp

@if (auth()->user()->hasAnyPermission(['Download Import Manager Management']))
    <li class="nav-item">
        <a href="{{ route('download.import.manager.index') }}" class="nav-link @if (in_array($current_route, $idm, true)) active @endif">
            <i class="ph-download"></i>
            <span>Import Download Manager</span>
        </a>
    </li>
@endif
