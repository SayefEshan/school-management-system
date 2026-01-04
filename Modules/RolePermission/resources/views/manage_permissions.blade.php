@extends('rolepermission::layouts.master')

@section('content')
    <div class="card mb-4">
        <div class="card-body p-4">
            <form action="{{ route('permissions.manage') }}" method="GET">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Permission Name</label>
                        <input value="{{ request()->name }}" type="text" name="name" class="form-control"
                            placeholder="Search by name">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Module</label>
                        <select name="module" class="form-select">
                            <option value="">All Modules</option>
                            @foreach ($modules as $moduleName)
                                <option value="{{ $moduleName }}"
                                    {{ request()->module == $moduleName ? 'selected' : '' }}>
                                    {{ $moduleName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end mb-3">
                        <div class="w-100 text-end">
                            <a href="{{ route('permissions.manage') }}" class="btn btn-outline-primary w-sm me-2">Reset</a>
                            <button type="submit" class="btn btn-primary w-sm">Filter</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h4 class="card-title mb-0">Manage Permissions</h4>
            <div class="d-flex">
                <a href="{{ route('permission.matrix') }}" class="btn btn-success w-sm me-2">
                    <i class="ph-table me-1"></i> Permission Matrix
                </a>
                <a href="#" class="btn btn-danger w-sm me-2" id="bulk-delete-btn" style="display: none;">
                    <i class="ph-trash me-1"></i> Delete Selected
                </a>
                <a href="#" class="btn btn-primary w-sm me-2" data-bs-toggle="modal"
                    data-bs-target="#createPermissionModal">
                    <i class="ph-plus me-1"></i> Create Permission
                </a>
                <a href="{{ route('permission.sync') }}" class="btn btn-success w-sm"
                    onclick="return confirm('Are you sure you want to sync permissions? This will run the PermissionSeeder.')">
                    <i class="ph-sync me-1"></i> Sync Permissions
                </a>
            </div>
        </div>
        <div class="card-body pt-2 px-2">
            <form id="bulk-delete-form" action="{{ route('permission.bulk-delete') }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="table-responsive">
                    <table class="table table-nowrap align-middle">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" class="form-check-input" id="select-all">
                                </th>
                                <th>Module</th>
                                <th>Permission Name</th>
                                <th>Description</th>
                                <th>Used By Roles</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($permissions as $permission)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input permission-checkbox"
                                            name="permission_ids[]" value="{{ $permission->id }}">
                                    </td>
                                    <td>{{ $permission->module_name }}</td>
                                    <td>{{ $permission->name }}</td>
                                    <td>{{ $permission->description ?: 'No description' }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $permission->roles_count }} roles</span>
                                        @if ($permission->roles_count > 0)
                                            <a href="#" class="show-roles" data-permission-id="{{ $permission->id }}"
                                                data-bs-toggle="tooltip" title="Click to see roles">
                                                <i class="ph-info-fill"></i>
                                            </a>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <form action="{{ route('permission.delete', $permission->id) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this permission? This might break functionality if the permission is in use.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="ph-trash me-1"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </form>
            <div class="d-flex justify-content-end mt-3">
                {{ $permissions->withQueryString()->links() }}
            </div>
        </div>
    </div>

    <!-- Create Permission Modal -->
    <div class="modal fade" id="createPermissionModal" tabindex="-1" aria-labelledby="createPermissionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('permission.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="createPermissionModalLabel">Create New Permission</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="module_name" class="form-label">Module Name</label>
                            <select class="form-select" id="module_name" name="module_name" required>
                                <option value="">Select Module</option>
                                @foreach ($modules as $moduleName)
                                    <option value="{{ $moduleName }}">{{ $moduleName }}</option>
                                @endforeach
                                <option value="new">+ Create New Module</option>
                            </select>
                        </div>
                        <div class="mb-3" id="new-module-container" style="display: none;">
                            <label for="new_module_name" class="form-label">New Module Name</label>
                            <input type="text" class="form-control" id="new_module_name" name="new_module_name">
                        </div>
                        <div class="mb-3">
                            <label for="permission_name" class="form-label">Permission Name</label>
                            <input type="text" class="form-control" id="permission_name" name="permission_name"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description (Optional)</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Create Permission</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Permission Roles Modal -->
    <div class="modal fade" id="permissionRolesModal" tabindex="-1" aria-labelledby="permissionRolesModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="permissionRolesModalLabel">Roles Using This Permission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="permission-roles-list">
                        Loading...
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Handle select all checkbox
            $("#select-all").change(function() {
                $(".permission-checkbox").prop('checked', $(this).prop('checked'));
                toggleBulkDeleteButton();
            });

            // Handle individual checkboxes
            $(".permission-checkbox").change(function() {
                toggleBulkDeleteButton();
            });

            // Show/hide bulk delete button based on selections
            function toggleBulkDeleteButton() {
                if ($(".permission-checkbox:checked").length > 0) {
                    $("#bulk-delete-btn").show();
                } else {
                    $("#bulk-delete-btn").hide();
                }
            }

            // Handle bulk delete button click
            $("#bulk-delete-btn").click(function(e) {
                e.preventDefault();
                if (confirm(
                        'Are you sure you want to delete all selected permissions? This might break functionality if any of them are in use.'
                    )) {
                    $("#bulk-delete-form").submit();
                }
            });

            // Handle new module field toggle
            $("#module_name").change(function() {
                if ($(this).val() === 'new') {
                    $("#new-module-container").show();
                } else {
                    $("#new-module-container").hide();
                }
            });

            // Show roles using a permission
            $(".show-roles").click(function(e) {
                e.preventDefault();
                const permissionId = $(this).data('permission-id');

                // Show modal with loading state
                $('#permissionRolesModal').modal('show');

                // Fetch roles data
                $.ajax({
                    url: "{{ route('permission.roles', ':id') }}".replace(':id', permissionId),
                    type: 'GET',
                    success: function(response) {
                        let rolesHtml = '<ul class="list-group">';
                        if (response.roles.length > 0) {
                            response.roles.forEach(function(role) {
                                rolesHtml += '<li class="list-group-item">' + role
                                    .name + '</li>';
                            });
                        } else {
                            rolesHtml +=
                                '<li class="list-group-item">No roles are using this permission.</li>';
                        }
                        rolesHtml += '</ul>';
                        $('.permission-roles-list').html(rolesHtml);
                    },
                    error: function() {
                        $('.permission-roles-list').html(
                            '<div class="alert alert-danger">Failed to load roles information.</div>'
                        );
                    }
                });
            });
        });
    </script>
@endpush
