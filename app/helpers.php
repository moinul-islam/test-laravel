<?php

if (!function_exists('getVisitorLocationPath')) {
    /**
     * Get the appropriate location path based on visitor's IP
     * 
     * @return string|null
     */
    function getVisitorLocationPath()
    {
        $ipAddress = request()->ip();
        $visitor = \App\Models\VisitorUser::where('ip_address', $ipAddress)->first();
        
        if (!$visitor) {
            return null; // Return null instead of empty string
        }
        
       
        
        // If no area, check city
        if ($visitor->selected_city_id) {
            $city = \App\Models\City::find($visitor->selected_city_id);
            if ($city) {
                return '/' . $city->username;
            }
        }
        
        // If no city, check country
        if ($visitor->selected_country_id) {
            $country = \App\Models\Country::find($visitor->selected_country_id);
            if ($country) {
                return '/' . $country->username;
            }
        }
        
        // Default - return null instead of empty string
        return null;
    }
}