@extends('settings::layouts.master')
@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Create New Setting</h5>
            <a href="{{ route('system_settings.manage') }}" class="btn btn-secondary">
                <i class="ph-arrow-left me-1"></i>
                Back to Settings
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('system_settings.store_new') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="key" class="form-label required">Key</label>
                        <input type="text" class="form-control @error('key') is-invalid @enderror" id="key"
                            name="key" value="{{ old('key') }}" required>
                        <small class="text-muted">The unique identifier for this setting (e.g., site_title)</small>
                        @error('key')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="group" class="form-label required">Group</label>
                        <div class="input-group">
                            <select class="form-select @error('group') is-invalid @enderror" id="existing_group"
                                onchange="if(this.value=='custom'){document.getElementById('group_container').style.display='block';document.getElementById('group').required=true;}else{document.getElementById('group_container').style.display='none';document.getElementById('group').value=this.value;}">
                                <option value="">Select or add new group</option>
                                @foreach ($groups as $group)
                                    <option value="{{ $group }}">{{ $group }}</option>
                                @endforeach
                                <option value="custom">Add new group</option>
                            </select>
                        </div>
                        <div id="group_container" style="display: none; margin-top: 10px;">
                            <input type="text" class="form-control @error('group') is-invalid @enderror" id="group"
                                name="group" value="{{ old('group') }}" placeholder="Enter custom group">
                        </div>
                        @error('group')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="type" class="form-label required">Type</label>
                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type"
                            required onchange="showValueField()">
                            <option value="">Select type</option>
                            @foreach ($types as $type)
                                <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                            @endforeach
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="description" class="form-label">Description</label>
                        <input type="text" class="form-control @error('description') is-invalid @enderror"
                            id="description" name="description" value="{{ old('description') }}">
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input" type="checkbox" id="is_visible" name="is_visible" checked>
                            <label class="form-check-label" for="is_visible">Visible in Settings Page</label>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input" type="checkbox" id="is_required" name="is_required" checked>
                            <label class="form-check-label" for="is_required">Required</label>
                        </div>
                    </div>
                </div>

                <!-- Dynamic value input based on type -->
                <div id="value_container" class="mt-3 mb-3" style="display: none;">
                    <h6 class="fw-semibold">Initial Value</h6>

                    <!-- Text input -->
                    <div id="value_text_container" class="mb-3" style="display: none;">
                        <label for="value_text" class="form-label">Value</label>
                        <input type="text" class="form-control" id="value_text" name="value_text"
                            value="{{ old('value_text') }}">
                    </div>

                    <!-- Textarea input -->
                    <div id="value_textarea_container" class="mb-3" style="display: none;">
                        <label for="value_textarea" class="form-label">Value</label>
                        <textarea class="form-control" id="value_textarea" name="value_textarea" rows="3">{{ old('value_textarea') }}</textarea>
                    </div>

                    <!-- Number input (integer) -->
                    <div id="value_integer_container" class="mb-3" style="display: none;">
                        <label for="value_integer" class="form-label">Value</label>
                        <input type="number" class="form-control" id="value_integer" name="value_integer"
                            value="{{ old('value_integer') }}">
                    </div>

                    <!-- Number input (float) -->
                    <div id="value_float_container" class="mb-3" style="display: none;">
                        <label for="value_float" class="form-label">Value</label>
                        <input type="number" step="0.01" class="form-control" id="value_float" name="value_float"
                            value="{{ old('value_float') }}">
                    </div>

                    <!-- Boolean input -->
                    <div id="value_boolean_container" class="mb-3" style="display: none;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="value_boolean" name="value_boolean"
                                {{ old('value_boolean') ? 'checked' : '' }}>
                            <label class="form-check-label" for="value_boolean">Enabled</label>
                        </div>
                    </div>

                    <!-- File input -->
                    <div id="value_file_container" class="mb-3" style="display: none;">
                        <label for="value_file" class="form-label">File</label>
                        <input type="file" class="form-control" id="value_file" name="value_file">
                    </div>

                    <!-- Image input -->
                    <div id="value_image_container" class="mb-3" style="display: none;">
                        <label for="value_image" class="form-label">Image</label>
                        <input type="file" class="form-control" id="value_image" name="value_image"
                            accept="image/*">
                    </div>

                    <!-- JSON input -->
                    <div id="value_json_container" class="mb-3" style="display: none;">
                        <label for="value_json" class="form-label">JSON Value</label>
                        <div id="json-editor" style="height: 400px; border: 1px solid #ddd;"></div>
                        <input type="hidden" id="value_json" name="value_json" value="{{ old('value_json', '{}') }}">
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-light" id="format-json">Format JSON</button>
                            <button type="button" class="btn btn-sm btn-light" id="validate-json">Validate</button>
                            <span id="json-validation-result" class="ms-2"></span>
                        </div>
                        <small class="text-muted">Use the editor to modify the JSON structure</small>
                    </div>
                </div>

                <!-- Options for select and multi-select -->
                <div id="options_container" class="mt-3 mb-3" style="display: none;">
                    <h6 class="fw-semibold">Options</h6>
                    <p class="text-muted">Add options for select/multi-select fields</p>

                    <div id="options_list">
                        <div class="row mb-2 option-row">
                            <div class="col-md-5">
                                <input type="text" class="form-control" name="option_keys[]"
                                    placeholder="Option Key">
                            </div>
                            <div class="col-md-5">
                                <input type="text" class="form-control" name="option_values[]"
                                    placeholder="Option Label">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-danger remove-option" onclick="removeOption(this)">
                                    <i class="ph-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="addOption()">
                        <i class="ph-plus"></i> Add Option
                    </button>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-primary">Create Setting</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.23.4/ace.js"></script>
    <script>
        function showValueField() {
            // Hide all value containers first
            document.querySelectorAll('[id^="value_"]').forEach(container => {
                if (container.id.includes('_container')) {
                    container.style.display = 'none';
                }
            });

            // Hide options container
            document.getElementById('options_container').style.display = 'none';

            const selectedType = document.getElementById('type').value;

            // Show the main value container
            document.getElementById('value_container').style.display = selectedType ? 'block' : 'none';

            // Show the specific value input based on type
            if (selectedType) {
                const valueContainer = document.getElementById(`value_${selectedType}_container`);
                if (valueContainer) {
                    valueContainer.style.display = 'block';
                }

                // Show options container for select and multi-select
                if (selectedType === 'select' || selectedType === 'multi-select') {
                    document.getElementById('options_container').style.display = 'block';
                }

                // Initialize JSON editor if selected
                if (selectedType === 'json' && !window.jsonEditorInitialized) {
                    initJsonEditor();
                }
            }
        }

        function initJsonEditor() {
            if (!document.getElementById('json-editor')) return;

            var editor = ace.edit("json-editor");
            editor.setTheme("ace/theme/xcode");
            editor.session.setMode("ace/mode/json");
            editor.setOptions({
                fontSize: "14px",
                showPrintMargin: false,
                enableBasicAutocompletion: true,
                enableLiveAutocompletion: true
            });

            // Initialize with the current value
            try {
                var jsonValue = document.getElementById('value_json').value || '{}';
                var prettyJson = JSON.stringify(JSON.parse(jsonValue), null, 2);
                editor.setValue(prettyJson, -1);
            } catch (e) {
                editor.setValue("{}", -1);
                console.error("Error parsing JSON:", e);
            }

            // Update hidden input when editor changes
            editor.getSession().on('change', function() {
                try {
                    // Parse to validate and then stringify to store
                    var jsonObj = JSON.parse(editor.getValue());
                    document.getElementById('value_json').value = JSON.stringify(jsonObj);
                    document.getElementById('json-validation-result').innerHTML =
                        '<span class="text-success">Valid JSON</span>';
                } catch (e) {
                    document.getElementById('json-validation-result').innerHTML =
                        '<span class="text-danger">Invalid JSON: ' + e.message + '</span>';
                }
            });

            // Format JSON button
            document.getElementById('format-json').addEventListener('click', function() {
                try {
                    var jsonObj = JSON.parse(editor.getValue());
                    var formatted = JSON.stringify(jsonObj, null, 2);
                    editor.setValue(formatted, -1);
                } catch (e) {
                    alert("Cannot format: " + e.message);
                }
            });

            // Validate JSON button
            document.getElementById('validate-json').addEventListener('click', function() {
                try {
                    JSON.parse(editor.getValue());
                    document.getElementById('json-validation-result').innerHTML =
                        '<span class="text-success">Valid JSON</span>';
                } catch (e) {
                    document.getElementById('json-validation-result').innerHTML =
                        '<span class="text-danger">Invalid JSON: ' + e.message + '</span>';
                }
            });

            window.jsonEditorInitialized = true;
        }

        function addOption() {
            const optionsList = document.getElementById('options_list');
            const newRow = document.createElement('div');
            newRow.className = 'row mb-2 option-row';
            newRow.innerHTML = `
            <div class="col-md-5">
                <input type="text" class="form-control" name="option_keys[]" placeholder="Option Key">
            </div>
            <div class="col-md-5">
                <input type="text" class="form-control" name="option_values[]" placeholder="Option Label">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger remove-option" onclick="removeOption(this)">
                    <i class="ph-trash"></i>
                </button>
            </div>
        `;
            optionsList.appendChild(newRow);
        }

        function removeOption(button) {
            const row = button.closest('.option-row');
            if (document.querySelectorAll('.option-row').length > 1) {
                row.remove();
            }
        }

        // Initialize JSON editor if needed
        document.addEventListener('DOMContentLoaded', function() {
            showValueField();
        });
    </script>
@endpush
