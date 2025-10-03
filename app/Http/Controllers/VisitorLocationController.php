<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VisitorUser;
use App\Models\Country;
use App\Models\City;
use App\Models\Area;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;

class VisitorLocationController extends Controller
{
    /**
     * ভিজিটর লোকেশন সাবমিট - প্রতিটি সিলেক্ট ইভেন্টে কল হবে
     */
    public function saveLocation(Request $request)
    {
        // কোন ফিল্ড আপডেট করতে হবে চেক করি
        $updatedFields = [];
        
        // ভিজিটরের IP অ্যাড্রেস নিন 
        $ipAddress = $request->ip();
        
        // ভিজিটর খুঁজি বা নতুন তৈরি করি
        $visitor = VisitorUser::firstOrNew(['ip_address' => $ipAddress]);
        
        // কান্ট্রি পরিবর্তন করলে শহর এবং এলাকা রিসেট করুন
        if ($request->has('reset_city') && $request->reset_city) {
            $visitor->selected_city_id = null;
        }
        
        // শহর পরিবর্তন করলে বা কান্ট্রি পরিবর্তন করলে এলাকা রিসেট করুন
        if ($request->has('reset_area') && $request->reset_area) {
            $visitor->selected_area_id = null;
        }
        
        if ($request->has('country_id')) {
            $request->validate([
                'country_id' => 'required|exists:countries,id',
            ]);
            $visitor->selected_country_id = $request->country_id;
        }
        
        if ($request->has('city_id')) {
            $request->validate([
                'city_id' => 'required|exists:cities,id',
            ]);
            $visitor->selected_city_id = $request->city_id;
        }
        
        if ($request->has('area_id')) {
            $request->validate([
                'area_id' => 'required|exists:areas,id',
            ]);
            $visitor->selected_area_id = $request->area_id;
        }
        
        // সর্বদা লাস্ট ভিজিট টাইম আপডেট করি
        $visitor->last_visit_at = now();
        $visitor->save();
        
        // সফল হলে JSON রিটার্ন করি
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'লোকেশন সফলভাবে সেভ করা হয়েছে।',
                'visitor_id' => $visitor->id,
                'country_id' => $visitor->selected_country_id,
                'city_id' => $visitor->selected_city_id,
                'area_id' => $visitor->selected_area_id
            ]);
        }
        
        // নন-এজাক্স রিকোয়েস্টের জন্য রিডিরেক্ট
        return redirect()->back()->with('success', 'আপনার লোকেশন সফলভাবে সেভ করা হয়েছে।');
    }
    
    /**
     * কান্ট্রি অনুসারে সিটি লিস্ট দেয়
     */
    public function getCitiesByCountry($countryId)
    {
        $cities = City::where('country_id', $countryId)->get();
        return response()->json($cities);
    }
    
    /**
     * সিটি অনুসারে এরিয়া লিস্ট দেয়
     */
    public function getAreasByCity($cityId)
    {
        $areas = Area::where('city_id', $cityId)->get();
        return response()->json($areas);
    }
    
    /**
     * ভিজিটরের লোকেশন অনুসারে লোকাল কন্টেন্ট দেখায়
     */
    public function showLocalContent(Request $request)
    {
        $ipAddress = $request->ip();
        $visitor = VisitorUser::where('ip_address', $ipAddress)->first();
        
        if (!$visitor) {
            // যদি ভিজিটর না থাকে, তবে নতুন তৈরি করি
            $visitor = new VisitorUser();
            $visitor->ip_address = $ipAddress;
            $visitor->last_visit_at = now();
            $visitor->save();
        }
        
        // নির্বাচিত লোকেশন তথ্য নিই
        $country = $visitor->selectedCountry;
        $city = $visitor->selectedCity;
        $area = $visitor->selectedArea;
        
        // লোকাল কন্টেন্ট ভিউ রিটার্ন করি
        return view('content.localized', compact('visitor', 'country', 'city', 'area'));
    }
}