<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class PointController extends Controller
{
    // add point
    public static function add(int $userId, int $point)
    {
        DB::table('users_points')->updateOrInsert(
            ['user_id' => $userId],
            ['points' => DB::raw("points + $point")]
        );
    }

    // subtract point (never negative)
    public static function subtract(int $userId, int $point)
    {
        DB::table('users_points')
            ->where('user_id', $userId)
            ->update([
                'points' => DB::raw("GREATEST(points - $point, 0)")
            ]);
    }

    // win–win add
    public static function winWin(
        int $actorId,
        int $receiverId,
        int $actorPoint = 1,
        int $receiverPoint = 2
    ) {
        if ($actorId == $receiverId) return;

        self::add($actorId, $actorPoint);
        self::add($receiverId, $receiverPoint);
    }

    // win–win reverse
    public static function winWinReverse(
        int $actorId,
        int $receiverId,
        int $actorPoint = 1,
        int $receiverPoint = 2
    ) {
        if ($actorId == $receiverId) return;

        self::subtract($actorId, $actorPoint);
        self::subtract($receiverId, $receiverPoint);
    }

    // get point
    public static function get(int $userId): int
    {
        return DB::table('users_points')
            ->where('user_id', $userId)
            ->value('points') ?? 0;
    }
}
