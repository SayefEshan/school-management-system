@extends('settings::layouts.master')
@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Email Mailers Settings</h5>
        </div>
        <div class="card-body">
            <!-- Information Alert about Email Mailers -->
            <div class="alert alert-info mb-4">
                <h5><i class="ph ph-info me-2"></i>How to Configure Email Mailers</h5>
                <p>Configure your email mailer providers here. You can set up multiple mailers and switch between them as
                    needed.</p>
                <ul class="mb-0">
                    <li><strong>Type:</strong> A unique name for identifying this mailer configuration.</li>
                    <li><strong>Transport:</strong> Usually "smtp" for standard email servers.</li>
                    <li><strong>Host/IP:</strong> The SMTP server address (e.g., smtp.gmail.com, smtp.office365.com).</li>
                    <li><strong>Port:</strong> Common SMTP ports are 587 (TLS), 465 (SSL), or 25 (unencrypted).</li>
                    <li><strong>Encryption:</strong> Usually "tls" or "ssl" for secure connections.</li>
                    <li><strong>From Address/Name:</strong> The email address and name that will appear as the sender.</li>
                </ul>
            </div>

            <!-- Common Email Providers Example -->
            <div class="alert alert-secondary mb-4">
                <h5><i class="ph ph-envelope me-2"></i>Common Email Provider Settings</h5>
                <div class="row">
                    <div class="col-md-6">
                        <p class="fw-bold mb-1">Gmail:</p>
                        <ul>
                            <li>Host: smtp.gmail.com</li>
                            <li>Port: 587</li>
                            <li>Encryption: tls</li>
                            <li>Username: your.email@gmail.com</li>
                            <li>Password: Your app password (not regular Gmail password)</li>
                            <li>Note: You need to <a href="https://myaccount.google.com/apppasswords" target="_blank">create
                                    an app password</a> for less secure apps</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <p class="fw-bold mb-1">Microsoft 365 / Outlook:</p>
                        <ul>
                            <li>Host: smtp.office365.com</li>
                            <li>Port: 587</li>
                            <li>Encryption: tls</li>
                            <li>Username: your.email@outlook.com</li>
                            <li>Password: Your email password</li>
                        </ul>
                    </div>
                </div>
            </div>

            <form action="{{ route('special_settings.update_email_mailers') }}" method="POST">
                @csrf

                <!-- Test Email Mailer (Input Email Address) -->
                <div class="mb-4">
                    <div class="form-control form-control-sm p-3 mb-2">
                        <h6 class="mb-2">Test Email Mailer</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <label for="email_address" class="form-label required">Email Address</label>
                                <input type="email" id="email_address" name="email_address"
                                    placeholder="Enter email address" class="form-control">
                            </div>
                            <div class="col-md-2 mt-4">
                                <button type="button" id="sendEmail" class="btn btn-sm btn-primary mt-1">Send Test
                                    Email</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Select Email Mailer -->
                <div class="mb-4">
                    <label for="email_mailer" class="form-label required">Selected Email Mailer</label>
                    <select name="email_mailer" id="email_mailer" class="form-control">
                        @if (is_array($emailMailers->value))
                            @foreach ($emailMailers->value as $mailer)
                                <option value="{{ $mailer['TYPE'] }}"
                                    {{ isset($emailMailer) && $emailMailer->value === $mailer['TYPE'] ? 'selected' : '' }}>
                                    {{ $mailer['TYPE'] }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div id="emailInputFieldsContainer">
                    @if (is_array($emailMailers->value) && count($emailMailers->value) > 0)
                        @foreach ($emailMailers->value as $index => $mailer)
                            <div class="col-md-12 mb-3">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Mailer Configuration: {{ $mailer['TYPE'] }}</h6>
                                        <i class="ph ph-trash text-danger cursor-pointer"
                                            onclick="removeEmailMailer(this)"></i>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="email_mailers[{{ $index }}][TYPE]"
                                                        class="form-label required">Type</label>
                                                    <input type="text" name="email_mailers[{{ $index }}][TYPE]"
                                                        class="form-control" value="{{ $mailer['TYPE'] }}" required>
                                                    <small class="text-muted">A unique name to identify this mailer</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="email_mailers[{{ $index }}][VALUE][transport]"
                                                        class="form-label required">Mailer Type
                                                        <i class="ph ph-question text-muted" data-bs-toggle="tooltip"
                                                            title="Usually 'smtp' for standard email servers"></i>
                                                    </label>
                                                    <input type="text"
                                                        name="email_mailers[{{ $index }}][VALUE][transport]"
                                                        class="form-control"
                                                        value="{{ $mailer['VALUE']['transport'] ?? '' }}" required>
                                                    <small class="text-muted">Usually 'smtp' for most email
                                                        providers</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="email_mailers[{{ $index }}][VALUE][host]"
                                                        class="form-label required">Host/IP
                                                        <i class="ph ph-question text-muted" data-bs-toggle="tooltip"
                                                            title="SMTP server address (e.g., smtp.gmail.com, smtp.office365.com)"></i>
                                                    </label>
                                                    <input type="text"
                                                        name="email_mailers[{{ $index }}][VALUE][host]"
                                                        class="form-control" value="{{ $mailer['VALUE']['host'] ?? '' }}"
                                                        placeholder="e.g., smtp.gmail.com" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="email_mailers[{{ $index }}][VALUE][port]"
                                                        class="form-label required">Port
                                                        <i class="ph ph-question text-muted" data-bs-toggle="tooltip"
                                                            title="Common ports: 587 (TLS), 465 (SSL), or 25 (unencrypted)"></i>
                                                    </label>
                                                    <input type="number"
                                                        name="email_mailers[{{ $index }}][VALUE][port]"
                                                        class="form-control" value="{{ $mailer['VALUE']['port'] ?? '' }}"
                                                        placeholder="587" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="email_mailers[{{ $index }}][VALUE][encryption]"
                                                        class="form-label">Encryption
                                                        <i class="ph ph-question text-muted" data-bs-toggle="tooltip"
                                                            title="Usually 'tls' for port 587 or 'ssl' for port 465"></i>
                                                    </label>
                                                    <input type="text"
                                                        name="email_mailers[{{ $index }}][VALUE][encryption]"
                                                        class="form-control"
                                                        value="{{ $mailer['VALUE']['encryption'] ?? '' }}"
                                                        placeholder="tls">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="email_mailers[{{ $index }}][VALUE][username]"
                                                        class="form-label">Username
                                                        <i class="ph ph-question text-muted" data-bs-toggle="tooltip"
                                                            title="Usually your full email address"></i>
                                                    </label>
                                                    <input type="text"
                                                        name="email_mailers[{{ $index }}][VALUE][username]"
                                                        class="form-control"
                                                        value="{{ $mailer['VALUE']['username'] ?? '' }}"
                                                        placeholder="your.email@example.com">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="email_mailers[{{ $index }}][VALUE][password]"
                                                        class="form-label">Password
                                                        <i class="ph ph-question text-muted" data-bs-toggle="tooltip"
                                                            title="For Gmail, use an App Password. For others, use your regular email password"></i>
                                                    </label>
                                                    <input type="password"
                                                        name="email_mailers[{{ $index }}][VALUE][password]"
                                                        class="form-control"
                                                        value="{{ $mailer['VALUE']['password'] ?? '' }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="email_mailers[{{ $index }}][VALUE][from][address]"
                                                        class="form-label required">From Address
                                                        <i class="ph ph-question text-muted" data-bs-toggle="tooltip"
                                                            title="The email address that will appear as the sender"></i>
                                                    </label>
                                                    <input type="email"
                                                        name="email_mailers[{{ $index }}][VALUE][from][address]"
                                                        class="form-control"
                                                        value="{{ $mailer['VALUE']['from']['address'] ?? '' }}"
                                                        placeholder="noreply@yourcompany.com" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="email_mailers[{{ $index }}][VALUE][from][name]"
                                                        class="form-label required">From Name
                                                        <i class="ph ph-question text-muted" data-bs-toggle="tooltip"
                                                            title="The name that will appear as the sender"></i>
                                                    </label>
                                                    <input type="text"
                                                        name="email_mailers[{{ $index }}][VALUE][from][name]"
                                                        class="form-control"
                                                        value="{{ $mailer['VALUE']['from']['name'] ?? '' }}"
                                                        placeholder="Your Company Name" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <div class="mb-3">
                    <button type="button" class="btn btn-primary" onclick="addEmailMailer()">
                        <i class="ph-plus"></i> Add New Email Mailer
                    </button>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-primary">Update Email Mailers</button>
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

        // Send test email
        document.getElementById('sendEmail').addEventListener('click', function() {
            const email = document.getElementById('email_address').value;
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Invalid email address',
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
                beforeSend: function() {
                    document.getElementById('sendEmail').innerHTML =
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
                    document.getElementById('sendEmail').innerHTML = 'Send Test Email';
                }
            });
        });

        // Add new email mailer
        function addEmailMailer() {
            const container = document.getElementById('emailInputFieldsContainer');
            const index = document.querySelectorAll('.card').length;

            const template = `
            <div class="col-md-12 mb-3">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">New Mailer Configuration</h6>
                        <i class="ph ph-trash text-danger cursor-pointer" onclick="removeEmailMailer(this)"></i>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email_mailers[${index}][TYPE]" class="form-label required">Type</label>
                                    <input type="text" name="email_mailers[${index}][TYPE]" class="form-control" required>
                                    <small class="text-muted">A unique name to identify this mailer</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email_mailers[${index}][VALUE][transport]" class="form-label required">Mailer Type
                                        <i class="ph ph-question text-muted" data-bs-toggle="tooltip"
                                        title="Usually 'smtp' for standard email servers"></i>
                                    </label>
                                    <input type="text" name="email_mailers[${index}][VALUE][transport]" class="form-control" value="smtp" required>
                                    <small class="text-muted">Usually 'smtp' for most email providers</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email_mailers[${index}][VALUE][host]" class="form-label required">Host/IP
                                        <i class="ph ph-question text-muted" data-bs-toggle="tooltip"
                                        title="SMTP server address (e.g., smtp.gmail.com, smtp.office365.com)"></i>
                                    </label>
                                    <input type="text" name="email_mailers[${index}][VALUE][host]" class="form-control" placeholder="e.g., smtp.gmail.com" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email_mailers[${index}][VALUE][port]" class="form-label required">Port
                                        <i class="ph ph-question text-muted" data-bs-toggle="tooltip"
                                        title="Common ports: 587 (TLS), 465 (SSL), or 25 (unencrypted)"></i>
                                    </label>
                                    <input type="number" name="email_mailers[${index}][VALUE][port]" value="587" class="form-control" placeholder="587" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email_mailers[${index}][VALUE][encryption]" class="form-label">Encryption
                                        <i class="ph ph-question text-muted" data-bs-toggle="tooltip"
                                        title="Usually 'tls' for port 587 or 'ssl' for port 465"></i>
                                    </label>
                                    <input type="text" name="email_mailers[${index}][VALUE][encryption]" value="tls" class="form-control" placeholder="tls">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email_mailers[${index}][VALUE][username]" class="form-label">Username
                                        <i class="ph ph-question text-muted" data-bs-toggle="tooltip"
                                        title="Usually your full email address"></i>
                                    </label>
                                    <input type="text" name="email_mailers[${index}][VALUE][username]" class="form-control" placeholder="your.email@example.com">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email_mailers[${index}][VALUE][password]" class="form-label">Password
                                        <i class="ph ph-question text-muted" data-bs-toggle="tooltip"
                                        title="For Gmail, use an App Password. For others, use your regular email password"></i>
                                    </label>
                                    <input type="password" name="email_mailers[${index}][VALUE][password]" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email_mailers[${index}][VALUE][from][address]" class="form-label required">From Address
                                        <i class="ph ph-question text-muted" data-bs-toggle="tooltip"
                                        title="The email address that will appear as the sender"></i>
                                    </label>
                                    <input type="email" name="email_mailers[${index}][VALUE][from][address]" class="form-control" placeholder="noreply@yourcompany.com" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email_mailers[${index}][VALUE][from][name]" class="form-label required">From Name
                                        <i class="ph ph-question text-muted" data-bs-toggle="tooltip"
                                        title="The name that will appear as the sender"></i>
                                    </label>
                                    <input type="text" name="email_mailers[${index}][VALUE][from][name]" class="form-control" placeholder="Your Company Name" required>
                                </div>
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

        // Remove email mailer
        function removeEmailMailer(element) {
            if (confirm('Are you sure you want to remove this mailer?')) {
                element.closest('.col-md-12').remove();
            }
        }
    </script>
@endpush
