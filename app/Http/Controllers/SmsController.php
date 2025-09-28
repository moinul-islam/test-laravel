<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SmsService;
use App\Models\User;
use App\Models\UserFcmToken;

class SmsController extends Controller
{
    public function index()
    {
        return view('sms.form');
    }

    public function send(Request $request, SmsService $smsService)
    {
        $request->validate([
            'phone'   => 'required|string',
            'message' => 'required|string|max:255',
        ]);

        $response = $smsService->sendSms($request->phone, $request->message);

        return back()->with('status', 'SMS Sent! Response: ' . json_encode($response));
    }

   public function registerToken(Request $request)
{
    $request->validate([
        'user_id' => 'required|string',
        'firebase_token' => 'required|string',
    ]);

    $user = User::where('username', $request->user_id)->first();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User not found'
        ], 404);
    }

    $existingToken = UserFcmToken::where('user_id', $user->id)
                     ->where('fcm_token', $request->firebase_token)
                     ->first();

    if (!$existingToken) {
        $userFcmToken = new UserFcmToken();
        $userFcmToken->user_id = $user->id;
        $userFcmToken->fcm_token = $request->firebase_token;
        $userFcmToken->save();
    }

    return response()->json([
        'success' => true,
        'message' => 'Token saved successfully'
    ]);
}

}
