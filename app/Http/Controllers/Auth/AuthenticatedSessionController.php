<?php

namespace App\Http\Controllers\Auth;
use App\Models\UserFcmToken;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        // Validation (email or phone)
        $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
            'fcm_token' => ['nullable', 'string'],
        ], [
            'email.required' => 'The email or phone field is required.',
            'password.required' => 'The password field is required.',
        ]);
    
        $loginInput = $request->email; // email input box এ email/phone আসবে
        $password = $request->password;
    
        // চেক করব email না phone
        if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
            // Email দিয়ে লগইন
            $credentials = ['email' => $loginInput, 'password' => $password];
            $user = \App\Models\User::where('email', $loginInput)->first();
        } else {
            // Phone দিয়ে লগইন
            $credentials = ['phone_number' => $loginInput, 'password' => $password];
            $user = \App\Models\User::where('phone_number', $loginInput)->first();
        }
    
        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['The provided email/phone is not registered.'],
            ]);
        }
    
        // Attempt login
        if (Auth::attempt($credentials, true)) {
            $request->session()->regenerate();
    
            // FCM token save করুন
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
    
            // User identifier
            $userIdentifier = $user->username ?? str_replace(['@', '.', '+', '-', ' '], '', $user->email);
    
            return redirect("/login-success/{$userIdentifier}");
        }
    
        // Wrong password
        throw ValidationException::withMessages([
            'password' => ['The provided password is incorrect.'],
        ]);
    }
    
    /**
     * Destroy an authenticated session.
     */
   public function destroy(Request $request): RedirectResponse
{
    // Current user এর identifier নিন logout করার আগে
    $user = Auth::user();
    $userIdentifier = $user->username ?? str_replace(['@', '.', '+', '-', ' '], '', $user->email);
    
    // FCM token remove করুন logout এর সময়
    if ($user) {
        // সব FCM tokens delete করুন
        $user->fcmTokens()->delete();  // এটা সব tokens delete করবে
        
        // অথবা শুধু দেখতে চাইলে:
        // $fcmTokens = $user->fcmTokens()->get();
        // dd($fcmTokens);
    }
    
    Auth::guard('web')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
}

    /**
     * Save FCM token after successful login
     */
    public function saveFcmTokenOnLogin(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Check if there's a stored FCM token in the request or session
            $fcmToken = $request->input('fcm_token') ?? session('temp_fcm_token');
            
            if ($fcmToken) {
                $user->fcm_token = $fcmToken;
                $user->save();
                
                // Clear temporary token from session
                session()->forget('temp_fcm_token');
                
                return response()->json([
                    'success' => true,
                    'message' => 'FCM token saved on login'
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'No token to save or user not authenticated'
        ]);
    }
}