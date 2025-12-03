@extends("frontend.master")
@section('main-content')

<div class="container mt-5">
    <div class="text-center">
        <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#authModal">
            Login / Register
        </button>
    </div>
</div>

<!-- Auth Modal -->
<div class="modal fade" id="authModal" tabindex="-1" aria-labelledby="authModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="authModalLabel">Welcome</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                
                <!-- Step 1: Email Input -->
                <div id="step-email" class="auth-step">
                    <h6 class="mb-3">Enter your email to continue</h6>
                    <form id="emailForm">
                        @csrf
                        <div class="mb-3">
                            <label for="auth_email" class="form-label">Email or Phone Number</label>
                            <input type="text" class="form-control" id="auth_email" name="email" 
                                   pattern="(^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$)|(^\+?\d{10,15}$)"
                                   title="Please enter a valid email address or phone number" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Continue</button>
                    </form>
                </div>

                <!-- Step 2: Login (if user exists with password) -->
                <div id="step-login" class="auth-step d-none">
                    <h6 class="mb-3">Welcome back! Please login</h6>
                    <p class="text-muted small mb-3">Email: <span id="display-email"></span></p>
                    <form id="loginForm">
                        @csrf
                        <input type="hidden" name="email" id="login_email">
                        <div class="mb-3">
                            <label for="login_password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="login_password" name="password" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mb-2">Login</button>
                        <button type="button" class="btn btn-link w-100 text-decoration-none" id="forgotPasswordBtn">
                            Forgot Password?
                        </button>
                    </form>
                </div>

                <!-- Step 3: Registration (if user doesn't exist) -->
                <div id="step-register" class="auth-step d-none">
                    <h6 class="mb-3">Complete your registration</h6>
                    <p class="text-muted small mb-3">Email: <span id="display-email-register"></span></p>
                    <form id="registerForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="email" id="register_email">
                        
                        <div class="mb-3">
                            <label for="register_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="register_name" name="name" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="register_image" class="form-label">Profile Image</label>
                            <input type="file" class="form-control" id="register_image" name="image" accept="image/*">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="register_country" class="form-label">Country</label>
                            <select id="register_country" name="country_id" class="form-select" required>
                                <option value="">Select Country</option>
                                @if(isset($countries))
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="register_city" class="form-label">City</label>
                            <select id="register_city" name="city_id" class="form-select" required>
                                <option value="">Select City</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Send OTP</button>
                    </form>
                </div>

                <!-- Step 4: OTP Verification -->
                <div id="step-otp" class="auth-step d-none">
                    <h6 class="mb-3">Verify OTP</h6>
                    <p class="text-muted small mb-3">We've sent a verification code to <span id="display-email-otp"></span></p>
                    <form id="otpForm">
                        @csrf
                        <input type="hidden" name="email" id="otp_email">
                        <div class="mb-3">
                            <label for="otp_code" class="form-label">Enter OTP</label>
                            <input type="text" class="form-control text-center" id="otp_code" name="otp" maxlength="6" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mb-2">Verify OTP</button>
                        <button type="button" class="btn btn-link w-100 text-decoration-none" id="resendOtpBtn">
                            Resend OTP
                        </button>
                    </form>
                </div>

                <!-- Step 5: Set Password -->
                <div id="step-password" class="auth-step d-none">
                    <h6 class="mb-3">Set Your Password</h6>
                    <p class="text-muted small mb-3">Create a secure password for your account</p>
                    <form id="passwordForm">
                        @csrf
                        <input type="hidden" name="email" id="password_email">
                        <input type="hidden" name="otp" id="password_otp">
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="password" minlength="8" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="password_confirmation" minlength="8" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Complete Registration</button>
                    </form>
                </div>

                <!-- Step 6: Success Message -->
                <div id="step-success" class="auth-step d-none text-center">
                    <div class="mb-3">
                        <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                    </div>
                    <h6 class="mb-2">Success!</h6>
                    <p class="text-muted">Your account has been created successfully. Redirecting...</p>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let currentEmail = '';
    let registrationData = {};

    // Reset modal on close
    $('#authModal').on('hidden.bs.modal', function () {
        resetModal();
    });

    function resetModal() {
        $('.auth-step').addClass('d-none');
        $('#step-email').removeClass('d-none');
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        $('form').trigger('reset');
        currentEmail = '';
        registrationData = {};
    }

    function showStep(stepId) {
        $('.auth-step').addClass('d-none');
        $('#' + stepId).removeClass('d-none');
    }

    function showError(inputId, message) {
        const input = $('#' + inputId);
        input.addClass('is-invalid');
        input.siblings('.invalid-feedback').text(message);
    }

    function clearErrors() {
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    }

    // Step 1: Check Email
    $('#emailForm').on('submit', function(e) {
        e.preventDefault();
        clearErrors();
        const email = $('#auth_email').val();
        currentEmail = email;

        $.ajax({
            url: '/check-email',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                email: email
            },
            success: function(response) {
                if (response.exists && response.has_password) {
                    // User exists with password - show login
                    $('#display-email').text(email);
                    $('#login_email').val(email);
                    showStep('step-login');
                } else if (response.exists && !response.has_password) {
                    // User exists without password - send OTP
                    sendOTP(email, 'reset');
                } else {
                    // New user - show registration
                    $('#display-email-register').text(email);
                    $('#register_email').val(email);
                    showStep('step-register');
                }
            },
            error: function(xhr) {
                showError('auth_email', 'An error occurred. Please try again.');
            }
        });
    });

    // Step 2: Login
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        clearErrors();

        $.ajax({
            url: '/login',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    window.location.href = response.redirect || '/';
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const error = xhr.responseJSON.error || 'Invalid credentials';
                    showError('login_password', error);
                } else {
                    showError('login_password', 'Login failed. Please try again.');
                }
            }
        });
    });

    // Forgot Password
    $('#forgotPasswordBtn').on('click', function() {
        sendOTP(currentEmail, 'reset');
    });

    // Step 3: Registration Form
    $('#registerForm').on('submit', function(e) {
        e.preventDefault();
        clearErrors();

        // Store form data
        registrationData = new FormData(this);
        
        // Send OTP
        sendOTP(currentEmail, 'register');
    });

    // Country change - load cities
    $('#register_country').on('change', function() {
        const countryId = $(this).val();
        $('#register_city').html('<option value="">Select City</option>');
        
        if (countryId) {
            $.ajax({
                url: '/get-cities/' + countryId,
                method: 'GET',
                success: function(data) {
                    $.each(data, function(key, value) {
                        $('#register_city').append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                }
            });
        }
    });

    // Function to send OTP
    function sendOTP(email, type) {
        $.ajax({
            url: '/send-otp',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                email: email,
                type: type
            },
            success: function(response) {
                $('#display-email-otp').text(email);
                $('#otp_email').val(email);
                showStep('step-otp');
            },
            error: function(xhr) {
                alert('Failed to send OTP. Please try again.');
            }
        });
    }

    // Step 4: Verify OTP
    $('#otpForm').on('submit', function(e) {
        e.preventDefault();
        clearErrors();

        $.ajax({
            url: '/verify-otp',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.verified) {
                    $('#password_email').val(currentEmail);
                    $('#password_otp').val($('#otp_code').val());
                    showStep('step-password');
                }
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.error || 'Invalid or expired OTP. Please try again.';
                showError('otp_code', error);
            }
        });
    });

    // Resend OTP
    $('#resendOtpBtn').on('click', function() {
        sendOTP(currentEmail, 'register');
        alert('OTP sent successfully!');
    });

    // Step 5: Set Password
    $('#passwordForm').on('submit', function(e) {
        e.preventDefault();
        clearErrors();

        const password = $('#new_password').val();
        const confirmPassword = $('#confirm_password').val();

        if (password !== confirmPassword) {
            showError('confirm_password', 'Passwords do not match.');
            return;
        }

        // Prepare final data
        let finalData;
        if (Object.keys(registrationData).length > 0) {
            // Registration flow - FormData already exists
            finalData = registrationData;
            finalData.set('password', password);
            finalData.set('password_confirmation', confirmPassword);
            
            // Make sure email is in FormData
            if (!finalData.has('email')) {
                finalData.set('email', currentEmail);
            }
        } else {
            // Password reset flow - create new FormData
            finalData = new FormData();
            finalData.append('_token', '{{ csrf_token() }}');
            finalData.append('email', currentEmail);
            finalData.append('password', password);
            finalData.append('password_confirmation', confirmPassword);
        }

        console.log('Submitting registration with email:', currentEmail);

        $.ajax({
            url: '/complete-registration',
            method: 'POST',
            data: finalData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Registration success:', response);
                if (response.success) {
                    showStep('step-success');
                    setTimeout(function() {
                        window.location.href = response.redirect || '/';
                    }, 2000);
                }
            },
            error: function(xhr) {
                console.error('Registration error:', xhr.responseJSON);
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON?.errors;
                    const error = xhr.responseJSON?.error;
                    
                    if (error) {
                        alert(error);
                    } else if (errors?.password) {
                        showError('new_password', errors.password[0]);
                    } else {
                        alert('Validation failed. Please check your inputs.');
                    }
                } else if (xhr.status === 403) {
                    alert('Please verify OTP first');
                    showStep('step-otp');
                } else {
                    alert('Registration failed. Please try again.');
                }
            }
        });
    });
});
</script>

<style>
    .modal-content {
        border-radius: 15px;
    }
    .auth-step {
        min-height: 250px;
    }
    #otp_code {
        font-size: 1.5rem;
        letter-spacing: 0.5rem;
    }
</style>

@endsection