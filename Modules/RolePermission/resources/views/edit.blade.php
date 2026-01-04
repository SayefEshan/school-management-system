{{-- edit modal --}}
<div class="modal fade" id="updateRoleModal" tabindex="-1" aria-labelledby="updateRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="updateForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateRoleModalLabel">Update Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group">
                            <div class="mb-3">
                                <label for="role_name" class="form-label required">Role Name</label>
                                <input type="text" name="role_name" id="role_name" class="form-control"
                                    placeholder="Role Name" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="mb-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" id="main-update-button" class="btn btn-primary w-sm">Submit</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
{{-- edit modal --}}
