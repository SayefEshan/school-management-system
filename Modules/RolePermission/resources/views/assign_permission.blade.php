@extends('rolepermission::layouts.master')

@section('content')
    {!! Form::open(['route' => ['role.assign.permission', $role->id], 'id' => 'updatePermission', 'method' => 'post']) !!}
    <div class="card">
        <div class="card-header">
            <div class="d-flex align-items-center">
                <input type="checkbox" class="form-check-input" id="checkAll">
                <label class="ms-2 form-check-label" for="checkAll" data-on-label="Check All"
                       data-off-label="Uncheck All">Check All</label>
            </div>
        </div>
        <div class="card-body row">
            @foreach($all_permissions as $key => $permission)
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <div class="d-flex justify-content-between">
                                <label class="form-check-label"
                                       for="checkByModule{{str_replace(' ','-',$key)}}">{{ $key }}</label>
                                <input type="checkbox" class="checkByModule form-check-input"
                                       data-id="{{str_replace(' ','-',$key)}}">
                            </div>
                        </div>
                        <div class="card-body">
                            @foreach($permission as  $item)
                                <div class="d-flex align-items-center mb-2">
                                    <input class="form-check-input inputCheckbox {{str_replace(' ','-',$key)}}"
                                           type="checkbox" name="permission[{{ $item->name }}]"
                                           {{ $role->hasPermissionTo($item->name) ? 'checked' : '' }}
                                           id="checkBox{{ $item->id }}">
                                    <label class="ms-2 form-check-label"
                                           for="checkBox{{ $item->id }}">{{ $item->name }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="card-footer d-flex justify-content-end">
            <a href="{{ route('role.index') }}" class="btn btn-secondary">Back</a>
            <button type="submit" id="main-submit-button" class="btn btn-primary ms-2">Update
            </button>
        </div>
    </div>
    {!! Form::close() !!}
@endsection

@push('scripts')
    <script>
        $(document).on("click", '#checkAll', function (event) {
            if (this.checked) {
                $('.inputCheckbox').each(function () {
                    this.checked = true;
                });
            } else {
                $('.inputCheckbox').each(function () {
                    this.checked = false;
                });
            }
        });

        $(document).on("click", '.checkByModule', function (event) {
            var targetClass = $(this).attr('data-id');
            if (this.checked) {
                $('.' + targetClass).each(function () {
                    this.checked = true;
                });
            } else {
                $('.' + targetClass).each(function () {
                    this.checked = false;
                });
            }
        });
    </script>
@endpush
