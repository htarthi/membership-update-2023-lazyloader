<?php

namespace App\Providers;

use App\Events\CheckBillingAttemptFailure;
use App\Events\CheckBillingAttemptSuccess;
use App\Events\CheckCustomerPaymentMethodUpdate;
use App\Events\CheckProductUpdate;
use App\Events\CheckSubscriptionContract;
use App\Events\HandleWebhooks;
use App\Listeners\BillingAttemptFailure;
use App\Listeners\BillingAttemptSuccess;
use App\Listeners\CustomerPaymentMethodUpdate;
use App\Listeners\ProcessedWebhooks;
use App\Listeners\ProductUpdate;
use App\Listeners\SubscriptionContract;
use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

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
        CheckSubscriptionContract::class => [
            SubscriptionContract::class
        ],
        CheckBillingAttemptSuccess::class => [
            BillingAttemptSuccess::class
        ],
        CheckBillingAttemptFailure::class => [
            BillingAttemptFailure::class
        ],
        CheckProductUpdate::class => [
            ProductUpdate::class
        ],
        CheckCustomerPaymentMethodUpdate::class => [
            CustomerPaymentMethodUpdate::class
        ],
        HandleWebhooks::class => [
            ProcessedWebhooks::class
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
        User::observe(UserObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
