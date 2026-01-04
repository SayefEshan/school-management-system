@extends('rolepermission::layouts.master')

@section('content')
    <div class="card mb-4">
        <div class="card-body p-4">
            <form action="{{ route('permission.matrix') }}" method="GET">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Filter by Module</label>
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
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Roles Per Page</label>
                        <select name="per_page" class="form-select">
                            <option value="5" {{ request()->per_page == 5 ? 'selected' : '' }}>5</option>
                            <option value="10"
                                {{ request()->per_page == 10 || !request()->per_page ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request()->per_page == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request()->per_page == 50 ? 'selected' : '' }}>50</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end mb-3">
                        <div class="w-100 text-end">
                            <a href="{{ route('permission.matrix') }}" class="btn btn-outline-primary w-sm me-2">Reset</a>
                            <button type="submit" class="btn btn-primary w-sm">Apply</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h4 class="card-title mb-0">Permission Matrix</h4>
            <div>
                <a href="{{ route('role.index') }}" class="btn btn-secondary">
                    <i class="ph-arrow-left me-1"></i> Back to Roles
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="matrix-container">
                <form action="{{ route('permission.matrix.update') }}" method="POST" id="matrix-form">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th style="min-width: 200px;">Permissions</th>
                                    @foreach ($roles as $role)
                                        <th class="text-center">
                                            {{ $role->name }}
                                            <div class="form-check mt-2 d-flex justify-content-center">
                                                <input type="checkbox" class="form-check-input select-all-for-role"
                                                    data-role-id="{{ $role->id }}" id="role-{{ $role->id }}">
                                                <label class="form-check-label small" for="role-{{ $role->id }}">
                                                    All
                                                </label>
                                            </div>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($groupedPermissions as $module => $permissions)
                                    <tr class="table-secondary">
                                        <th colspan="{{ count($roles) + 1 }}">
                                            {{ $module }}
                                            <div class="form-check form-check-inline ms-3">
                                                <input type="checkbox" class="form-check-input select-all-for-module"
                                                    data-module="{{ $module }}"
                                                    id="module-{{ Str::slug($module) }}">
                                                <label class="form-check-label" for="module-{{ Str::slug($module) }}">
                                                    Select All in {{ $module }}
                                                </label>
                                            </div>
                                        </th>
                                    </tr>
                                    @foreach ($permissions as $permission)
                                        <tr data-module="{{ $module }}">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>{{ $permission->name }}</div>
                                                    @if ($permission->description)
                                                        <i class="ph-info-fill ms-2" data-bs-toggle="tooltip"
                                                            title="{{ $permission->description }}"></i>
                                                    @endif
                                                </div>
                                            </td>
                                            @foreach ($roles as $role)
                                                <td class="text-center">
                                                    <div class="form-check justify-content-center d-flex">
                                                        <input type="checkbox" class="form-check-input matrix-checkbox"
                                                            name="matrix[{{ $role->id }}][{{ $permission->id }}]"
                                                            data-role-id="{{ $role->id }}"
                                                            data-module="{{ $module }}"
                                                            {{ $rolePermissions[$role->id][$permission->id] ? 'checked' : '' }}>
                                                    </div>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
            <div class="d-flex justify-content-end mt-3">
                {{ $roles->withQueryString()->links() }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize tooltips
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(
                tooltipTriggerEl));

            // Select all permissions for a role
            $('.select-all-for-role').change(function() {
                const roleId = $(this).data('role-id');
                const isChecked = $(this).prop('checked');

                $(`.matrix-checkbox[data-role-id="${roleId}"]`).prop('checked', isChecked);
            });

            // Select all permissions in a module
            $('.select-all-for-module').change(function() {
                const module = $(this).data('module');
                const isChecked = $(this).prop('checked');

                $(`tr[data-module="${module}"] .matrix-checkbox`).prop('checked', isChecked);
            });
        });
    </script>
@endpush
