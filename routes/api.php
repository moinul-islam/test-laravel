<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SmsController;

// ✅ Token Store API
Route::post('/store-fcm-token', [SmsController::class, 'registerToken']);

// ✅ Notification পাঠানোর API
Route::post('/send-test-notification', [NotificationController::class, 'sendNotificationUserWise']);

// ✅ GET method টেস্ট করার জন্য
Route::get('/test', function() {
    return response()->json(['message' => 'API route working!']);
});

// ✅ POST method কাজ করছে কি না তা চেক করার জন্য
Route::post('/check-test-post', function () {
    return response()->json(['status' => 'Success, POST method is working']);
});



Route::post('/update-seen', [NotificationController::class, 'updateSeen']);

Route::post('/notifications/mark-as-seen', [NotificationController::class, 'markAsSeen'])->name('notifications.markAsSeen');

Route::post('/final-check', function (Request $request) {
    return response()->json([
        'status' => '✅ POST method working!',
        'method' => $request->method(),
        'data' => $request->all()
    ]);
});

