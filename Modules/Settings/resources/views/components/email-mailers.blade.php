<div class="row" id="emailInputFieldsContainer_{{ $key }}">
    <!-- Test Email Gateway (Input Email Address) -->
    <div class="col-md-12">
        <div class="form-control form-control-sm p-2 mb-2">
            <label for="email_address" class="form-label required">Email Address</label>
            <input type="email" id="email_address" name="email_address" placeholder="Enter email address"
                   class="form-control">
            <button type="button" id="sendEmail" class="btn btn-sm btn-primary mt-2">Send Test Email</button>
        </div>
    </div>
    <!-- /Test Email Gateway (Input Email Address) -->

    @if(is_array($value) && count($value) > 0)
        @foreach($value as $index => $mailer)
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Email Mailer</h4>
                        <i class="ph ph-trash text-danger cursor-pointer" onclick="removeEmailInputField(this)"></i>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email_mailers[{{ $index }}][TYPE]"
                                           class="form-label required">Type</label>
                                    <input type="text" name="email_mailers[{{ $index }}][TYPE]" class="form-control"
                                           value="{{ $mailer['TYPE'] }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email_mailers[{{ $index }}][VALUE][transport]"
                                           class="form-label required">Mailer Type (smtp)</label>
                                    <input type="text" name="email_mailers[{{ $index }}][VALUE][transport]"
                                           class="form-control" value="{{ $mailer['VALUE']['transport'] ?? '' }}"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email_mailers[{{ $index }}][VALUE][host]"
                                           class="form-label required">Host/IP</label>
                                    <input type="text" name="email_mailers[{{ $index }}][VALUE][host]"
                                           class="form-control" value="{{ $mailer['VALUE']['host'] ?? "" }}"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email_mailers[{{ $index }}][VALUE][port]" class="form-label required">Port</label>
                                    <input type="number" name="email_mailers[{{ $index }}][VALUE][port]"
                                           class="form-control"
                                           value="{{ $mailer['VALUE']['port'] ?? "" }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email_mailers[{{ $index }}][VALUE][encryption]"
                                           class="form-label">Encryption</label>
                                    <input type="text" name="email_mailers[{{ $index }}][VALUE][encryption]"
                                           class="form-control" value="{{ $mailer['VALUE']['encryption'] ?? ""  }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email_mailers[{{ $index }}][VALUE][username]"
                                           class="form-label">Username</label>
                                    <input type="text" name="email_mailers[{{ $index }}][VALUE][username]"
                                           class="form-control" value="{{ $mailer['VALUE']['username'] ?? "" }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email_mailers[{{ $index }}][VALUE][password]"
                                           class="form-label">Password</label>
                                    <input type="password" name="email_mailers[{{ $index }}][VALUE][password]"
                                           class="form-control" value="{{ $mailer['VALUE']['password'] ?? "" }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email_mailers[{{ $index }}][VALUE][from][address]"
                                           class="form-label required">From Address</label>
                                    <input type="email" name="email_mailers[{{ $index }}][VALUE][from][address]"
                                           class="form-control" value="{{ $mailer['VALUE']['from']['address'] ?? "" }}"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email_mailers[{{ $index }}][VALUE][from][name]"
                                           class="form-label required">From Name</label>
                                    <input type="text" name="email_mailers[{{ $index }}][VALUE][from][name]"
                                           class="form-control" value="{{ $mailer['VALUE']['from']['name'] ?? "" }}"
                                           required>
                                </div>
                            </div>
                        </div>
                        @if($loop->last)
                            <button type="button" class="btn btn-sm btn-primary"
                                    :onclick="'addEmailInputField(\'' . $key . '\', \'' . $index . '\')'">Add Mailer
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="col-md-12 mb-2">
            <button type="button" class="btn btn-sm btn-primary"
                    :onclick="'addEmailInputField(\'' . $key . '\', \'' . ($index ?? -1) . '\')'">Add Mailer
            </button>
        </div>
    @endif
</div>

@push('scripts')
    <script>
        document.getElementById('sendEmail').addEventListener('click', function () {
            const email = document.getElementById('email_address').value;
            // check if email using regex
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Invalid email address',
                    confirmButtonText: 'OK!',
                    customClass: {
                        confirmButton: 'btn btn-primary',
                    },
                });
                return;
            }

            $.ajax({
                url: '{{ route("special_settings.send_test_email") }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data: {
                    email: email
                },
                beforeSend: function () {
                    // show loader inside button
                    document.getElementById('sendEmail').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
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
                    document.getElementById('sendEmail').innerHTML = 'Send Test Email';
                }
            });
        });
    </script>
    <script>
        function addEmailInputField(key, previousIndex) {
            const index = parseInt(previousIndex) + 1;
            const inputField = `
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Email Mailer</h4>
                        <i class="ph ph-trash text-danger cursor-pointer" onclick="removeEmailInputField(this)"></i>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email_mailers[${index}][TYPE]" class="form-label required">Type</label>
                                    <input type="text" name="email_mailers[${index}][TYPE]" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email_mailers[${index}][VALUE][transport]" class="form-label required">Mailer Type (smtp)</label>
                                    <input type="text" name="email_mailers[${index}][VALUE][transport]" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email_mailers[${index}][VALUE][host]" class="form-label required">Host/IP</label>
                                    <input type="text" name="email_mailers[${index}][VALUE][host]" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email_mailers[${index}][VALUE][port]" class="form-label required">Port</label>
                                    <input type="number" name="email_mailers[${index}][VALUE][port]" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email_mailers[${index}][VALUE][encryption]" class="form-label">Encryption</label>
                                    <input type="text" name="email_mailers[${index}][VALUE][encryption]" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email_mailers[${index}][VALUE][username]" class="form-label">Username</label>
                                    <input type="text" name="email_mailers[${index}][VALUE][username]" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email_mailers[${index}][VALUE][password]" class="form-label">Password</label>
                                    <input type="password" name="email_mailers[${index}][VALUE][password]" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email_mailers[${index}][VALUE][from][address]" class="form-label required">From Address</label>
                                    <input type="email" name="email_mailers[${index}][VALUE][from][address]" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email_mailers[${index}][VALUE][from][name]" class="form-label required">From Name</label>
                                    <input type="text" name="email_mailers[${index}][VALUE][from][name]" class="form-control" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
            document.getElementById(`emailInputFieldsContainer_${key}`).insertAdjacentHTML('beforeend', inputField);
        }

        function removeEmailInputField(element) {
            element.closest('.col-md-12').remove();
        }
    </script>
@endpush
