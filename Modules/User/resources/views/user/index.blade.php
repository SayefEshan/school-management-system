@extends('user::layouts.master')

@section('breadcrumb')
    <span class="breadcrumb-item active">User List</span>
@endsection

@section('content')
    <x-search-card>
        <div class="col-md-4 mb-3">
            <label class="form-label">Search (Name, Email, Phone)</label>
            {!! Form::text('search', request()->search, ['class' => 'form-control', 'placeholder' => 'Search...']) !!}
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Role</label>
            {!! Form::select('role_id[]', $roles, request()->role_id, [
                'class' => 'form-control select',
                'data-placeholder' => 'Select a Role...',
                'multiple',
            ]) !!}
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Status</label>
            {!! Form::select('is_active', [true => 'Active', false => 'Inactive'], request()->is_active, [
                'class' => 'form-control select',
                'data-placeholder' => 'Select Status...',
                'placeholder' => 'Select Status',
            ]) !!}
        </div>
    </x-search-card>

    <x-table-view-pagination title="User List" :data="$users">
        @push('actions')
            @can('Create User')
                <a href="{{ route('admin.users.create') }}" type="submit" class="btn btn-primary w-sm">
                    <i class="fas fa-plus me-2"></i> Add User
                </a>
            @endcan
            @can('Import User')
                <a onclick="openItemBulkUploadModal()" href="#" class="btn btn-info w-sm">
                    <i class="fas fa-file-import me-2"></i> Import Users
                </a>
            @endcan
            @can('Export User')
                <a href="{{ route('admin.users.export') }}?{{ request()->getQueryString() }}" type="submit"
                    class="btn btn-success w-sm swal-confirm" data-text="Do you want to export users?">
                    <i class="fas fa-file-excel me-2"></i> Export Users
                </a>
            @endcan
        @endpush

        <thead>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Role</th>
                <th>Mobile</th>
                <th>Status</th>
                <th class="text-end">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>
                        <img src="{{ $user->image }}" class="rounded-circle"
                            style="height: 60px; width: 60px; object-fit: cover" alt="">
                    </td>
                    <td>
                        <a href="#" class="view-user" data-id="{{ $user->id }}">
                            {{ $user->name }}
                        </a>
                    </td>
                    <td>
                        @foreach ($user->roles as $role)
                            <span class="badge bg-primary">{{ $role->name }}</span>
                        @endforeach
                    </td>

                    <td> {{ $user->phone }} </td>
                    <td>
                        @if ($user->is_active === false)
                            <span class="badge bg-danger">Inactive</span>
                        @else
                            <span class="badge bg-success">Active</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <x-dropdown-menu>
                            @can('View User')
                                <x-dropdown-link :url="route('admin.users.show', $user->id)">
                                    <i class="fas fa-eye me-2"></i> View User
                                </x-dropdown-link>
                            @endcan
                            @can('Edit User')
                                <x-dropdown-link :url="route('admin.users.edit', $user->id)">
                                    <i class="fas fa-edit me-2"></i> Edit User
                                </x-dropdown-link>
                            @endcan
                            @can('User Password Reset')
                                <x-dropdown-link :url="route('admin.user.password.reset', $user->id)" data-text="Are you sure you want to reset the password?"
                                    class="swal-confirm">
                                    <i class="fas fa-key me-2"></i> Reset Password
                                </x-dropdown-link>
                            @endcan

                            @if (!app()->environment('production'))
                                @can('Delete User')
                                    <x-dropdown-link :url="'#'" 
                                        :onclick="'showAccountManageModal(' . $user->id . ')'"
                                        class="text-warning">
                                        <i class="fas fa-cog me-2"></i> Manage Account
                                    </x-dropdown-link>
                                @endcan
                            @endif
                        </x-dropdown-menu>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </x-table-view-pagination>

    <x-modal id="itemBulkUploadModal" title="Users Bulk Upload">
        @include('user::user.partials.bulk_upload')
    </x-modal>

    @if (!app()->environment('production'))
        <!-- Account Management Modal -->
        <div class="modal fade" id="accountManageModal" tabindex="-1" aria-labelledby="accountManageModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="accountManageModalLabel">Manage User Account</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="accountManageForm" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group mb-4">
                                <label class="form-label fw-bold">Select Action:</label>
                                <div class="border rounded p-3">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="action" id="actionReset"
                                            value="reset" checked onchange="updateWarningMessage()">
                                        <label class="form-check-label" for="actionReset">
                                            <i class="fas fa-redo text-warning me-2"></i> Reset Account
                                        </label>
                                        <div class="text-muted small ms-4">Clears profile data but keeps login credentials
                                        </div>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="action" id="actionDelete"
                                            value="delete" onchange="updateWarningMessage()">
                                        <label class="form-check-label" for="actionDelete">
                                            <i class="fas fa-trash-alt text-danger me-2"></i> Delete Account
                                        </label>
                                        <div class="text-muted small ms-4">Permanently removes the user and all associated
                                            data</div>
                                    </div>
                                </div>
                            </div>

                            <div id="resetWarning" class="alert alert-warning">
                                <strong>Warning:</strong> This will clear all user data but retain login credentials. User
                                will be assigned only the basic user role.
                            </div>

                            <div id="deleteWarning" class="alert alert-danger" style="display: none;">
                                <strong>Danger:</strong> This will permanently delete the user account and all associated
                                data. This action cannot be undone.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" id="confirmButton" class="btn btn-warning">Confirm Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('top_js')
    <script src="{{ asset('assets/js/vendor/forms/selects/select2.min.js') }}"></script>
    <script src="{{ asset('assets/demo/pages/form_select2.js') }}"></script>
@endpush

@push('scripts')
    <script>
        function openItemBulkUploadModal() {
            $('#itemBulkUploadModal').modal("show");
        }
    </script>

    @if (!app()->environment('production'))
        <script>
            function showAccountManageModal(userId) {
                const form = document.getElementById('accountManageForm');
                form.action = "{{ route('admin.users.account.manage', ['id' => ':id']) }}".replace(':id', userId);
                $('#accountManageModal').modal('show');
                // Default to reset action
                document.getElementById('actionReset').checked = true;
                updateWarningMessage();
            }

            function updateWarningMessage() {
                const isDeleteAction = document.getElementById('actionDelete').checked;
                document.getElementById('resetWarning').style.display = isDeleteAction ? 'none' : 'block';
                document.getElementById('deleteWarning').style.display = isDeleteAction ? 'block' : 'none';

                // Update button text and class
                const confirmButton = document.getElementById('confirmButton');
                if (isDeleteAction) {
                    confirmButton.textContent = 'Confirm Delete';
                    confirmButton.classList.remove('btn-warning');
                    confirmButton.classList.add('btn-danger');
                } else {
                    confirmButton.textContent = 'Confirm Reset';
                    confirmButton.classList.remove('btn-danger');
                    confirmButton.classList.add('btn-warning');
                }
            }
        </script>
    @endif
@endpush
