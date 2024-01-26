<?php

declare(strict_types=1);

namespace App\Models\Ticketing;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

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

    public function scopeStripeCheckoutId(Builder $query, string $sessionId): void
    {
        $query->where('stripe_checkout_id', $sessionId);
    }
}
