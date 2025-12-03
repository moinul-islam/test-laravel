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

        $loginId = $request->email;
        $otp = rand(100000, 999999);
        
        // Session te store - 5 minutes hold korbe
        session([
            'otp_' . $loginId => $otp,
            'otp_time_' . $loginId => now()->timestamp
        ]);
        
        \Log::info('=== OTP SENT ===');
        \Log::info('Email: ' . $loginId);
        \Log::info('OTP: ' . $otp);
        \Log::info('Time: ' . now());
        
        try {
            if (filter_var($loginId, FILTER_VALIDATE_EMAIL)) {
                // Email OTP
                Mail::raw("Your OTP code is: $otp\n\nThis OTP will expire in 5 minutes.", function($message) use ($loginId) {
                    $message->to($loginId)->subject('Email Verification - eINFO');
                });
                \Log::info('OTP email sent successfully');
            } else {
                // SMS OTP
                $phone = preg_replace('/\D/', '', $loginId);
                $this->smsService->sendSms($phone, "Your eINFO OTP is: " . $otp);
                \Log::info('OTP SMS sent successfully');
            }

            return response()->json(['success' => true, 'message' => 'OTP sent successfully']);
        } catch (\Exception $e) {
            \Log::error('OTP send failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to send OTP'], 500);
        }
    }

    /**
     * Verify OTP and Store Registration Data in Session
     */
    public function verifyOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'otp' => 'required|digits:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $loginId = $request->email;
        $inputOtp = $request->otp;
        
        // Session theke OTP ber koro
        $sessionOtp = session('otp_' . $loginId);
        $otpTime = session('otp_time_' . $loginId);
        
        \Log::info('=== OTP VERIFY ===');
        \Log::info('Email: ' . $loginId);
        \Log::info('Input OTP: ' . $inputOtp);
        \Log::info('Session OTP: ' . $sessionOtp);
        \Log::info('OTP Time: ' . $otpTime);
        \Log::info('Current Time: ' . now()->timestamp);
        
        if (!$sessionOtp || !$otpTime) {
            \Log::error('OTP not found in session');
            return response()->json(['error' => 'OTP expired. Please request a new one.'], 422);
        }

        // 5 minutes = 300 seconds check
        $timeDiff = now()->timestamp - $otpTime;
        \Log::info('Time difference: ' . $timeDiff . ' seconds');
        
        if ($timeDiff > 300) {
            session()->forget(['otp_' . $loginId, 'otp_time_' . $loginId]);
            \Log::error('OTP expired - More than 5 minutes');
            return response()->json(['error' => 'OTP expired. Please request a new one.'], 422);
        }

        if ($sessionOtp != $inputOtp) {
            \Log::error('Invalid OTP');
            return response()->json(['error' => 'Invalid OTP. Please try again.'], 422);
        }

        // OTP correct - Mark as verified for 5 more minutes
        session(['otp_verified_' . $loginId => true]);
        
        \Log::info('✓ OTP VERIFIED SUCCESSFULLY');
        
        return response()->json(['verified' => true, 'message' => 'OTP verified successfully']);
    }

    /**
     * Complete Registration - Only if OTP verified
     */
    public function completeRegistration(Request $request)
    {
        $loginId = $request->email;

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
            $email = strtolower($loginId);
            $user = User::where('email', $email)->first();
        } else {
            $phone = preg_replace('/\D/', '', $loginId);
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
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
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

                $user = User::create([
                    'image' => $imageName,
                    'name' => $request->name,
                    'username' => $username,
                    'email' => $email,
                    'phone_number' => $phone,
                    'country_id' => $request->country_id,
                    'city_id' => $request->city_id,
                    'password' => Hash::make($request->password),
                    'fcm_token' => $request->fcm_token ?? null,
                ]);

                \Log::info('✓ NEW USER CREATED');
                \Log::info('User ID: ' . $user->id);
                \Log::info('Username: ' . $username);

            } catch (\Exception $e) {
                \Log::error('User creation failed: ' . $e->getMessage());
                return response()->json(['error' => 'Registration failed'], 500);
            }

        } else {
            // EXISTING USER - Password Reset
            $validator = Validator::make($request->all(), [
                'email' => 'required|string',
                'password' => 'required|string|min:8|confirmed'
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
            } catch (\Exception $e) {
                \Log::error('Password update failed: ' . $e->getMessage());
                return response()->json(['error' => 'Password update failed'], 500);
            }
        }

        // Clear session - Security
        session()->forget([
            'otp_' . $loginId,
            'otp_time_' . $loginId,
            'otp_verified_' . $loginId
        ]);
        \Log::info('Session cleared for security');

        // Login user
        Auth::login($user);
        \Log::info('✓ USER LOGGED IN: ' . $user->username);
        
        return response()->json([
            'success' => true,
            'message' => 'Registration completed successfully',
            'redirect' => url('/')
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
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $loginId = $request->email;
        $credentials = [];

        if (filter_var($loginId, FILTER_VALIDATE_EMAIL)) {
            $credentials = ['email' => strtolower($loginId), 'password' => $request->password];
        } else {
            $phone = preg_replace('/\D/', '', $loginId);
            $credentials = ['phone_number' => $phone, 'password' => $request->password];
        }

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return response()->json(['success' => true, 'redirect' => url('/')]);
        }

        return response()->json(['error' => 'Invalid credentials'], 422);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
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