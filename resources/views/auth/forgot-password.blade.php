<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
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

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Reset Password') }}
            </x-primary-button>
        </div>
    </form>
    @if(session('email') || session('phone'))
    @php
        $user = null;
        if(session('email')) {
            $user = \App\Models\User::where('email', session('email'))->first();
        } elseif(session('phone')) {
            $user = \App\Models\User::where('phone_number', session('phone'))->first();
        }
    @endphp
    
    @if($user && $user->otp_verified && $user->otp_verified > 0 && $user->otp_verified < 9)
        <p class="text-info">Password reset attempts: {{ $user->otp_verified }}/9</p>
    @endif
@endif
</x-guest-layout>
