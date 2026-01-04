@extends('settings::layouts.master')
@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Import Settings</h5>
            <a href="{{ route('system_settings.manage') }}" class="btn btn-secondary">
                <i class="ph-arrow-left me-1"></i>
                Back to Settings
            </a>
        </div>
        <div class="card-body">
            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <div class="alert alert-info">
                <h6 class="alert-heading">Import Instructions</h6>
                <p>You can import settings from a JSON file that was previously exported from this system. The file should
                    contain an array of setting objects, each with at least the following properties: <code>key</code>,
                    <code>group</code>, <code>type</code>, and <code>value</code>.</p>
                <p class="mb-0">Choose the import mode:</p>
                <ul>
                    <li><strong>Merge</strong>: Only create new settings that don't already exist, preserving existing ones.
                    </li>
                    <li><strong>Overwrite</strong>: Replace existing settings with imported ones if they have the same key.
                    </li>
                </ul>
            </div>

            <form action="{{ route('system_settings.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="settings_file" class="form-label required">Settings JSON File</label>
                    <input type="file" class="form-control @error('settings_file') is-invalid @enderror"
                        id="settings_file" name="settings_file" required accept=".json">
                    @error('settings_file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Only JSON files are accepted</small>
                </div>

                <div class="mb-3">
                    <label for="import_mode" class="form-label required">Import Mode</label>
                    <select class="form-select @error('import_mode') is-invalid @enderror" id="import_mode"
                        name="import_mode" required>
                        <option value="merge">Merge - Add new settings only</option>
                        <option value="overwrite">Overwrite - Replace existing settings</option>
                    </select>
                    @error('import_mode')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="alert alert-warning">
                    <div class="d-flex">
                        <i class="ph-warning-circle me-2 mt-1"></i>
                        <div>
                            <strong>Warning:</strong> Importing settings can potentially override existing configuration.
                            Make sure you have a backup of your current settings.
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-primary">Import Settings</button>
                </div>
            </form>
        </div>
    </div>
@endsection
