<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SmsService
{
    protected $apiKey;
    protected $senderId;

    public function __construct()
    {
        $this->apiKey = env('BULK_SMS_API_KEY');
        $this->senderId = env('BULK_SMS_SENDER_ID');
    }

    public function sendSms($phone, $message)
    {
        $response = Http::get('http://bulksmsbd.net/api/smsapi', [
            'api_key' => $this->apiKey,
            'senderid' => $this->senderId,
            'number' => $phone,
            'message' => $message,
        ]);

        return $response->json();
    }
}
