<?php
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

Route::post('/review/store',  [ReviewController::class, 'store'])->name('review.store');

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

// Products routes - এই order টা important
Route::get('/products', function () {
    return view('frontend.products');
});

// // Level 3: Sub-subcategory (সবার আগে থাকতে হবে)
// Route::get('/products/{category}/{subcategory}/{subsubcategory}', [PostController::class, 'showByCategory'])->name('products.category.subsub');

// // Level 2: Subcategory  
// Route::get('/products/{category}/{subcategory}', [PostController::class, 'showByCategory'])->name('products.category.sub');

// // Level 1: Main category (সবার শেষে থাকতে হবে)
// Route::get('/products/{category}', [PostController::class, 'showByCategory'])->name('products.category');




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


require __DIR__.'/auth.php';
// agula sob somoy niche thakbe
Route::get('/{username}/{slug}', [PostController::class, 'showByCategory'])->name('products.category');
Route::get('/{username}',  [LocationController::class, 'usernameWiseHome'])->name('profile.show');