<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitorUser extends Model
{
    use HasFactory;

    protected $guarded = [];


    protected $casts = [
        'last_visit_at' => 'datetime',
    ];

    // Relationship with Country model for user-selected country
    public function selectedCountry()
    {
        return $this->belongsTo(Country::class, 'selected_country_id');
    }

    // Relationship with City model for user-selected city
    public function selectedCity()
    {
        return $this->belongsTo(City::class, 'selected_city_id');
    }


    // পছন্দ করা কান্ট্রি
    public function getCountryAttribute()
    {
        return $this->selectedCountry;
    }

    // পছন্দ করা সিটি
    public function getCityAttribute()
    {
        return $this->selectedCity;
    }

 

    
}