@extends('settings::layouts.master')
@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">SMS Gateways Settings</h5>
        </div>
        <div class="card-body">
            <!-- Information Alert about SMS Gateways -->
            <div class="alert alert-info mb-4">
                <h5><i class="ph ph-info me-2"></i>How to Configure SMS Gateways</h5>
                <p>Configure your SMS gateway providers here. You can set up multiple gateways and switch between them as
                    needed.</p>
                <ul class="mb-0">
                    <li><strong>Endpoint:</strong> The API URL provided by your SMS service provider.</li>
                    <li><strong>Method:</strong> Choose GET or POST based on your provider's requirements.</li>
                    <li><strong>Mobile Key:</strong> The parameter name used to send the recipient's phone number.</li>
                    <li><strong>Message Key:</strong> The parameter name used to send the SMS text content.</li>
                    <li><strong>Headers:</strong> Add HTTP headers required by your provider (e.g., Authorization,
                        Content-Type).</li>
                    <li><strong>Parameters:</strong> Add any additional parameters required by your SMS provider.</li>
                </ul>
            </div>

            <!-- Basic Auth Example -->
            <div class="alert alert-secondary mb-4">
                <h5><i class="ph ph-key me-2"></i>Example: Adding Basic Authentication to Headers</h5>
                <p>To add Basic Authentication to your SMS gateway:</p>
                <ol>
                    <li>In the Headers section, add a new header with key <code>Authorization</code></li>
                    <li>For the value, enter <code>Basic</code> followed by a space and your base64-encoded credentials.
                    </li>
                    <li>Format: <code>Basic base64(username:password)</code></li>
                </ol>
                <p class="mb-0"><strong>Example:</strong> If your username is "apiuser" and password is "secret123", add a
                    header with:</p>
                <ul class="mb-0">
                    <li>Key: <code>Authorization</code></li>
                    <li>Value: <code>Basic YXBpdXNlcjpzZWNyZXQxMjM=</code> (where YXBpdXNlcjpzZWNyZXQxMjM= is base64 of
                        "apiuser:secret123")</li>
                </ul>
                <p class="mt-2">You can generate base64 encoding online or use this <a
                        href="https://www.base64encode.org/" target="_blank">Base64 Encoder</a>.</p>
            </div>

            <form action="{{ route('special_settings.update_sms_gateways') }}" method="POST">
                @csrf

                <!-- Test SMS Gateway (Input Mobile Number) -->
                <div class="mb-4">
                    <div class="form-control form-control-sm p-3 mb-2">
                        <h6 class="mb-2">Test SMS Gateway</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <label for="mobile_no" class="form-label required">Mobile Number</label>
                                <input type="text" id="mobile_no" name="mobile_no" placeholder="Enter mobile number"
                                    class="form-control">
                            </div>
                            <div class="col-md-2 mt-4">
                                <button type="button" id="sendSms" class="btn btn-sm btn-primary mt-1">Send Test
                                    SMS</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Select SMS Gateway -->
                <div class="mb-4">
                    <label for="sms_gateway" class="form-label required">Selected SMS Gateway</label>
                    <select name="sms_gateway" id="sms_gateway" class="form-control">
                        @if (is_array($smsGateways->value))
                            @foreach ($smsGateways->value as $gateway)
                                <option value="{{ $gateway['TYPE'] }}"
                                    {{ $smsGateway->value === $gateway['TYPE'] ? 'selected' : '' }}>
                                    {{ $gateway['TYPE'] }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div id="smsInputFieldsContainer">
                    @if (is_array($smsGateways->value) && count($smsGateways->value) > 0)
                        @foreach ($smsGateways->value as $index => $gateway)
                            <div class="col-md-12 mb-3">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Gateway Configuration: {{ $gateway['TYPE'] }}</h6>
                                        <i class="ph ph-trash text-danger cursor-pointer"
                                            onclick="removeSmsGateway(this)"></i>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="sms_gateways[{{ $index }}][TYPE]"
                                                        class="form-label required">Type</label>
                                                    <input type="text" name="sms_gateways[{{ $index }}][TYPE]"
                                                        class="form-control" value="{{ $gateway['TYPE'] }}" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="sms_gateways[{{ $index }}][VALUE][endpoint]"
                                                        class="form-label required">Endpoint</label>
                                                    <input type="text"
                                                        name="sms_gateways[{{ $index }}][VALUE][endpoint]"
                                                        class="form-control"
                                                        value="{{ $gateway['VALUE']['endpoint'] ?? '' }}" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="sms_gateways[{{ $index }}][VALUE][method]"
                                                        class="form-label required">Method</label>
                                                    <select name="sms_gateways[{{ $index }}][VALUE][method]"
                                                        class="form-control select" required>
                                                        <option value="GET"
                                                            {{ isset($gateway['VALUE']['method']) && $gateway['VALUE']['method'] === 'GET' ? 'selected' : '' }}>
                                                            GET
                                                        </option>
                                                        <option value="POST"
                                                            {{ isset($gateway['VALUE']['method']) && $gateway['VALUE']['method'] === 'POST' ? 'selected' : '' }}>
                                                            POST
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="sms_gateways[{{ $index }}][VALUE][mobile_prefix]"
                                                        class="form-label">Mobile Prefix</label>
                                                    <input type="text"
                                                        name="sms_gateways[{{ $index }}][VALUE][mobile_prefix]"
                                                        class="form-control"
                                                        value="{{ $gateway['VALUE']['mobile_prefix'] ?? '' }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="sms_gateways[{{ $index }}][VALUE][mobile_key]"
                                                        class="form-label required">Mobile Key</label>
                                                    <input type="text"
                                                        name="sms_gateways[{{ $index }}][VALUE][mobile_key]"
                                                        class="form-control"
                                                        value="{{ $gateway['VALUE']['mobile_key'] ?? '' }}" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="sms_gateways[{{ $index }}][VALUE][message_key]"
                                                        class="form-label required">Message Key</label>
                                                    <input type="text"
                                                        name="sms_gateways[{{ $index }}][VALUE][message_key]"
                                                        class="form-control"
                                                        value="{{ $gateway['VALUE']['message_key'] ?? '' }}" required>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Headers -->
                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <h6 class="mb-2">Headers <i class="ph ph-question text-muted"
                                                        data-bs-toggle="tooltip"
                                                        title="Add HTTP headers required by your SMS provider. Common examples include: 'Authorization', 'Content-Type', 'api-key', etc."></i>
                                                </h6>
                                                <div class="headers-container">
                                                    @if (isset($gateway['VALUE']['headers']) && is_array($gateway['VALUE']['headers']))
                                                        @foreach ($gateway['VALUE']['headers'] as $headerKey => $headerValue)
                                                            <div class="row mb-2 header-row">
                                                                <div class="col-md-5">
                                                                    <input type="text" class="form-control"
                                                                        name="sms_gateways[{{ $index }}][VALUE][headers][keys][]"
                                                                        value="{{ $headerKey }}"
                                                                        placeholder="Header Key (e.g., Authorization)">
                                                                </div>
                                                                <div class="col-md-5">
                                                                    <input type="text" class="form-control"
                                                                        name="sms_gateways[{{ $index }}][VALUE][headers][values][]"
                                                                        value="{{ is_array($headerValue) ? json_encode($headerValue) : $headerValue }}"
                                                                        placeholder="Header Value (e.g., Basic YXBpOmtleQ==)">
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <button type="button"
                                                                        class="btn btn-danger remove-header"
                                                                        onclick="removeHeader(this)">
                                                                        <i class="ph-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                                <button type="button" class="btn btn-sm btn-secondary mt-2"
                                                    :onclick="'addHeader(this, ' . $index . ')'">
                                                    <i class="ph-plus"></i> Add Header
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Additional Parameters -->
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h6 class="mb-2">Additional Parameters</h6>
                                                <div class="additional-params-container">
                                                    @if (isset($gateway['VALUE']['params']) && is_array($gateway['VALUE']['params']))
                                                        @foreach ($gateway['VALUE']['params'] as $paramKey => $paramValue)
                                                            <div class="row mb-2 param-row">
                                                                <div class="col-md-5">
                                                                    <input type="text" class="form-control"
                                                                        name="sms_gateways[{{ $index }}][VALUE][params][keys][]"
                                                                        value="{{ $paramKey }}"
                                                                        placeholder="Parameter Key">
                                                                </div>
                                                                <div class="col-md-5">
                                                                    <input type="text" class="form-control"
                                                                        name="sms_gateways[{{ $index }}][VALUE][params][values][]"
                                                                        value="{{ is_array($paramValue) ? json_encode($paramValue) : $paramValue }}"
                                                                        placeholder="Parameter Value">
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <button type="button"
                                                                        class="btn btn-danger remove-param"
                                                                        onclick="removeParam(this)">
                                                                        <i class="ph-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                                <button type="button" class="btn btn-sm btn-secondary mt-2"
                                                    :onclick="'addParam(this, ' . $index . ')'">
                                                    <i class="ph-plus"></i> Add Parameter
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <div class="mb-3">
                    <button type="button" class="btn btn-primary" onclick="addSmsGateway()">
                        <i class="ph-plus"></i> Add New SMS Gateway
                    </button>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-primary">Update SMS Gateways</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        // Send test SMS
        document.getElementById('sendSms').addEventListener('click', function() {
            const mobile = document.getElementById('mobile_no').value;
            if (!mobile) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Please enter a mobile number',
                });
                return;
            }

            $.ajax({
                url: '{{ route("special_settings.send_test_sms") }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data: {
                    mobile_no: mobile
                },
                beforeSend: function() {
                    document.getElementById('sendSms').innerHTML =
                        '<i class="fas fa-spinner fa-spin"></i> Sending...';
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                    });
                },
                error: function(error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.responseJSON ? error.responseJSON.message :
                            'An error occurred',
                    });
                },
                complete: function() {
                    document.getElementById('sendSms').innerHTML = 'Send Test SMS';
                }
            });
        });

        // Add new SMS gateway
        function addSmsGateway() {
            const container = document.getElementById('smsInputFieldsContainer');
            const index = document.querySelectorAll('.card').length;

            const template = `
            <div class="col-md-12 mb-3">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">New Gateway Configuration</h6>
                        <i class="ph ph-trash text-danger cursor-pointer" onclick="removeSmsGateway(this)"></i>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sms_gateways[${index}][TYPE]" class="form-label required">Type</label>
                                    <input type="text" name="sms_gateways[${index}][TYPE]" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sms_gateways[${index}][VALUE][endpoint]" class="form-label required">Endpoint</label>
                                    <input type="text" name="sms_gateways[${index}][VALUE][endpoint]" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sms_gateways[${index}][VALUE][method]" class="form-label required">Method</label>
                                    <select name="sms_gateways[${index}][VALUE][method]" class="form-control select" required>
                                        <option value="GET">GET</option>
                                        <option value="POST" selected>POST</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sms_gateways[${index}][VALUE][mobile_prefix]" class="form-label">Mobile Prefix</label>
                                    <input type="text" name="sms_gateways[${index}][VALUE][mobile_prefix]" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sms_gateways[${index}][VALUE][mobile_key]" class="form-label required">Mobile Key</label>
                                    <input type="text" name="sms_gateways[${index}][VALUE][mobile_key]" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sms_gateways[${index}][VALUE][message_key]" class="form-label required">Message Key</label>
                                    <input type="text" name="sms_gateways[${index}][VALUE][message_key]" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <!-- Headers -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <h6 class="mb-2">Headers <i class="ph ph-question text-muted" data-bs-toggle="tooltip" title="Add HTTP headers required by your SMS provider. Common examples include: 'Authorization', 'Content-Type', 'api-key', etc."></i></h6>
                                <div class="headers-container">
                                </div>
                                <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="addHeader(this, ${index})">
                                    <i class="ph-plus"></i> Add Header
                                </button>
                            </div>
                        </div>

                        <!-- Additional Parameters -->
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="mb-2">Additional Parameters</h6>
                                <div class="additional-params-container">
                                </div>
                                <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="addParam(this, ${index})">
                                    <i class="ph-plus"></i> Add Parameter
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;

            container.insertAdjacentHTML('beforeend', template);

            // Re-initialize tooltips for newly added elements
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        // Remove SMS gateway
        function removeSmsGateway(element) {
            if (confirm('Are you sure you want to remove this gateway?')) {
                element.closest('.col-md-12').remove();
            }
        }

        // Add parameter
        function addParam(button, gatewayIndex) {
            const container = button.previousElementSibling;
            const paramRow = `
            <div class="row mb-2 param-row">
                <div class="col-md-5">
                    <input type="text" class="form-control" name="sms_gateways[${gatewayIndex}][VALUE][params][keys][]" placeholder="Parameter Key">
                </div>
                <div class="col-md-5">
                    <input type="text" class="form-control" name="sms_gateways[${gatewayIndex}][VALUE][params][values][]" placeholder="Parameter Value">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger remove-param" onclick="removeParam(this)">
                        <i class="ph-trash"></i>
                    </button>
                </div>
            </div>`;

            container.insertAdjacentHTML('beforeend', paramRow);
        }

        // Remove parameter
        function removeParam(button) {
            button.closest('.param-row').remove();
        }

        // Add header
        function addHeader(button, gatewayIndex) {
            const container = button.previousElementSibling;
            const headerRow = `
            <div class="row mb-2 header-row">
                <div class="col-md-5">
                    <input type="text" class="form-control" name="sms_gateways[${gatewayIndex}][VALUE][headers][keys][]" placeholder="Header Key (e.g., Authorization)">
                </div>
                <div class="col-md-5">
                    <input type="text" class="form-control" name="sms_gateways[${gatewayIndex}][VALUE][headers][values][]" placeholder="Header Value (e.g., Basic YXBpOmtleQ==)">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger remove-header" onclick="removeHeader(this)">
                        <i class="ph-trash"></i>
                    </button>
                </div>
            </div>`;

            container.insertAdjacentHTML('beforeend', headerRow);
        }

        // Remove header
        function removeHeader(button) {
            button.closest('.header-row').remove();
        }
    </script>
@endpush
