<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFcmToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'fcm_token',
    ];

    /**
     * Token যেই user-এর, তার সাথে relation
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
