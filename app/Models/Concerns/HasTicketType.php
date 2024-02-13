<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Event;
use App\Models\Ticketing\TicketTransfer;
use App\Models\Ticketing\TicketType;
use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @property bool $has_active_transfer
 */
trait HasTicketType
{
    public function transfers(): MorphToMany
    {
        return $this->morphToMany(TicketTransfer::class, 'ticket', 'ticket_transfer_items');
    }

    // public function latestTransfer(): MorphOne
    // {
    //     return $this->morphOne(TicketTransfer::class, 'ticket')->latestOfMany();
    // }

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): HasOneThrough
    {
        // Set the keys directly because we're effectively going backwards from the intended way
        return $this->hasOneThrough(
            Event::class,
            TicketType::class,
            'id', // Foreign Key for ticketType
            'id', // Foreign Key for Event
            'ticket_type_id', // Local key for purchasedTicket
            'event_id' //Local key for ticketType
        );
    }

    public function hasActiveTransfer(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                return $this->transfers()->where('completed', false)->count() > 0;
            }
        );
    }
}
