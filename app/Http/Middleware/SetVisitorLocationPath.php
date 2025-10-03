<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\VisitorUser;
use App\Models\Country;
use App\Models\City;

class SetVisitorLocationPath
{
    public function handle(Request $request, Closure $next)
    {
        $locationPath = 'international'; // Default to 'international' instead of '/'
        $locationName = 'International'; // Default name 
        $ipAddress = $request->ip();
        $visitor = VisitorUser::where('ip_address', $ipAddress)->first();
        if ($visitor) {
            if ($visitor->selected_city_id) {
                $city = City::find($visitor->selected_city_id);
                if ($city) {
                    $locationPath = $city->username;
                    $locationName = $city->name;
                }
            } elseif ($visitor->selected_country_id) {
                $country = Country::find($visitor->selected_country_id);
                if ($country) {
                    $locationPath = $country->username;
                    $locationName = $country->name;
                }
            }
        }
        
        // Share both path and name with all views
        view()->share('visitorLocationPath', $locationPath);
        view()->share('visitorLocationName', $locationName);
        
        return $next($request);
    }
}