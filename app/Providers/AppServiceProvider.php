<?php

namespace App\Providers;

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
        \Illuminate\Support\Facades\Blade::directive('livewireScripts', function ($expression) {
            return "<?php echo str_replace('data-update-uri=\"/livewire/update\"', 'data-update-uri=\"/public/livewire/update\"', \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts({$expression})); ?>";
        });
    }
}
