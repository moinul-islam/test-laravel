<?php
namespace App\Http\Controllers\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Country;
use App\Models\Category;
use App\Services\SmsService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $countries = Country::all();
        // Get only profile type categories for job title suggestions
        $categories = Category::where('cat_type', 'profile')->get();
        return view('auth.register', compact('countries', 'categories'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, SmsService $smsService): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'login_id' => ['required', 'string', 'max:255'], // Email বা Phone আসবে
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'country_id' => ['required', 'exists:countries,id'],
            'city_id' => ['required', 'exists:cities,id'],
            'area' => ['nullable', 'string', 'max:255'],
        ]);
    
        $imageName = null;
        $otp = rand(100000, 999999);
    
        // Generate unique username
        $username = $this->generateUniqueUsername($request->name);
    
        // Image upload
        if ($request->hasFile('image')) {
            $imageName = time().'.'.$request->image->extension();  
            $request->image->move(public_path('profile-image'), $imageName);
        }
    
        // Determine email or phone
        $email = null;
        $phone = null;
    
        if (filter_var($request->login_id, FILTER_VALIDATE_EMAIL)) {
            // ইনপুট Email
            $email = strtolower($request->login_id);
    
            // Email unique চেক
            if (User::where('email', $email)->exists()) {
                return back()->withErrors(['login_id' => 'This email is already registered.'])->withInput();
            }
        } else {
            // ইনপুট Phone Number
            $phone = preg_replace('/\D/', '', $request->login_id); // শুধু সংখ্যা রাখবে
    
            // Phone unique চেক
            if (User::where('phone_number', $phone)->exists()) {
                return back()->withErrors(['login_id' => 'This phone number is already registered.'])->withInput();
            }
        }
    
        // Category / Job Title
        $categoryId = null;
        $jobTitle = null;
        if ($request->filled('category_id') && $request->category_id != '') {
            $categoryExists = Category::where('id', $request->category_id)
                                      ->where('cat_type', 'profile')
                                      ->exists();
            if ($categoryExists) {
                $categoryId = $request->category_id;
            } else {
                $jobTitle = $request->job_title;
            }
        } else {
            $jobTitle = $request->job_title;
        }
    
        // User create
        $user = User::create([
            'image' => $imageName,
            'name' => $request->name,
            'job_title' => $jobTitle,
            'username' => $username,
            'otp' => $otp,
            'country_id' => $request->country_id,
            'city_id' => $request->city_id,
            'area' => $request->area,
            'fcm_token' => $request->fcm_token,
            'email' => $email,           // যদি email হয়
            'phone_number' => $phone,    // যদি phone হয়
            'password' => Hash::make($request->password),
            'category_id' => $categoryId,
        ]);
    
        // OTP পাঠানো
        if ($email) {
            // Email OTP পাঠানো
            Mail::raw("Your OTP code is: $otp", function($message) use ($user) {
                $message->to($user->email)
                        ->subject('Email Verification - eINFO');
            });
        } elseif ($phone) {
           $response = $smsService->sendSms($phone, "Your eINFO OTP is: " . $otp);
        }
    
        session(['otp' => $otp]);
        event(new Registered($user));
        Auth::login($user);
    
        return redirect("/login-success/{$user->username}");
    }
    
    /**
     * Generate a unique random username - FINAL VERSION
     */
    private function generateUniqueUsername($baseName = null)
    {
        if ($baseName) {
            // Try transliteration first
            $username = $this->transliterateName($baseName);
        } else {
            // Generate random username
            $username = 'user' . Str::random(6) . rand(100, 999);
        }

        // Ensure username is valid
        if (empty($username) || strlen($username) < 3) {
            $username = 'user' . Str::random(6) . rand(100, 999);
        }

        // Make unique
        $originalUsername = $username;
        $counter = 1;
        
        while (User::where('username', $username)->exists()) {
            $username = $originalUsername . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Transliterate name to English username - FINAL VERSION
     */
    private function transliterateName($name)
    {
        $name = trim(strtolower($name));
        
        // Try iconv first (handles many languages automatically)
        if (function_exists('iconv')) {
            $transliterated = @iconv('UTF-8', 'ASCII//TRANSLIT', $name);
            if (!empty($transliterated) && $transliterated !== false) {
                $cleaned = preg_replace('/[^a-zA-Z0-9]/', '', $transliterated);
                if (strlen($cleaned) >= 3) {
                    return $cleaned;
                }
            }
        }

        // Manual transliteration for unsupported languages
        $result = $this->manualTransliterate($name);
        $cleaned = preg_replace('/[^a-zA-Z0-9]/', '', $result);
        
        // If successful transliteration, return it
        if (strlen($cleaned) >= 3) {
            return $cleaned;
        }

        // Fallback to random username
        return 'user' . Str::random(6) . rand(100, 999);
    }

    /**
     * Manual transliteration mapping - COMPREHENSIVE VERSION
     */
    private function manualTransliterate($name)
    {
        $charMap = [
            // English (already supported)
            
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

            // Chinese (most common)
            "李" => "li", "王" => "wang", "张" => "zhang", "刘" => "liu", "陈" => "chen", 
            "杨" => "yang", "黄" => "huang", "赵" => "zhao", "吴" => "wu", "周" => "zhou", 
            "徐" => "xu", "孙" => "sun", "马" => "ma", "朱" => "zhu", "胡" => "hu", 
            "明" => "ming", "华" => "hua", "国" => "guo", "文" => "wen", "伟" => "wei", 
            "强" => "qiang", "军" => "jun", "敏" => "min", "静" => "jing", "丽" => "li",

            // Russian (Cyrillic)
            "а" => "a", "б" => "b", "в" => "v", "г" => "g", "д" => "d", "е" => "e", 
            "ж" => "zh", "з" => "z", "и" => "i", "к" => "k", "л" => "l", "м" => "m", 
            "н" => "n", "о" => "o", "п" => "p", "р" => "r", "с" => "s", "т" => "t", 
            "у" => "u", "ф" => "f", "х" => "kh", "ч" => "ch", "ш" => "sh", "я" => "ya",

            // Japanese (basic)
            "田" => "ta", "中" => "naka", "山" => "yama", "本" => "moto", "川" => "kawa",
            "太" => "ta", "郎" => "ro", "子" => "ko", "美" => "mi", "花" => "hana",

            // Korean (basic)
            "김" => "kim", "이" => "lee", "박" => "park", "최" => "choi", "정" => "jung",
            "수" => "soo", "민" => "min", "영" => "young", "현" => "hyun",

            // Persian (additional)
            "پ" => "p", "چ" => "ch", "ژ" => "zh", "گ" => "g",

            // Remove symbols
            " " => "", "-" => "", "_" => "", "." => "", "," => ""
        ];

        return strtr($name, $charMap);
    }
}