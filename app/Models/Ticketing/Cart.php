<?php

declare(strict_types=1);

namespace App\Models\Ticketing;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
    ];

    protected $casts = [
        'expiration_date' => 'datetime',
    ];

    protected static function booted()
    {
        static::deleted(function (Cart $cart) {
            $cart->items()->delete();
        });

        // Don't allow a cart to be created for a user if one already exists
        static::creating(function (Cart $cart) {
            $cart->expiration_date = now()->addMinutes(config('app.cart_expiration_minutes'));

            if (Cart::where('user_id', $cart->user_id)->exists()) {
                return false;
            }
        });

        // Don't allow a cart to be updated
        // This is mostly so the expiration date doesn't accidentally get changed
        static::updating(fn () => false);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired()
    {
        return $this->expiration_date < now();
    }

    /**
     * Create CartItem models for the given ticket types and quantities
     */
    public function fillItems(array $input): void
    {
        foreach ($input as $row) {
            CartItem::create([
                'cart_id' => $this->id,
                'ticket_type_id' => $row['id'],
                'quantity' => $row['quantity'],
            ]);
        }
    }
}
