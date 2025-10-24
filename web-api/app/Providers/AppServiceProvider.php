<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\UserRepository;
use App\Services\Interfaces\AuthServiceInterface;
use App\Services\AuthService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        
        // Service bindings
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
