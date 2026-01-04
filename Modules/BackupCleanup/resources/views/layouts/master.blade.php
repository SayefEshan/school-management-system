<x-app-layout>
    <x-slot name="breadcrumbs">
        <a href="{{ route('admin.dashboard') }}" class="breadcrumb-item">Home</a>
        <a href="#" class="breadcrumb-item">Backup Cleanup</a>
        @yield('breadcrumb')
    </x-slot>
    @yield('content')
</x-app-layout>
