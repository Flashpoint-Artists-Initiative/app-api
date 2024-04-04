<?php

declare(strict_types=1);

namespace App\Models\Ticketing;

use App\Models\Event;
use App\Models\User;
use App\Notifications\TicketTransferNotification;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as ContractsAuditable;

/**
 * @property Event $event
 * @property int $ticketCount
 */
class TicketTransfer extends Model implements ContractsAuditable
{
    use Auditable, HasFactory;

    protected $fillable = [
        'user_id',
        'recipient_email',
        'completed',
    ];

    protected $with = [
        'purchasedTickets',
        'reservedTickets',
    ];

    public function purchasedTickets(): MorphToMany
    {
        return $this->morphedByMany(PurchasedTicket::class, 'ticket', 'ticket_transfer_items');
    }

    public function reservedTickets(): MorphToMany
    {
        return $this->morphedByMany(ReservedTicket::class, 'ticket', 'ticket_transfer_items');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_email', 'email');
    }

    public function ticketCount(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                if (! $this->relationLoaded('purchasedTickets') || ! $this->relationLoaded('reservedTickets')) {
                    $this->load(['purchasedTickets', 'reservedTickets']);
                }

                return $this->purchasedTickets->count() + $this->reservedTickets->count();
            }
        );
    }

    public function event(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                if (! $this->relationLoaded('purchasedTickets') || ! $this->relationLoaded('reservedTickets')) {
                    $this->load(['purchasedTickets', 'reservedTickets']);
                }

                if ($this->purchasedTickets->count() > 0) {
                    return $this->purchasedTickets->first()->event;
                } else {
                    return $this->reservedTickets->first()->event;
                }
            }
        );
    }

    /**
     * Finish the transfer and mark it as completed
     */
    public function complete(): static
    {
        if ($this->completed) {
            return $this;
        }

        $user = User::where('email', $this->recipient_email)->firstOrFail();

        /** @var Collection $tickets */
        $tickets = $this->purchasedTickets->concat($this->reservedTickets);

        $tickets->each(fn ($ticket) => $ticket->update(['user_id' => $user->id]));

        $this->updateQuietly(['completed' => true, 'recipient_user_id' => $user->id]);

        return $this;
    }

    public static function createTransfer(int $userId, string $email, ?array $purchasedTicketIds = [], ?array $reservedTicketIds = []): TicketTransfer
    {
        $transfer = TicketTransfer::create([
            'user_id' => $userId,
            'recipient_email' => $email,
        ]);

        PurchasedTicket::findMany($purchasedTicketIds)->each(function (PurchasedTicket $ticket) use ($transfer) {
            $transfer->purchasedTickets()->attach($ticket);
        });

        ReservedTicket::findMany($reservedTicketIds)->each(function (ReservedTicket $ticket) use ($transfer) {
            $transfer->reservedTickets()->attach($ticket);
        });

        $transfer->load(['reservedTickets', 'purchasedTickets']);

        $user = User::find($userId);
        $user->notify(new TicketTransferNotification($transfer));

        return $transfer;
    }
}
