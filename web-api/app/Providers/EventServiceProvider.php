<?php

namespace App\Providers;

use App\Events\LoanApproved;
use App\Events\LoanRejected;
use App\Events\LoanRequested;
use App\Events\InstallmentPaid;
use App\Events\PaymentFailed;
use App\Events\LoanFullyPaid;
use App\Events\UserRegistered;
use App\Listeners\LoanEventListener;
use App\Listeners\SendLoanRequestNotificationToAdmins;
use App\Listeners\SendUserRegistrationNotificationToAdmins;
use App\Listeners\SendPaymentFailedNotification;
use App\Listeners\SendLoanFullyPaidNotification;
use App\Listeners\SendInstallmentPaidNotificationToAdmins;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        
        // User registration event
        UserRegistered::class => [
            SendUserRegistrationNotificationToAdmins::class,
        ],
        
        // Loan events
        LoanRequested::class => [
            SendLoanRequestNotificationToAdmins::class,
        ],
        
        LoanApproved::class => [
            [LoanEventListener::class, 'handleLoanApproved'],
        ],
        
        LoanRejected::class => [
            [LoanEventListener::class, 'handleLoanRejected'],
        ],
        
        InstallmentPaid::class => [
            [LoanEventListener::class, 'handleInstallmentPaid'],
            SendInstallmentPaidNotificationToAdmins::class,
        ],
        
        PaymentFailed::class => [
            SendPaymentFailedNotification::class,
        ],
        
        LoanFullyPaid::class => [
            SendLoanFullyPaidNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
