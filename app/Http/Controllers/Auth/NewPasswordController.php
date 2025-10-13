<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }
    public function setPassword(Request $request): View
    {
        return view('auth.set-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'email' => ['required_without:phone', 'email'],
            'phone' => ['required_without:email', 'string'],
        ]);
       
        // Find user by email or phone
        if ($request->email) {
            $user = User::where('email', $request->email)->first();
        } else {
            $user = User::where('phone_number', $request->phone)->first();
        }
       
        if (!$user) {
            return back()->withErrors(['otp' => 'User not found.']);
        }
       
        // Verify OTP
        if ($user->otp != $request->otp) {
            return back()->withErrors(['otp' => 'Invalid OTP code.']);
        }
       
        // Reset password
        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
            'otp' => null, // Clear OTP
            'otp_verified' => 0, // Reset password reset attempts counter
        ])->save();
       
        // Fire password reset event (optional)
        event(new PasswordReset($user));
       
        // Automatically login the user
        Auth::login($user);
       
        // Redirect to dashboard or intended page
        return redirect()->intended(route('dashboard'))->with('status', 'Password has been reset successfully and you are now logged in!');
    }
    public function setPasswordStore(Request $request): RedirectResponse
    {
      
        $loginInput = $request->email;

        // চেক করব email না phone
        if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
            // Email দিয়ে check
            $user = \App\Models\User::where('email', $loginInput)->first();
            $inputType = 'email';
        } else {
            // Phone দিয়ে check
            $user = \App\Models\User::where('phone_number', $loginInput)->first();
            $inputType = 'phone';
        }
       
       
       
       


        // Verify OTP
        if ($user->otp != $request->otp) {
            return back()->withErrors(['otp' => 'Invalid OTP code.']);
        }
       
        // Reset password
        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
            'otp' => null, // Clear OTP
            'otp_verified' => 0, // Reset password reset attempts counter
        ])->save();
       
        // Fire password reset event (optional)
        event(new PasswordReset($user));
       
        // Automatically login the user
        Auth::login($user);
       
        // Redirect to dashboard or intended page
        return redirect()->intended(route('dashboard'))->with('status', 'Password has been reset successfully and you are now logged in!');
    }
}
