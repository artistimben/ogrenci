<?php

namespace App\Providers;

use App\Overrides\HandleRequestsWithSubdir;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Livewire\Mechanisms\HandleRequests\HandleRequests;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * Livewire'ın HandleRequests sınıfını bizim override'ımızla
     * değiştiriyoruz. Bu sayede getUpdateUri() /public/livewire/update
     * döndürür.
     */
    public function register(): void
    {
        // Livewire'ın HandleRequests sınıfını override et
        $this->app->singleton(HandleRequests::class, HandleRequestsWithSubdir::class);
    }

    /**
     * Bootstrap any application services.
     *
     * Sunucu /public alt dizininde çalıştığı için Livewire update
     * POST endpoint'ini /public/livewire/update olarak kaydet.
     */
    public function boot(): void
    {
        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('/public/livewire/update', $handle)->middleware('web');
        });
    }
}

