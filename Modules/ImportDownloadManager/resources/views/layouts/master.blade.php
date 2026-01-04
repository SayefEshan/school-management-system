<x-app-layout>
    <x-slot name="breadcrumbs">
        <a href="{{ route('admin.dashboard') }}" class="breadcrumb-item">Home</a>
        <a href="{{ route('download.import.manager.index') }}" class="breadcrumb-item">Import Download Manager</a>
        @yield('breadcrumb')
    </x-slot>

    @yield('content')
</x-app-layout>
