<?php

declare(strict_types=1);

namespace App\Models\Ticketing;

use App\Models\Concerns\HasTicketType;
use App\Models\Concerns\TicketInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as ContractsAuditable;

/**
 * @property bool $is_purchased
 * @property bool $can_be_purchased
 */
class ReservedTicket extends Model implements ContractsAuditable, TicketInterface
{
    use Auditable, HasFactory, HasTicketType;

    protected $fillable = [
        'user_id',
        'ticket_type_id',
        'email',
        'expiration_date',
        'note',
        'name',
    ];

    protected $casts = [
        'expiration_date' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (ReservedTicket $reservedTicket) {
            // Check submitted email for a matching user, and if found assign to user_id
            if ($reservedTicket->isDirty('email')) {
                $user_id = User::where('email', $reservedTicket->email)->value('id');

                if ($user_id) {
                    $reservedTicket->user_id = $user_id;
                }
            }
        });

        static::saved(function (ReservedTicket $reservedTicket) {
            // If the reserved ticket type has a price of 0, automatically create a purchased ticket when possible
            if ($reservedTicket->user_id
                && $reservedTicket->ticketType->price === 0
                && $reservedTicket->can_be_purchased
            ) {
                $purchasedTicket = new PurchasedTicket();
                $purchasedTicket->ticket_type_id = $reservedTicket->ticket_type_id;
                $purchasedTicket->user_id = $reservedTicket->user_id;
                $purchasedTicket->reserved_ticket_id = $reservedTicket->id;
                $purchasedTicket->save();
            }
        });

        static::updating(function (ReservedTicket $reservedTicket) {
            if ($reservedTicket->is_purchased) {
                return false;
            }
        });

        static::deleting(function (ReservedTicket $reservedTicket) {
            if ($reservedTicket->is_purchased) {
                return false;
            }
        });
    }

    public function purchasedTicket(): HasOne
    {
        return $this->hasOne(PurchasedTicket::class);
    }

    /**
     * Query scope that matches all of the following:
     * - ticketType.active is true
     * - no purchased ticket
     * - Either: There's no expiration_date set on the reservedTicket AND the ticketType is still on sale
     * - Or: There is an expiration_date set on the reservedTicket AND it's not expired
     */
    public function scopeCanBePurchased(Builder $query): void
    {
        $query->whereRelation('ticketType', 'active', true);
        $query->whereDoesntHave('purchasedTicket');
        $query->where(function (Builder $query) {
            $query->where(function (Builder $query) {
                $query->whereRelation('ticketType', fn ($query) => $query->onSale());
                $query->where('expiration_date', null);
            });
            $query->orWhere(function (Builder $query) {
                $query->whereNot('expiration_date', null);
                $query->where('expiration_date', '>', now());
            });
        });
    }

    public function isPurchased(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                return $this->purchasedTicket()->exists();
            }
        );
    }

    public function canBePurchased(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                return $this->ticketType->active &&
                ! $this->is_purchased &&
                (
                    (! is_null($attributes['expiration_date']) && $attributes['expiration_date'] > now()) ||
                    (is_null($attributes['expiration_date']) && $this->ticketType->on_sale)
                );
            }
        );
    }
}
