<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PageContentEnum: string implements HasLabel
{
    use Concerns\EnumToArray;

    case AppDashboard = 'app-dashboard';
    case TicketPurchase = 'ticket-purchase';
    case Checkout = 'checkout';
    case CheckoutComplete = 'checkout-complete';

    public function getLabel(): string
    {
        return match ($this) {
            self::AppDashboard => 'Home Page',
            self::TicketPurchase => 'Ticket Purchase Page',
            self::Checkout => 'Checkout',
            self::CheckoutComplete => 'Checkout Complete',
        };
    }

    public function hasTitle(): bool
    {
        return match ($this) {
            self::AppDashboard => true,
            default => false,
        };
    }
}
