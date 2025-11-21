<?php

namespace App\Providers;

use App\Events\Maintenance\MaintenanceCancelled;
use App\Events\Maintenance\MaintenanceCompleted;
use App\Events\Maintenance\VerifyPaymentMaintenance;
use App\Listeners\Maintenance\NotifyAdminMaintenanceCancelledListener;
use App\Listeners\Maintenance\NotifyAdminMaintenanceCompletedListener;
use App\Listeners\Maintenance\NotifyAdminPaymentVerifiedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        MaintenanceCancelled::class => [
            NotifyAdminMaintenanceCancelledListener::class,
        ],
        MaintenanceCompleted::class => [
            NotifyAdminMaintenanceCompletedListener::class,
        ],
        VerifyPaymentMaintenance::class => [
            NotifyAdminPaymentVerifiedListener::class,
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

