<?php

declare(strict_types=1);

namespace App\Models\Ticketing;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class PurchasedTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ticket_type_id',
        'reserved_ticket_id',
        'order_id',
    ];

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    public function reservedTicket(): BelongsTo
    {
        return $this->belongsTo(ReservedTicket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): HasOneThrough
    {
        // Set the keys directly because we're effectively going backwards from the indended way
        return $this->hasOneThrough(
            Event::class,
            TicketType::class,
            'id', // Foreign Key for ticketType
            'id', // Foreign Key for Event
            'ticket_type_id', // Local key for purchasedTicket
            'event_id' //Local key for ticketType
        );
    }

    public static function createFromCartItem(CartItem $item, ?int $userId = null, ?int $orderId = null): void
    {
        for ($i = 0; $i < $item->quantity; $i++) {
            static::create([
                'user_id' => $userId ?? $item->cart->user_id,
                'ticket_type_id' => $item->ticket_type_id,
                'reserved_ticket_id' => $item->reserved_ticket_id,
                'order_id' => $orderId,
            ]);
        }
    }
}