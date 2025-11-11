<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\City;
use App\Models\User;
use App\Models\Post;
use App\Models\PostCategory;
use App\Services\SmsService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\Category;

class LocationController extends Controller
{


    public function usernameWiseHome($username)
{
    $not_user = User::where('username', $username)->first();
    
    if($not_user){
        // ইউজার খুঁজে বের করো
        $user = User::where('username', $username)->first();
        
        // যদি ইউজার না পাওয়া যায় → redirect to /
        if (!$user) {
            return redirect('/');
        }
        
        // ওই ইউজারের সব পোস্ট নাও - pagination সহ (category relationship সহ)
        $posts = Post::with(['user', 'category'])
                    ->where('user_id', $user->id)
                    ->latest()
                    ->paginate(3); // get() এর পরিবর্তে paginate() ব্যবহার করুন
        
        // Categories fetch করা (form এর জন্য - শুধুমাত্র নিজের প্রোফাইলে দেখাবে)
        $categories = \App\Models\Category::whereIn('cat_type', ['product', 'service','post'])->get();
        
        // view এ পাঠাও
        return view("dashboard", compact('posts', 'user', 'categories'));
    } else {
        // ✅ এখানে সমস্যা - $posts variable ছিল না
        // এখন location অনুযায়ী posts fetch করবো
        
        $path = $username; // 'international' অথবা country/city username
        
        // Initialize user IDs based on location
        $userIds = [];
        
        if ($path == 'international') {
            $userIds = User::where(function($query) {
                $query->where('phone_verified', 0)
                      ->orWhere('email_verified', 0);
            })->pluck('id')->toArray();
        } else {
            $country = \App\Models\Country::where('username', $path)->first();
            if ($country) {
                $userIds = User::where('country_id', $country->id)
                    ->where(function($query) {
                        $query->where('phone_verified', 0)
                              ->orWhere('email_verified', 0);
                    })
                    ->pluck('id')
                    ->toArray();
            } else {
                $city = \App\Models\City::where('username', $path)->first();
                if ($city) {
                    $userIds = User::where('city_id', $city->id)
                        ->where(function($query) {
                            $query->where('phone_verified', 0)
                                  ->orWhere('email_verified', 0);
                        })
                        ->pluck('id')
                        ->toArray();
                } else {
                    $userIds = User::where(function($query) {
                        $query->where('phone_verified', 0)
                              ->orWhere('email_verified', 0);
                    })->pluck('id')->toArray();
                }
            }
        }
        
        // ✅ Posts fetch করুন location অনুযায়ী
        $posts = Post::with(['user', 'category'])
                    ->whereIn('user_id', $userIds)
                    ->latest()
                    ->paginate(12);
        
        // 🔍 Debugging
        \Log::info('Location Posts', [
            'path' => $path,
            'userIds_count' => count($userIds),
            'posts_count' => $posts->count()
        ]);
        
        return view("frontend.index", compact('posts'));
    }
}

    public function getCities($countryId)
    {
        $cities = City::where('country_id', $countryId)
                    ->orderBy('name', 'asc')
                    ->get();
        
        return response()->json($cities);
    }

    public function follow(User $user)
{
    $authUser = Auth::user();
    if ($authUser->id === $user->id) {
        return response()->json(['error' => 'You cannot follow yourself.']);
    }

    if (!$authUser->following->contains($user->id)) {
        $authUser->following()->attach($user->id);
    }

    return response()->json(['success' => true]);
}

public function unfollow(User $user)
{
    $authUser = Auth::user();
    if ($authUser->id === $user->id) {
        return response()->json(['error' => 'You cannot unfollow yourself.']);
    }

    $authUser->following()->detach($user->id);

    return response()->json(['success' => true]);
}



    public function show($username)
    {
        // ইউজার খুঁজে বের করো
        $user = User::where('username', $username)->first();
        
        // যদি ইউজার না পাওয়া যায় → redirect to /
        if (!$user) {
            return redirect('/');
        }
        
        // ওই ইউজারের সব পোস্ট নাও - pagination সহ (category relationship সহ)
        $posts = Post::with(['user', 'category'])
                     ->where('user_id', $user->id)
                     ->latest()
                     ->paginate(3); // get() এর পরিবর্তে paginate() ব্যবহার করুন
        
        // Categories fetch করা (form এর জন্য - শুধুমাত্র নিজের প্রোফাইলে দেখাবে)
        $categories = \App\Models\Category::whereIn('cat_type', ['product', 'service'])->get();
        
        // view এ পাঠাও
        return view("dashboard", compact('posts', 'user', 'categories'));
    }

    // User-specific posts এর জন্য AJAX load more method
    public function loadMoreUserPosts(Request $request, $userId)
    {
        $posts = Post::with(['user', 'category'])
            ->where('user_id', $userId)
            ->latest()
            ->paginate(3); // প্রতি page এ 3টি post
        
        // যদি AJAX request হয় (lazy loading এর জন্য)
        if ($request->ajax()) {
            return response()->json([
                'posts' => view('frontend.posts-partial', compact('posts'))->render(),
                'hasMore' => $posts->hasMorePages()
            ]);
        }
        
        return response()->json(['error' => 'Invalid request'], 400);
    }
    public function sendOtp()
    {
        $user = Auth::user();
        if (!$user) {
            return back()->with('error', 'User not found.');
        }
    
        // Email registered user এর জন্য
        if ($user->email && $user->email_verified !== 0) {
            // যদি ইতিমধ্যেই email_verified 9 হয়, আর OTP পাঠানো যাবে না
            if ($user->email_verified == 9) {
                return back()->with('error', 'You have reached the maximum OTP requests.');
            }
    
            // Current email_verified count check করুন
            $currentCount = $user->email_verified ?? 0;
           
            // যদি 9 বার হয়ে গেছে তাহলে 9 set করুন এবং OTP পাঠানো বন্ধ করুন
            if ($currentCount >= 9) {
                $user->email_verified = 9;
                $user->save();
                return back()->with('error', 'Maximum OTP attempts reached. Your account is suspended.');
            }
    
            // OTP Generate & Save
            $otp = rand(100000, 999999);
            $user->otp = $otp;
           
            // email_verified count বৃদ্ধি করুন
            $user->email_verified = $currentCount + 1;
            $user->save();
    
            // Mail send
            Mail::raw("Your OTP code is: $otp", function($message) use ($user) {
                $message->to($user->email)
                        ->subject('Email Verification - eINFO');
            });
        } 
        // Phone registered user এর জন্য
        elseif ($user->phone_number) {
            // যদি ইতিমধ্যেই phone_verified 9 হয়, আর OTP পাঠানো যাবে না
            if ($user->phone_verified == 9) {
                return back()->with('error', 'You have reached the maximum OTP requests.');
            }
    
            // Current phone_verified count check করুন
            $currentCount = $user->phone_verified ?? 0;
           
            // যদি 9 বার হয়ে গেছে তাহলে 9 set করুন এবং OTP পাঠানো বন্ধ করুন
            if ($currentCount >= 9) {
                $user->phone_verified = 9;
                $user->save();
                return back()->with('error', 'Maximum OTP attempts reached. Your account is suspended.');
            }
    
            // OTP Generate & Save
            $otp = rand(100000, 999999);
            $user->otp = $otp;
           
            // phone_verified count বৃদ্ধি করুন
            $user->phone_verified = $currentCount + 1;
            $user->save();
    
            // SMS send
            app(SmsService::class)->sendSms($user->phone_number, "Your eINFO OTP is: " . $otp);
        }
    
        return back()->with('success', 'OTP sent successfully!');
    }
    
    // Verify OTP Method - Updated
    public function verifyOtp(Request $request)
    {
     
        $user = Auth::user();
        if($user->otp == $request->otp){
            // Email user এর জন্য
            if ($user->email && $user->email_verified !== 0) {
                $user->email_verified = 0; // verified status
            }
            // Phone user এর জন্য
            elseif ($user->phone_number) {
                $user->phone_verified = 0; // verified status
            }

           $user->save();

            return back()->with('success', 'Verification successful!');
        } else {
            return back()->with('error', 'Your OTP is incorrect');
        }
    }
    
    // Resend OTP Method
    public function reSendOtp()
    {
        return $this->sendOtp();
    }
    
    public function index()
    {
        //
    }
    
    public function create()
    {
        //
    }
    
    public function store(Request $request)
    {
        //
    }
    
   
    
    public function edit(string $id)
    {
        //
    }
    
    public function update(Request $request, string $id)
    {
        //
    }
    
    public function destroy(string $id)
    {
        //
    }
}