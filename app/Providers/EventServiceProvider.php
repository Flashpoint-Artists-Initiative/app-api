<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\EmailUpdated;
use App\Listeners\EmailVerifiedListener;
use App\Models\Ticketing\Cart;
use App\Models\Ticketing\CartItem;
use App\Models\Ticketing\Order;
use App\Models\Ticketing\ReservedTicket;
use App\Models\Ticketing\TicketTransfer;
use App\Observers\CartItemObserver;
use App\Observers\CartObserver;
use App\Observers\OrderObserver;
use App\Observers\ReservedTicketObserver;
use App\Observers\TicketTransferObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
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
        EmailUpdated::class => [
            SendEmailVerificationNotification::class,
        ],
        Verified::class => [
            EmailVerifiedListener::class,
        ]
    ];

    protected $observers = [
        Cart::class => CartObserver::class,
        CartItem::class => CartItemObserver::class,
        TicketTransfer::class => TicketTransferObserver::class,
        Order::class => OrderObserver::class,
        ReservedTicket::class => ReservedTicketObserver::class,
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
