@extends('settings::layouts.master')
@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Edit Setting: {{ $setting->key }}</h5>
            <a href="{{ route('system_settings.manage') }}" class="btn btn-secondary">
                <i class="ph-arrow-left me-1"></i>
                Back to Settings
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('system_settings.update', $setting) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="key" class="form-label required">Key</label>
                        <input type="text" class="form-control" id="key" value="{{ $setting->key }}" readonly
                            disabled>
                        <small class="text-muted">The key cannot be changed</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="group" class="form-label required">Group</label>
                        <div class="input-group">
                            <select class="form-select @error('group') is-invalid @enderror" id="existing_group"
                                onchange="if(this.value=='custom'){document.getElementById('group_container').style.display='block';document.getElementById('group').required=true;}else{document.getElementById('group_container').style.display='none';document.getElementById('group').value=this.value;}">
                                <option value="">Select or add new group</option>
                                @foreach ($groups as $group)
                                    <option value="{{ $group }}" {{ $setting->group === $group ? 'selected' : '' }}>
                                        {{ $group }}</option>
                                @endforeach
                                <option value="custom">Add new group</option>
                            </select>
                        </div>
                        <div id="group_container" style="display: none; margin-top: 10px;">
                            <input type="text" class="form-control @error('group') is-invalid @enderror" id="group"
                                name="group" value="{{ old('group', $setting->group) }}" placeholder="Enter custom group">
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
                                <option value="{{ $type }}" {{ $setting->type === $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}</option>
                            @endforeach
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="description" class="form-label">Description</label>
                        <input type="text" class="form-control @error('description') is-invalid @enderror"
                            id="description" name="description" value="{{ old('description', $setting->description) }}">
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input" type="checkbox" id="is_visible" name="is_visible"
                                {{ $setting->is_visible ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_visible">Visible in Settings Page</label>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input" type="checkbox" id="is_required" name="is_required"
                                {{ $setting->is_required ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_required">Required</label>
                        </div>
                    </div>
                </div>

                <!-- Dynamic value input based on type -->
                <div id="value_container" class="mt-3 mb-3">
                    <h6 class="fw-semibold">Current Value</h6>

                    <!-- Text input -->
                    <div id="value_text_container" class="mb-3"
                        style="display: {{ $setting->type === 'text' ? 'block' : 'none' }};">
                        <label for="value_text" class="form-label">Value</label>
                        <input type="text" class="form-control" id="value_text" name="value_text"
                            value="{{ old('value_text', $setting->type === 'text' ? $setting->value : '') }}">
                    </div>

                    <!-- Textarea input -->
                    <div id="value_textarea_container" class="mb-3"
                        style="display: {{ $setting->type === 'textarea' ? 'block' : 'none' }};">
                        <label for="value_textarea" class="form-label">Value</label>
                        <textarea class="form-control" id="value_textarea" name="value_textarea" rows="3">{{ old('value_textarea', $setting->type === 'textarea' ? $setting->value : '') }}</textarea>
                    </div>

                    <!-- Number input (integer) -->
                    <div id="value_integer_container" class="mb-3"
                        style="display: {{ $setting->type === 'integer' ? 'block' : 'none' }};">
                        <label for="value_integer" class="form-label">Value</label>
                        <input type="number" class="form-control" id="value_integer" name="value_integer"
                            value="{{ old('value_integer', $setting->type === 'integer' ? $setting->value : '') }}">
                    </div>

                    <!-- Number input (float) -->
                    <div id="value_float_container" class="mb-3"
                        style="display: {{ $setting->type === 'float' ? 'block' : 'none' }};">
                        <label for="value_float" class="form-label">Value</label>
                        <input type="number" step="0.01" class="form-control" id="value_float" name="value_float"
                            value="{{ old('value_float', $setting->type === 'float' ? $setting->value : '') }}">
                    </div>

                    <!-- Boolean input -->
                    <div id="value_boolean_container" class="mb-3"
                        style="display: {{ $setting->type === 'boolean' ? 'block' : 'none' }};">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="value_boolean" name="value_boolean"
                                {{ $setting->type === 'boolean' && $setting->value ? 'checked' : '' }}>
                            <label class="form-check-label" for="value_boolean">Enabled</label>
                        </div>
                    </div>

                    <!-- File input -->
                    <div id="value_file_container" class="mb-3"
                        style="display: {{ $setting->type === 'file' ? 'block' : 'none' }};">
                        <label for="value_file" class="form-label">File</label>
                        @if ($setting->type === 'file' && $setting->value)
                            <div class="mb-2">
                                <a href="{{ $setting->value }}" target="_blank">Current File</a>
                            </div>
                        @endif
                        <input type="file" class="form-control" id="value_file" name="value_file">
                        <small class="text-muted">Leave empty to keep current file</small>
                    </div>

                    <!-- Image input -->
                    <div id="value_image_container" class="mb-3"
                        style="display: {{ $setting->type === 'image' ? 'block' : 'none' }};">
                        <label for="value_image" class="form-label">Image</label>
                        @if ($setting->type === 'image' && $setting->value)
                            <div class="mb-2">
                                <img src="{{ $setting->value }}" alt="{{ $setting->key }}" style="max-width: 200px;"
                                    class="img-thumbnail">
                            </div>
                        @endif
                        <input type="file" class="form-control" id="value_image" name="value_image"
                            accept="image/*">
                        <small class="text-muted">Leave empty to keep current image</small>
                    </div>

                    <!-- JSON input -->
                    <div id="value_json_container" class="mb-3"
                        style="display: {{ $setting->type === 'json' ? 'block' : 'none' }};">
                        <label for="value_json" class="form-label">JSON Value</label>
                        <div id="json-editor" style="height: 400px; border: 1px solid #ddd;"></div>
                        <input type="hidden" id="value_json" name="value_json"
                            value="{{ old('value_json', $setting->type === 'json' ? (is_string($setting->value) ? $setting->value : json_encode($setting->value)) : '{}') }}">
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-light" id="format-json">Format JSON</button>
                            <button type="button" class="btn btn-sm btn-light" id="validate-json">Validate</button>
                            <span id="json-validation-result" class="ms-2"></span>
                        </div>
                        <small class="text-muted">Use the editor to modify the JSON structure</small>
                    </div>

                    <!-- Array input -->
                    <div id="value_array_container" class="mb-3"
                        style="display: {{ $setting->type === 'array' ? 'block' : 'none' }};">
                        <label class="form-label">Array Values</label>
                        <div id="array_values_container">
                            @if ($setting->type === 'array' && is_array($setting->value))
                                @foreach ($setting->value as $index => $value)
                                    <div class="input-group mb-2 array-value-row">
                                        <input type="text" class="form-control" name="value_array[]"
                                            value="{{ $value }}">
                                        <button type="button" class="btn btn-danger" onclick="removeArrayValue(this)">
                                            <i class="ph-trash"></i>
                                        </button>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="addArrayValue()">
                            <i class="ph-plus"></i> Add Value
                        </button>
                    </div>
                </div>

                <!-- Options for select and multi-select -->
                <div id="options_container" class="mt-3 mb-3"
                    style="display: {{ in_array($setting->type, ['select', 'multi-select']) ? 'block' : 'none' }};">
                    <h6 class="fw-semibold">Options</h6>
                    <p class="text-muted">Add options for select/multi-select fields</p>

                    <div id="options_list">
                        @if (in_array($setting->type, ['select', 'multi-select']) && is_array($setting->options))
                            @foreach ($setting->options as $key => $value)
                                <div class="row mb-2 option-row">
                                    <div class="col-md-5">
                                        <input type="text" class="form-control" name="option_keys[]"
                                            value="{{ $key }}" placeholder="Option Key">
                                    </div>
                                    <div class="col-md-5">
                                        <input type="text" class="form-control" name="option_values[]"
                                            value="{{ $value }}" placeholder="Option Label">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger remove-option"
                                            onclick="removeOption(this)">
                                            <i class="ph-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @else
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
                                    <button type="button" class="btn btn-danger remove-option"
                                        onclick="removeOption(this)">
                                        <i class="ph-trash"></i>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>

                    <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="addOption()">
                        <i class="ph-plus"></i> Add Option
                    </button>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-primary">Update Setting</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.23.4/ace.js"></script>
    <script>
        // Set initial group value from existing selection
        document.addEventListener('DOMContentLoaded', function() {
            const existingGroup = document.getElementById('existing_group');
            const groupContainer = document.getElementById('group_container');
            const groupInput = document.getElementById('group');

            // If group is not in the dropdown options, show the custom input
            let groupFound = false;
            for (let i = 0; i < existingGroup.options.length; i++) {
                if (existingGroup.options[i].value === '{{ $setting->group }}') {
                    groupFound = true;
                    break;
                }
            }

            if (!groupFound && '{{ $setting->group }}') {
                existingGroup.value = 'custom';
                groupContainer.style.display = 'block';
                groupInput.value = '{{ $setting->group }}';
            } else {
                groupInput.value = existingGroup.value;
            }
        });

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
            }
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

        function addArrayValue() {
            const container = document.getElementById('array_values_container');
            const newRow = document.createElement('div');
            newRow.className = 'input-group mb-2 array-value-row';
            newRow.innerHTML = `
            <input type="text" class="form-control" name="value_array[]" value="">
            <button type="button" class="btn btn-danger" onclick="removeArrayValue(this)">
                <i class="ph-trash"></i>
            </button>
        `;
            container.appendChild(newRow);
        }

        function removeArrayValue(button) {
            const row = button.closest('.array-value-row');
            row.remove();
        }

        // Setup JSON editor with Ace
        if (document.getElementById('json-editor')) {
            var editor = ace.edit("json-editor");
            editor.setTheme("ace/theme/xcode");
            editor.session.setMode("ace/mode/json");
            editor.setOptions({
                fontSize: "14px",
                showPrintMargin: false,
                enableBasicAutocompletion: true,
                enableLiveAutocompletion: true,
                tabSize: 2
            });

            // Initialize with the current value
            try {
                var jsonValue = document.getElementById('value_json').value;

                // Handle empty or undefined values
                if (!jsonValue || jsonValue === "null" || jsonValue === "undefined") {
                    jsonValue = "{}";
                }

                // Try to parse and format the JSON
                var jsonObj = JSON.parse(jsonValue);
                var prettyJson = JSON.stringify(jsonObj, null, 2);
                editor.setValue(prettyJson, -1);
                document.getElementById('json-validation-result').innerHTML =
                    '<span class="text-success">Valid JSON</span>';
            } catch (e) {
                console.error("Error parsing JSON:", e, "Value was:", jsonValue);
                // Set a default value if parsing fails
                editor.setValue("{\n  \"error\": \"Could not parse original JSON. Please edit carefully.\"\n}", -1);
                document.getElementById('json-validation-result').innerHTML =
                    '<span class="text-danger">Warning: Original JSON was invalid. Starting with empty object.</span>';
            }

            // Make sure form submissions capture the latest editor content
            document.querySelector('form').addEventListener('submit', function(e) {
                try {
                    var jsonObj = JSON.parse(editor.getValue());
                    document.getElementById('value_json').value = JSON.stringify(jsonObj);
                } catch (e) {
                    // Prevent form submission if JSON is invalid
                    e.preventDefault();
                    alert("Cannot submit form: JSON is invalid. Please fix the JSON structure before submitting.");
                    document.getElementById('json-validation-result').innerHTML =
                        '<span class="text-danger">Invalid JSON: ' + e.message + '</span>';
                }
            });

            // Update hidden input when editor changes, but handle errors gracefully
            var updateTimeout;
            editor.getSession().on('change', function() {
                // Clear previous timeout to prevent multiple rapid updates
                clearTimeout(updateTimeout);

                // Set a small timeout to avoid updating on every keystroke
                updateTimeout = setTimeout(function() {
                    try {
                        // Parse to validate and then stringify to store
                        var jsonObj = JSON.parse(editor.getValue());
                        document.getElementById('value_json').value = JSON.stringify(jsonObj);
                        document.getElementById('json-validation-result').innerHTML =
                            '<span class="text-success">Valid JSON</span>';
                    } catch (e) {
                        // Don't update the hidden field if JSON is invalid
                        document.getElementById('json-validation-result').innerHTML =
                            '<span class="text-danger">Invalid JSON: ' + e.message + '</span>';
                    }
                }, 300);
            });

            // Format JSON button with improved error handling
            document.getElementById('format-json').addEventListener('click', function() {
                try {
                    var jsonObj = JSON.parse(editor.getValue());
                    var formatted = JSON.stringify(jsonObj, null, 2);
                    editor.setValue(formatted, -1);
                    document.getElementById('json-validation-result').innerHTML =
                        '<span class="text-success">JSON formatted successfully</span>';
                } catch (e) {
                    document.getElementById('json-validation-result').innerHTML =
                        '<span class="text-danger">Cannot format: ' + e.message + '</span>';
                }
            });

            // Validate JSON button with more detailed feedback
            document.getElementById('validate-json').addEventListener('click', function() {
                try {
                    var jsonObj = JSON.parse(editor.getValue());
                    var keyCount = Object.keys(jsonObj).length;
                    document.getElementById('json-validation-result').innerHTML =
                        '<span class="text-success">Valid JSON with ' + keyCount + ' top-level keys</span>';
                } catch (e) {
                    document.getElementById('json-validation-result').innerHTML =
                        '<span class="text-danger">Invalid JSON: ' + e.message + '</span>';
                }
            });
        }
    </script>
@endpush
