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
use Illuminate\Support\Str;

class AuthController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Show auth page with countries (register page er moto)
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
            return response()->json([
                'error' => 'Invalid input'
            ], 422);
        }

        $loginId = $request->email;
        $user = null;

        // Check if email or phone (register page er logic)
        if (filter_var($loginId, FILTER_VALIDATE_EMAIL)) {
            // Email check
            $email = strtolower($loginId);
            $user = User::where('email', $email)->first();
        } else {
            // Phone check - shudhu number rakhbo
            $phone = preg_replace('/\D/', '', $loginId);
            $user = User::where('phone_number', $phone)->first();
        }
        
        return response()->json([
            'exists' => $user ? true : false,
            'has_password' => ($user && $user->password) ? true : false
        ]);
    }

    /**
     * Send OTP to email or phone (register page er moto)
     */
    public function sendOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'type' => 'required|in:register,reset'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ], 422);
        }

        // Generate 6 digit OTP
        $otp = rand(100000, 999999);
        $loginId = $request->email;
        
        // Store OTP in session (register page er moto)
        session(['otp' => $otp]);
        session(['otp_' . $loginId => $otp]);
        session(['otp_time_' . $loginId => now()]);
        
        try {
            // Check if email or phone
            if (filter_var($loginId, FILTER_VALIDATE_EMAIL)) {
                // Send Email OTP (register page er exact code)
                Mail::raw("Your OTP code is: $otp", function($message) use ($loginId) {
                    $message->to($loginId)
                            ->subject('Email Verification - eINFO');
                });
            } else {
                // Send SMS OTP (register page er exact code)
                $phone = preg_replace('/\D/', '', $loginId);
                $response = $this->smsService->sendSms($phone, "Your eINFO OTP is: " . $otp);
            }

            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to send OTP. Please try again.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify OTP
     */
    public function verifyOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'otp' => 'required|digits:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ], 422);
        }

        $loginId = $request->email;
        $storedOTP = session('otp_' . $loginId);
        $otpTime = session('otp_time_' . $loginId);
        
        if (!$storedOTP) {
            return response()->json([
                'error' => 'OTP not found. Please request a new one.'
            ], 422);
        }

        // Check if OTP expired (10 minutes)
        if ($otpTime && now()->diffInMinutes($otpTime) > 10) {
            session()->forget(['otp_' . $loginId, 'otp_time_' . $loginId]);
            return response()->json([
                'error' => 'OTP expired. Please request a new one.'
            ], 422);
        }

        if ($storedOTP != $request->otp) {
            return response()->json([
                'error' => 'Invalid OTP. Please try again.'
            ], 422);
        }

        // Mark OTP as verified
        session(['otp_verified_' . $loginId => true]);
        
        return response()->json([
            'verified' => true,
            'message' => 'OTP verified successfully'
        ]);
    }

    /**
     * Complete registration or password reset (register page er moto logic)
     */
    public function completeRegistration(Request $request)
    {
        $loginId = $request->email;

        // Check if OTP was verified
        if (!session('otp_verified_' . $loginId)) {
            return response()->json([
                'error' => 'Please verify OTP first'
            ], 422);
        }

        // Determine if email or phone (register page logic)
        $email = null;
        $phone = null;

        if (filter_var($loginId, FILTER_VALIDATE_EMAIL)) {
            // Email input
            $email = strtolower($loginId);
            
            // Email unique check (register page logic)
            $user = User::where('email', $email)->first();
        } else {
            // Phone input - shudhu number rakhbo
            $phone = preg_replace('/\D/', '', $loginId);
            
            // Phone unique check (register page logic)
            $user = User::where('phone_number', $phone)->first();
        }

        if (!$user) {
            // New user registration (register page er logic follow kore)
            $validator = Validator::make($request->all(), [
                'email' => 'required|string',
                'name' => 'required|string|max:255',
                'password' => 'required|string|min:8|confirmed',
                'country_id' => 'required|exists:countries,id',
                'city_id' => 'required|exists:cities,id',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            // Generate unique username (register page theke newa)
            $username = $this->generateUniqueUsername($request->name);

            // Handle image upload (register page er exact code)
            $imageName = null;
            if ($request->hasFile('image')) {
                $imageName = time().'.'.$request->image->extension();  
                $request->image->move(public_path('profile-image'), $imageName);
            }

            // User create (register page er moto)
            $user = User::create([
                'image' => $imageName,
                'name' => $request->name,
                'username' => $username,
                'email' => $email,           // jodi email hoy
                'phone_number' => $phone,    // jodi phone hoy
                'country_id' => $request->country_id,
                'city_id' => $request->city_id,
                'password' => Hash::make($request->password),
                'fcm_token' => $request->fcm_token ?? null,
            ]);

        } else {
            // Existing user - password update (jodi user ache but password nai)
            $validator = Validator::make($request->all(), [
                'email' => 'required|string',
                'password' => 'required|string|min:8|confirmed'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            $user->password = Hash::make($request->password);
            $user->save();
        }

        // Clear OTP sessions
        session()->forget([
            'otp',
            'otp_' . $loginId, 
            'otp_time_' . $loginId, 
            'otp_verified_' . $loginId
        ]);

        // Login user (register page er moto)
        Auth::login($user);
        
        return response()->json([
            'success' => true,
            'message' => 'Registration completed successfully',
            'redirect' => url('/') // Change to your home route
        ]);
    }

    /**
     * Get cities by country ID (register page theke)
     */
    public function getCities($country_id)
    {
        $cities = City::where('country_id', $country_id)
                      ->orderBy('name', 'asc')
                      ->get(['id', 'name']);
        
        return response()->json($cities);
    }

    /**
     * Handle login (existing login logic)
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ], 422);
        }

        $loginId = $request->email;
        $credentials = [];

        // Determine if email or phone
        if (filter_var($loginId, FILTER_VALIDATE_EMAIL)) {
            $credentials = [
                'email' => strtolower($loginId),
                'password' => $request->password
            ];
        } else {
            $phone = preg_replace('/\D/', '', $loginId);
            $credentials = [
                'phone_number' => $phone,
                'password' => $request->password
            ];
        }

        // Attempt to login
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'redirect' => url('/') // Change to your home route
            ]);
        }

        return response()->json([
            'error' => 'Invalid credentials'
        ], 422);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }

    /**
     * Generate a unique username (register page theke exact code)
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
     * Transliterate name to ASCII (register page theke)
     */
    private function transliterateName($name)
    {
        $name = trim(strtolower($name));
        
        // Try iconv first
        if (function_exists('iconv')) {
            $transliterated = @iconv('UTF-8', 'ASCII//TRANSLIT', $name);
            if (!empty($transliterated) && $transliterated !== false) {
                $cleaned = preg_replace('/[^a-zA-Z0-9]/', '', $transliterated);
                if (strlen($cleaned) >= 3) {
                    return $cleaned;
                }
            }
        }

        // Manual transliteration
        $result = $this->manualTransliterate($name);
        $cleaned = preg_replace('/[^a-zA-Z0-9]/', '', $result);
        
        if (strlen($cleaned) >= 3) {
            return $cleaned;
        }

        // Fallback
        return 'user' . Str::random(6) . rand(100, 999);
    }

    /**
     * Manual transliteration mapping (register page theke exact code)
     */
    private function manualTransliterate($name)
    {
        $charMap = [
            // Bangla
            "আ" => "a", "ই" => "i", "উ" => "u", "এ" => "e", "ও" => "o", "অ" => "a",
            "ক" => "k", "খ" => "kh", "গ" => "g", "ঘ" => "gh", "চ" => "ch", "ছ" => "chh", 
            "জ" => "j", "ঝ" => "jh", "ট" => "t", "ঠ" => "th", "ড" => "d", "ঢ" => "dh", 
            "ত" => "t", "থ" => "th", "দ" => "d", "ধ" => "dh", "ন" => "n", "প" => "p", 
            "ফ" => "ph", "ব" => "b", "ভ" => "bh", "ম" => "m", "য" => "j", "র" => "r", 
            "ল" => "l", "শ" => "sh", "ষ" => "sh", "স" => "s", "হ" => "h",

            // Arabic
            "أ" => "a", "ا" => "a", "ب" => "b", "ت" => "t", "ث" => "th", "ج" => "j", 
            "ح" => "h", "خ" => "kh", "د" => "d", "ذ" => "th", "ر" => "r", "ز" => "z", 
            "س" => "s", "ش" => "sh", "ص" => "s", "ض" => "d", "ط" => "t", "ظ" => "z", 
            "ع" => "a", "غ" => "gh", "ف" => "f", "ق" => "q", "ك" => "k", "ل" => "l", 
            "م" => "m", "ن" => "n", "ه" => "h", "و" => "w", "ي" => "y",

            // Hindi/Devanagari
            "अ" => "a", "आ" => "aa", "इ" => "i", "ई" => "ii", "उ" => "u", "ऊ" => "uu", 
            "ए" => "e", "ओ" => "o", "क" => "k", "ख" => "kh", "ग" => "g", "घ" => "gh", 
            "च" => "ch", "छ" => "chh", "ज" => "j", "झ" => "jh", "ट" => "t", "ठ" => "th", 
            "ड" => "d", "ढ" => "dh", "त" => "t", "थ" => "th", "द" => "d", "ध" => "dh", 
            "न" => "n", "प" => "p", "फ" => "ph", "ब" => "b", "भ" => "bh", "म" => "m", 
            "य" => "y", "र" => "r", "ल" => "l", "व" => "v", "श" => "sh", "स" => "s", "ह" => "h",

            // Remove symbols
            " " => "", "-" => "", "_" => "", "." => "", "," => ""
        ];

        return strtr($name, $charMap);
    }
}