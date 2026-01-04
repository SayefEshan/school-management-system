<div class="card">
    <div class="card-body">
        {{ Form::model($_REQUEST, ['method' => 'GET']) }}
        <div class="row">
            {{ $slot }}
            <div class="col-md-4 mb-3">
                <label class="form-label">Show Data</label>
                {{Form::select('per_page',getParPagePaginate(),request('per_page'),['id' => 'pagination','class' => 'form-control select', 'placeholder' => 'Show Data', 'data-placeholder' => 'Show Data'])}}
            </div>
            <div class="col-lg-12 text-end">
                <a href="{{ route(Route::currentRouteName()) }}"
                   class="btn btn-outline-primary w-sm me-2">Reset</a>
                <button type="submit" class="btn btn-primary w-sm">Filter</button>
            </div>
        </div>
        {{ Form::close() }}
    </div>
</div>
