{!! Form::open(['route' => 'admin.users.bulk', 'method' => 'post', 'files' => true]) !!}
<div class="modal-body">
    <div class="mb-3">
        <label class="form-label required">Select File</label>
        {{Form::file('users',['class'=>'form-control', 'required'])}}
    </div>
    <div class="mb-3">
        <label class="form-label required">Download Sample File</label>
        <a class="btn btn-danger" href="{{asset('sample/users_sample.xlsx')}}">Download</a>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    <button type="submit" id="item_submit_button" class="btn btn-primary">Save</button>
</div>
{!! Form::close() !!}
