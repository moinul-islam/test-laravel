<?php

namespace App\Http\Controllers;
use App\Models\Post;
use App\Models\User;
use App\Models\Country;
use App\Models\City;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }


    public function discount_wise_product(Request $request, $username)
    {
        $path = $username;
        $userIds = [];
    
        if ($path == 'international') {
            $userIds = User::where(function($query) {
                $query->where('phone_verified', 0)
                      ->orWhere('email_verified', 0);
            })->pluck('id')->toArray();
        } else {
            $country = Country::where('username', $path)->first();
            if ($country) {
                $userIds = User::where('country_id', $country->id)
                    ->where(function($query) {
                        $query->where('phone_verified', 0)
                              ->orWhere('email_verified', 0);
                    })
                    ->pluck('id')
                    ->toArray();
            } else {
                $city = City::where('username', $path)->first();
                if ($city) {
                    $userIds = User::where('city_id', $city->id)
                        ->where(function($query) {
                            $query->where('phone_verified', 0)
                                  ->orWhere('email_verified', 0);
                        })
                        ->pluck('id')
                        ->toArray();
                } else {
                    return redirect('/');
                }
            }
        }
    
        // ✅ এখানে discount_until ও check করুন
        $discount_wise_products = Post::whereNotNull('discount_price')
            ->where(function($query) {
                $query->whereNull('discount_until') // যদি কোন expiry না থাকে
                      ->orWhere('discount_until', '>', now()); // অথবা এখনো valid
            })
            ->whereIn('user_id', $userIds)
            ->with(['user', 'category'])
            ->orderBy('created_at', 'desc')
            ->paginate(12);
    
        if ($request->ajax()) {
            return response()->json([
                'posts' => view('frontend.products-partial', compact('discount_wise_products'))->render(),
                'hasMore' => $discount_wise_products->hasMorePages()
            ]);
        }
    
        return view('frontend.discount_wise_product', compact('discount_wise_products', 'path'));
    }
    



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
