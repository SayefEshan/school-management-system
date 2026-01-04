<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <h5 class="font-size-16 mb-3">Notifications</h5>
                    <div class="table-responsive">
                        <table class="table table-nowrap align-middle">
                            <thead>
                            <tr>
                                <th>Id</th>
                                <th>Title</th>
                                <th>Body</th>
                                <th>Created At</th>
                                <th class="text-end">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($notifications as $notification)
                                <tr>
                                    <td>{{ $notification->id }}</td>
                                    <td>{{ $notification->title }}</td>
                                    <td>
                                        <x-truncated-text :text="$notification->body" :limit="50"/>
                                    </td>
                                    <td>{{ $notification->created_at->diffForHumans() }}</td>
                                    <td class="text-end">
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
