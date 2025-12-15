
<!-- 
<div class="container mt-5">
    <div class="text-center">
        <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#authModal">
            Login / Register
        </button>
    </div>
</div>
-->
<!-- Auth Modal -->
<div class="modal fade" id="authModal" tabindex="-1" aria-labelledby="authModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="authModalLabel">Welcome</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x"></i>
                </button>
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
                        <button type="submit" class="btn btn-primary w-100" id="emailBtn">
                            <span class="btn-text"><i class="bi bi-arrow-right"></i></span>
                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        </button>
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
                        <button type="submit" class="btn btn-primary w-100 mb-2" id="loginBtn">
                            <span class="btn-text"><i class="bi bi-check-lg"></i></span>
                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        </button>
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





<!-- ///////////////////////////////////// image start ///////////////////////////////////////// -->

                        <div class="mb-3">
                            <label for="register_image" class="form-label">Profile Image</label>
                            <input type="file" class="form-control" id="register_image" name="image" accept="image/*">
                            <input type="hidden" name="image_data" id="register_imageData">
                            <div id="registerImageProcessingStatus" style="display: none;" class="mt-2">
                                <div class="progress">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                                         role="progressbar"
                                         style="width: 0%" 
                                         id="registerImageProgress"></div>
                                </div>
                                <small id="registerImageStatusText">Image processing...</small>
                            </div>
                            <div class="mt-2">
                                <img id="registerImagePreview"
                                     src=""
                                     alt="Preview"
                                     style="max-width: 180px; display: none; border-radius: 6px; border: 2px solid #ddd;">
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>
                        <script src="https://cdn.jsdelivr.net/npm/heic2any@0.0.4/dist/heic2any.min.js"></script>
                        <script>
                        // Reusable image compression logic (from product-services.blade.php)
                        function setupImageProcessing(inputId, dataInputId, statusId, progressId, statusTextId, previewId = null) {
                            const MAX_WIDTH = 1800;
                            const MAX_HEIGHT = 1800;
                            const QUALITY = 0.7;
                        
                            const imageInput = document.getElementById(inputId);
                            const imageDataInput = document.getElementById(dataInputId);
                            const imageProcessingStatus = document.getElementById(statusId);
                            const imageProgress = document.getElementById(progressId);
                            const imageStatusText = document.getElementById(statusTextId);
                            const imagePreview = previewId ? document.getElementById(previewId) : null;
                        
                            if (!imageInput) return;
                        
                            imageInput.addEventListener('change', function(e) {
                                const file = this.files[0];
                                if (!file) return;
                        
                                // Clear previous preview
                                if (imagePreview) imagePreview.src = '';
                        
                                // File type validation
                                const fileExt = file.name.split('.').pop().toLowerCase();
                                const allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heic', 'heif'];
                        
                                if (!allowedExts.includes(fileExt)) {
                                    alert('Please upload only JPG, PNG, GIF, WEBP, HEIC or HEIF files!');
                                    this.value = '';
                                    return;
                                }
                        
                                // Show processing status
                                const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                                imageProcessingStatus.style.display = 'block';
                                imageProgress.style.width = '10%';
                        
                                if (fileExt === 'heic' || fileExt === 'heif') {
                                    imageStatusText.textContent = `HEIC/HEIF image (${fileSizeMB} MB) is being converted...`;
                                } else {
                                    imageStatusText.textContent = `Image (${fileSizeMB} MB) is being optimized...`;
                                }
                        
                                // Process the image
                                processImage(file, imageDataInput, imageProgress, imageStatusText, imageProcessingStatus, imagePreview);
                            });
                        
                            function processImage(file, dataInput, progress, statusText, processingStatus, preview) {
                                const originalSize = file.size;
                                const fileExt = file.name.split('.').pop().toLowerCase();
                        
                                if ((fileExt === 'heic' || fileExt === 'heif') && typeof heic2any !== 'undefined') {
                                    convertHeicToJpeg(file, originalSize, dataInput, progress, statusText, processingStatus, preview);
                                } else {
                                    loadImageWithOrientation(file, originalSize, dataInput, progress, statusText, processingStatus, preview);
                                }
                            }
                        
                            function convertHeicToJpeg(file, originalSize, dataInput, progress, statusText, processingStatus, preview) {
                                progress.style.width = '20%';
                        
                                const fileReader = new FileReader();
                                fileReader.onload = function(event) {
                                    const arrayBuffer = event.target.result;
                        
                                    heic2any({
                                        blob: new Blob([arrayBuffer]),
                                        toType: 'image/jpeg',
                                        quality: 0.8
                                    }).then(function(jpegBlob) {
                                        progress.style.width = '40%';
                                        statusText.textContent = 'HEIC conversion successful! Now optimizing...';
                                        loadImageWithOrientation(jpegBlob, originalSize, dataInput, progress, statusText, processingStatus, preview);
                                    }).catch(function(err) {
                                        console.error('HEIC conversion error:', err);
                                        statusText.textContent = 'HEIC conversion error! Trying standard procedure...';
                                        loadImageWithOrientation(file, originalSize, dataInput, progress, statusText, processingStatus, preview);
                                    });
                                };
                        
                                fileReader.readAsArrayBuffer(file);
                            }
                        
                            function loadImageWithOrientation(file, originalSize, dataInput, progress, statusText, processingStatus, preview) {
                                progress.style.width = '50%';
                        
                                const urlReader = new FileReader();
                                urlReader.onload = function(event) {
                                    const img = new Image();
                        
                                    img.onload = function() {
                                        progress.style.width = '60%';
                        
                                        let width = img.width;
                                        let height = img.height;
                                        let targetWidth = width;
                                        let targetHeight = height;
                        
                                        if (width > MAX_WIDTH || height > MAX_HEIGHT) {
                                            if (width > height) {
                                                targetHeight = Math.round(height * (MAX_WIDTH / width));
                                                targetWidth = MAX_WIDTH;
                                            } else {
                                                targetWidth = Math.round(width * (MAX_HEIGHT / height));
                                                targetHeight = MAX_HEIGHT;
                                            }
                                        }
                        
                                        const canvas = document.createElement('canvas');
                                        canvas.width = targetWidth;
                                        canvas.height = targetHeight;
                                        const ctx = canvas.getContext('2d');
                        
                                        ctx.fillStyle = '#FFFFFF';
                                        ctx.fillRect(0, 0, targetWidth, targetHeight);
                                        ctx.drawImage(img, 0, 0, targetWidth, targetHeight);
                        
                                        let targetQuality = QUALITY;
                                        let fileSizeMB = file.size / (1024 * 1024);
                        
                                        if (fileSizeMB > 10) targetQuality = 0.5;
                                        else if (fileSizeMB > 5) targetQuality = 0.6;
                        
                                        progress.style.width = '90%';
                        
                                        canvas.toBlob(function(blob) {
                                            finalizeImageProcessing(blob, originalSize, dataInput, statusText, processingStatus, preview);
                                        }, 'image/jpeg', targetQuality);
                                    };
                        
                                    img.src = event.target.result;
                                };
                        
                                urlReader.readAsDataURL(file);
                            }
                        
                            function finalizeImageProcessing(blob, originalSize, dataInput, statusText, processingStatus, preview) {
                                const reader = new FileReader();
                                reader.onload = function(e) {
                                    if (preview) {
                                        preview.src = e.target.result;
                                        preview.style.display = 'block';
                                        preview.style.border = '3px solid #28a745';
                                    }
                        
                                    const compressedSize = blob.size;
                                    const compressionRatio = Math.round((1 - (compressedSize / originalSize)) * 100);
                                    statusText.innerHTML = `<i class="fas fa-check-circle"></i> Optimization complete! <span class="text-success">(${formatFileSize(originalSize)} → ${formatFileSize(compressedSize)}, ${compressionRatio}% Reduced!)</span>`;
                                    statusText.style.color = '#28a745';
                                    processingStatus.style.display = 'none';
                                };
                                reader.readAsDataURL(blob);
                        
                                const dataReader = new FileReader();
                                dataReader.onload = function(e) {
                                    dataInput.value = e.target.result;
                                };
                                dataReader.readAsDataURL(blob);
                            }
                        
                            function formatFileSize(bytes) {
                                if (bytes < 1024) {
                                    return bytes + " B";
                                } else if (bytes < 1048576) {
                                    return (bytes / 1024).toFixed(1) + " KB";
                                } else {
                                    return (bytes / 1048576).toFixed(2) + " MB";
                                }
                            }
                        }
                        
                        // Initialize image processing for register form
                        document.addEventListener('DOMContentLoaded', function() {
                            setupImageProcessing('register_image', 'register_imageData', 'registerImageProcessingStatus', 'registerImageProgress', 'registerImageStatusText', 'registerImagePreview');
                        });
                        </script>





<!-- ///////////////////////////////////// image end ///////////////////////////////////////// -->






                        <div class="mb-3">
                            <label for="register_country" class="form-label">Country</label>
                            <select id="register_country" name="country_id" class="form-select" required>
                                <option value="">Select Country</option>
                                @if(isset($countries))
                                    @foreach($countries as $country)
                                        @if(strtolower($country->name) !== 'international')
                                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                                        @endif
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

                        <script>
                            // Searchable Select Dropdown Function
function makeSelectSearchable(selectElement) {
    const wrapper = document.createElement('div');
    wrapper.className = 'searchable-select-wrapper';
    wrapper.style.position = 'relative';
    wrapper.style.width = '100%';
    
    selectElement.parentNode.insertBefore(wrapper, selectElement);
    wrapper.appendChild(selectElement);
    
    // Create search input
    const searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.className = 'form-control searchable-select-input';
    searchInput.placeholder = 'Search...';
    searchInput.style.display = 'none';
    
    // Create dropdown container
    const dropdownList = document.createElement('div');
    dropdownList.className = 'searchable-select-dropdown';
    dropdownList.style.cssText = `
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        max-height: 250px;
        overflow-y: auto;
        background: white;
        border: 1px solid #ced4da;
        border-top: none;
        border-radius: 0 0 0.375rem 0.375rem;
        display: none;
        z-index: 1000;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    `;
    
    // Hide original select
    selectElement.style.display = 'none';
    
    // Display selected value
    const displayDiv = document.createElement('div');
    displayDiv.className = 'form-select searchable-select-display';
    displayDiv.style.cursor = 'pointer';
    displayDiv.textContent = selectElement.options[selectElement.selectedIndex].text;
    
    wrapper.appendChild(displayDiv);
    wrapper.appendChild(searchInput);
    wrapper.appendChild(dropdownList);
    
    // Get all options
    function getOptions() {
        const options = [];
        for (let i = 0; i < selectElement.options.length; i++) {
            options.push({
                value: selectElement.options[i].value,
                text: selectElement.options[i].text
            });
        }
        return options;
    }
    
    // Render dropdown options
    function renderOptions(filter = '') {
        const options = getOptions();
        dropdownList.innerHTML = '';
        
        const filtered = options.filter(opt => 
            opt.text.toLowerCase().includes(filter.toLowerCase())
        );
        
        filtered.forEach(opt => {
            const optDiv = document.createElement('div');
            optDiv.className = 'searchable-select-option';
            optDiv.textContent = opt.text;
            optDiv.dataset.value = opt.value;
            optDiv.style.cssText = `
                padding: 0.5rem 0.75rem;
                cursor: pointer;
                transition: background-color 0.2s;
            `;
            
            optDiv.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#0d6efd';
                this.style.color = 'white';
            });
            
            optDiv.addEventListener('mouseleave', function() {
                this.style.backgroundColor = 'white';
                this.style.color = 'black';
            });
            
            optDiv.addEventListener('click', function() {
                selectElement.value = this.dataset.value;
                displayDiv.textContent = this.textContent;
                closeDropdown();
                
                // Trigger change event
                const event = new Event('change', { bubbles: true });
                selectElement.dispatchEvent(event);
            });
            
            dropdownList.appendChild(optDiv);
        });
        
        if (filtered.length === 0) {
            dropdownList.innerHTML = '<div style="padding: 0.5rem 0.75rem; color: #6c757d;">No results found</div>';
        }
    }
    
    // Open dropdown
    function openDropdown() {
        displayDiv.style.display = 'none';
        searchInput.style.display = 'block';
        dropdownList.style.display = 'block';
        searchInput.focus();
        renderOptions();
    }
    
    // Close dropdown
    function closeDropdown() {
        displayDiv.style.display = 'block';
        searchInput.style.display = 'none';
        dropdownList.style.display = 'none';
        searchInput.value = '';
    }
    
    // Event listeners
    displayDiv.addEventListener('click', openDropdown);
    
    searchInput.addEventListener('input', function() {
        renderOptions(this.value);
    });
    
    searchInput.addEventListener('blur', function() {
        setTimeout(closeDropdown, 200);
    });
    
    // Close on click outside
    document.addEventListener('click', function(e) {
        if (!wrapper.contains(e.target)) {
            closeDropdown();
        }
    });
}

// Initialize for both selects
document.addEventListener('DOMContentLoaded', function() {
    const countrySelect = document.getElementById('register_country');
    const citySelect = document.getElementById('register_city');
    
    if (countrySelect) {
        makeSelectSearchable(countrySelect);
    }
    
    if (citySelect) {
        makeSelectSearchable(citySelect);
    }
});

// If you're loading cities dynamically via AJAX, call this after loading:
// makeSelectSearchable(document.getElementById('register_city'));
                        </script>

                        <button type="submit" class="btn btn-primary w-100" id="registerBtn">
                            <span class="btn-text"><i class="bi bi-arrow-right"></i></span>
                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        </button>
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
                            <input type="number" class="form-control text-center" id="otp_code" name="otp" maxlength="6" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mb-2" id="otpBtn">
                            <span class="btn-text"><i class="bi bi-check-lg"></i></span>
                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        </button>
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
                        <input type="hidden" name="fcm_token" id="password_fcm_token">
                        
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

                        <button type="submit" class="btn btn-primary w-100" id="passwordBtn">
                            <span class="btn-text"><i class="bi bi-check-lg"></i></span>
                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        </button>
                    </form>
                </div>

                <!-- Step 6: Success Message -->
                <div id="step-success" class="auth-step d-none text-center">
                    <div class="mb-3">
                        <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                    </div>
                    <h6 class="mb-2">Success!</h6>
                    <p class="text-muted">Redirecting...</p>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let currentEmail = '';
    let registrationData = null;
    let isNewUser = false;

    // ✅ Loading state functions
    function setButtonLoading(buttonId, isLoading) {
        const btn = $('#' + buttonId);
        const btnText = btn.find('.btn-text');
        const spinner = btn.find('.spinner-border');
        
        if (isLoading) {
            btn.prop('disabled', true);
            btnText.addClass('invisible');
            spinner.removeClass('d-none');
        } else {
            btn.prop('disabled', false);
            btnText.removeClass('invisible');
            spinner.addClass('d-none');
        }
    }

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
        registrationData = null;
        isNewUser = false;
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
        setButtonLoading('emailBtn', true); // ✅ Loading start
        
        const email = $('#auth_email').val().trim();
        currentEmail = email;

        $.ajax({
            url: '/check-email',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                email: email
            },
            success: function(response) {
                setButtonLoading('emailBtn', false); // ✅ Loading stop
                
                if (response.exists && response.has_password) {
                    isNewUser = false;
                    $('#display-email').text(email);
                    $('#login_email').val(email);
                    showStep('step-login');
                } else if (response.exists && !response.has_password) {
                    isNewUser = false;
                    sendOTP(email, 'reset');
                } else {
                    isNewUser = true;
                    $('#display-email-register').text(email);
                    $('#register_email').val(email);
                    showStep('step-register');
                }
            },
            error: function(xhr) {
                setButtonLoading('emailBtn', false); // ✅ Loading stop
                showError('auth_email', 'An error occurred. Please try again.');
            }
        });
    });

    // Step 2: Login
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        clearErrors();
        setButtonLoading('loginBtn', true); // ✅ Loading start

        const formData = $(this).serialize();
        const fcmToken = localStorage.getItem('fcm_token');
        
        let finalData = formData;
        if (fcmToken) {
            finalData += '&fcm_token=' + encodeURIComponent(fcmToken);
        }

        $.ajax({
            url: '/myauth/login',
            method: 'POST',
            data: finalData,
            success: function(response) {
                if (response.success) {
                    window.location.href = response.redirect;
                }
            },
            error: function(xhr) {
                setButtonLoading('loginBtn', false); // ✅ Loading stop
                
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
        isNewUser = false;
        sendOTP(currentEmail, 'reset');
    });

    // Step 3: Registration Form
    $('#registerForm').on('submit', function(e) {
        e.preventDefault();
        clearErrors();
        setButtonLoading('registerBtn', true); // ✅ Loading start

        registrationData = new FormData(this);
        
        console.log('Registration data stored:');
        for (let [key, value] of registrationData.entries()) {
            console.log(key + ':', value);
        }
        
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
                setButtonLoading('registerBtn', false); // ✅ Loading stop
                console.log('OTP sent successfully');
                $('#display-email-otp').text(email);
                $('#otp_email').val(email);
                showStep('step-otp');
            },
            error: function(xhr) {
                setButtonLoading('registerBtn', false); // ✅ Loading stop
                alert('Failed to send OTP. Please try again.');
            }
        });
    }

    // Step 4: Verify OTP
    $('#otpForm').on('submit', function(e) {
        e.preventDefault();
        clearErrors();
        setButtonLoading('otpBtn', true); // ✅ Loading start
        
        const formData = {
            _token: '{{ csrf_token() }}',
            email: currentEmail,
            otp: $('#otp_code').val().trim()
        };
        
        console.log('Verifying OTP with data:', formData);

        $.ajax({
            url: '/myauth/verify-otp',
            method: 'POST',
            data: formData,
            success: function(response) {
                setButtonLoading('otpBtn', false); // ✅ Loading stop
                console.log('OTP verified:', response);
                
                if (response.verified) {
                    $('#password_email').val(currentEmail);
                    $('#password_otp').val($('#otp_code').val());
                    showStep('step-password');
                }
            },
            error: function(xhr) {
                setButtonLoading('otpBtn', false); // ✅ Loading stop
                console.error('OTP verify error:', xhr);
                
                const error = xhr.responseJSON?.error || 'Invalid or expired OTP. Please try again.';
                showError('otp_code', error);
            }
        });
    });

    // Resend OTP
    $('#resendOtpBtn').on('click', function() {
        const type = isNewUser ? 'register' : 'reset';
        sendOTP(currentEmail, type);
        alert('OTP sent successfully!');
    });

    // Step 5: Set Password and Complete Registration
    $('#passwordForm').on('submit', function(e) {
        e.preventDefault();
        clearErrors();
        setButtonLoading('passwordBtn', true); // ✅ Loading start

        const password = $('#new_password').val();
        const confirmPassword = $('#confirm_password').val();

        if (password !== confirmPassword) {
            setButtonLoading('passwordBtn', false); // ✅ Loading stop
            showError('confirm_password', 'Passwords do not match.');
            return;
        }

        const fcmToken = localStorage.getItem('fcm_token');
        if (fcmToken) {
            $('#password_fcm_token').val(fcmToken);
        }

        let finalData;
        
        if (isNewUser && registrationData) {
            finalData = registrationData;
            finalData.set('password', password);
            finalData.set('password_confirmation', confirmPassword);
            
            if (fcmToken) {
                finalData.set('fcm_token', fcmToken);
            }
            
            console.log('Submitting NEW USER registration:');
        } else {
            finalData = new FormData();
            finalData.append('_token', '{{ csrf_token() }}');
            finalData.append('email', currentEmail);
            finalData.append('password', password);
            finalData.append('password_confirmation', confirmPassword);
            
            if (fcmToken) {
                finalData.append('fcm_token', fcmToken);
            }
            
            console.log('Submitting PASSWORD RESET:');
        }

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
                setButtonLoading('passwordBtn', false); // ✅ Loading stop
                console.error('Registration error:', xhr.responseJSON);
                
                if (xhr.status === 422) {
                    const error = xhr.responseJSON?.error;
                    alert(error || 'Validation failed. Please check your inputs.');
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
    /* ✅ Button loading styles */
    .btn .spinner-border {
        width: 1rem;
        height: 1rem;
        border-width: 2px;
    }
    .btn .btn-text.invisible {
        visibility: hidden;
    }
</style>