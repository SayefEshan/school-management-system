<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">User Profile</h5>
        <div class="d-flex">
            @can('Edit User')
                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary me-2">
                    <i class="fas fa-edit me-2"></i> Edit User
                </a>
            @endcan

            @can('User Password Reset')
                <a href="{{ route('admin.user.password.reset', $user->id) }}" class="btn btn-outline-warning me-2 swal-confirm"
                    data-text="Are you sure you want to reset the password?">
                    <i class="fas fa-key me-2"></i> Reset Password
                </a>
            @endcan
        </div>
    </div>
    <div class="card-body">
        <ul class="nav nav-tabs mb-3" id="user-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal"
                    type="button" role="tab" aria-controls="personal" aria-selected="true">
                    <i class="fas fa-user me-2"></i>Personal Information
                </button>
            </li>
            @can('View Activity Log')
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity"
                        type="button" role="tab" aria-controls="activity" aria-selected="false">
                        <i class="fas fa-history me-2"></i>Activity Log
                    </button>
                </li>
            @endcan
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents"
                    type="button" role="tab" aria-controls="documents" aria-selected="false">
                    <i class="fas fa-file-alt me-2"></i>Documents
                </button>
            </li>
        </ul>

        <div class="tab-content" id="myTabContent">
            <!-- Personal Information Tab -->
            <div class="tab-pane fade show active" id="personal" role="tabpanel" aria-labelledby="personal-tab">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table">
                                    <tbody>
                                        <tr>
                                            <th>Id</th>
                                            <td>{{ $user->id }}</td>
                                        </tr>
                                        <tr>
                                            <th>Full Name</th>
                                            <td>{{ $user->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Email</th>
                                            <td>{{ $user->email ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Phone</th>
                                            <td>{{ $user->phone }}</td>
                                        </tr>
                                        <tr>
                                            <th>Role</th>
                                            <td>
                                                @foreach ($user->roles as $role)
                                                    <span class="badge bg-primary">{{ $role->name }}</span>
                                                @endforeach
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td>
                                                @if ($user->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-nowrap mb-0">
                                    <tbody>
                                        <tr>
                                            <th>Gender</th>
                                            <td>{{ $user->gender ? strtoupper($user->gender) : 'N/A' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Last Login</th>
                                            <td>
                                                {{ $user->last_login_at ?? 'N/A' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Updated At</th>
                                            <td>{{ $user->updated_at->format('d M Y h:i A') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Created At</th>
                                            <td>{{ $user->created_at->format('d M Y h:i A') }}</td>
                                        </tr>
                                        <tr>
                                            <th>IP Address</th>
                                            <td>{{ $user->ip_address ?? 'N/A' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 text-center">
                        <div class="card shadow-none border">
                            <div class="card-body">
                                <img src="{{ $user->image }}" alt="{{ $user->last_name }}"
                                    class="img-thumbnail rounded-circle mb-3"
                                    style="width: 150px; height: 150px; object-fit: cover;">
                                <h5 class="mt-2 mb-1">{{ $user->name }}</h5>
                                <p class="text-muted">{{ $user->email }}</p>
                                <p class="text-muted">{{ $user->phone }}</p>

                            </div>
                        </div>
                    </div>
                </div>


            </div>


            <!-- Activity Log Tab -->
            @can('View Activity Log')
                <div class="tab-pane fade" id="activity" role="tabpanel" aria-labelledby="activity-tab">
                    <div class="row">
                        <div class="col-12">
                            <div class="card shadow-none border">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Recent Activity</h6>
                                    <a href="{{ route('activity-logs.index', ['user_id' => $user->id]) }}" target="_blank"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-external-link-alt me-1"></i> View All
                                    </a>
                                </div>
                                <div class="card-body">
                                    @if (isset($activities) && count($activities) > 0)
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>Action</th>
                                                        <th>Details</th>
                                                        <th>Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($activities as $activity)
                                                        <tr>
                                                            <td>{{ $activity->action }}</td>
                                                            <td>{{ $activity->details }}</td>
                                                            <td>{{ $activity->created_at->format('d M Y h:i A') }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center py-3">
                                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                            <p>Visit the activity logs page to view this user's complete activity history.
                                            </p>
                                            <a href="{{ route('activity-logs.index', ['user_id' => $user->id]) }}"
                                                target="_blank" class="btn btn-outline-primary">
                                                <i class="fas fa-external-link-alt me-2"></i> Go to Activity Logs
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endcan

            <!-- Documents Tab -->
            <div class="tab-pane fade" id="documents" role="tabpanel" aria-labelledby="documents-tab">
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-none border">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">User Documents</h6>
                            </div>
                            <div class="card-body">
                                
                                <!-- Document List -->
                                <div class="table-responsive mb-4">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Type</th>
                                                <th>Number</th>
                                                <th>Files</th>
                                                <th>Status</th>
                                                <th>Expiry Date</th>
                                                <th>Uploaded At</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($user->documents as $document)
                                                <tr>
                                                    <td>{{ ucfirst(str_replace('_', ' ', $document->document_type)) }}</td>
                                                    <td>{{ $document->document_number ?? 'N/A' }}</td>
                                                    <td>
                                                        <a href="{{ \App\Services\FileManagerService::getFile($document->file_path) }}" target="_blank" class="btn btn-sm btn-info">View Front</a>
                                                        @if($document->back_file_path)
                                                            <a href="{{ \App\Services\FileManagerService::getFile($document->back_file_path) }}" target="_blank" class="btn btn-sm btn-info">View Back</a>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $document->status === 'verified' ? 'success' : ($document->status === 'rejected' ? 'danger' : 'warning') }}">
                                                            {{ ucfirst($document->status) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $document->expiry_date ? $document->expiry_date->format('d M Y') : 'N/A' }}</td>
                                                    <td>{{ $document->created_at->format('d M Y h:i A') }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center">No documents uploaded</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Upload Form -->
                                <hr>
                                <h6 class="mb-3">Upload New Document</h6>
                                <form action="{{ route('admin.users.documents.store', $user->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Document Type <span class="text-danger">*</span></label>
                                            <select name="document_type" class="form-control" required>
                                                <option value="">Select Type</option>
                                                <option value="nid">NID</option>
                                                <option value="passport">Passport</option>
                                                <option value="trade_license">Trade License</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Document Number</label>
                                            <input type="text" name="document_number" class="form-control" placeholder="Enter document number">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Expiry Date</label>
                                            <input type="date" name="expiry_date" class="form-control">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Front Side File <span class="text-danger">*</span></label>
                                            <input type="file" name="file" class="form-control" required accept="image/*,.pdf">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Back Side File (Optional)</label>
                                            <input type="file" name="back_file" class="form-control" accept="image/*,.pdf">
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Upload Document</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
