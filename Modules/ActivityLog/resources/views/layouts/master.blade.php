<x-app-layout>
    <x-slot name="breadcrumbs">
        <a href="{{ route('admin.dashboard') }}" class="breadcrumb-item">Home</a>
        <a href="{{ route('activity-logs.index') }}" class="breadcrumb-item">Activity Log</a>
        @yield('breadcrumb')
    </x-slot>
    @yield('content')
</x-app-layout>
