<?php

namespace App\Providers;

use App\Models\VtSearchGroup;
use App\Models\VtString;
use App\Models\VtTemplate;
use App\Observers\VtSearchGroupObserver;
use App\Observers\VtStringObserver;
use App\Observers\VtTemplateObserver;
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
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        VtTemplate::observe(VtTemplateObserver::class);
        VtString::observe(VtStringObserver::class);
        VtSearchGroup::observe(VtSearchGroupObserver::class);
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
