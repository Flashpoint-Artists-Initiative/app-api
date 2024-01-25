<?php

declare(strict_types=1);

namespace App\Models\Ticketing;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property bool $is_expired
 * @property Event $event
 * @property int $quantity
 */
class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
    ];

    protected $casts = [
        'expiration_date' => 'datetime',
    ];

    protected $with = [
        'items.ticketType',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                return $this->items->first()->ticketType->event;
            });
    }

    public function quantity(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                return $this->items->sum('quantity');
            });
    }

    public function isExpired(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                return $attributes['expiration_date'] < now();
            }
        );
    }

    public function expire(): void
    {
        // is_expired doesn't trigger correctly if it's been less than a second, so subtract 2 to be sure
        $this->expiration_date = now()->subSeconds(2);
        $this->saveQuietly();
    }

    public function scopeNotExpired(Builder $query): void
    {
        $query->where('expiration_date', '>', now());
    }

    public function scopeStripeCheckoutId(Builder $query, string $id): void
    {
        $query->where('stripe_checkout_id', $id);
    }

    public function setStripeCheckoutIdAndSave(string $id): void
    {
        $this->stripe_checkout_id = $id;
        $this->saveQuietly();
    }
}
