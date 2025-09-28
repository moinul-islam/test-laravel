<?php

namespace App\Providers;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

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
        // Existing code রাখুন, শুধু এটা add করুন
        Blade::directive('t', function ($expression) {
            return "<?php echo \App\Helpers\AutoTranslate::text($expression); ?>";
        });
    }
}
