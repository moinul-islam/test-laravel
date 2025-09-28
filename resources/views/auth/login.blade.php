@extends("frontend.master")
@section('main-content')
<!-- Page Header -->
<div class="container mt-4">


<div class="">
    <div class="">
        <div class="card shadow-sm rounded-3">
            <div class="card-body p-4">
                <h3 class="mb-4 text-center">Login</h3>

                <!-- Session Status -->
                @if (session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email or Phone Number</label>
                        <input id="email" type="text"
                               class="form-control @error('email') is-invalid @enderror"
                               name="email" value="{{ old('email') }}" required autofocus
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
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input id="password" type="password"
                               class="form-control @error('password') is-invalid @enderror"
                               name="password" required>
                               <input type="hidden" name="fcm_token" id="fcm_token_field" value="">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="mb-3 form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember Me</label>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        @if (Route::has('password.request'))
                            <a class="small text-decoration-none" href="{{ route('password.request') }}">
                                Forgot your password?
                            </a>
                        @endif

                        <button type="submit" class="btn btn-primary px-4">
                            Log in
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="mt-4 text-center">
            <a href="/register">Create a Account</a>
        </div>
    </div>
</div>

</div>
@endsection