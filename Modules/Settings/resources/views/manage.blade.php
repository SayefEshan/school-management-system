@extends('settings::layouts.master')
@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Manage Settings</h5>
            <div>
                <div class="btn-group me-2">
                    <a href="{{ route('system_settings.export') }}" class="btn btn-outline-success">
                        <i class="ph-download me-1"></i>
                        Export
                    </a>
                    <a href="{{ route('system_settings.import_form') }}" class="btn btn-outline-primary">
                        <i class="ph-upload me-1"></i>
                        Import
                    </a>
                </div>
                <a href="{{ route('system_settings.create') }}" class="btn btn-primary">
                    <i class="ph-plus-circle me-1"></i>
                    Create New Setting
                </a>
            </div>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('import_errors'))
                <div class="alert alert-warning">
                    <h6 class="alert-heading">Import completed with errors:</h6>
                    <ul class="mb-0">
                        @foreach (session('import_errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <p class="mb-3">
                From this page, you can create, edit, and delete settings. Be careful when modifying settings as it may
                affect system functionality.
            </p>

            <!-- Search and Filter -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" id="settings-search" class="form-control" placeholder="Search settings...">
                        <button type="button" class="btn btn-light btn-icon" id="clear-search">
                            <i class="ph-x"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-3">
                    <select id="group-filter" class="form-select">
                        <option value="">All Groups</option>
                        @php
                            $groups = $settings->pluck('group')->unique()->sort()->values();
                        @endphp
                        @foreach ($groups as $group)
                            <option value="{{ $group }}">{{ $group }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="type-filter" class="form-select">
                        <option value="">All Types</option>
                        @php
                            $types = $settings->pluck('type')->unique()->sort()->values();
                        @endphp
                        @foreach ($types as $type)
                            <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Bulk Operations -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="card card-body bg-light">
                        <h6 class="mb-2">Bulk Operations</h6>
                        <div class="d-flex flex-wrap">
                            <div class="me-3 mb-2">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="select-all">
                                    <i class="ph-check-square me-1"></i> Select All
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="deselect-all">
                                    <i class="ph-square me-1"></i> Deselect All
                                </button>
                            </div>

                            <div class="me-3 mb-2">
                                <div class="input-group input-group-sm">
                                    <select id="bulk-action" class="form-select">
                                        <option value="">Select Action</option>
                                        <option value="visibility">Change Visibility</option>
                                        <option value="group">Change Group</option>
                                        <option value="delete">Delete Selected</option>
                                    </select>

                                    <!-- Options for visibility -->
                                    <select id="bulk-visibility" class="form-select" style="display: none;">
                                        <option value="1">Make Visible</option>
                                        <option value="0">Make Invisible</option>
                                    </select>

                                    <!-- Options for group -->
                                    <select id="bulk-group" class="form-select" style="display: none;">
                                        @foreach ($groups as $group)
                                            <option value="{{ $group }}">{{ $group }}</option>
                                        @endforeach
                                        <option value="new">Add New Group...</option>
                                    </select>

                                    <input type="text" id="bulk-new-group" class="form-control"
                                        placeholder="New group name" style="display: none;">

                                    <button type="button" id="apply-bulk-action" class="btn btn-primary">Apply</button>
                                </div>
                            </div>

                            <div id="bulk-selection-info" class="text-muted mt-1 pt-1" style="display: none;">
                                <span id="selected-count">0</span> settings selected
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="settings-table">
                    <thead>
                        <tr>
                            <th width="40">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="check-all">
                                </div>
                            </th>
                            <th>Key</th>
                            <th>Group</th>
                            <th>Type</th>
                            <th>Value</th>
                            <th>Description</th>
                            <th>Visible</th>
                            <th>Required</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($settings->count() > 0)
                            @foreach($settings as $setting)
                            <tr class="setting-row" data-key="{{ $setting->key }}" data-group="{{ $setting->group }}"
                                data-type="{{ $setting->type }}" data-id="{{ $setting->id }}">
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input setting-checkbox" type="checkbox"
                                            value="{{ $setting->id }}">
                                    </div>
                                </td>
                                <td>{{ $setting->key }}</td>
                                <td>{{ $setting->group }}</td>
                                <td>{{ $setting->type }}</td>
                                <td>
                                    @if ($setting->type === 'image')
                                        <img src="{{ $setting->value }}" alt="{{ $setting->key }}" class="img-fluid"
                                            style="max-width: 50px;">
                                    @elseif($setting->type === 'file')
                                        <a href="{{ $setting->value }}" target="_blank">View File</a>
                                    @elseif($setting->type === 'boolean')
                                        {!! $setting->value ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>' !!}
                                    @elseif($setting->type === 'json')
                                        <span class="badge bg-info">JSON</span>
                                    @elseif($setting->type === 'array' || $setting->type === 'multi-select')
                                        {{ is_array($setting->value) ? implode(', ', $setting->value) : $setting->value }}
                                    @else
                                        {{ Str::limit($setting->value, 30) }}
                                    @endif
                                </td>
                                <td>{{ $setting->description }}</td>
                                <td>{!! $setting->is_visible
                                    ? '<span class="badge bg-success">Yes</span>'
                                    : '<span class="badge bg-danger">No</span>' !!}</td>
                                <td>{!! $setting->required ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>' !!}</td>
                                <td class="text-center">
                                    <div class="d-inline-flex">
                                        <a href="{{ route('system_settings.edit', $setting) }}"
                                            class="btn btn-sm btn-warning me-2">
                                            <i class="ph-pencil"></i>
                                        </a>
                                        <form action="{{ route('system_settings.destroy', $setting) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this setting?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="ph-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="9" class="text-center">No settings found</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div id="no-results" class="alert alert-info mt-3" style="display: none;">
                No settings match your search criteria. <a href="#" id="reset-filters">Reset filters</a>
            </div>

            <!-- Bulk Action Forms -->
            <form id="bulk-visibility-form" action="{{ route('system_settings.bulk_update') }}" method="POST"
                style="display: none;">
                @csrf
                @method('PUT')
                <input type="hidden" name="action" value="visibility">
                <input type="hidden" name="visibility" id="visibility-value">
                <div id="visibility-ids-container"></div>
            </form>

            <form id="bulk-group-form" action="{{ route('system_settings.bulk_update') }}" method="POST"
                style="display: none;">
                @csrf
                @method('PUT')
                <input type="hidden" name="action" value="group">
                <input type="hidden" name="group" id="group-value">
                <div id="group-ids-container"></div>
            </form>

            <form id="bulk-delete-form" action="{{ route('system_settings.bulk_delete') }}" method="POST"
                style="display: none;">
                @csrf
                @method('DELETE')
                <div id="delete-ids-container"></div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('settings-search');
            const groupFilter = document.getElementById('group-filter');
            const typeFilter = document.getElementById('type-filter');
            const clearSearchBtn = document.getElementById('clear-search');
            const resetFiltersLink = document.getElementById('reset-filters');
            const settingsTable = document.getElementById('settings-table');
            const noResults = document.getElementById('no-results');
            const settingRows = document.querySelectorAll('.setting-row');

            // Bulk operations elements
            const checkAll = document.getElementById('check-all');
            const selectAllBtn = document.getElementById('select-all');
            const deselectAllBtn = document.getElementById('deselect-all');
            const bulkAction = document.getElementById('bulk-action');
            const bulkVisibility = document.getElementById('bulk-visibility');
            const bulkGroup = document.getElementById('bulk-group');
            const bulkNewGroup = document.getElementById('bulk-new-group');
            const applyBulkBtn = document.getElementById('apply-bulk-action');
            const bulkSelectionInfo = document.getElementById('bulk-selection-info');
            const selectedCountSpan = document.getElementById('selected-count');

            // Search and filter functionality
            function filterSettings() {
                const searchTerm = searchInput.value.toLowerCase();
                const selectedGroup = groupFilter.value;
                const selectedType = typeFilter.value;

                let visibleCount = 0;

                settingRows.forEach(row => {
                    const key = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                    const description = row.querySelector('td:nth-child(6)').textContent.toLowerCase();
                    const group = row.dataset.group;
                    const type = row.dataset.type;

                    const matchesSearch = key.includes(searchTerm) || description.includes(searchTerm);
                    const matchesGroup = selectedGroup === '' || group === selectedGroup;
                    const matchesType = selectedType === '' || type === selectedType;

                    if (matchesSearch && matchesGroup && matchesType) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                if (visibleCount === 0) {
                    noResults.style.display = 'block';
                } else {
                    noResults.style.display = 'none';
                }

                updateSelectedCount();
            }

            function resetFilters() {
                searchInput.value = '';
                groupFilter.value = '';
                typeFilter.value = '';

                settingRows.forEach(row => {
                    row.style.display = '';
                });

                noResults.style.display = 'none';
                updateSelectedCount();
            }

            // Checkbox functionality
            function updateSelectedCount() {
                const visibleRows = Array.from(settingRows).filter(row => row.style.display !== 'none');
                const checkedBoxes = visibleRows.filter(row => row.querySelector('.setting-checkbox').checked);

                selectedCountSpan.textContent = checkedBoxes.length;
                bulkSelectionInfo.style.display = checkedBoxes.length > 0 ? 'block' : 'none';

                // Update "check all" checkbox state
                if (visibleRows.length > 0) {
                    checkAll.checked = checkedBoxes.length === visibleRows.length;
                    checkAll.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < visibleRows.length;
                } else {
                    checkAll.checked = false;
                    checkAll.indeterminate = false;
                }
            }

            // Bulk action handlers
            function handleBulkActionChange() {
                const action = bulkAction.value;

                // Hide all specific action inputs
                bulkVisibility.style.display = 'none';
                bulkGroup.style.display = 'none';
                bulkNewGroup.style.display = 'none';

                // Show specific action input based on selection
                if (action === 'visibility') {
                    bulkVisibility.style.display = 'block';
                } else if (action === 'group') {
                    bulkGroup.style.display = 'block';
                }
            }

            function handleBulkGroupChange() {
                if (bulkGroup.value === 'new') {
                    bulkNewGroup.style.display = 'block';
                } else {
                    bulkNewGroup.style.display = 'none';
                }
            }

            function applyBulkAction() {
                const action = bulkAction.value;
                if (!action) {
                    alert('Please select an action to perform');
                    return;
                }

                const checkedBoxes = document.querySelectorAll('.setting-checkbox:checked');
                if (checkedBoxes.length === 0) {
                    alert('Please select at least one setting');
                    return;
                }

                const selectedIds = Array.from(checkedBoxes).map(checkbox => checkbox.value);

                if (action === 'visibility') {
                    const visibilityValue = bulkVisibility.value;
                    document.getElementById('visibility-value').value = visibilityValue;

                    const container = document.getElementById('visibility-ids-container');
                    container.innerHTML = '';
                    selectedIds.forEach(id => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'ids[]';
                        input.value = id;
                        container.appendChild(input);
                    });

                    if (confirm(`Are you sure you want to change visibility for ${selectedIds.length} settings?`)) {
                        document.getElementById('bulk-visibility-form').submit();
                    }
                } else if (action === 'group') {
                    let groupValue = bulkGroup.value;
                    if (groupValue === 'new') {
                        groupValue = bulkNewGroup.value.trim();
                        if (!groupValue) {
                            alert('Please enter a new group name');
                            return;
                        }
                    }

                    document.getElementById('group-value').value = groupValue;

                    const container = document.getElementById('group-ids-container');
                    container.innerHTML = '';
                    selectedIds.forEach(id => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'ids[]';
                        input.value = id;
                        container.appendChild(input);
                    });

                    if (confirm(
                            `Are you sure you want to move ${selectedIds.length} settings to the "${groupValue}" group?`
                        )) {
                        document.getElementById('bulk-group-form').submit();
                    }
                } else if (action === 'delete') {
                    const container = document.getElementById('delete-ids-container');
                    container.innerHTML = '';
                    selectedIds.forEach(id => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'ids[]';
                        input.value = id;
                        container.appendChild(input);
                    });

                    if (confirm(
                            `Are you sure you want to delete ${selectedIds.length} settings? This action cannot be undone.`
                        )) {
                        document.getElementById('bulk-delete-form').submit();
                    }
                }
            }

            // Event listeners for search and filter
            searchInput.addEventListener('input', filterSettings);
            groupFilter.addEventListener('change', filterSettings);
            typeFilter.addEventListener('change', filterSettings);
            clearSearchBtn.addEventListener('click', function() {
                searchInput.value = '';
                filterSettings();
            });
            resetFiltersLink.addEventListener('click', function(e) {
                e.preventDefault();
                resetFilters();
            });

            // Event listeners for checkboxes
            checkAll.addEventListener('change', function() {
                const visibleRows = Array.from(settingRows).filter(row => row.style.display !== 'none');
                visibleRows.forEach(row => {
                    const checkbox = row.querySelector('.setting-checkbox');
                    checkbox.checked = checkAll.checked;
                });
                updateSelectedCount();
            });

            document.querySelectorAll('.setting-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectedCount);
            });

            // Event listeners for bulk actions
            selectAllBtn.addEventListener('click', function() {
                const visibleRows = Array.from(settingRows).filter(row => row.style.display !== 'none');
                visibleRows.forEach(row => {
                    const checkbox = row.querySelector('.setting-checkbox');
                    checkbox.checked = true;
                });
                updateSelectedCount();
            });

            deselectAllBtn.addEventListener('click', function() {
                document.querySelectorAll('.setting-checkbox').forEach(checkbox => {
                    checkbox.checked = false;
                });
                updateSelectedCount();
            });

            bulkAction.addEventListener('change', handleBulkActionChange);
            bulkGroup.addEventListener('change', handleBulkGroupChange);
            applyBulkBtn.addEventListener('click', applyBulkAction);

            // Initialize
            updateSelectedCount();
        });
    </script>
@endpush
