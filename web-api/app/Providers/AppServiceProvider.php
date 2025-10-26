<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\UserRepository;
use App\Repositories\Interfaces\LoanRepositoryInterface;
use App\Repositories\LoanRepository;
use App\Repositories\Interfaces\WalletRepositoryInterface;
use App\Repositories\WalletRepository;
use App\Repositories\Interfaces\PaymentRepositoryInterface;
use App\Repositories\PaymentRepository;
use App\Services\Interfaces\AuthServiceInterface;
use App\Services\AuthService;
use App\Services\Interfaces\PaymentServiceInterface;
use App\Services\PaymentService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(LoanRepositoryInterface::class, LoanRepository::class);
        $this->app->bind(WalletRepositoryInterface::class, WalletRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);
        
        // Service bindings
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(PaymentServiceInterface::class, PaymentService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Policies
        $this->registerPolicies();
    }

    /**
     * Register application policies.
     */
    protected function registerPolicies(): void
    {
        // Payment Policies
        \Illuminate\Support\Facades\Gate::policy(\App\Models\LoanPayment::class, \App\Policies\PaymentPolicy::class);
        
        // Loan Policies
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Loan::class, \App\Policies\LoanPolicy::class);
    }
}
