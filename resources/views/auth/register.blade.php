@extends("frontend.master")
@section('main-content')
<!-- Page Header -->
<div class="container mt-4">

    <div class="row justify-content-center">
        <div class="">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title mb-4 text-center">Register</h4>

                    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
                        @csrf

                        

                        <!-- Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                       


                        <!-- Email -->
                        <div class="mb-3">
                        <label for="login_id" class="form-label">Email or Phone Number</label>
                        <input id="login_id" type="text"
                            class="form-control @error('login_id') is-invalid @enderror"
                            name="login_id" value="{{ old('login_id') }}" required autocomplete="username"
                            pattern="(^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$)|(^\+?\d{10,15}$)"
                            title="Please enter a valid email address or phone number"
                            oninput="
                                var v = this.value.trim();
                                var emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                                var phonePattern = /^\+?\d{10,15}$/;
                                if(v && !(emailPattern.test(v) || phonePattern.test(v))) {
                                    this.setCustomValidity('Please enter a valid email address or phone number');
                                } else {
                                    this.setCustomValidity('');
                                }
                            ">
                        @error('login_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                                

                      <!-- Country & City Dropdown with working search -->
<div class="mb-3">
    <label for="country" class="form-label">Address</label>
    <div class="row g-0">
        <div class="col-6">
            <select id="country" name="country_id" 
                class="form-select @error('country') is-invalid @enderror" required>
                <option value="">Select Country</option>
                @foreach($countries as $country)
                    <option value="{{ $country->id }}">{{ $country->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6">
            <select id="city" name="city_id" 
                class="form-select @error('city') is-invalid @enderror" required>
                <option value="">Select City</option>
            </select>
        </div>
    </div>
    @error('country')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
    @error('city')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>

<style>
    /* Remove gap between selects and make them look like a single input group */
   /* Country select - remove right radius */
.row.g-0 .col-6:first-child .select2-container--bootstrap4 .select2-selection {
    border-top-right-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
}

/* City select - remove left radius */
.row.g-0 .col-6:last-child .select2-container--bootstrap4 .select2-selection {
    border-top-left-radius: 0 !important;
    border-bottom-left-radius: 0 !important;
    margin-left: -1px !important;
}


    /* Select2 Bootstrap look */
    .select2-container--bootstrap-5 .select2-selection {
        height: calc(2.5rem + 2px) !important;
        padding: 0.375rem 0.75rem !important;
        font-size: 1rem !important;
        line-height: 1.5 !important;
        border: 1px solid #ced4da !important;
        border-radius: 0.375rem !important;
    }
    .select2-container--bootstrap-5 .select2-selection__rendered {
        line-height: 1.5 !important;
    }
    .select2-container--bootstrap-5 .select2-selection__arrow {
        height: 100% !important;
    }
</style>

<!-- Select2 with Bootstrap 5 theme -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize select2 with Bootstrap theme
        $('#country').select2({
            placeholder: "Select Country",
            allowClear: true,
            width: '100%',
            theme: 'bootstrap4'
        });
        $('#city').select2({
            placeholder: "Select City",
            allowClear: true,
            width: '100%',
            theme: 'bootstrap4'
        });

        // When country changes, fetch cities
        $('#country').on('change', function() {
            var country_id = $(this).val();
            $('#city').html('<option value="">Select City</option>');
            $('#city').val(null).trigger('change');
            if(country_id) {
                $.ajax({
                    url: '/get-cities/' + country_id,
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        var cityOptions = '<option value="">Select City</option>';
                        $.each(data, function(key, value) {
                            cityOptions += '<option value="' + value.id + '">' + value.name + '</option>';
                        });
                        $('#city').html(cityOptions);
                        $('#city').val(null).trigger('change');
                    }
                });
            }
        });
    });
</script>


                       

                          <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input id="password_confirmation" type="password" class="form-control @error('password_confirmation') is-invalid @enderror" name="password_confirmation" required autocomplete="new-password">
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <input type="hidden" name="fcm_token" id="fcm_token_field" value="">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('login') }}" class="text-decoration-underline">Already registered?</a>
                            <button type="submit" class="btn btn-primary">Register</button>
                        </div>

                    </form>

                </div>
            </div>

            <div class="mt-4 mx-auto text-center text-muted" style="width: 70%;">
                <small>
                By clicking Register, you agree to our <a href="/terms-and-condition">Terms</a>, <a href="/privacy-policy">Privacy Policy</a> and Cookies Policy. You may receive SMS or Email notifications from us and can opt out at any time.
                </small>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#country').change(function() {
            var country_id = $(this).val();
            if(country_id) {
                $.ajax({
                    url: '/get-cities/'+country_id, // route to get cities
                    type: "GET",
                    dataType: "json",
                    success:function(data) {
                        $('#city').empty();
                        $('#city').append('<option value="">Select City</option>');
                        $.each(data, function(key, value) {
                            $('#city').append('<option value="'+ value.id +'">'+ value.name +'</option>');
                        });
                    }
                });
            } else {
                $('#city').empty();
                $('#city').append('<option value="">Select City</option>');
            }
        });
    });
</script>


@endsection