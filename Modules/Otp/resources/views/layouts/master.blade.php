<x-app-layout>
    <x-slot name="breadcrumbs">
        <a href="{{ route('admin.dashboard') }}" class="breadcrumb-item">Home</a>
        <a href="{{ route('otp-whitelist.index') }}" class="breadcrumb-item">OTP Whitelist</a>
        @yield('breadcrumb')
    </x-slot>

    @yield('content')
</x-app-layout>
