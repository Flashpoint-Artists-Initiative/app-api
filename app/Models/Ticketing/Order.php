<?php

declare(strict_types=1);

namespace App\Models\Ticketing;

use App\Models\Event;
use App\Models\User;
use App\Observers\OrderObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as ContractsAuditable;

#[ObservedBy(OrderObserver::class)]
class Order extends Model implements ContractsAuditable
{
    use Auditable, HasFactory;

    protected $fillable = [
        'user_email',
        'user_id',
        'event_id',
        'cart_id',
        'amount_subtotal',
        'amount_total',
        'amount_tax',
        'quantity',
        'stripe_checkout_id',
        'ticket_data',
    ];

    protected $casts = [
        'ticket_data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function ticketTypes(): Collection
    {
        return once(function () {
            $ids = array_column($this->ticket_data, 'ticket_type_id');

            return TicketType::whereIn('id', $ids)->get();
        });
    }

    public function scopeStripeCheckoutId(Builder $query, string $sessionId): void
    {
        $query->where('stripe_checkout_id', $sessionId);
    }
}
