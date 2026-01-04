{{-- create modal --}}
<div class="modal fade" id="createRoleModal" tabindex="-1" aria-labelledby="createRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        {{ Form::open(['route' => 'role.store', 'method' => 'post', 'id' => 'createForm']) }}
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createRoleModalLabel">Create Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <div class="row">
                    <div class="form-gorup">
                        <div class="mb-3">
                            {!! Form::label('name', 'Role Name', ['class' => 'form-label required']) !!}
                            {!! Form::text('role_name', null, ['class' => 'form-control', 'placeholder' => 'Role Name', 'required']) !!}
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="mb-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" id="main-submit-button" class="btn btn-primary w-sm">Submit</button>
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </div>
</div>
{{-- create modal --}}
