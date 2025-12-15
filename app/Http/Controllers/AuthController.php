<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Country;
use App\Models\City;
use App\Services\SmsService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Show auth page with countries
     */
    public function showAuthPage()
    {
        $countries = Country::all();
        return view('myauth', compact('countries'));
    }

    /**
     * Check if email/phone exists and has password
     */
    public function checkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid input'], 422);
        }

        $loginId = $request->email;
        $user = null;

        if (filter_var($loginId, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', strtolower($loginId))->first();
        } else {
            $phone = preg_replace('/\D/', '', $loginId);
            $user = User::where('phone_number', $phone)->first();
        }
        
        return response()->json([
            'exists' => $user ? true : false,
            'has_password' => ($user && $user->password) ? true : false
        ]);
    }

    /**
     * Send OTP - Store in Session for 5 minutes
     */
    public function sendOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'type' => 'required|in:register,reset'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        // ✅ Email/Phone normalize করুন - IMPORTANT
        $loginId = trim($request->email);
        
        // Email হলে lowercase করুন
        if (filter_var($loginId, FILTER_VALIDATE_EMAIL)) {
            $loginId = strtolower($loginId);
        } else {
            // Phone হলে শুধু numbers রাখুন
            $loginId = preg_replace('/\D/', '', $loginId);
        }
        
        $otp = rand(100000, 999999);
        
        // ✅ Session te store - 5 minutes (300 seconds)
        session([
            'otp_' . $loginId => $otp,
            'otp_time_' . $loginId => now()->timestamp,
            'otp_type_' . $loginId => $request->type
        ]);
        
        // ✅ Session force save করুন
        session()->save();
        
        \Log::info('=== OTP SENT ===');
        \Log::info('Login ID (normalized): ' . $loginId);
        \Log::info('Session Key: otp_' . $loginId);
        \Log::info('OTP: ' . $otp);
        \Log::info('Time: ' . now()->timestamp);
        \Log::info('Type: ' . $request->type);
        \Log::info('Session ID: ' . session()->getId());
        
        try {
            if (filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
                // Email OTP
                Mail::raw("Your OTP code is: $otp\n\nThis OTP will expire in 5 minutes.", function($message) use ($request) {
                    $message->to($request->email)->subject('Email Verification - eINFO');
                });
                \Log::info('✓ OTP email sent successfully to: ' . $request->email);
            } else {
                // SMS OTP
                $phone = preg_replace('/\D/', '', $request->email);
                $this->smsService->sendSms($phone, "Your eINFO OTP is: " . $otp);
                \Log::info('✓ OTP SMS sent successfully to: ' . $phone);
            }

            return response()->json([
                'success' => true, 
                'message' => 'OTP sent successfully',
                'debug' => [
                    'session_id' => session()->getId(),
                    'login_id' => $loginId
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('OTP send failed: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Failed to send OTP. Please try again.'], 500);
        }
    }

    /**
     * Verify OTP and Mark as Verified
     */
    public function verifyOTP(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string',
                'otp' => 'required|digits:6'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 422);
            }

            // ✅ Normalize করুন
            $loginId = trim($request->email);
            
            if (filter_var($loginId, FILTER_VALIDATE_EMAIL)) {
                $loginId = strtolower($loginId);
            } else {
                $loginId = preg_replace('/\D/', '', $loginId);
            }
            
            $inputOtp = trim($request->otp);
            
            // Session data retrieve
            $sessionOtp = session('otp_' . $loginId);
            $otpTime = session('otp_time_' . $loginId);
            
            \Log::info('=== OTP VERIFY ATTEMPT ===');
            \Log::info('Login ID: ' . $loginId);
            \Log::info('Session Key: otp_' . $loginId);
            \Log::info('Input OTP: ' . $inputOtp);
            \Log::info('Session OTP: ' . $sessionOtp);
            \Log::info('OTP Time: ' . $otpTime);
            \Log::info('Current Time: ' . now()->timestamp);
            
            // Check 1: OTP exists?
            if (empty($sessionOtp) || empty($otpTime)) {
                \Log::error('❌ OTP not found in session');
                \Log::error('Available keys: ' . json_encode(array_keys(session()->all())));
                
                return response()->json([
                    'error' => 'OTP not found or expired. Please request a new one.'
                ], 422);
            }

            // Check 2: Time check (5 minutes = 300 seconds)
            $timeDiff = now()->timestamp - $otpTime;
            \Log::info('Time difference: ' . $timeDiff . ' seconds');
            
            if ($timeDiff > 300) {
                session()->forget([
                    'otp_' . $loginId, 
                    'otp_time_' . $loginId,
                    'otp_type_' . $loginId
                ]);
                
                \Log::error('❌ OTP expired (' . $timeDiff . ' seconds)');
                return response()->json([
                    'error' => 'OTP expired. Please request a new one.'
                ], 422);
            }

            // Check 3: OTP match
            if ($sessionOtp != $inputOtp) {
                \Log::error('❌ Invalid OTP. Expected: ' . $sessionOtp . ', Got: ' . $inputOtp);
                return response()->json([
                    'error' => 'Invalid OTP. Please try again.'
                ], 422);
            }

            // ✅ Success - Mark as verified
            session([
                'otp_verified_' . $loginId => true,
                'otp_verified_time_' . $loginId => now()->timestamp
            ]);
            session()->save();
            
            \Log::info('✅ OTP VERIFIED SUCCESSFULLY');
            
            return response()->json([
                'verified' => true, 
                'message' => 'OTP verified successfully'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('❌ VERIFY OTP EXCEPTION: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'error' => 'Verification failed. Please try again.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Complete Registration - Only if OTP verified
     */
   /**
 * Complete Registration - Only if OTP verified
 */
    public function completeRegistration(Request $request)
    {
        $loginId = $request->email;

        // ✅ Normalize করুন - IMPORTANT
        if (filter_var($loginId, FILTER_VALIDATE_EMAIL)) {
            $loginId = strtolower($loginId);
        } else {
            $loginId = preg_replace('/\D/', '', $loginId);
        }

        // SECURITY CHECK: OTP verify hoiche kina check koro
        if (!session('otp_verified_' . $loginId)) {
            \Log::error('=== UNAUTHORIZED ATTEMPT ===');
            \Log::error('Email: ' . $loginId);
            \Log::error('OTP not verified');
            return response()->json(['error' => 'Please verify OTP first'], 403);
        }

        \Log::info('=== REGISTRATION START ===');
        \Log::info('Email: ' . $loginId);

        // Email or Phone determine koro
        $email = null;
        $phone = null;
        if (filter_var($loginId, FILTER_VALIDATE_EMAIL)) {
            $email = $loginId;
            $user = User::where('email', $email)->first();
        } else {
            $phone = $loginId;
            $user = User::where('phone_number', $phone)->first();
        }

        if (!$user) {
            // NEW USER - Registration
            $validator = Validator::make($request->all(), [
                'email' => 'required|string',
                'name' => 'required|string|max:255',
                'password' => 'required|string|min:8|confirmed',
                'country_id' => 'required|exists:countries,id',
                'city_id' => 'required|exists:cities,id',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
                'fcm_token' => 'nullable|string'  // ✅ FCM token validation
            ]);

            if ($validator->fails()) {
                \Log::error('Validation failed: ' . json_encode($validator->errors()));
                return response()->json(['error' => $validator->errors()->first()], 422);
            }

            try {
                $username = $this->generateUniqueUsername($request->name);
                
                $imageName = null;
                if ($request->hasFile('image')) {
                    $imageName = time().'.'.$request->image->extension();  
                    $request->image->move(public_path('profile-image'), $imageName);
                }

                // ✅ User create - fcm_token REMOVED
                $user = User::create([
                    'image' => $imageName,
                    'name' => $request->name,
                    'username' => $username,
                    'email' => $email,
                    'phone_number' => $phone,
                    'country_id' => $request->country_id,
                    'city_id' => $request->city_id,
                    'password' => Hash::make($request->password),
                ]);

                \Log::info('✓ NEW USER CREATED');
                \Log::info('User ID: ' . $user->id);
                \Log::info('Username: ' . $username);

                // ✅ FCM token শুধুমাত্র user_fcm_tokens table এ save করুন
                if ($request->filled('fcm_token')) {
                    \App\Models\UserFcmToken::create([
                        'user_id' => $user->id,
                        'fcm_token' => $request->fcm_token,
                    ]);
                    \Log::info('✓ FCM TOKEN SAVED in user_fcm_tokens table');
                    \Log::info('FCM Token: ' . $request->fcm_token);
                }

            } catch (\Exception $e) {
                \Log::error('User creation failed: ' . $e->getMessage());
                \Log::error('Stack trace: ' . $e->getTraceAsString());
                return response()->json(['error' => 'Registration failed'], 500);
            }

        } else {
            // EXISTING USER - Password Reset
            $validator = Validator::make($request->all(), [
                'email' => 'required|string',
                'password' => 'required|string|min:8|confirmed',
                'fcm_token' => 'nullable|string'  // ✅ FCM token validation for password reset
            ]);

            if ($validator->fails()) {
                \Log::error('Password validation failed: ' . json_encode($validator->errors()));
                return response()->json(['error' => $validator->errors()->first()], 422);
            }

            try {
                $user->password = Hash::make($request->password);
                $user->save();
                \Log::info('✓ PASSWORD UPDATED');
                \Log::info('User ID: ' . $user->id);

                // ✅ Password reset এর সময়ও FCM token save করুন (শুধু user_fcm_tokens table এ)
                if ($request->filled('fcm_token')) {
                    $existingToken = \App\Models\UserFcmToken::where('user_id', $user->id)
                        ->where('fcm_token', $request->fcm_token)
                        ->first();

                    if (!$existingToken) {
                        \App\Models\UserFcmToken::create([
                            'user_id' => $user->id,
                            'fcm_token' => $request->fcm_token,
                        ]);
                        \Log::info('✓ FCM TOKEN SAVED during password reset');
                        \Log::info('FCM Token: ' . $request->fcm_token);
                    }
                }

            } catch (\Exception $e) {
                \Log::error('Password update failed: ' . $e->getMessage());
                \Log::error('Stack trace: ' . $e->getTraceAsString());
                return response()->json(['error' => 'Password update failed'], 500);
            }
        }

        // Clear session - Security
        session()->forget([
            'otp_' . $loginId,
            'otp_time_' . $loginId,
            'otp_verified_' . $loginId,
            'otp_type_' . $loginId
        ]);
        \Log::info('Session cleared for security');

        // Login user
        Auth::login($user);
        \Log::info('✓ USER LOGGED IN: ' . $user->username);
        
        // ✅ User identifier তৈরি করুন (অন্য controllers এর মতো)
        $userIdentifier = $user->username ?? str_replace(['@', '.', '+', '-', ' '], '', $user->email);
        
        // ✅ Redirect to /login-success/{userIdentifier}
        return response()->json([
            'success' => true,
            'message' => 'Registration completed successfully',
            'redirect' => url("/login-success/{$userIdentifier}")
        ]);
    } 

    /**
     * Get cities by country
     */
    public function getCities($country_id)
    {
        $cities = City::where('country_id', $country_id)->orderBy('name')->get(['id', 'name']);
        return response()->json($cities);
    }

    /**
     * Login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'password' => 'required|string',
            'fcm_token' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $loginId = $request->email;
        $credentials = [];
        $user = null;

        if (filter_var($loginId, FILTER_VALIDATE_EMAIL)) {
            $email = strtolower($loginId);
            $credentials = ['email' => $email, 'password' => $request->password];
            $user = User::where('email', $email)->first();
        } else {
            $phone = preg_replace('/\D/', '', $loginId);
            $credentials = ['phone_number' => $phone, 'password' => $request->password];
            $user = User::where('phone_number', $phone)->first();
        }

        if (!$user) {
            return response()->json(['error' => 'The provided email/phone is not registered.'], 422);
        }

        if (Auth::attempt($credentials, true)) {
            $request->session()->regenerate();

            // ✅ FCM token save করুন (যদি পাঠানো হয়)
            if ($request->filled('fcm_token')) {
                $existingToken = \App\Models\UserFcmToken::where('user_id', $user->id)
                    ->where('fcm_token', $request->fcm_token)
                    ->first();

                if (!$existingToken) {
                    \App\Models\UserFcmToken::create([
                        'user_id' => $user->id,
                        'fcm_token' => $request->fcm_token,
                    ]);
                }
            }

            // ✅ User identifier তৈরি করুন (অন্য controllers এর মতো)
            $userIdentifier = $user->username ?? str_replace(['@', '.', '+', '-', ' '], '', $user->email);

            // ✅ Redirect to /login-success/{userIdentifier}
            return response()->json([
                'success' => true, 
                'redirect' => url("/login-success/{$userIdentifier}")
            ]);
        }

        return response()->json(['error' => 'The provided password is incorrect.'], 422);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        // ✅ Current user এর identifier নিন logout করার আগে
        $user = Auth::user();
        
        if ($user) {
            $userIdentifier = $user->username ?? str_replace(['@', '.', '+', '-', ' '], '', $user->email);
            
            // ✅ FCM tokens delete করুন
            $user->fcmTokens()->delete();
        }
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }

    /**
     * Generate unique username
     */
    private function generateUniqueUsername($baseName = null)
    {
        if ($baseName) {
            $username = $this->transliterateName($baseName);
        } else {
            $username = 'user' . Str::random(6) . rand(100, 999);
        }

        if (empty($username) || strlen($username) < 3) {
            $username = 'user' . Str::random(6) . rand(100, 999);
        }

        $originalUsername = $username;
        $counter = 1;
        
        while (User::where('username', $username)->exists()) {
            $username = $originalUsername . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Transliterate name
     */
    private function transliterateName($name)
    {
        $name = trim(strtolower($name));
        
        if (function_exists('iconv')) {
            $transliterated = @iconv('UTF-8', 'ASCII//TRANSLIT', $name);
            if (!empty($transliterated) && $transliterated !== false) {
                $cleaned = preg_replace('/[^a-zA-Z0-9]/', '', $transliterated);
                if (strlen($cleaned) >= 3) {
                    return $cleaned;
                }
            }
        }

        $result = $this->manualTransliterate($name);
        $cleaned = preg_replace('/[^a-zA-Z0-9]/', '', $result);
        
        if (strlen($cleaned) >= 3) {
            return $cleaned;
        }

        return 'user' . Str::random(6) . rand(100, 999);
    }

    /**
     * Manual transliteration
     */
    private function manualTransliterate($name)
    {
        $charMap = [
            "আ" => "a", "ই" => "i", "উ" => "u", "এ" => "e", "ও" => "o", "অ" => "a",
            "ক" => "k", "খ" => "kh", "গ" => "g", "ঘ" => "gh", "চ" => "ch", "ছ" => "chh", 
            "জ" => "j", "ঝ" => "jh", "ট" => "t", "ঠ" => "th", "ড" => "d", "ঢ" => "dh", 
            "ত" => "t", "থ" => "th", "দ" => "d", "ধ" => "dh", "ন" => "n", "প" => "p", 
            "ফ" => "ph", "ব" => "b", "ভ" => "bh", "ম" => "m", "য" => "j", "র" => "r", 
            "ল" => "l", "শ" => "sh", "ষ" => "sh", "স" => "s", "হ" => "h",
            " " => "", "-" => "", "_" => "", "." => "", "," => ""
        ];

        return strtr($name, $charMap);
    }
}