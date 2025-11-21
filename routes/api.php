<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SmsController;

// এই একটা লাইন যোগ করলেই সব ঠিক হয়ে যাবে
Route::middleware('api')->group(function () {

    Route::post('/update-seen', [NotificationController::class, 'updateSeen']);
    Route::post('/notifications/mark-as-seen', [NotificationController::class, 'markAsSeen']);
    Route::post('/store-fcm-token', [SmsController::class, 'registerToken']);
    Route::post('/send-test-notification', [NotificationController::class, 'sendNotificationUserWise']);

    // টেস্ট করার জন্য এটা রাখো
    Route::post('/test-api', function (Request $request) {
        return response()->json([
            'message' => 'API middleware is now working!',
            'data' => $request->all()
        ]);
    });
});