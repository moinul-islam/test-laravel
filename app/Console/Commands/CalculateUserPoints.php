<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CalculateUserPoints extends Command
{
    protected $signature = 'points:calculate';
    protected $description = 'Calculate user points based on likes, comments, reviews, follows, and posts';

    public function handle()
    {
        $this->info('Point calculation started...');

        User::chunk(100, function ($users) {

            foreach ($users as $user) {
                $userId = $user->id;

                /* ========= POSTS ========= */
                $postPoints = DB::table('posts')
                    ->where('user_id', $userId)
                    ->count() * 10;

                /* ========= LIKES ========= */
                // Likes given (user_id) → +1
                $likeGiven = DB::table('likes')
                    ->where('user_id', $userId)
                    ->whereNull('comment_id') // ignore like comments
                    ->count();

                // Likes received (post owner) → +2
                $likeReceived = DB::table('likes')
                    ->whereNull('comment_id')
                    ->join('posts', 'posts.id', '=', 'likes.post_id')
                    ->where('posts.user_id', $userId)
                    ->count() * 2;

                /* ========= COMMENTS ========= */
                // Comments given (user_id) → +1
                $commentGiven = DB::table('comments')
                    ->where('user_id', $userId)
                    ->whereNull('comment_id') // ignore reply comments
                    ->count();

                // Comments received (post owner) → +2
                $commentReceived = DB::table('comments')
                    ->whereNull('comment_id')
                    ->join('posts', 'posts.id', '=', 'comments.post_id')
                    ->where('posts.user_id', $userId)
                    ->count() * 2;

                /* ========= FOLLOWS ========= */
                $followGiven = DB::table('follows')
                    ->where('follower_id', $userId)
                    ->count() * 2;

                $followReceived = DB::table('follows')
                    ->where('following_id', $userId)
                    ->count() * 1;

                /* ========= REVIEWS ========= */
                // Reviews given → +1
                $reviewGiven = DB::table('reviews')
                    ->where('user_id', $userId)
                    ->count();

                // Reviews received → +2 (post owner via product_id)
                $reviewReceived = DB::table('reviews')
                    ->join('posts', 'posts.id', '=', 'reviews.product_id')
                    ->where('posts.user_id', $userId)
                    ->count() * 2;

                /* ========= TOTAL POINT ========= */
                $totalPoints =
                    $postPoints +
                    $likeGiven + $likeReceived +
                    $commentGiven + $commentReceived +
                    $followGiven + $followReceived +
                    $reviewGiven + $reviewReceived;

                // Update user points
                DB::table('users')
                    ->where('id', $userId)
                    ->update(['users_points' => $totalPoints]);
            }

        });

        $this->info('Point calculation completed successfully!');
    }
}
