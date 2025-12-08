<?php

namespace App\Http\Controllers;
use App\Models\Post;
use App\Models\User;
use App\Models\Country;
use App\Models\City;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        //
    }

    public function userProductServices(Request $request, $username)
    {
        $path = $username;
        $userIds = [];
        $user = null;
        
        if ($path == 'international') {
            $userIds = User::pluck('id')->toArray();
            
            $user = new \stdClass();
            $user->id = 0;
            $user->username = 'international';
            $user->name = 'International';
            $user->profile_image = null;
            $user->service_hr = null;
        } else {
            $country = Country::where('username', $path)->first();
            if ($country) {
                $userIds = User::where('country_id', $country->id)
                    ->pluck('id')
                    ->toArray();
                
                $user = new \stdClass();
                $user->id = 0;
                $user->username = $country->username;
                $user->name = $country->name;
                $user->profile_image = null;
                $user->service_hr = null;
            } else {
                $city = City::where('username', $path)->first();
                if ($city) {
                    $userIds = User::where('city_id', $city->id)
                        ->pluck('id')
                        ->toArray();
                    
                    $user = new \stdClass();
                    $user->id = 0;
                    $user->username = $city->username;
                    $user->name = $city->name;
                    $user->profile_image = null;
                    $user->service_hr = null;
                } else {
                    $user = User::where('username', $path)->first();
                    if ($user) {
                        $userIds = [$user->id];
                    } else {
                        return redirect('/');
                    }
                }
            }
        }
        
        $products_services = Post::whereIn('user_id', $userIds)
            ->whereHas('category', function($q) {
                $q->whereIn('cat_type', ['product', 'service']);
            })
            ->with(['user', 'category'])
            ->orderBy('created_at', 'desc')
            ->paginate(12);
        
        // Profile card এর জন্য posts count
        if ($user && $user->id > 0) {
            $posts = Post::where('user_id', $user->id)
                        ->with(['user', 'category'])
                        ->latest()
                        ->paginate(10);
        } else {
            // Location based এর জন্য dummy posts object
            $posts = new \Illuminate\Pagination\LengthAwarePaginator(
                [],
                0,
                10,
                1
            );
        }
        
        if ($request->ajax()) {
            return response()->json([
                'posts' => view('frontend.products-partial', compact('products_services'))->render(),
                'hasMore' => $products_services->hasMorePages()
            ]);
        }
        
        return view('frontend.product-services', compact('products_services', 'path', 'user', 'posts'));
    }

    public function discount_wise_product(Request $request, $username)
    {
        $path = $username;
        $userIds = [];
    
        if ($path == 'international') {
            $userIds = User::pluck('id')->toArray();
        } else {
            $country = Country::where('username', $path)->first();
            if ($country) {
                $userIds = User::where('country_id', $country->id)
                    ->pluck('id')
                    ->toArray();
            } else {
                $city = City::where('username', $path)->first();
                if ($city) {
                    $userIds = User::where('city_id', $city->id)
                        ->pluck('id')
                        ->toArray();
                } else {
                    return redirect('/');
                }
            }
        }
    
        $discount_wise_products = Post::whereNotNull('discount_price')
            ->where(function($query) {
                $query->whereNull('discount_until')
                      ->orWhere('discount_until', '>', now());
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

    public function notice(Request $request, $username)
    {
        $path = $username;
        $userIds = [];
        
        if ($path == 'international') {
            $userIds = User::pluck('id')->toArray();
        } else {
            $country = Country::where('username', $path)->first();
            if ($country) {
                $userIds = User::where('country_id', $country->id)
                    ->pluck('id')
                    ->toArray();
            } else {
                $city = City::where('username', $path)->first();
                if ($city) {
                    $userIds = User::where('city_id', $city->id)
                        ->pluck('id')
                        ->toArray();
                } else {
                    return redirect('/');
                }
            }
        }
        
        $discount_wise_products = Post::whereIn('user_id', $userIds)
            ->whereHas('category', function($q) {
                $q->where('cat_type', 'post');
            })
            ->with(['user', 'category'])
            ->orderBy('created_at', 'desc')
            ->paginate(12);
        
        if ($request->ajax()) {
            return response()->json([
                'posts' => view('frontend.products-partial', compact('discount_wise_products'))->render(),
                'hasMore' => $discount_wise_products->hasMorePages()
            ]);
        }
        
        return view('frontend.notice_wise_product', compact('discount_wise_products', 'path'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}