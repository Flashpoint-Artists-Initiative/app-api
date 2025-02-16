<?php

declare(strict_types=1);

namespace App\Models\Ticketing;

use App\Models\Concerns\HasTicketType;
use App\Models\Concerns\TicketInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as ContractsAuditable;

/**
 * @property-read ReservedTicket $reservedTicket
 */
class PurchasedTicket extends Model implements ContractsAuditable, TicketInterface
{
    use Auditable, HasFactory, HasTicketType;

    protected $fillable = [
        'user_id',
        'ticket_type_id',
        'reserved_ticket_id',
        'order_id',
    ];

    /**
     * @return BelongsTo<ReservedTicket, $this>
     */
    public function reservedTicket(): BelongsTo
    {
        return $this->belongsTo(ReservedTicket::class);
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
