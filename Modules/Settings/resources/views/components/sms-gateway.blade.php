<div class="row" id="inputFieldsContainer_{{ $key }}">
    <!-- Test SMS Gateway (Input Mobile No: 8801000000000) -->
    <div class="col-md-12">
        <div class="form-control form-control-sm p-2 mb-2">
            <label for="mobileNo" class="form-label">Test SMS Gateway (Input Mobile No: 880100000000)</label>
            <input type="text" class="form-control" placeholder="88XXXXXXXXXX" id="mobileNo"/>
            <button type="button" class="btn btn-primary mt-2" id="sendTestSMS">
                Send Test SMS
            </button>
        </div>
    </div>
    <!-- Test SMS Gateway (Input Mobile No: 8801000000000) -->

    @if(is_array($value) && count($value) > 0)
        @foreach($value as $index => $gateway)
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">SMS Gateway</h4>
                        <i class="ph ph-trash text-danger cursor-pointer" onclick="removeGatewayInputField(this)"></i>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sms_gateways[{{ $index }}][TYPE]"
                                           class="form-label required">Type</label>
                                    <input type="text" name="sms_gateways[{{ $index }}][TYPE]" class="form-control"
                                           value="{{ $gateway['TYPE'] }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sms_gateways[{{ $index }}][VALUE][endpoint]"
                                           class="form-label required">Endpoint</label>
                                    <input type="text" name="sms_gateways[{{ $index }}][VALUE][endpoint]"
                                           class="form-control" value="{{ $gateway['VALUE']['endpoint'] ?? "" }}"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sms_gateways[{{ $index }}][VALUE][method]" class="form-label required">Method</label>
                                    <select name="sms_gateways[{{ $index }}][VALUE][method]" class="form-control select"
                                            required>
                                        <option
                                            value="GET" {{ (isset($gateway['VALUE']['method']) && $gateway['VALUE']['method'] === 'GET') ? 'selected' : '' }}>
                                            GET
                                        </option>
                                        <option
                                            value="POST" {{ (isset($gateway['VALUE']['method']) && $gateway['VALUE']['method'] === 'POST') ? 'selected' : '' }}>
                                            POST
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sms_gateways[{{ $index }}][VALUE][mobile_prefix]"
                                           class="form-label">Mobile Prefix</label>
                                    <input type="text" name="sms_gateways[{{ $index }}][VALUE][mobile_prefix]"
                                           class="form-control" value="{{ $gateway['VALUE']['mobile_prefix'] ?? "" }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sms_gateways[{{ $index }}][VALUE][mobile_key]"
                                           class="form-label required">Mobile Key</label>
                                    <input type="text" name="sms_gateways[{{ $index }}][VALUE][mobile_key]"
                                           class="form-control" value="{{ $gateway['VALUE']['mobile_key'] ?? ""  }}"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sms_gateways[{{ $index }}][VALUE][message_key]"
                                           class="form-label required">Message Key</label>
                                    <input type="text" name="sms_gateways[{{ $index }}][VALUE][message_key]"
                                           class="form-control" value="{{ $gateway['VALUE']['message_key'] ?? "" }}"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row" id="paramInputFieldsContainer_{{ $index }}">
                                    <label for="sms_gateways[{{ $index }}][VALUE][params]" class="form-label required">Params</label>
                                    @php
                                        $params = $gateway['VALUE']['params'] ?? [];
                                    @endphp
                                    @foreach($params as $paramKey => $param)
                                        <div class="input-group mb-2">
                                            <input type="hidden"
                                                   name="sms_gateways[{{ $index }}][VALUE][params][{{ $paramKey }}]"
                                                   class="form-control" value="{{ $param }}" required>
                                            <input type="text" class="form-control" value="{{ $paramKey }}" required
                                                   onkeyup="this.previousElementSibling.name = 'sms_gateways[{{ $index }}][VALUE][params][' + this.value + ']';">
                                            <input type="text" class="form-control" value="{{ $param }}" required
                                                   onkeyup="this.previousElementSibling.previousElementSibling.value = this.value">
                                            <button type="button" class="btn btn-danger"
                                                    onclick="removeParamInputField(this)">Delete
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" class="btn btn-sm btn-primary"
                                        :onclick="'addParamInputField(\'' . $index . '\')'">Add Param
                                </button>
                            </div>
                        </div>
                        @if($loop->last)
                            <button type="button" class="btn btn-sm btn-primary mt-2"
                                    :onclick="'addGatewayInputField(\'' . $key . '\', \'' . $index . '\')'">Add Gateway
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="col-md-12 mb-2 mt-2">
            <button type="button" class="btn btn-sm btn-primary"
                    :onclick="'addGatewayInputField(\'' . $key . '\', \'' . ($index ?? -1) . '\')'">Add Gateway
            </button>
        </div>
    @endif
</div>

@push('scripts')
    <script>
        document.getElementById('sendTestSMS').addEventListener('click', function () {
            const mobileNo = document.getElementById('mobileNo').value;
            if (mobileNo.length !== 13) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Mobile No',
                    text: 'Mobile No should be 14 characters long',
                    confirmButtonText: 'OK!',
                    customClass: {
                        confirmButton: 'btn btn-primary',
                    },
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
                    mobile_no: mobileNo
                },
                beforeSend: function () {
                    // show loader inside button
                    document.getElementById('sendTestSMS').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
                },
                success: function (response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                    });
                },
                error: function (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.responseJSON.message,
                    });
                },
                complete: function () {
                    // hide loader inside button
                    document.getElementById('sendTestSMS').innerHTML = 'Send Test SMS';
                }
            });
        });
    </script>

    <script>
        function addGatewayInputField(key, previousIndex) {
            const index = parseInt(previousIndex) + 1;
            const inputField = `
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">SMS Gateway</h4>
                        <i class="ph ph-trash text-danger cursor-pointer" onclick="removeGatewayInputField(this)"></i>
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
                                        <option value="GET" {{ (isset($gateway['VALUE']['method']) && $gateway['VALUE']['method'] === 'GET') ? 'selected' : '' }}>GET</option>
                                        <option value="POST" {{ (isset($gateway['VALUE']['method']) && $gateway['VALUE']['method'] === 'POST') ? 'selected' : '' }}>POST</option>
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
                            <div class="col-md-6">
                                <div class="row" id="paramInputFieldsContainer_${index}">
                                    <label for="sms_gateways[${index}][VALUE][params]" class="form-label required">Params</label>
                                </div>
                                <button type="button" class="btn btn-sm btn-primary" onclick="addParamInputField('${index}')">Add Param</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
            document.getElementById(`inputFieldsContainer_${key}`).insertAdjacentHTML('beforeend', inputField);
        }

        function removeGatewayInputField(element) {
            element.closest('.col-md-12').remove();
        }

        function addParamInputField(index) {
            const inputField = `
            <div class="input-group mb-2">
                <input type="hidden" name="sms_gateways[${index}][VALUE][params][key]" class="form-control" value="" required>
                <input type="text" class="form-control" required onkeyup="this.previousElementSibling.name = 'sms_gateways[${index}][VALUE][params][' + this.value + ']';">
                <input type="text" class="form-control" required onkeyup="this.previousElementSibling.previousElementSibling.value = this.value">
                <button type="button" class="btn btn-danger" onclick="removeParamInputField(this)">Delete</button>
            </div>`;
            document.getElementById(`paramInputFieldsContainer_${index}`).insertAdjacentHTML('beforeend', inputField);
        }

        function removeParamInputField(element) {
            element.closest('.input-group').remove();
        }
    </script>
@endpush
