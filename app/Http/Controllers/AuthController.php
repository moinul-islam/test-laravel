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
     * Send OTP - Store in Cache for 5 minutes (CONTROLLER MEMORY)
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
        
        // CACHE te store - 5 minutes hold korbe (DB te na)
        Cache::put('otp_' . $loginId, $otp, now()->addMinutes(5));
        
        \Log::info('OTP generated and cached for 5 min: ' . $loginId . ' | OTP: ' . $otp);
        
        try {
            if (filter_var($loginId, FILTER_VALIDATE_EMAIL)) {
                // Email OTP
                Mail::raw("Your OTP code is: $otp\n\nThis OTP will expire in 5 minutes.", function($message) use ($loginId) {
                    $message->to($loginId)->subject('Email Verification - eINFO');
                });
                \Log::info('OTP email sent to: ' . $loginId);
            } else {
                // SMS OTP
                $phone = preg_replace('/\D/', '', $loginId);
                $this->smsService->sendSms($phone, "Your eINFO OTP is: " . $otp);
                \Log::info('OTP SMS sent to: ' . $phone);
            }

            return response()->json(['success' => true, 'message' => 'OTP sent successfully']);
        } catch (\Exception $e) {
            \Log::error('OTP send failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to send OTP'], 500);
        }
    }

    /**
     * Verify OTP and Store Registration Data in Cache
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
        
        // Cache theke OTP check koro
        $cachedOtp = Cache::get('otp_' . $loginId);
        
        \Log::info('Verifying OTP for: ' . $loginId . ' | Input: ' . $inputOtp . ' | Cached: ' . $cachedOtp);
        
        if (!$cachedOtp) {
            \Log::error('OTP expired or not found for: ' . $loginId);
            return response()->json(['error' => 'OTP expired. Please request a new one.'], 422);
        }

        if ($cachedOtp != $inputOtp) {
            \Log::error('Invalid OTP for: ' . $loginId);
            return response()->json(['error' => 'Invalid OTP. Please try again.'], 422);
        }

        // OTP correct - Mark as verified in cache for 5 more minutes
        Cache::put('otp_verified_' . $loginId, true, now()->addMinutes(5));
        
        \Log::info('OTP verified successfully for: ' . $loginId);
        
        return response()->json(['verified' => true, 'message' => 'OTP verified successfully']);
    }

    /**
     * Complete Registration - Only if OTP verified
     */
    public function completeRegistration(Request $request)
    {
        $loginId = $request->email;

        // SECURITY CHECK: OTP verify hoiche kina check koro
        if (!Cache::get('otp_verified_' . $loginId)) {
            \Log::error('Unauthorized attempt - OTP not verified for: ' . $loginId);
            return response()->json(['error' => 'Please verify OTP first'], 403);
        }

        \Log::info('Starting registration for verified user: ' . $loginId);

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

                \Log::info('New user created: ' . $user->id . ' | Username: ' . $username);

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
                return response()->json(['error' => $validator->errors()->first()], 422);
            }

            try {
                $user->password = Hash::make($request->password);
                $user->save();
                \Log::info('Password updated for user: ' . $user->id);
            } catch (\Exception $e) {
                \Log::error('Password update failed: ' . $e->getMessage());
                return response()->json(['error' => 'Password update failed'], 500);
            }
        }

        // Clear cache - Security
        Cache::forget('otp_' . $loginId);
        Cache::forget('otp_verified_' . $loginId);
        \Log::info('Cache cleared for security: ' . $loginId);

        // Login user
        Auth::login($user);
        \Log::info('User logged in: ' . $user->username);
        
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