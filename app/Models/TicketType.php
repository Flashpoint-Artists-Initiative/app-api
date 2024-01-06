<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $remaining_ticket_count
 */
class TicketType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'sale_start_date',
        'sale_end_date',
        'quantity',
        'price',
        'active',
    ];

    protected $casts = [
        'sale_start_date' => 'date',
        'sale_end_date' => 'date',
        'active' => 'boolean',
    ];

    protected $withCount = [
        'purchasedTickets',
        'reservedTickets',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function purchasedTickets(): HasMany
    {
        return $this->hasMany(PurchasedTicket::class);
    }

    public function reservedTickets(): HasMany
    {
        return $this->hasMany(ReservedTicket::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('active', 1);
    }

    public function scopeOnSale(Builder $query): void
    {
        $query->where('sale_start_date', '<=', Carbon::now());
        $query->where('sale_end_date', '>=', Carbon::now());
    }

    public function scopeHasQuantity(Builder $query): void
    {
        $query->where('quantity', '>', 0);
    }

    public function scopeEvent(Builder $query, int $eventId): void
    {
        $query->where('event_id', $eventId);
    }

    public function remainingTicketCount(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                if ($attributes['quantity'] == 0) {
                    return 0;
                }

                return $attributes['quantity'] - $attributes['reserved_tickets_count'] - $attributes['purchased_tickets_count'];
            }
        );
    }
}
