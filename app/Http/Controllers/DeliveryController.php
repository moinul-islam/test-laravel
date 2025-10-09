<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;

class DeliveryController extends Controller
{
    public function deliveryIndex()
    {
        return view('frontend.delivery');
    }
   
   
    public function adminIndex(Request $request)
    {
        $search = $request->get('search');
        
        $users = User::query()
            ->when($search, function($query, $search) {
                return $query->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%")
                      ->orWhere('phone_number', 'LIKE', "%{$search}%")
                      ->orWhere('job_title', 'LIKE', "%{$search}%")
                      ->orWhere('username', 'LIKE', "%{$search}%")
                      ->orWhere('area', 'LIKE', "%{$search}%");
                });
            })
            ->with(['category', 'country', 'city'])
            ->latest() // Order by created_at DESC (newest first)
            ->paginate(100);
        
        return view('frontend.admin', compact('users'));
    }
}