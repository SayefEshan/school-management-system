<x-app-layout>
    <x-slot name="breadcrumbs">
        <a href="{{ route('admin.dashboard') }}" class="breadcrumb-item">Home</a>
        <span class="breadcrumb-item active">Settings</span>
    </x-slot>

    @yield('content')
</x-app-layout>
