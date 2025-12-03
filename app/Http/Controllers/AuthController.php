<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Country;
use App\Models\City;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{
    /**
     * Show auth page with countries
     */
    public function showAuthPage()
    {
        $countries = Country::all();
        return view('myauth', compact('countries'));
    }

    /**
     * Check if email exists and has password
     */
    public function checkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid email format'
            ], 422);
        }

        $user = User::where('email', $request->email)->first();
        
        return response()->json([
            'exists' => $user ? true : false,
            'has_password' => ($user && $user->password) ? true : false
        ]);
    }

    /**
     * Send OTP to email
     */
    public function sendOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'type' => 'required|in:register,reset'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ], 422);
        }

        // Generate 6 digit OTP
        $otp = rand(100000, 999999);
        
        // Store OTP in cache for 10 minutes
        Cache::put('otp_' . $request->email, $otp, now()->addMinutes(10));
        
        try {
            // Send OTP via email
            Mail::send('emails.otp', ['otp' => $otp], function($message) use ($request) {
                $message->to($request->email)
                        ->subject('Your OTP Code');
            });

            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to send OTP. Please try again.'
            ], 500);
        }
    }

    /**
     * Verify OTP
     */
    public function verifyOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|digits:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ], 422);
        }

        $storedOTP = Cache::get('otp_' . $request->email);
        
        if (!$storedOTP) {
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
        Cache::put('otp_verified_' . $request->email, true, now()->addMinutes(10));
        
        return response()->json([
            'verified' => true,
            'message' => 'OTP verified successfully'
        ]);
    }

    /**
     * Complete registration or password reset
     */
    public function completeRegistration(Request $request)
    {
        // Check if OTP was verified
        if (!Cache::get('otp_verified_' . $request->email)) {
            return response()->json([
                'error' => 'Please verify OTP first'
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // New user registration
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|unique:users',
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

            $user = new User();
            $user->email = $request->email;
            $user->name = $request->name;
            $user->country_id = $request->country_id;
            $user->city_id = $request->city_id;
            
            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('uploads/profiles'), $imageName);
                $user->image = 'uploads/profiles/' . $imageName;
            }
            
            $user->password = Hash::make($request->password);
            $user->save();

        } else {
            // Existing user - update password
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
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

        // Clear OTP cache
        Cache::forget('otp_' . $request->email);
        Cache::forget('otp_verified_' . $request->email);

        // Login user
        Auth::login($user);
        
        return response()->json([
            'success' => true,
            'message' => 'Registration completed successfully',
            'redirect' => route('home') // Change this to your desired route
        ]);
    }

    /**
     * Get cities by country ID
     */
    public function getCities($country_id)
    {
        $cities = City::where('country_id', $country_id)
                      ->orderBy('name', 'asc')
                      ->get(['id', 'name']);
        
        return response()->json($cities);
    }

    /**
     * Handle login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ], 422);
        }

        // Attempt to login
        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'redirect' => route('home') // Change this to your desired route
            ]);
        }

        return response()->json([
            'error' => 'Invalid email or password'
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
}