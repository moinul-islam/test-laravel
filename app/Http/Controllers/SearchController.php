<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;

class SearchController extends Controller
{
    public function search(Request $request)
    {
    
        $query = $request->input('q', '');
        
        if (strlen($query) < 2) {
            return redirect()->back()->with('error', 'অন্তত ২টি অক্ষর লিখুন');
        }

        // Search in users
        $users = User::where('username', 'like', '%' . $query . '%')
            ->orWhere('name', 'like', '%' . $query . '%')
            ->limit(20)
            ->get();

        // Search in posts
        $posts = Post::where('title', 'like', '%' . $query . '%')
            ->orWhere('description', 'like', '%' . $query . '%')
            ->with('user', 'category')
            ->latest()
            ->limit(20)
            ->get();

        return view('search-results', compact('users', 'posts', 'query'));
    }
}