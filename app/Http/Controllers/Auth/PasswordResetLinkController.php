<?php

namespace App\Http\Controllers\Auth;
use App\Services\SmsService;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, SmsService $smsService): RedirectResponse
    {
        $request->validate([
            'login_id' => ['required', 'string'], // email বা phone
        ]);
       
        $login = $request->input('login_id');
       
        // Check if input is email
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $user = \App\Models\User::where('email', $login)->first();
            if (!$user) {
                return back()->withErrors(['login_id' => 'User with this email not found.']);
            }
            
            // Check if OTP attempts exceeded for password reset
            if ($user->otp_verified == 9) {
                return back()->withErrors(['login_id' => 'You have reached the maximum password reset attempts. Please contact support.']);
            }
    
            // Current otp_verified count check করুন
            $currentCount = $user->otp_verified ?? 0;
            
            // যদি 9 বার হয়ে গেছে তাহলে 9 set করুন এবং OTP পাঠানো বন্ধ করুন
            if ($currentCount >= 9) {
                $user->otp_verified = 9;
                $user->save();
                return back()->withErrors(['login_id' => 'Maximum password reset attempts reached. Please contact support.']);
            }
           
            // Generate OTP
            $otp = rand(100000, 999999);
            $user->otp = $otp;
            
            // otp_verified count বৃদ্ধি করুন
            $user->otp_verified = $currentCount + 1;
            $user->save();
           
            // Send OTP via Email
            Mail::raw("Your password reset OTP code is: $otp", function($message) use ($user) {
                $message->to($user->email)
                        ->subject('Password Reset OTP - eINFO');
            });
           
            // Redirect to reset password page with email
            return redirect()->route('password.reset', ['token' => 'otp-reset'])
                            ->with('email', $user->email)
                            ->with('status', 'OTP sent to your email.');
           
        } else {
            // Assume it's phone number
            $user = \App\Models\User::where('phone_number', $login)->first();
            if (!$user) {
                return back()->withErrors(['login_id' => 'User with this phone number not found.']);
            }
            
            // Check if OTP attempts exceeded for password reset
            if ($user->otp_verified == 9) {
                return back()->withErrors(['login_id' => 'You have reached the maximum password reset attempts. Please contact support.']);
            }
    
            // Current otp_verified count check করুন
            $currentCount = $user->otp_verified ?? 0;
            
            // যদি 9 বার হয়ে গেছে তাহলে 9 set করুন এবং OTP পাঠানো বন্ধ করুন
            if ($currentCount >= 9) {
                $user->otp_verified = 9;
                $user->save();
                return back()->withErrors(['login_id' => 'Maximum password reset attempts reached. Please contact support.']);
            }
           
            // Generate OTP
            $otp = rand(100000, 999999);
            $user->otp = $otp;
            
            // otp_verified count বৃদ্ধি করুন
            $user->otp_verified = $currentCount + 1;
            $user->save();
           
            // Send SMS
            $smsService->sendSms($user->phone_number, "Your eINFO password reset OTP is: " . $otp);
           
            // Redirect to reset password page with phone
            return redirect()->route('password.reset', ['token' => 'otp-reset'])
                            ->with('phone', $user->phone_number)
                            ->with('status', 'OTP sent to your phone number.');
        }
    }

}
