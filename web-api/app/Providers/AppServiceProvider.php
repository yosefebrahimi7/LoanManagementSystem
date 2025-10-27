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
use App\Services\Interfaces\UserServiceInterface;
use App\Services\UserService;
use App\Services\Interfaces\NotificationServiceInterface;
use App\Services\NotificationService;

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
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(NotificationServiceInterface::class, NotificationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Policies
        $this->registerPolicies();
        
        // Register Event Listeners
        $this->registerEventListeners();
    }

    /**
     * Register application policies.
     */
    protected function registerPolicies(): void
    {
        // User Policies
        \Illuminate\Support\Facades\Gate::policy(\App\Models\User::class, \App\Policies\UserPolicy::class);
        
        // Payment Policies
        \Illuminate\Support\Facades\Gate::policy(\App\Models\LoanPayment::class, \App\Policies\PaymentPolicy::class);
        
        // Loan Policies
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Loan::class, \App\Policies\LoanPolicy::class);
        
        // Notification Policies
        \Illuminate\Support\Facades\Gate::policy(
            \Illuminate\Notifications\DatabaseNotification::class,
            \App\Policies\NotificationPolicy::class
        );
    }

    /**
     * Register event listeners.
     */
    protected function registerEventListeners(): void
    {
        $events = app('events');

        // Register loan requested event
        $events->listen(
            \App\Events\LoanRequested::class,
            \App\Listeners\SendLoanRequestNotificationToAdmins::class
        );
    }
}
