<x-guest-layout>
    <!-- Success Message -->
    @if(session('status'))
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        
        <!-- Hidden fields for email/phone -->
        @if(session('email'))
            <input type="hidden" name="email" value="{{ session('email') }}">
        @endif
        @if(session('phone'))
            <input type="hidden" name="phone" value="{{ session('phone') }}">
        @endif
        
        <!-- Display current email/phone -->
        <div>
            <x-input-label for="login_display" :value="__('Email/Phone')" />
            <x-text-input id="login_display" class="block mt-1 w-full bg-gray-100" 
                         type="text" 
                         value="{{ session('email') ?? session('phone') ?? old('email') ?? old('phone') }}" 
                         readonly />
        </div>
        
        <!-- OTP Input -->
        <div class="mt-4">
            <x-input-label for="otp" :value="__('Enter OTP Code')" />
            <x-text-input id="otp" class="block mt-1 w-full" 
                         type="text" 
                         name="otp" 
                         :value="old('otp')"
                         required 
                         autofocus 
                         autocomplete="off" 
                         maxlength="6" />
            <x-input-error :messages="$errors->get('otp')" class="mt-2" />
        </div>
        
        <!-- New Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('New Password')" />
            <x-text-input id="password" class="block mt-1 w-full" 
                         type="password" 
                         name="password" 
                         required 
                         autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>
        
        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                         type="password"
                         name="password_confirmation" 
                         required 
                         autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>
        
        <div class="flex items-center justify-between mt-6">
            <!-- Resend OTP Link -->
            <a href="{{ route('password.request') }}" class="text-sm text-gray-600 hover:text-gray-900">
                {{ __('Resend OTP?') }}
            </a>
            
            <x-primary-button>
                {{ __('Reset Password') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>