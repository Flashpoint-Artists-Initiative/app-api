<?php

declare(strict_types=1);

namespace App\Models\Ticketing;

use App\Models\Event;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as ContractsAuditable;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @property int $remaining_ticket_count
 * @property ?string $cart_items_quantity
 * @property bool $available
 * @property bool $on_sale
 * @property-read Event $event
 */
class TicketType extends Model implements ContractsAuditable
{
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'event_id',
        'name',
        'description',
        'sale_start_date',
        'sale_end_date',
        'quantity',
        'price',
        'active',
        'transferrable',
        'addon',
    ];

    protected $casts = [
        'sale_start_date' => 'datetime',
        'sale_end_date' => 'datetime',
        'active' => 'boolean',
        'transferable' => 'boolean',
        'addon' => 'boolean',
    ];

    protected $withCount = [
        'purchasedTickets',
        'unsoldReservedTickets',
    ];

    protected static function booted()
    {
        // Don't allow the price to be updated if any tickets have been sold
        static::updating(function (TicketType $type) {
            if (! $type->purchasedTickets()->count()) {
                return;
            }

            if ($type->isDirty('price')) {
                throw new HttpException(422, "Ticket type cannot update it's price after tickets have been sold.");
            }
        });
    }

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * @return HasMany<PurchasedTicket, $this>
     */
    public function purchasedTickets(): HasMany
    {
        return $this->hasMany(PurchasedTicket::class);
    }

    /**
     * @return HasMany<ReservedTicket, $this>
     */
    public function reservedTickets(): HasMany
    {
        return $this->hasMany(ReservedTicket::class);
    }

    /**
     * @return HasMany<ReservedTicket, $this>
     */
    public function unsoldReservedTickets(): HasMany
    {
        return $this->hasMany(ReservedTicket::class)
            ->whereDoesntHave('purchasedTicket');
    }

    /**
     * @return HasMany<CartItem, $this>
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * @return HasMany<CartItem, $this>
     */
    public function activeCartItems(): HasMany
    {
        return $this->hasMany(CartItem::class)
            ->whereHas('cart', fn ($query) => $query->notExpired()); /** @phpstan-ignore method.notFound */
    }

    /**
     * @param  Builder<TicketType>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('active', 1);
    }

    /**
     * @param  Builder<TicketType>  $query
     */
    public function scopeOnSale(Builder $query): void
    {
        $query->where('sale_start_date', '<=', now());
        $query->where('sale_end_date', '>=', now());
    }

    /**
     * @param  Builder<TicketType>  $query
     */
    public function scopeHasQuantity(Builder $query): void
    {
        $query->where('quantity', '>', 0);
    }

    /**
     * @param  Builder<TicketType>  $query
     */
    public function scopeEvent(Builder $query, int $eventId): void
    {
        $query->where('event_id', $eventId);
    }

    /**
     * @param  Builder<TicketType>  $query
     */
    public function scopeActiveEvent(Builder $query): void
    {
        $query->whereRelation('event', 'active', true);
    }

    /**
     * @param  Builder<TicketType>  $query
     */
    public function scopeAvailable(Builder $query): void
    {
        $query->active()->onSale()->hasQuantity();
    }

    /**
     * @param  Builder<TicketType>  $query
     */
    public function scopeAdmittance(Builder $query, ?int $eventId = null): void
    {
        $query->where('addon', false)->activeEvent();

        if ($eventId) {
            $query->event($eventId);
        }
    }

    /**
     * Overloaded method to eager load a sum aggregate
     *
     * @return Builder<Model>
     *
     * @phpstan-ignore method.childReturnType
     */
    public function newQueryWithoutScopes(): Builder
    {
        /** @var Builder<Model> $query */
        $query = parent::newQueryWithoutScopes();

        return $query->withSum('activeCartItems as cart_items_quantity', 'quantity');
    }

    /**
     * @return Attribute<int, void>
     */
    public function remainingTicketCount(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                if ($attributes['quantity'] == 0) {
                    return 0;
                }

                return $attributes['quantity']
                     - $attributes['purchased_tickets_count']
                     - $attributes['cart_items_quantity'];
            }
        );
    }

    /**
     * @return Attribute<bool, void>
     */
    public function available(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                return $this->remaining_ticket_count > 0
                && $attributes['active'] == true
                && now() < $attributes['sale_end_date']
                && now() > $attributes['sale_start_date'];
            }
        );
    }

    /**
     * @return Attribute<bool, void>
     */
    public function onSale(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                return now() < $attributes['sale_end_date']
                && now() > $attributes['sale_start_date'];
            }
        );
    }

    public function hasAvailable(int $quantity): bool
    {
        return $this->available && $this->remaining_ticket_count > $quantity;
    }
}
