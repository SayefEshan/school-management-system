@extends('rolepermission::layouts.master')

@section('content')
    <div class="card">
        <div class="card-body p-4">
            <form action="{{ route('role.index') }}">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Search</label>
                                <input value="{{ request()->name }}" type="text" name="name" class="form-control"
                                    placeholder="Name" id="">
                            </div>
                        </div>
                        <!-- end row -->
                    </div>
                    <!-- end col -->

                    <div class="col-lg-12">
                        <div class="row">
                            <!-- end col -->
                            <div class="col-lg-12 text-end">
                                <a href="{{ route('role.index') }}" class="btn btn-outline-primary w-sm me-2">Reset</a>
                                <button type="submit" class="btn btn-primary w-sm">Filter</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <!-- end card body -->
    </div>
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h4 class="card-title mb-0">Role List</h4>
            <div class="d-flex">
                @can('Assign Permission')
                    <a href="{{ route('permission.matrix') }}" class="btn btn-success w-sm me-2">
                        <i class="ph-table me-1"></i> Permission Matrix
                    </a>
                    <a href="{{ route('permissions.manage') }}" class="btn btn-info w-sm me-2">
                        <i class="ph-shield me-1"></i> Manage Permissions
                    </a>
                @endcan
                @can('Create Role')
                    <a data-bs-toggle="modal" class="btn btn-primary w-sm" data-bs-target="#createRoleModal">
                        <i data-feather="plus" width="19px"> </i> Create
                    </a>
                @endcan
            </div>
        </div>
        <!-- end card body -->
        <div class="card-body pt-2 px-2">
            <div class="table-responsive">
                <table class="table table-nowrap align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Total Assigned Permission</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roles as $role)
                            <tr>
                                <td> {{ $role->name }} </td>
                                <td> {{ $role->permissions_count }} </td>
                                <td class="text-end">
                                    <x-dropdown-menu>
                                        @can('Edit Role')
                                            <span class="dropdown-item edit_role" roleId="{{ $role->id }}"
                                                roleName="{{ $role->name }}">
                                                <i class="ph-pencil me-2"></i>
                                                Edit
                                            </span>
                                        @endcan
                                        @can('Assign Permission')
                                            <x-dropdown-link :url="route('role.assign.permission.get', $role->id)">
                                                <i class="ph-shield-check me-2"></i> Assign Permission
                                            </x-dropdown-link>
                                        @endcan
                                        @can('Create Role')
                                            <x-dropdown-link :url="route('role.clone', $role->id)"
                                                onclick="return confirm('Are you sure you want to clone this role with all its permissions?')">
                                                <i class="ph-copy me-2"></i> Clone Role
                                            </x-dropdown-link>
                                        @endcan
                                    </x-dropdown-menu>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end">
                {{ $roles->withQueryString()->links() }}
            </div>

        </div>

    </div>

    @include('rolepermission::create')
    @include('rolepermission::edit')
@endsection

@push('scripts')
    <script>
        $('.edit_role').on('click', function(e) {
            e.preventDefault();
            let role_id = $(this).attr('roleId');
            let role_name = $(this).attr('roleName');

            let url = "{{ url('role') }}" + "/" + role_id;

            $('#updateForm').attr('action', url);

            $('#role_name').val(role_name);
            $('#updateRoleModal').modal('show');
        })
    </script>
@endpush
