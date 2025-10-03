<?php
namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Models\Country;
use App\Models\City;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Existing Blade directive
        Blade::directive('t', function ($expression) {
            return "<?php echo \App\Helpers\AutoTranslate::text($expression); ?>";
        });
        
        // Apply middleware to all routes
        \Illuminate\Support\Facades\Route::middleware([
            \App\Http\Middleware\SetVisitorLocationPath::class,
        ])->group(function () {
            require base_path('routes/web.php');
        });
        
        // Share dashboard variables with the master layout
        View::composer('frontend.master', function ($view) {
            // Get the current route parameters
            $routeParameters = request()->route()->parameters();
            $username = $routeParameters['username'] ?? null;
            
            if ($username) {
                // Initialize $name with a default value
                $name = null;
                
                // Check User
                $user = User::where('username', $username)
                        ->first();
                       
                // Check Country
                $country = Country::where('username', $username)->first();
               
                // Check City
                $city = City::where('username', $username)->first();
               
                // Default values
                $cities = [];
                $current_user = null;
                $currentCity = null;
                $table = null;
               
                // Always get locations
                $locations = Country::all();
               
                // Determine which entity is being viewed
                if ($user) {
                    $table = 'Country';
                    $current_user = $user;
                    $name = $user->name ?? null;
                } elseif ($country) {
                    $table = 'City';
                    $cities = City::where('country_id', $country->id)->get();
                    $name = $country->name ?? null;
                } elseif ($city) {
                    $table = 'City';
                    $country = Country::find($city->country_id);
                    $cities = City::where('country_id', $country->id)->get();
                    $currentCity = $city;
                    $name = $city->name ?? null;
                }
               
                // Share all variables with the view
                $view->with(compact(
                    'username',
                    'name',
                    'user',
                    'country',
                    'city',
                    'locations',
                    'cities',
                    'current_user',
                    'currentCity',
                    'table'
                ));
            }

            if (!session()->has('language')) {
                session()->put('language', 'bangla');
            }
        });
    }
}