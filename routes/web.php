<?php
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\VisitorLocationController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\AdminPostController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\AuthController;




// টিকেট উত্তলনের জন্য (controller ছাড়া, route এ logic থাকবে)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Moderator দ্বারা নতুন টিকেট বিক্রি (user না থাকলে create হবে, থাকলে update হবে এবং টিকেট সেভ হবে)
Route::post('/moderator/sell-ticket', function(Request $request) {
    // Only moderator can access
    if (!auth()->check() || auth()->user()->role !== 'moderator') {
        abort(403, 'Unauthorized');
    }
    $request->validate([
        'name' => 'required|string|max:255',
        'phone_number' => 'required|string|max:20',
        'ticket_quantity' => 'required|integer|min:1',
    ]);

    // 1. ইউজার খুঁজি, না থাকলে তৈরি করি
    $user = \App\Models\User::where('phone_number', $request->phone_number)->first();
    if (!$user) {
        // random username generate
        do {
            $randomUsername = 'user' . rand(100000, 999999);
        } while (\App\Models\User::where('username', $randomUsername)->exists());

        // শুধুমাত্র রেগুলার user role দিবো এবং country_id / city_id সেট করবো এবং random username ও থাকবো
        $user = \App\Models\User::create([
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            'username' => $randomUsername,
            'contributor' => auth()->id(), // moderator er id ta contributor hisabe save
            'country_id' => 19,
            'city_id' => 153868,
        ]);
    } else {
        // নাম আপডেট করা যেতে পারে এবং country_id, city_id প্রয়োজনে overwrite করতে চাইলে এখানে করো (ঐচ্ছিক)
        $user->name = $request->name;
        // আবারও overwrite করলে চাইলে uncomment:
        // $user->country_id = 19;
        // $user->city_id = 153868;
        $user->save();
    }

    // 2. mela_ticket এ টিকেট যুক্ত করি (একই user_id, ফোন নম্বর, মডারেটর ও কবে যেন একই দিনে একাধিক না হয়)
    DB::table('mela_ticket')->insert([
        'user_id' => $user->id,
        'user_ticket' => $request->ticket_quantity,
        'moderator_id' => auth()->id(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    return redirect()->back()->with('success', 'নতুন টিকেট সফলভাবে বিক্রি হয়েছে!');
})->name('moderator.sellTicket')->middleware('auth');


Route::post('/claim-mela-ticket', function(Request $request) {
    $request->validate([
        'user_id' => 'required|integer',
        'user_ticket' => 'required|integer|min:1',
    ]);
    // Ticket একবারই উত্তলন করা যাবে - একই user_id হলে insert হবে না
    $alreadyClaimed = DB::table('mela_ticket')
        ->where('user_id', $request->user_id)
        ->exists();
    if ($alreadyClaimed) {
        return redirect()->back()->with('error', 'আপনি ইতিমধ্যে টিকেট উত্তলন করেছেন!');
    }
    DB::table('mela_ticket')->insert([
        'user_id' => $request->user_id,
        'user_ticket' => $request->user_ticket,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    return redirect()->back()->with('success', 'আপনার টিকেট সফলভাবে উত্তলন হয়েছে!');
})->name('claim.mela.ticket')->middleware('auth');


// Moderator দ্বারা টিকেট accept (moderator_id save)
Route::post('/mela-ticket/accept', function(Request $request) {
    $request->validate([
        'ticket_id' => 'required|integer|exists:mela_ticket,id',
    ]);
    // Moderator only
    if (!auth()->check() || auth()->user()->role !== 'moderator') {
        abort(403, 'Unauthorized');
    }
    $ticket = DB::table('mela_ticket')->where('id', $request->ticket_id)->first();
    if (!$ticket) {
        return redirect()->back()->with('error', 'Ticket not found!');
    }
    if ($ticket->moderator_id) {
        return redirect()->back()->with('error', 'এই টিকেটটি ইতিমধ্যেই গ্রহণ করা হয়েছে!');
    }
    DB::table('mela_ticket')->where('id', $request->ticket_id)->update([
        'moderator_id' => auth()->id(),
        'updated_at' => now(),
    ]);
    return redirect()->back()->with('success', 'আপনি সফলভাবে টিকেট গ্রহণ করেছেন!');
})->name('mela_ticket.accept')->middleware('auth');






Route::get('/myauth', [AuthController::class, 'showAuthPage'])->name('myauth');
Route::post('/check-email', [AuthController::class, 'checkEmail']);
Route::post('/send-otp', [AuthController::class, 'sendOTP']);
Route::post('/myauth/verify-otp', [AuthController::class, 'verifyOTP']); // ✅ AuthController
Route::post('/complete-registration', [AuthController::class, 'completeRegistration']);
Route::post('/myauth/login', [AuthController::class, 'login'])->name('login');

// City route এ conflict আছে - এইটা AuthController এ যাবে
// Route::get('/get-cities/{country_id}', [AuthController::class, 'getCities']);


Route::middleware(['auth'])->group(function () {
    Route::post('/post/like', [LikeController::class, 'togglePostLike'])->name('post.like');
    Route::post('/comment/like', [LikeController::class, 'toggleCommentLike'])->name('comment.like');
});

Route::post('/review/store',  [ReviewController::class, 'store'])->name('review.store');
// Route::get('/{username}/notice',  [ProductController::class, 'notice'])->name('notice');
Route::get('/{username}/discount-wise-product',  [ProductController::class, 'discount_wise_product'])->name('discount_wise_product');

Route::post('/review/update/{id}',  [ReviewController::class, 'update'])->name('review.update')->middleware('auth');
Route::delete('/review/delete/{id}',  [ReviewController::class, 'destroy'])->name('review.delete')->middleware('auth');

// Language switch route - সবার উপরে রাখুন
Route::post('/set-locale', function (\Illuminate\Http\Request $request) {
    $locale = $request->input('locale');
    
    if (in_array($locale, ['en', 'bn'])) {
        \Session::put('locale', $locale);
        \App::setLocale($locale);
    }
    
    return redirect()->back();
})->name('set-locale');

Route::view('/privacy-policy', 'footerpage.privacy-policy');
Route::view('/about-us', 'footerpage.about-us');
Route::view('/terms-and-condition', 'footerpage.terms-and-condition');

    
Route::middleware(['auth', 'role:admin'])->get('/admin', [DeliveryController::class, 'adminIndex'])
    ->name('admin.page');
    Route::middleware(['auth', 'role:moderator'])->get('/moderator', [DeliveryController::class, 'moderatorIndex'])
    ->name('moderator.page');

// Post Approval Routes - role:admin middleware add korte hobe
Route::middleware(['auth', 'role:admin|moderator'])->group(function () {
    Route::get('/admin/posts/approval', [AdminPostController::class, 'postApproval'])->name('admin.posts.approval');
    Route::post('/admin/posts/{id}/status', [AdminPostController::class, 'updatePostStatus']);
    Route::put('/admin/posts/{id}', [AdminPostController::class, 'updatePost'])->name('admin.posts.update');
    Route::get('/admin/posts/{id}/view', [AdminPostController::class, 'viewPost'])->name('admin.posts.view');
});



// Category Management Routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::get('/subcategories', [CategoryController::class, 'getSubcategories'])->name('subcategories');
});


// Admin Routes
Route::middleware(['auth'])->group(function() {
    Route::get('/admin/create-post', [AdminPostController::class, 'showCreateForm'])->name('admin.post.create');
    Route::post('/admin/create-post', [AdminPostController::class, 'store'])->name('admin.post.store');
});

// web.php এ
Route::middleware(['auth', 'role:admin'])->group(function () {
    // Other admin routes...
    
    // AJAX route for getting user data - এইটা add করো
    Route::get('/admin/users/{userId}/data', [ProfileController::class, 'getUserData'])->name('admin.users.data');
    Route::put('/admin/users/{userId}', [ProfileController::class, 'adminUserUpdate'])->name('admin.users.update');
});

// web.php এ add করুন
// Route::middleware(['auth', 'role:delivery|admin'])->group(function () {
//     Route::get('/delivery', [OrderController::class, 'deliveryPage'])->name('delivery.page');
//     Route::post('/orders/{id}/accept-delivery', [OrderController::class, 'acceptForDelivery'])->name('orders.accept-delivery');
//     Route::post('/orders/{id}/complete-delivery', [OrderController::class, 'completeDelivery'])->name('orders.complete-delivery');
// });

Route::middleware('auth')->group(function () {
    Route::get('/delivery', [OrderController::class, 'deliveryPage'])->name('delivery.page');
    Route::post('/orders/{id}/accept-delivery', [OrderController::class, 'acceptForDelivery'])->name('orders.accept-delivery');
    Route::post('/orders/{id}/complete-delivery', [OrderController::class, 'completeDelivery'])->name('orders.complete-delivery');
});

// Vendor route (sell page এ button add করতে হবে shipped করার জন্য)
Route::post('/orders/{id}/mark-shipped', [OrderController::class, 'markAsShipped'])->name('orders.mark-shipped');

Route::get('/send', [LocationController::class, 'sendOtp']);
Route::post('/verify-otp', [LocationController::class, 'verifyOtp'])->name('verify.otp');
Route::get('/resend-otp', [LocationController::class, 'reSendOtp']);
Route::get('/get-cities/{country_id}', [LocationController::class, 'getCities']);

// Home route with pagination
Route::get('/', [PostController::class, 'index']);

// AJAX routes
Route::get('/posts/load-more', [PostController::class, 'index'])->name('posts.loadmore');
Route::get('/posts/load-more/{userId}', [LocationController::class, 'loadMoreUserPosts'])->name('posts.loadmore.user');
Route::post('/store', [PostController::class, 'store'])->name('post.store');



Route::get('/post/{slug}', [PostController::class, 'postDetails'])->name('post.details');

Route::get('/dashboard', function () {
    $user = Auth::user();
    return redirect('/'.$user->username);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/contribute/store', [ProfileController::class, 'contributeStore'])->name('contribute.store');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Buy and Sell routes
    Route::get('/buy', [OrderController::class, 'buyPage'])->name('buy');
    Route::get('/sell', [OrderController::class, 'sellPage'])->name('sell');
});

Route::post('/comment/store', [CommentController::class, 'commentStore'])->name('comment.store');
Route::post('/comment/delete', [CommentController::class, 'delete'])->name('comment.delete')->middleware('auth');

// Products routes - এই order টা important
Route::get('/products', function () {
    return view('frontend.products');
});



// Add this route for post deletion
Route::delete('/posts/{id}', [PostController::class, 'destroy'])->name('posts.destroy')->middleware('auth');

// Order routes
Route::middleware('auth')->group(function () {
    Route::post('/orders/store', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    
    // Add this new cancel order route
    Route::patch('/orders/{id}/cancel', [OrderController::class, 'cancelOrder'])->name('orders.cancel');
});

Route::middleware('auth')->group(function () {
    // Follow a user
    Route::post('/follow/{user}', [LocationController::class, 'follow'])->name('user.follow');

    // Unfollow a user
    Route::post('/unfollow/{user}', [LocationController::class, 'unfollow'])->name('user.unfollow');
});

// Add this route in your web.php
Route::get('/check-profile-completeness', [ProfileController::class, 'checkCompleteness'])
    ->middleware('auth');



Route::get('/sms', [SmsController::class, 'index'])->name('sms.form');
Route::post('/sms', [SmsController::class, 'send'])->name('sms.send');


Route::get('/login-success/{identifier}', function ($identifier) {
    return redirect('/'.$identifier);
})->name('login.success');

// Logout success - 300ms পর home এ redirect
Route::get('/logout-success/{identifier}', function ($identifier) {
     return redirect('/');
})->name('logout.success');

Route::get('/search', [SearchController::class, 'search'])->name('search');

Route::middleware('auth')->group(function () {
    Route::get('/post/{id}/edit', [PostController::class, 'edit'])->name('post.edit');
    Route::put('/post/{id}/update', [PostController::class, 'update'])->name('post.update');
});


Route::get('/get-cities/{countryId}', [LocationController::class, 'getCities']);
Route::post('/save-location', [VisitorLocationController::class, 'saveLocation'])->name('save.location');
Route::get('/contribute', [ProfileController::class, 'ContributeCreate'])->name('contribute');
Route::post('/account-check', [ProfileController::class, 'accountCheck'])->name('account.check');


// Notification routes
Route::middleware('auth')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-as-seen', [NotificationController::class, 'markAsSeen'])->name('notifications.markAsSeen');
    Route::post('/notifications/mark-all-seen', [NotificationController::class, 'markAllAsSeen'])->name('notifications.markAllSeen');
    Route::get('/notifications/unseen-count', [NotificationController::class, 'getUnseenCount'])->name('notifications.unseenCount');
    Route::delete('/notifications/delete', [NotificationController::class, 'destroy'])->name('notifications.delete');
});

require __DIR__.'/auth.php';
Route::get('/{username}/products-services', [ProductController::class, 'userProductServices'])->name('user.products.services');
// agula sob somoy niche thakbe
// Products category route - check first if it's a product/service category
Route::get('/{username}/{slug}', [PostController::class, 'showByCategory'])->name('products.category');
// Home page with category filter - will handle post categories
Route::get('/{username}/{category}', [LocationController::class, 'usernameWiseHome'])->name('home.category');
Route::get('/{username}',  [LocationController::class, 'usernameWiseHome'])->name('profile.show');