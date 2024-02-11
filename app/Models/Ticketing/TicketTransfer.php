<?php

declare(strict_types=1);

namespace App\Models\Ticketing;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;

class TicketTransfer extends Model
{
    use HasFactory;

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
        // @phpstan-ignore-next-line
        $tickets = $this->purchasedTickets->concat($this->reservedTickets);

        $tickets->each(fn ($ticket) => $ticket->update(['user_id' => $user->id]));

        $this->update(['completed' => true]);

        return $this;
    }

    public static function createTransfer(int $userId, string $email, Collection $purchasedTicketIds, Collection $reservedTicketIds): TicketTransfer
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

        return $transfer;
    }
}
