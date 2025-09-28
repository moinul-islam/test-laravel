@extends("frontend.master")
@section('main-content')
<!-- Page Header -->
<div class="container mt-4">
<div class="">
@php
    $passwordError = $errors->userDeletion->first('password');
@endphp
@if($passwordError)
    <div class="text-danger mt-2 mb-4">
        {{ $passwordError }}
    </div>
@endif

    <!-- Update Profile Information -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Update Profile Information</h5>
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf

                <!-- Profile Image -->
                <div class="mb-3">
                    <label for="image" class="form-label">Profile Image</label>
                    @if(auth()->user()->image)
                        <div class="mb-3">
                            {{-- FIXED: Use correct image path --}}
                            <img src="{{ asset('profile-image/' . auth()->user()->image) }}" alt="Current Profile" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
                            <p class="text-muted small mt-1">Current profile image</p>
                        </div>
                    @endif
                    <input type="file" name="image" id="image" class="form-control" accept="image/*">
                    <small class="text-muted">Upload a new profile image (optional)</small>
                    @error('image')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Name -->
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', auth()->user()->name) }}" required>
                    @error('name')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Username -->
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" id="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', auth()->user()->username) }}" placeholder="Enter your username">
                    @error('username')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Job Title -->
                <div class="mb-3">
                    <label for="job_title" class="form-label">Job Title or Business Category</label>
                    <input type="text" name="job_title" id="job_title" class="form-control @error('job_title') is-invalid @enderror" value="{{ old('job_title', auth()->user()->job_title) }}" placeholder="e.g. Software Engineer, Restaurant Owner, etc.">
                    @error('job_title')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', auth()->user()->email) }}" required>
                    @error('email')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                    
                    @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                        <div class="mt-2">
                            <p class="text-muted small">
                                Your email address is unverified.
                                <form method="POST" action="{{ route('verification.send') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-link p-0 text-decoration-underline small">
                                        Click here to re-send the verification email.
                                    </button>
                                </form>
                            </p>
                            @if (session('status') === 'verification-link-sent')
                                <p class="text-success small mt-1">
                                    A new verification link has been sent to your email address.
                                </p>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Country Dropdown -->
                <div class="mb-3">
                    <label for="country" class="form-label">Country</label>
                    <select id="country" name="country_id" class="form-select @error('country_id') is-invalid @enderror">
                        <option value="">Select Country</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->id }}" {{ old('country_id', auth()->user()->country_id) == $country->id ? 'selected' : '' }}>
                                {{ $country->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('country_id')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- City Dropdown -->
                <div class="mb-3">
                    <label for="city" class="form-label">City</label>
                    <select id="city" name="city_id" class="form-select @error('city_id') is-invalid @enderror">
                        <option value="">Select City</option>
                        @if(auth()->user()->city_id && isset($cities))
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ old('city_id', auth()->user()->city_id) == $city->id ? 'selected' : '' }}>
                                    {{ $city->name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                    @error('city_id')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Area -->
                <div class="mb-3">
                    <label for="area" class="form-label">Area</label>
                    <input type="text" name="area" id="area" class="form-control @error('area') is-invalid @enderror" value="{{ old('area', auth()->user()->area) }}" placeholder="Enter your area/locality">
                    @error('area')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Update Profile</button>
                
                @if (session('status') === 'profile-updated')
                    <span class="text-success ms-2">Profile updated successfully!</span>
                @endif
            </form>
        </div>
    </div>

    <!-- Update Password -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Update Password</h5>
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                @method('PUT')
                <!-- Current Password -->
                <div class="mb-3">
                    <label for="current_password" class="form-label">Current Password</label>
                    <input type="password" name="current_password" id="current_password" class="form-control @error('current_password') is-invalid @enderror">
                    @error('current_password')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <!-- New Password -->
                <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror">
                    @error('password')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <!-- Confirm Password -->
                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirm New Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror">
                    @error('password_confirmation')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-warning">Update Password</button>
                
                @if (session('status') === 'password-updated')
                    <span class="text-success ms-2">Password updated successfully!</span>
                @endif
            </form>
        </div>
    </div>

    <!-- Delete User -->
    <div class="card mb-4 border-danger">
        <div class="card-body">
            @include("profile.partials.delete-user-form")
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const countrySelect = document.getElementById('country');
        const citySelect = document.getElementById('city');

        countrySelect.addEventListener('change', function() {
            const countryId = this.value;
            
            if(countryId) {
                fetch(`/get-cities/${countryId}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    citySelect.innerHTML = '<option value="">Select City</option>';
                    data.forEach(function(city) {
                        citySelect.innerHTML += `<option value="${city.id}">${city.name}</option>`;
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            } else {
                citySelect.innerHTML = '<option value="">Select City</option>';
            }
        });
    });
</script>
</div>
@endsection