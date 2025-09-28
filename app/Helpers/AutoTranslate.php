<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Session;

class AutoTranslate
{
    public static function text($text)
    {
        $locale = Session::get('locale', 'en');
        
        // আপাতত translation বন্ধ, শুধু text return করবে
        return $text;
    }
}