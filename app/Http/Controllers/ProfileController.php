<?php
namespace App\Http\Controllers;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Services\SmsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use App\Models\User;  // ⬅️ এই line টা add করো
use App\Models\Country;
use App\Models\City;
use App\Models\Category;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use App\Http\Controllers\Auth\RegisteredUserController;

class ProfileController extends Controller
{


 

// Update method for AJAX
public function adminUserUpdate(Request $request, $userId)
{
    $user = User::findOrFail($userId);
    
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'nullable|email|unique:users,email,' . $user->id,
        'username' => 'required|string|max:255|unique:users,username,' . $user->id,
        'phone_number' => 'nullable|string|max:25|unique:users,phone_number,' . $user->id,
        'job_title' => 'nullable|string|max:255',
        'category_id' => 'nullable|exists:categories,id',
        'country_id' => 'required|exists:countries,id',
        'city_id' => 'required|exists:cities,id',
        'area' => 'nullable|string|max:255',
        'service_hr' => 'nullable|array',
        'email_verified' => 'nullable',
        'phone_verified' => 'nullable',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
    ]);

    // Service hours processing
    if ($request->has('service_hr')) {
        $serviceHours = [];
        foreach ($request->service_hr as $day => $data) {
            if ($data['status'] === 'closed') {
                $serviceHours[$day] = 'closed';
            } else {
                $serviceHours[$day] = [
                    'open' => $data['open'],
                    'close' => $data['close']
                ];
            }
        }
        $validated['service_hr'] = json_encode($serviceHours);
    }

    // Handle job title and category
    if ($request->filled('category_id') && $request->category_id != '') {
        $validated['category_id'] = $request->category_id;
        $validated['job_title'] = null;
    } else {
        $validated['job_title'] = $request->job_title;
        $validated['category_id'] = null;
    }

    // Handle image upload
    if ($request->hasFile('image')) {
        if ($user->image) {
            $oldImagePath = public_path('profile-image/' . $user->image);
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }
        
        $imageName = time() . '.' . $request->image->extension();
        $request->image->move(public_path('profile-image'), $imageName);
        $validated['image'] = $imageName;
    }

    $user->update($validated);

    return response()->json([
        'success' => true,
        'message' => 'User updated successfully!'
    ]);
}


public function getUserData($userId)
{
    try {
        // with('category') add করো category data পাওয়ার জন্য
        $user = User::with('category')->findOrFail($userId);
        $countries = Country::all();
        $categories = Category::where('cat_type', 'profile')->get();
        
        return response()->json([
            'success' => true,
            'user' => $user,
            'countries' => $countries,
            'categories' => $categories
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'User not found'
        ], 404);
    }
}
   

    public function getMissingFields($user = null)
{
    if (!$user) {
        $user = auth()->user();
    }
    
    $missingFields = [];
    $fieldLabels = [
        'image' => 'Profile Image',
        'job_title' => 'Job Title',
        'category_id' => 'Category',
        'area' => 'Area',
        'phone_number' => 'Phone Number',
        'service_hr' => 'Service Hours'
    ];
    
    // Check basic required fields
    if (empty($user->image)) {
        $missingFields[] = $fieldLabels['image'];
    }
    
    if (empty($user->area)) {
        $missingFields[] = $fieldLabels['area'];
    }
    
    if (empty($user->phone_number)) {
        $missingFields[] = $fieldLabels['phone_number'];
    }
    
    if (empty($user->service_hr)) {
        $missingFields[] = $fieldLabels['service_hr'];
    }
    
    // Check if either job_title OR category_id exists
    if (empty($user->job_title) && empty($user->category_id)) {
        $missingFields[] = $fieldLabels['job_title'] . ' or ' . $fieldLabels['category_id'];
    }
    
    return $missingFields;
}

// Update existing checkCompleteness method
public function checkCompleteness(Request $request)
{
    $user = auth()->user();
    $missingFields = $this->getMissingFields($user);
    $isComplete = empty($missingFields);
    
    return response()->json([
        'isComplete' => $isComplete,
        'missingFields' => $missingFields,
        'message' => $isComplete 
            ? 'Profile is complete' 
            : 'If you want to create post update your profile first'
    ]);
}



    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $countries = Country::all();
        $cities = [];
       
        // If user has a country selected, get cities for that country
        if ($request->user()->country_id) {
            $cities = City::where('country_id', $request->user()->country_id)->get();
        }

        // Get only profile type categories for job title suggestions
        $categories = Category::where('cat_type', 'profile')->get();
       
        return view('profile.edit', [
            'user' => $request->user(),
            'countries' => $countries,
            'cities' => $cities,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request, SmsService $smsService): RedirectResponse
    {
        $user = $request->user();
        
        // Check which form was submitted
        $isBusinessUpdate = $request->has('update_business');
        
        if ($isBusinessUpdate) {
            // ============================================
            // BUSINESS PROFILE UPDATE
            // ============================================
            
            $validated = $request->validate([
                'job_title' => ['nullable', 'string', 'max:255'],
                'service_hr' => ['nullable', 'array'],
                'area' => ['nullable', 'string', 'max:255'],
                'phone_number' => ['nullable', 'string', 'max:25', 'unique:users,phone_number,' . $user->id],
            ]);
            
            // Service hours processing
            if ($request->has('service_hr')) {
                $serviceHours = [];
                foreach ($request->service_hr as $day => $data) {
                    if ($data['status'] === 'closed') {
                        $serviceHours[$day] = 'closed';
                    } else {
                        $serviceHours[$day] = [
                            'open' => $data['open'],
                            'close' => $data['close']
                        ];
                    }
                }
                $validated['service_hr'] = json_encode($serviceHours);
            }
            
            // Handle job title and category
            $categoryId = null;
            $jobTitle = null;
            
            if ($request->filled('category_id') && $request->category_id != '') {
                $categoryExists = Category::where('id', $request->category_id)
                                        ->where('cat_type', 'profile')
                                        ->exists();
                if ($categoryExists) {
                    $categoryId = $request->category_id;
                    $jobTitle = null;
                } else {
                    $jobTitle = $request->job_title;
                    $categoryId = null;
                }
            } else {
                $jobTitle = $request->job_title;
                $categoryId = null;
            }
            
            $validated['category_id'] = $categoryId;
            $validated['job_title'] = $jobTitle;
            
            $user->update($validated);
            
            return redirect()->back()->with('success', 'Business profile updated successfully.');
            
        } else {
            // ============================================
            // PERSONAL PROFILE UPDATE
            // ============================================
            
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:255', 'unique:users,username,' . $user->id],
                'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
                
                'country_id' => ['required', 'exists:countries,id'],
                'city_id' => ['required', 'exists:cities,id'],
                
                'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            ]);
        
            $emailChanged = isset($validated['email']) && $validated['email'] !== $user->email;
            $phoneChanged = isset($validated['phone_number']) && $validated['phone_number'] !== $user->phone_number;
            
            if ($emailChanged && $phoneChanged) {
                return redirect()->back()->withErrors(['error' => 'You cannot update both email and phone number at the same time.']);
            }
            
            if ($request->hasFile('image')) {
                if ($user->image) {
                    $oldImagePath = public_path('profile-image/' . $user->image);
                    $newImagePath = storage_path('app/public/profile-images/' . $user->image);
                
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                    if (file_exists($newImagePath)) {
                        unlink($newImagePath);
                    }
                }
            
                $imageName = time() . '.' . $request->image->extension();
                $request->image->move(public_path('profile-image'), $imageName);
                $validated['image'] = $imageName;
            }
        
            if ($emailChanged) {
                $newEmail = $validated['email'];
                $otp = rand(100000, 999999);
                $validated['otp'] = $otp;
                $validated['email_verified'] = null;
            
                Mail::raw("Your OTP code for email verification is: $otp", function($message) use ($newEmail) {
                    $message->to($newEmail)->subject('Email Update Verification - eINFO');
                });
            }
            
            if ($phoneChanged) {
                $newPhone = $validated['phone_number'];
                $otp = rand(100000, 999999);
                $validated['otp'] = $otp;
                $validated['phone_verified'] = null;
                
                $smsService->sendSms($newPhone, "Your OTP code for phone verification is: $otp");
            }
            
            $user->update($validated);
            
            if ($emailChanged) {
                return redirect()->back()->with('success', 'Profile updated successfully. Please check your email for OTP verification.');
            } elseif ($phoneChanged) {
                return redirect()->back()->with('success', 'Profile updated successfully. Please check your phone for OTP verification.');
            } else {
                return redirect()->back()->with('success', 'Profile updated successfully.');
            }
        }
    }


    public function contributeStore(Request $request, SmsService $smsService): RedirectResponse
    {
        // Validate all required fields
        $validated = $request->validate([
            'contributor' => ['required', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:25', 'unique:users,phone_number'],
            'country_id' => ['required', 'exists:countries,id'],
            'city_id' => ['required', 'exists:cities,id'],
            'area' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'service_hr' => ['nullable', 'array'],
        ]);
    
        // Generate unique username from name
        $registeredController = new RegisteredUserController();
        $validated['username'] = $registeredController->generateUniqueUsername($validated['name']);
    
        // Handle image upload
        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('profile-image'), $imageName);
            $validated['image'] = $imageName;
        }
    
        // Process service hours
        if ($request->has('service_hr')) {
            $serviceHours = [];
            foreach ($request->service_hr as $day => $data) {
                if ($data['status'] === 'closed') {
                    $serviceHours[$day] = 'closed';
                } else {
                    $serviceHours[$day] = [
                        'open' => $data['open'],
                        'close' => $data['close']
                    ];
                }
            }
            $validated['service_hr'] = json_encode($serviceHours);
        }
    
        // Handle job title and category
        $categoryId = null;
        $jobTitle = null;
        
        if ($request->filled('category_id') && $request->category_id != '') {
            $categoryExists = Category::where('id', $request->category_id)
                                    ->where('cat_type', 'profile')
                                    ->exists();
            if ($categoryExists) {
                $categoryId = $request->category_id;
                $jobTitle = null;
            } else {
                $jobTitle = $request->job_title;
                $categoryId = null;
            }
        } else {
            $jobTitle = $request->job_title;
            $categoryId = null;
        }
        
        $validated['category_id'] = $categoryId;
        $validated['job_title'] = $jobTitle;
    
        // Create the new user
        try {
            $newUser = User::create($validated);
            
            return redirect()->back()->with('success', 'User account created successfully!');
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to create user account. Please try again. Error: ' . $e->getMessage()])
                ->withInput();
        }
    }



    public function accountCheck(Request $request)
    {
        // Validation
        $request->validate([
            'email' => ['required', 'string'],
        ], [
            'email.required' => 'The email or phone field is required.',
        ]);

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

        // যদি user না থাকে তাহলে register route এ redirect with cookie
        if (!$user) {
            return redirect()->route('register')->withCookie(cookie('email', $loginInput, 60, '/'));
        }

        // যদি user থাকে কিন্তু password null থাকে তাহলে password reset route এ redirect with cookie
        if (is_null($user->password)) {
            if ($inputType === 'email') {
                // যদি ইতিমধ্যেই otp_verified 9 হয়, আর OTP পাঠানো যাবে না
                if ($user->otp_verified == 9) {
                    return back()->with('error', 'You have reached the maximum OTP requests.');
                }

                // Current otp_verified count check করুন
                $currentCount = $user->otp_verified ?? 0;
                
                // যদি 9 বার হয়ে গেছে তাহলে 9 set করুন এবং OTP পাঠানো বন্ধ করুন
                if ($currentCount >= 9) {
                    $user->otp_verified = 9;
                    $user->save();
                    return back()->with('error', 'Maximum OTP attempts reached. Your account is suspended.');
                }

                // OTP Generate & Save
                $otp = rand(100000, 999999);
                $user->otp = $otp;
            
                // otp_verified count বৃদ্ধি করুন
                $user->otp_verified = $currentCount + 1;
                $user->save();
        
                // Mail send
                \Mail::raw("Your OTP code is: $otp", function($message) use ($loginInput) {
                    $message->to($loginInput)
                            ->subject('Email Verification - eINFO');
                });
    
                return redirect()->route('set-password', ['token' => 'otp-reset'])
                ->withCookie(cookie('email', $loginInput, 60))
                ->with('status', 'OTP sent to your email.');
            } else {

   
                // যদি ইতিমধ্যেই otp_verified 9 হয়, আর OTP পাঠানো যাবে না
                if ($user->otp_verified == 9) {
                    return back()->with('error', 'You have reached the maximum OTP requests.');
                }

            
                // Current otp_verified count check করুন
                $currentCount = $user->otp_verified ?? 0;
                
                // যদি 9 বার হয়ে গেছে তাহলে 9 set করুন এবং OTP পাঠানো বন্ধ করুন
                if ($currentCount >= 9) {
                    $user->otp_verified = 9;
                    $user->save();
                    return back()->with('error', 'Maximum OTP attempts reached. Your account is suspended.');
                }

                // OTP Generate & Save
                $otp = rand(100000, 999999);
                $user->otp = $otp;
            
                // otp_verified count বৃদ্ধি করুন
                $user->otp_verified = $currentCount + 1;
                $user->save();

                // SMS send
                app(\App\Services\SmsService::class)->sendSms($loginInput, "Your eINFO OTP is: " . $otp);

                return redirect()->route('set-password', ['token' => 'otp-reset'])
                ->withCookie(cookie('email', $loginInput, 60))
                ->with('status', 'OTP sent to your email.');
            }
        }
        
        // যদি user থাকে এবং password ও থাকে তাহলে login route এ redirect with cookie
        return redirect()->route('login')->withCookie(cookie('email', $loginInput, 60, '/'));
    }
    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);
        $user = $request->user();
        // Delete user's profile image if exists
        if ($user->image) {
            $oldImagePath = public_path('profile-image/' . $user->image);
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }
        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return Redirect::to('/');
    }

    // for user entry contribute 

    public function ContributeCreate(){
        $countries = Country::all();
       

        // Get only profile type categories for job title suggestions
        $categories = Category::where('cat_type', 'profile')->get();
        return view("frontend.contribute_create",compact("countries","categories"));
    }
}