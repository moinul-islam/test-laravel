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
use App\Models\Country;
use App\Models\City;
use App\Models\Category;

class ProfileController extends Controller
{

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
        
        // Custom validation since we're handling categories
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username,' . $user->id],
            'job_title' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone_number' => ['nullable', 'string', 'max:25', 'unique:users,phone_number,' . $user->id],
            'service_hr' => ['nullable', 'array'],
            'country_id' => ['required', 'exists:countries,id'],
            'city_id' => ['required', 'exists:cities,id'],
            'area' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ]);
    
        // Check if both email and phone are being updated simultaneously
        $emailChanged = isset($validated['email']) && $validated['email'] !== $user->email;
        $phoneChanged = isset($validated['phone_number']) && $validated['phone_number'] !== $user->phone_number;
        
        if ($emailChanged && $phoneChanged) {
            return redirect()->back()->withErrors(['error' => 'You cannot update both email and phone number at the same time.']);
        }
    
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
    
        // Check if category_id is provided (existing category selected)
       // Check if category_id is provided (existing category selected)
    if ($request->filled('category_id') && $request->category_id != '') {
        // Validate that the category exists and is profile type
        $categoryExists = Category::where('id', $request->category_id)
                                ->where('cat_type', 'profile')
                                ->exists();
        if ($categoryExists) {
            $categoryId = $request->category_id;
            // If category is selected, clear job_title
            $jobTitle = null;
        } else {
            // If category_id doesn't exist, treat as custom job title
            $jobTitle = $request->job_title;
            $categoryId = null;
        }
    } else {
        // User typed a custom job title
        $jobTitle = $request->job_title;
        $categoryId = null;
    }
       
        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($user->image) {
                // Handle both old and new image path formats
                $oldImagePath = public_path('profile-image/' . $user->image);
                $newImagePath = storage_path('app/public/profile-images/' . $user->image);
               
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
                if (file_exists($newImagePath)) {
                    unlink($newImagePath);
                }
            }
           
            // Store new image using the same method as registration
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('profile-image'), $imageName);
            $validated['image'] = $imageName;
        }
       
        // Handle email change
        if ($emailChanged) {
            $newEmail = $validated['email'];
           
            // Generate OTP
            $otp = rand(100000, 999999);
           
            // Add OTP to the data that will be saved
            $validated['otp'] = $otp;
            
            // Set email as unverified
            $validated['email_verified'] = null;
           
            // Send OTP to new email
            Mail::raw("Your OTP code for email verification is: $otp", function($message) use ($newEmail) {
                $message->to($newEmail)
                        ->subject('Email Update Verification - eINFO');
            });
        }
        
        // Handle phone number change
        if ($phoneChanged) {
            $newPhone = $validated['phone_number'];
            
            // Generate OTP
            $otp = rand(100000, 999999);
            
            // Add OTP to the data that will be saved
            $validated['otp'] = $otp;
            
            // Set phone as unverified
            $validated['phone_verified'] = null;
            
            // Send OTP via SMS
            $smsService->sendSms($newPhone, "Your OTP code for phone verification is: $otp");
        }

            // Add category data to validated array
        $validated['category_id'] = $categoryId;
        $validated['job_title'] = $jobTitle;
        
        // Update user with validated data
        $user->update($validated);
        
        if ($emailChanged) {
            return redirect()->back()->with('success', 'Profile updated successfully. Please check your email for OTP verification.');
        } elseif ($phoneChanged) {
            return redirect()->back()->with('success', 'Profile updated successfully. Please check your phone for OTP verification.');
        } else {
            return redirect()->back()->with('success', 'Profile updated successfully.');
        }
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
}